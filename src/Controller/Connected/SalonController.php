<?php

namespace App\Controller\Connected;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
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
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @param mixed $id
     * @return JsonResponse|RedirectResponse|Response
     * @throws NonUniqueResultException
     */
    public function salonAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet, string $id = 'Public'): RedirectResponse|JsonResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $server = $usePlanet->getSector()->getGalaxy()->getServer();
        $commander = $user->getCommander($server);
        $now = new DateTime();
        $connected = new DateTime();
        $connected->sub(new DateInterval('PT' . 1800 . 'S'));

        if($commander->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if($commander->getAlliance()) {
            $tag = $commander->getAlliance()->getTag();
        } else {
            $tag = 'AKOUNAMATATA';
        }

        $salon = $doctrine->getRepository(Salon::class)
            ->createQueryBuilder('s')
            ->where('s.name = :public')
            ->orWhere('s.id = :id')
            ->AndWhere('s.server = :server')
            ->setParameters(['public' => $id, 'id' => $id, 'server' => $server])
            ->getQuery()
            ->getOneOrNullResult();

        if ($id != 1) {
            foreach($commander->getViews() as $view) {
                if($view->getSalon() == $salon) {
                    $em->remove($view);
                }
            }
        } else {
            $commander->setNewMessage(true);
        }

        $salons = $doctrine->getRepository(Salon::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.allys', 'a')
            ->leftJoin('s.commanders', 'c')
            ->where('a.tag = :tag')
            ->orWhere('s.name = :name and s.server = :server')
            ->orWhere('c.username = :commander')
            ->setParameters(['tag' => $tag, 'server' => $server, 'name' => 'Public', 'commander' => $commander->getUsername()])
            ->getQuery()
            ->getResult();

        $userCos = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->where('c.activityAt > :date')
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
            return $this->redirectToRoute('salon', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_message = $this->createForm(SalonType::class);
        $form_message->handleRequest($request);

        if($request->isXmlHttpRequest()) {
            $newMessages = $doctrine->getRepository(S_Content::class)
                ->createQueryBuilder('sc')
                ->orderBy('sc.sendAt', 'ASC')
                ->where('sc.salon = :attachSalon')
                ->andWhere('sc.commander != :commander')
                ->andWhere('sc.sendAt > :date')
                ->setParameters(['attachSalon' => $salon, 'commander' => $commander, 'date' => $commander->getSalonAt()])
                ->setMaxResults('1')
                ->getQuery()
                ->getResult();

            $response = new JsonResponse();
            if($newMessages || $commander->getSalonAt() == null) {
                $response->setData(
                    [
                        'has_error' => false,
                    ]
                );
                $commander->setSalonAt($now);

                $em->flush();
            } else {
                $response->setData(
                    [
                        'has_error' => true,
                    ]
                );
            }

            return $response;
        }

        if ($form_message->isSubmitted() && $form_message->isValid() && ($commander->getSalonBan() > $now || $commander->getSalonBan() == null)) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if (str_starts_with($form_message->get('content')->getData(), 'https://')) {
                $content = '<span><a target="_blank" href="' . $form_message->get('content')->getData() . '">' . $form_message->get('content')->getData() . '</a></span>';
            } else {
                $content = nl2br($form_message->get('content')->getData());
            }
            $message = new S_Content($commander, $content, $salon);

            if(count($salon->getContents()) > 50) {
                $removeMessage = $doctrine->getRepository(S_Content::class)
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
                $userViews = $doctrine->getRepository(Commander::class)
                    ->createQueryBuilder('c')
                    ->where('c.id != :commander')
                    ->andWhere('c.bot = false')
                    ->setParameters(['commander' => $commander->getId()])
                    ->getQuery()
                    ->getResult();

                foreach($userViews as $userView) {
                    $userView->setSalonAt(null);
                }
            } else {
                foreach($salon->getAlliances() as $ally) {
                    foreach($ally->getCommanders() as $tmpuser) {
                        if ($tmpuser != $commander) {
                            $view = new View($tmpuser, $salon);
                            $em->persist($view);
                        }
                    }
                }
                foreach($salon->getCommanders() as $tmpuser) {
                    if ($tmpuser != $commander) {
                        $view = new View($tmpuser, $salon);
                        $em->persist($view);
                    }
                }
            }
            $em->persist($message);
            $quest = $commander->checkQuests('salon_message');
            if($quest) {
                $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + $quest->getGain());
                $commander->removeQuest($quest);
            }

            $form_message = null;
            $form_message = $this->createForm(SalonType::class);
        }
        $commander->setSalonAt($now);

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
     * @Route("/rejoindre-salon/{tag}/{usePlanet}", name="ally_join_salon", requirements={"tag"="\w+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param $tag
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws NonUniqueResultException
     */
    public function addSalonAction(ManagerRegistry $doctrine, $tag, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $salon = $doctrine->getRepository(Salon::class)
            ->createQueryBuilder('s')
            ->where('s.name = :name')
            ->setParameter('name', "Ambassade - " . $tag)
            ->getQuery()
            ->getOneOrNullResult();

        $salon->addCommander($commander);

        $em->flush();

        return $this->redirectToRoute('salon', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/quitter-salon/{salon}/{usePlanet}", name="ally_leave_salon", requirements={"salon"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Salon $salon
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function leaveSalonAction(ManagerRegistry $doctrine, Salon $salon, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $salon->removeCommander($commander);

        $em->flush();

        return $this->redirectToRoute('salon', ['usePlanet' => $usePlanet->getId()]);
    }
}