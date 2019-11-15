<?php

namespace App\Controller\Connected;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\SalonType;
use App\Entity\Planet;
use App\Entity\S_Content;
use App\Entity\Salon;
use App\Entity\View;
use DateTime;
use DateTimeZone;
use DateInterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class SalonController extends AbstractController
{
    /**
     * @Route("/salon/{usePlanet}", name="salon", requirements={"usePlanet"="\d+"})
     * @Route("/salon/{usePlanet}/{id}", name="salon_id", requirements={"usePlanet"="\d+", "id"="\d+"})
     */
    public function salonAction(Request $request, Planet $usePlanet, $id = 1)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $connected = new DateTime();
        $connected->setTimezone(new DateTimeZone('Europe/Paris'));
        $connected->sub(new DateInterval('PT' . 1800 . 'S'));

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($user->getAlly()) {
            $sigle = $user->getAlly()->getSigle();
        } else {
            $sigle = 'AKOUNAMATATA';
        }

        $salon = $em->getRepository('App:Salon')->find(['id' => $id]);
        if ($id != 1) {
            foreach($user->getViews() as $view) {
                if($view->getSalon() == $salon) {
                    $em->remove($view);
                }
            }
        } else {
            $user->setViewMessage(true);
        }

        $salons = $em->getRepository('App:Salon')
            ->createQueryBuilder('s')
            ->leftJoin('s.allys', 'a')
            ->leftJoin('s.users', 'u')
            ->where('a.sigle = :sigle')
            ->orWhere('s.name = :name')
            ->orWhere('u.username = :user')
            ->setParameters(['sigle' => $sigle, 'name' => 'Public', 'user' => $user->getUserName()])
            ->getQuery()
            ->getResult();

        $userCos = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->where('u.lastActivity > :date')
            ->setParameters(['date' => $connected])
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();

        $ok = 0;
        foreach($salons as $one) {
            if($one == $salon) {
                $ok = 1;
            }
        }
        if($ok == 0) {
            return $this->redirectToRoute('salon', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_message = $this->createForm(SalonType::class);
        $form_message->handleRequest($request);

        if($request->isXmlHttpRequest()) {
            $newMessages = $em->getRepository('App:S_Content')
                ->createQueryBuilder('sc')
                ->orderBy('sc.sendAt', 'ASC')
                ->where('sc.salon = :attachSalon')
                ->andWhere('sc.user != :user')
                ->andWhere('sc.sendAt > :date')
                ->setParameters(['attachSalon' => $salon, 'user' => $user, 'date' => $user->getSalonAt()])
                ->setMaxResults('1')
                ->getQuery()
                ->getResult();

            if($newMessages || $user->getSalonAt() == null) {
                $response = new JsonResponse();
                $response->setData(
                    [
                        'has_error' => false,
                    ]
                );
                $user->setSalonAt($now);

                $em->flush();
                return $response;
            } else {
                $response = new JsonResponse();
                $response->setData(
                    [
                        'has_error' => true,
                    ]
                );
                return $response;
            }
        }

        if ($form_message->isSubmitted() && $form_message->isValid() && ($user->getSalonBan() > $now || $user->getSalonBan() == null)) {
            $message = new S_Content();
            $message->setSalon($salon);
            if (substr($form_message->get('content')->getData(), 0, 8) == 'https://' || substr($form_message->get('content')->getData(), 0, 7) == 'http://') {
                $message->setMessage('<span><a target="_blank" href="' . $form_message->get('content')->getData() . '">' . $form_message->get('content')->getData() . '</a></span>');
            } else {
                $message->setMessage(nl2br($form_message->get('content')->getData()));
            }
            $message->setSendAt($now);
            $message->setUser($user);

            if(count($salon->getContents()) > 50) {
                $removeMessage = $em->getRepository('App:S_Content')
                    ->createQueryBuilder('sc')
                    ->orderBy('sc.sendAt', 'ASC')
                    ->where('sc.salon = :attachSalon')
                    ->setParameters(['attachSalon' => $salon])
                    ->setMaxResults('10')
                    ->getQuery()
                    ->getResult();
                foreach($removeMessage as $oneMessage) {
                    $em->remove($oneMessage);
                }
            }
            if($salon->getId() == 1) {
                $userViews = $em->getRepository('App:User')
                    ->createQueryBuilder('u')
                    ->where('u.id != :user')
                    ->andWhere('u.bot = false')
                    ->setParameters(['user' => $user->getId()])
                    ->getQuery()
                    ->getResult();
                foreach($userViews as $userView) {
                    $userView->setSalonAt(null);
                }
            } else {
                foreach($salon->getAllys() as $ally) {
                    foreach($ally->getUsers() as $tmpuser) {
                        if ($tmpuser != $user) {
                            $view = new View();
                            $view->setUser($tmpuser);
                            $view->setSalon($salon);
                            $em->persist($view);
                        }
                    }
                }
                foreach($salon->getUsers() as $tmpuser) {
                    if ($tmpuser != $user) {
                        $view = new View();
                        $view->setUser($tmpuser);
                        $view->setSalon($salon);
                        $em->persist($view);
                    }
                }
            }
            $em->persist($message);
            $quest = $user->checkQuests('salon_message');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }

            $form_message = null;
            $form_message = $this->createForm(SalonType::class);
        }
        $user->setSalonAt($now);

        if(($user->getTutorial() == 24)) {
            $user->setTutorial(50);
        }
        $em->flush();

        return $this->render('connected/salon.html.twig', [
            'usePlanet' => $usePlanet,
            'salons' => $salons,
            'userCos' => $userCos,
            'connected' => $connected,
            'salon' => $salon,
            'form_message' => $form_message->createView(),
        ]);
    }

    /**
     * @Route("/rejoindre-salon/{sigle}/{usePlanet}", name="ally_join_salon", requirements={"sigle"="\w+", "usePlanet"="\d+"})
     */
    public function addSalonAction($sigle, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $salon = $em->getRepository('App:Salon')
            ->createQueryBuilder('s')
            ->where('s.name = :name')
            ->setParameter('name', "Ambassade - " . $sigle)
            ->getQuery()
            ->getOneOrNullResult();

        $salon->addUser($user);

        $em->flush();

        return $this->redirectToRoute('salon', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/quitter-salon/{salon}/{usePlanet}", name="ally_leave_salon", requirements={"salon"="\d+", "usePlanet"="\d+"})
     */
    public function leaveSalonAction(Salon $salon, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $salon->removeUser($user);

        $em->flush();

        return $this->redirectToRoute('salon', ['usePlanet' => $usePlanet->getId()]);
    }
}