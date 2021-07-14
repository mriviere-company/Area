<?php

namespace App\Controller\Connected;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * @param Request $request
     * @param Planet $usePlanet
     * @param mixed $id
     * @return JsonResponse|RedirectResponse|Response
     * @throws Exception
     */
    public function salonAction(Request $request, Planet $usePlanet, $id = 'Public')
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $server = $usePlanet->getSector()->getGalaxy()->getServer();
        $character = $user->getCharacter($server);
        $now = new DateTime();
        $connected = new DateTime();
        $connected->sub(new DateInterval('PT' . 1800 . 'S'));

        if($character->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if($character->getAlly()) {
            $sigle = $character->getAlly()->getSigle();
        } else {
            $sigle = 'AKOUNAMATATA';
        }

        $salon = $em->getRepository('App:Salon')
            ->createQueryBuilder('s')
            ->where('s.name = :public')
            ->orWhere('s.id = :id')
            ->AndWhere('s.server = :server')
            ->setParameters(['public' => $id, 'id' => $id, 'server' => $server])
            ->getQuery()
            ->getOneOrNullResult();

        if ($id != 1) {
            foreach($character->getViews() as $view) {
                if($view->getSalon() == $salon) {
                    $em->remove($view);
                }
            }
        } else {
            $character->setViewMessage(true);
        }

        $salons = $em->getRepository('App:Salon')
            ->createQueryBuilder('s')
            ->leftJoin('s.allys', 'a')
            ->leftJoin('s.characters', 'c')
            ->where('a.sigle = :sigle')
            ->orWhere('s.name = :name and s.server = :server')
            ->orWhere('c.username = :character')
            ->setParameters(['sigle' => $sigle, 'server' => $server, 'name' => 'Public', 'character' => $character->getUsername()])
            ->getQuery()
            ->getResult();

        $userCos = $em->getRepository('App:Character')
            ->createQueryBuilder('c')
            ->where('c.lastActivity > :date')
            ->setParameters(['date' => $connected])
            ->orderBy('c.username', 'ASC')
            ->getQuery()
            ->getResult();

        $ok = 0;
        foreach($salons as $one) {
            if($one == $salon) {
                $ok = 1;
            }
        }
        if($ok == 0) {
            var_dump($salon->getId());
            var_dump(count($salons)); exit;
            return $this->redirectToRoute('salon', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_message = $this->createForm(SalonType::class);
        $form_message->handleRequest($request);

        if($request->isXmlHttpRequest()) {
            $newMessages = $em->getRepository('App:S_Content')
                ->createQueryBuilder('sc')
                ->orderBy('sc.sendAt', 'ASC')
                ->where('sc.salon = :attachSalon')
                ->andWhere('sc.character != :character')
                ->andWhere('sc.sendAt > :date')
                ->setParameters(['attachSalon' => $salon, 'character' => $character, 'date' => $character->getSalonAt()])
                ->setMaxResults('1')
                ->getQuery()
                ->getResult();

            if($newMessages || $character->getSalonAt() == null) {
                $response = new JsonResponse();
                $response->setData(
                    [
                        'has_error' => false,
                    ]
                );
                $character->setSalonAt($now);

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

        if ($form_message->isSubmitted() && $form_message->isValid() && ($character->getSalonBan() > $now || $character->getSalonBan() == null)) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if (substr($form_message->get('content')->getData(), 0, 8) == 'https://' || substr($form_message->get('content')->getData(), 0, 7) == 'http://') {
                $content = '<span><a target="_blank" href="' . $form_message->get('content')->getData() . '">' . $form_message->get('content')->getData() . '</a></span>';
            } else {
                $content = nl2br($form_message->get('content')->getData());
            }
            $message = new S_Content($character, $content, $salon);

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
                $userViews = $em->getRepository('App:Character')
                    ->createQueryBuilder('c')
                    ->where('c.id != :character')
                    ->andWhere('c.bot = false')
                    ->setParameters(['character' => $character->getId()])
                    ->getQuery()
                    ->getResult();

                foreach($userViews as $userView) {
                    $userView->setSalonAt(null);
                }
            } else {
                foreach($salon->getAllys() as $ally) {
                    foreach($ally->getCharacters() as $tmpuser) {
                        if ($tmpuser != $character) {
                            $view = new View($tmpuser, $salon);
                            $em->persist($view);
                        }
                    }
                }
                foreach($salon->getCharacters() as $tmpuser) {
                    if ($tmpuser != $character) {
                        $view = new View($tmpuser, $salon);
                        $em->persist($view);
                    }
                }
            }
            $em->persist($message);
            $quest = $character->checkQuests('salon_message');
            if($quest) {
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                $character->removeQuest($quest);
            }

            $form_message = null;
            $form_message = $this->createForm(SalonType::class);
        }
        $character->setSalonAt($now);

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
     * @param $sigle
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function addSalonAction($sigle, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $salon = $em->getRepository('App:Salon')
            ->createQueryBuilder('s')
            ->where('s.name = :name')
            ->setParameter('name', "Ambassade - " . $sigle)
            ->getQuery()
            ->getOneOrNullResult();

        $salon->addCharacter($character);

        $em->flush();

        return $this->redirectToRoute('salon', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/quitter-salon/{salon}/{usePlanet}", name="ally_leave_salon", requirements={"salon"="\d+", "usePlanet"="\d+"})
     * @param Salon $salon
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function leaveSalonAction(Salon $salon, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $salon->removeCharacter($character);

        $em->flush();

        return $this->redirectToRoute('salon', ['usePlanet' => $usePlanet->getId()]);
    }
}