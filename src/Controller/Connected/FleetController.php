<?php

namespace App\Controller\Connected;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\SpatialEditFleetType;
use App\Form\Front\FleetRenameType;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\Front\FleetRessourcesType;
use App\Form\Front\FleetSendType;
use App\Form\Front\FleetAttackType;
use App\Form\Front\FleetListType;
use App\Form\Front\FleetSplitType;
use App\Form\Front\FleetEditShipType;
use App\Entity\Fleet;
use App\Entity\Report;
use App\Entity\Planet;
use App\Entity\Destination;
use App\Entity\Fleet_List;
use Datetime;
use DatetimeZone;
use DateInterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class FleetController  extends AbstractController
{
    /**
     * @Route("/flotte/{usePlanet}", name="fleet", requirements={"usePlanet"="\d+"})
     */
    public function fleetAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $fleetGiveMove = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->where('f.user = :user')
            ->andWhere('f.flightTime is not null')
            ->setParameters(['user' => $user])
            ->orderBy('f.flightTime')
            ->getQuery()
            ->getResult();

        $fleetUsePlanet = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->join('p.sector', 's')
            ->where('f.user = :user')
            ->andWhere('f.flightTime is null')
            ->andWhere('f.planet = :planet')
            ->setParameters(['user' => $user, 'planet' => $usePlanet])
            ->orderBy('s.position, p.position')
            ->getQuery()
            ->getResult();

        $fleetPlanets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->join('p.sector', 's')
            ->where('f.user = :user')
            ->andWhere('f.flightTime is null')
            ->andWhere('f.planet != :planet')
            ->andWhere('f.planet in (:planets)')
            ->setParameters(['user' => $user, 'planet' => $usePlanet, 'planets' => $user->getPlanets()])
            ->orderBy('s.position, p.position')
            ->getQuery()
            ->getResult();

        $fleetOther = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->join('p.sector', 's')
            ->where('f.user = :user')
            ->andWhere('f.flightTime is null')
            ->andWhere('f.planet not in (:planets)')
            ->setParameters(['user' => $user, 'planets' => $user->getPlanets()])
            ->orderBy('s.position, p.position')
            ->getQuery()
            ->getResult();

        if(($user->getTutorial() == 9)) {
            $user->setTutorial(10);
            $em->flush();
        }

        return $this->render('connected/fleet.html.twig', [
            'date' => $now,
            'usePlanet' => $usePlanet,
            'fleetMove' => $fleetGiveMove,
            'fleetOther' => $fleetOther,
            'fleetPlanets' => $fleetPlanets,
            'fleetUsePlanet' => $fleetUsePlanet,
        ]);
    }

    /**
     * @Route("/flotte-liste/{usePlanet}", name="fleet_list", requirements={"usePlanet"="\d+"})
     */
    public function fleetListAction(Request $request, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $form_listCreate = $this->createForm(FleetListType::class);
        $form_listCreate->handleRequest($request);


        if ($form_listCreate->isSubmitted()) {
            if(count($user->getFleetLists()) >= 10) {
                $this->addFlash("fail", "Vous avez atteint la limite de Cohortes autorisées par l'Instance.");
                return $this->redirectToRoute('fleet_list', ['usePlanet' => $usePlanet->getId()]);
            }

            $fleetList = new Fleet_List();
            $fleetList->setName($form_listCreate->get('name')->getData());
            $fleetList->setPriority($form_listCreate->get('priority')->getData());
            $fleetList->setUser($user);
            $em->persist($fleetList);
            $quest = $user->checkQuests('cohort');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
            if(($user->getTutorial() == 12)) {
                $user->setTutorial(13);
            }
            $em->flush();
        }

        $fleetLists = $em->getRepository('App:Fleet_List')
            ->createQueryBuilder('f')
            ->where('f.user = :user')
            ->setParameters(['user' => $user])
            ->orderBy('f.priority')
            ->getQuery()
            ->getResult();

        if(($user->getTutorial() == 11)) {
            $user->setTutorial(12);
            $em->flush();
        }

        return $this->render('connected/fleet_list.html.twig', [
            'date' => $now,
            'usePlanet' => $usePlanet,
            'fleetLists' => $fleetLists,
            'form_listCreate' => $form_listCreate->createView()
        ]);
    }

    /**
     * @Route("/flotte-liste-ajouter/{usePlanet}/{fleetList}/{fleet}", name="fleet_list_add", requirements={"usePlanet"="\d+","fleetList"="\d+","fleet"="\d+"})
     */
    public function fleetListAddAction(Planet $usePlanet, Fleet_List $fleetList, Fleet $fleet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if($user == $fleetList->getUser()) {
            $fleetList->addFleet($fleet);
            $fleet->setFleetList($fleetList);
            if(($user->getTutorial() == 13)) {
                $user->setTutorial(14);
            }
            $em->flush();
        }

        return $this->redirectToRoute('fleet_list', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/flotte-liste-sub/{usePlanet}/{fleetList}/{fleet}", name="fleet_list_sub", requirements={"usePlanet"="\d+","fleetList"="\d+","fleet"="\d+"})
     */
    public function fleetListSubAction(Planet $usePlanet, Fleet_List $fleetList, Fleet $fleet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if ($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if ($user == $fleetList->getUser()) {
            $fleetList->removeFleet($fleet);
            $fleet->setFleetList(null);
            $em->flush();
        }

        return $this->redirectToRoute('fleet_list', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/flotte-liste-destroy/{fleetList}/{usePlanet}", name="fleet_list_destroy", requirements={"usePlanet"="\d+","fleetList"="\d+"})
     */
    public function fleetListDestroyAction(Planet $usePlanet, Fleet_List $fleetList)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if ($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if ($user == $fleetList->getUser()) {
            foreach($fleetList->getFleets() as $fleet) {
                $fleetList->removeFleet($fleet);
                $fleet->setFleetList(null);
            }
            $em->remove($fleetList);
            $em->flush();
        }

        return $this->redirectToRoute('fleet_list', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/flotte-alliance-ajouter/{usePlanet}/{fleet}", name="fleet_ally_add", requirements={"usePlanet"="\d+","fleet"="\d+"})
     */
    public function fleetAllyAddAction(Planet $usePlanet, Fleet $fleet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if ($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if ($user == $fleet->getUser() && $user->getAlly()) {
            if (count($user->getAlly()->getFleets()) < round(count($user->getAlly()->getUsers()) / 2)) {
                $fleet->setAlly($user->getAlly());
                $em->flush();
            }
        }

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/flotte-alliance-sub/{usePlanet}/{fleet}", name="fleet_ally_sub", requirements={"usePlanet"="\d+","fleet"="\d+"})
     */
    public function fleetAllySubAction(Planet $usePlanet, Fleet $fleet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if ($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if ($user == $fleet->getUser() && $user->getAlly()) {
            $fleet->setAlly(null);
            $em->flush();
        }

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/gerer-flotte/{fleetGive}/{usePlanet}", name="manage_fleet", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function manageFleetAction(Request $request, Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            if (!$user->getAlly()) {
                return $this->redirectToRoute('home');
            } elseif ($user->getGrade()->getPlacement() != 1 || $fleetGive->getUser()->getAlly() != $user->getAlly()) {
                return $this->redirectToRoute('home');
            }
        }

        if ($fleetGive->getCargoFull() > $fleetGive->getCargoPlace()) {
            $lessRes = $fleetGive->getCargoFull() - $fleetGive->getCargoPlace();
            if ($fleetGive->getNiobium() > $lessRes) {
                $fleetGive->setNiobium($fleetGive->getNiobium() - $lessRes);
            } else {
                $lessRes = $lessRes - $fleetGive->getNiobium();
                $fleetGive->setNiobium(0);
            }
            if ($fleetGive->getWater() > $lessRes) {
                $fleetGive->setWater($fleetGive->getWater() - $lessRes);
            } else {
                $lessRes = $lessRes - $fleetGive->getWater();
                $fleetGive->setWater(0);
            }
            if ($fleetGive->getWorker() > $lessRes) {
                $fleetGive->setWorker($fleetGive->getWorker() - $lessRes);
            } else {
                $lessRes = $lessRes - $fleetGive->getWorker();
                $fleetGive->setWorker(0);
            }
            if ($fleetGive->getSoldier() > $lessRes) {
                $fleetGive->setSoldier($fleetGive->getSoldier() - $lessRes);
            } else {
                $lessRes = $lessRes - $fleetGive->getSoldier();
                $fleetGive->setSoldier(0);
            }
            if ($fleetGive->getScientist() > $lessRes) {
                $fleetGive->setScientist($fleetGive->getScientist() - $lessRes);
            } else {
                $lessRes = $lessRes - $fleetGive->getScientist();
                $fleetGive->setScientist(0);
            }
            if ($fleetGive->getTank() > $lessRes) {
                $fleetGive->setTank($fleetGive->getTank() - $lessRes);
            } else {
                $lessRes = $lessRes - $fleetGive->getTank();
                $fleetGive->setTank(0);
            }
            if ($fleetGive->getUranium() > $lessRes) {
                $fleetGive->setUranium($fleetGive->getUranium() - $lessRes);
            } else {
                $fleetGive->setUranium(0);
            }
            $em->flush();
        }

        $planetTake = $fleetGive->getPlanet();
        $form_manageFleet = $this->createForm(SpatialEditFleetType::class);
        $form_manageFleet->handleRequest($request);

        $form_manageRenameFleet = $this->createForm(FleetRenameType::class, null, ["name" => $fleetGive->getName()]);
        $form_manageRenameFleet->handleRequest($request);

        $form_manageAttackFleet = $this->createForm(FleetAttackType::class, $fleetGive);
        $form_manageAttackFleet->handleRequest($request);

        $form_sendFleet = $this->createForm(FleetSendType::class, null, ["user" => $user->getId()]);
        $form_sendFleet->handleRequest($request);

        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }

        if ($form_manageRenameFleet->isSubmitted()) {
            $fleetGive->setName($form_manageRenameFleet->get('name')->getData());
            $em->flush();
        }
        if($request->isXmlHttpRequest()) {
            $response = new JsonResponse();
            if ($request->get('name') == 'name') {
                $fleetGive->setName($request->get('data'));
                $em->flush();
                $response->setData(
                    [
                        'has_error' => false,
                    ]
                );
                return $response;
            }
            if ($request->get('name') == 'attack') {
                if ($fleetGive->getMissile() <= 0) {
                    $response->setData(
                        [
                            'has_error' => true,
                        ]
                    );
                    return $response;
                }
                $fleetGive->setAttack($request->get('data'));
                $eAlly = $user->getAllyEnnemy();
                $warAlly = [];
                $x = 0;
                foreach ($eAlly as $tmp) {
                    $warAlly[$x] = $tmp->getAllyTag();
                    $x++;
                }
                $fleetGives = $em->getRepository('App:Fleet')
                    ->createQueryBuilder('f')
                    ->join('f.user', 'u')
                    ->join('u.ally', 'a')
                    ->where('f.planet = :planet')
                    ->andWhere('f.attack = true OR a.sigle in (:ally)')
                    ->andWhere('f.user != :user')
                    ->andWhere('f.flightTime is null')
                    ->setParameters(['planet' => $usePlanet, 'ally' => $warAlly, 'user' => $user])
                    ->getQuery()
                    ->getResult();

                if (($fleetGive->getAttack() == true && $planetTake->getFleetNoFriends($user)) || $fleetGives) {
                    $allFleets = $em->getRepository('App:Fleet')
                        ->createQueryBuilder('f')
                        ->join('f.user', 'u')
                        ->where('f.planet = :planet')
                        ->andWhere('f.id != :id')
                        ->andWhere('f.flightTime is null')
                        ->setParameters(['planet' => $planetTake, 'id' => $fleetGive->getId()])
                        ->getQuery()
                        ->getResult();

                    $now = new DateTime();
                    $now->setTimezone(new DateTimeZone('Europe/Paris'));
                    $now->add(new DateInterval('PT' . 300 . 'S'));
                    foreach ($allFleets as $updateF) {
                        $updateF->setFightAt($now);
                    }
                    $fleetGive->setFightAt($now);
                    $em->flush();

                    $response->setData(
                        [
                            'has_error' => false,
                            'war' => true
                        ]
                    );
                    return $response;
                }

                $em->flush();
                $response->setData(
                    [
                        'has_error' => false,
                        'war' => false
                    ]
                );
                return $response;
            }
        }

        if ($form_manageFleet->isSubmitted()) {
            if (abs($form_manageFleet->get('moreNiobium')->getData()) <= $planetTake->getNiobium() and abs($form_manageFleet->get('moreNiobium')->getData()) != 0 and $fleetGive->getCargoPlace() > $fleetGive->getCargoFull()) {
                if (($fleetGive->getCargoPlace() - $fleetGive->getCargoFull()) >= abs($form_manageFleet->get('moreNiobium')->getData())) {
                    $fleetGive->setNiobium($fleetGive->getNiobium() + abs($form_manageFleet->get('moreNiobium')->getData()));
                    $planetTake->setNiobium($planetTake->getNiobium() - abs($form_manageFleet->get('moreNiobium')->getData()));
                } else {
                    $maxRes = $fleetGive->getCargoPlace() - $fleetGive->getCargoFull();
                    $fleetGive->setNiobium($fleetGive->getNiobium() + $maxRes);
                    $planetTake->setNiobium($planetTake->getNiobium() - $maxRes);
                }
            } elseif (abs($form_manageFleet->get('lessNiobium')->getData()) <= $fleetGive->getNiobium() and $planetTake->getNiobiumMax() > $planetTake->getNiobium()) {
                if ($planetTake->getNiobiumMax() >= $planetTake->getNiobium() + abs($form_manageFleet->get('lessNiobium')->getData())) {
                    $fleetGive->setNiobium($fleetGive->getNiobium() - abs($form_manageFleet->get('lessNiobium')->getData()));
                    $planetTake->setNiobium($planetTake->getNiobium() + abs($form_manageFleet->get('lessNiobium')->getData()));
                } else {
                    $maxRes = $planetTake->getNiobiumMax() - $planetTake->getNiobium();
                    $fleetGive->setNiobium($fleetGive->getNiobium() - $maxRes);
                    $planetTake->setNiobium($planetTake->getNiobium() + $maxRes);
                }
            }
            if (abs($form_manageFleet->get('moreWater')->getData()) <= $planetTake->getWater() and abs($form_manageFleet->get('moreWater')->getData()) != 0 and $fleetGive->getCargoPlace() > $fleetGive->getCargoFull()) {
                if (($fleetGive->getCargoPlace() - $fleetGive->getCargoFull()) >= abs($form_manageFleet->get('moreWater')->getData())) {
                    $fleetGive->setWater($fleetGive->getWater() + abs($form_manageFleet->get('moreWater')->getData()));
                    $planetTake->setWater($planetTake->getWater() - abs($form_manageFleet->get('moreWater')->getData()));
                } else {
                    $maxRes = $fleetGive->getCargoPlace() - $fleetGive->getCargoFull();
                    $fleetGive->setWater($fleetGive->getWater() + $maxRes);
                    $planetTake->setWater($planetTake->getWater() - $maxRes);
                }
            } elseif (abs($form_manageFleet->get('lessWater')->getData()) <= $fleetGive->getWater() and $planetTake->getWaterMax() > $planetTake->getWater()) {
                if ($planetTake->getWaterMax() >= $planetTake->getWater() + abs($form_manageFleet->get('lessWater')->getData())) {
                    $fleetGive->setWater($fleetGive->getWater() - abs($form_manageFleet->get('lessWater')->getData()));
                    $planetTake->setWater($planetTake->getWater() + abs($form_manageFleet->get('lessWater')->getData()));
                } else {
                    $maxRes = $planetTake->getWaterMax() - $planetTake->getWater();
                    $fleetGive->setWater($fleetGive->getWater() - $maxRes);
                    $planetTake->setWater($planetTake->getWater() + $maxRes);
                }
            }
            if (abs($form_manageFleet->get('moreUranium')->getData()) <= $planetTake->getUranium() and abs($form_manageFleet->get('moreUranium')->getData()) != 0 and $fleetGive->getCargoPlace() > $fleetGive->getCargoFull()) {
                if (($fleetGive->getCargoPlace() - $fleetGive->getCargoFull()) >= abs($form_manageFleet->get('moreUranium')->getData())) {
                    $fleetGive->setUranium($fleetGive->getUranium() + abs($form_manageFleet->get('moreUranium')->getData()));
                    $planetTake->setUranium($planetTake->getUranium() - abs($form_manageFleet->get('moreUranium')->getData()));
                } else {
                    $maxRes = $fleetGive->getCargoPlace() - $fleetGive->getCargoFull();
                    $fleetGive->setUranium($fleetGive->getUranium() + $maxRes);
                    $planetTake->setUranium($planetTake->getUranium() - $maxRes);
                }
            } elseif (abs($form_manageFleet->get('lessUranium')->getData()) <= $fleetGive->getUranium()) {
                $fleetGive->setUranium($fleetGive->getUranium() - abs($form_manageFleet->get('lessUranium')->getData()));
                $planetTake->setUranium($planetTake->getUranium() + abs($form_manageFleet->get('lessUranium')->getData()));
            }
            if (abs($form_manageFleet->get('moreSoldier')->getData()) <= $planetTake->getSoldier() and abs($form_manageFleet->get('moreSoldier')->getData()) != 0 and $fleetGive->getCargoPlace() > $fleetGive->getCargoFull()) {
                if (($fleetGive->getCargoPlace() - $fleetGive->getCargoFull()) >= abs($form_manageFleet->get('moreSoldier')->getData())) {
                    $fleetGive->setSoldier($fleetGive->getSoldier() + abs($form_manageFleet->get('moreSoldier')->getData()));
                    $planetTake->setSoldier($planetTake->getSoldier() - abs($form_manageFleet->get('moreSoldier')->getData()));
                } else {
                    $maxRes = $fleetGive->getCargoPlace() - $fleetGive->getCargoFull();
                    $fleetGive->setSoldier($fleetGive->getSoldier() + $maxRes);
                    $planetTake->setSoldier($planetTake->getSoldier() - $maxRes);
                }
            } elseif (abs($form_manageFleet->get('lessSoldier')->getData()) <= $fleetGive->getSoldier() and $planetTake->getSoldierMax() > $planetTake->getSoldier()) {
                if ($planetTake->getSoldierMax() >= $planetTake->getSoldier() + abs($form_manageFleet->get('lessSoldier')->getData())) {
                    $fleetGive->setSoldier($fleetGive->getSoldier() - abs($form_manageFleet->get('lessSoldier')->getData()));
                    $planetTake->setSoldier($planetTake->getSoldier() + abs($form_manageFleet->get('lessSoldier')->getData()));
                } else {
                    $maxRes = $planetTake->getSoldierMax() - $planetTake->getSoldier();
                    $fleetGive->setSoldier($fleetGive->getSoldier() - $maxRes);
                    $planetTake->setSoldier($planetTake->getSoldier() + $maxRes);
                }
            }
            if (abs($form_manageFleet->get('moreTank')->getData()) <= $planetTake->getTank() and abs($form_manageFleet->get('moreTank')->getData()) != 0 and $fleetGive->getCargoPlace() > $fleetGive->getCargoFull()) {
                if (($fleetGive->getCargoPlace() - $fleetGive->getCargoFull()) >= abs($form_manageFleet->get('moreTank')->getData())) {
                    $fleetGive->setTank($fleetGive->getTank() + abs($form_manageFleet->get('moreTank')->getData()));
                    $planetTake->setTank($planetTake->getTank() - abs($form_manageFleet->get('moreTank')->getData()));
                } else {
                    $maxRes = $fleetGive->getCargoPlace() - $fleetGive->getCargoFull();
                    $fleetGive->setTank($fleetGive->getTank() + $maxRes);
                    $planetTake->setTank($planetTake->getTank() - $maxRes);
                }
            } elseif (abs($form_manageFleet->get('lessTank')->getData()) <= $fleetGive->getTank() and 500 > $planetTake->getTank()) {
                if (500 >= $planetTake->getTank() + abs($form_manageFleet->get('lessTank')->getData())) {
                    $fleetGive->setTank($fleetGive->getTank() - abs($form_manageFleet->get('lessTank')->getData()));
                    $planetTake->setTank($planetTake->getTank() + abs($form_manageFleet->get('lessTank')->getData()));
                } else {
                    $maxRes = 500 - $planetTake->getTank();
                    $fleetGive->setTank($fleetGive->getTank() - $maxRes);
                    $planetTake->setTank($planetTake->getTank() + $maxRes);
                }
            }
            if (abs($form_manageFleet->get('moreWorker')->getData()) <= $planetTake->getWorker() and abs($form_manageFleet->get('moreWorker')->getData()) != 0 and $fleetGive->getCargoPlace() > $fleetGive->getCargoFull()) {
                if (($fleetGive->getCargoPlace() - $fleetGive->getCargoFull()) >= abs($form_manageFleet->get('moreWorker')->getData())) {
                    $fleetGive->setWorker($fleetGive->getWorker() + abs($form_manageFleet->get('moreWorker')->getData()));
                    $planetTake->setWorker($planetTake->getWorker() - abs($form_manageFleet->get('moreWorker')->getData()));
                } else {
                    $maxRes = $fleetGive->getCargoPlace() - $fleetGive->getCargoFull();
                    $fleetGive->setWorker($fleetGive->getWorker() + $maxRes);
                    $planetTake->setWorker($planetTake->getWorker() - $maxRes);
                }
            } elseif (abs($form_manageFleet->get('lessWorker')->getData()) <= $fleetGive->getWorker() and $planetTake->getWorkerMax() > $planetTake->getWorker()) {
                if ($planetTake->getWorkerMax() >= $planetTake->getWorker() + abs($form_manageFleet->get('lessWorker')->getData())) {
                    $fleetGive->setWorker($fleetGive->getWorker() - abs($form_manageFleet->get('lessWorker')->getData()));
                    $planetTake->setWorker($planetTake->getWorker() + abs($form_manageFleet->get('lessWorker')->getData()));
                } else {
                    $maxRes = $planetTake->getWorkerMax() - $planetTake->getWorker();
                    $fleetGive->setWorker($fleetGive->getWorker() - $maxRes);
                    $planetTake->setWorker($planetTake->getWorker() + $maxRes);
                }
            }
            if (abs($form_manageFleet->get('moreScientist')->getData()) <= $planetTake->getScientist() and abs($form_manageFleet->get('moreScientist')->getData()) != 0 and $fleetGive->getCargoPlace() > $fleetGive->getCargoFull()) {
                if (($fleetGive->getCargoPlace() - $fleetGive->getCargoFull()) >= abs($form_manageFleet->get('moreScientist')->getData())) {
                    $fleetGive->setScientist($fleetGive->getScientist() + abs($form_manageFleet->get('moreScientist')->getData()));
                    $planetTake->setScientist($planetTake->getScientist() - abs($form_manageFleet->get('moreScientist')->getData()));
                } else {
                    $maxRes = $fleetGive->getCargoPlace() - $fleetGive->getCargoFull();
                    $fleetGive->setScientist($fleetGive->getScientist() + $maxRes);
                    $planetTake->setScientist($planetTake->getScientist() - $maxRes);
                }
            } elseif (abs($form_manageFleet->get('lessScientist')->getData()) <= $fleetGive->getScientist() and $planetTake->getScientistMax() > $planetTake->getScientist()) {
                if ($planetTake->getScientistMax() >= $planetTake->getScientist() + abs($form_manageFleet->get('lessScientist')->getData())) {
                    $fleetGive->setScientist($fleetGive->getScientist() - abs($form_manageFleet->get('lessScientist')->getData()));
                    $planetTake->setScientist($planetTake->getScientist() + abs($form_manageFleet->get('lessScientist')->getData()));
                } else {
                    $maxRes = $planetTake->getScientistMax() - $planetTake->getScientist();
                    $fleetGive->setScientist($fleetGive->getScientist() - $maxRes);
                    $planetTake->setScientist($planetTake->getScientist() + $maxRes);
                }
            }
            $fleetGive->setSignature($fleetGive->getNbrSignatures());

            $em->flush();
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }

        $fleetGive->setSignature($fleetGive->getNbrSignatures());
        $em->flush();

        return $this->render('connected/fleet/edit.html.twig', [
            'fleet' => $fleetGive,
            'usePlanet' => $usePlanet,
            'form_manageFleet' => $form_manageFleet->createView(),
            'form_sendFleet' => $form_sendFleet->createView(),
            'form_manageRenameFleet' => $form_manageRenameFleet->createView(),
            'form_manageAttackFleet' => $form_manageAttackFleet->createView(),
        ]);
    }

    /**
     * @Route("/detruire-flotte/{fleetGive}/{usePlanet}", name="destroy_fleet", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function destroyFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet &&
            ($planetTake->getSoldier() + $fleetGive->getSoldier()) <= $planetTake->getSoldierMax() &&
            ($planetTake->getWorker() + $fleetGive->getWorker()) <= $planetTake->getWorkerMax() &&
            ($planetTake->getScientist() + $fleetGive->getScientist()) <= $planetTake->getScientistMax() &&
            ($planetTake->getNiobium() + $fleetGive->getNiobium()) <= $planetTake->getNiobiumMax() &&
            ($planetTake->getWater() + $fleetGive->getWater()) <= $planetTake->getWaterMax()) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }

        $planetTake->setColonizer($planetTake->getColonizer() + $fleetGive->getColonizer());
        $planetTake->setCargoI($planetTake->getCargoI() + $fleetGive->getCargoI());
        $planetTake->setCargoV($planetTake->getCargoV() + $fleetGive->getCargoV());
        $planetTake->setCargoX($planetTake->getCargoX() + $fleetGive->getCargoX());
        $planetTake->setRecycleur($planetTake->getRecycleur() + $fleetGive->getRecycleur());
        $planetTake->setBarge($planetTake->getBarge() + $fleetGive->getBarge());
        $planetTake->setMoonMaker($planetTake->getMoonMaker() + $fleetGive->getMoonMaker());
        $planetTake->setRadarShip($planetTake->getRadarShip() + $fleetGive->getRadarShip());
        $planetTake->setBrouilleurShip($planetTake->getBrouilleurShip() + $fleetGive->getBrouilleurShip());
        $planetTake->setMotherShip($planetTake->getMotherShip() + $fleetGive->getMotherShip());
        $planetTake->setSonde($planetTake->getSonde() + $fleetGive->getSonde());
        $planetTake->setHunter($planetTake->getHunter() + $fleetGive->getHunter());
        $planetTake->setHunterHeavy($planetTake->getHunterHeavy() + $fleetGive->getHunterHeavy());
        $planetTake->setHunterWar($planetTake->getHunterWar() + $fleetGive->getHunterWar());
        $planetTake->setCorvet($planetTake->getCorvet() + $fleetGive->getCorvet());
        $planetTake->setCorvetLaser($planetTake->getCorvetLaser() + $fleetGive->getCorvetLaser());
        $planetTake->setCorvetWar($planetTake->getCorvetWar() + $fleetGive->getCorvetWar());
        $planetTake->setFregate($planetTake->getFregate() + $fleetGive->getFregate());
        $planetTake->setFregatePlasma($planetTake->getFregatePlasma() + $fleetGive->getFregatePlasma());
        $planetTake->setCroiser($planetTake->getCroiser() + $fleetGive->getCroiser());
        $planetTake->setIronClad($planetTake->getIronClad() + $fleetGive->getIronClad());
        $planetTake->setDestroyer($planetTake->getDestroyer() + $fleetGive->getDestroyer());
        $planetTake->setNiobium($planetTake->getNiobium() + $fleetGive->getNiobium());
        $planetTake->setWater($planetTake->getWater() + $fleetGive->getWater());
        $planetTake->setSoldier($planetTake->getSoldier() + $fleetGive->getSoldier());
        $planetTake->setWorker($planetTake->getWorker() + $fleetGive->getWorker());
        $planetTake->setScientist($planetTake->getScientist() + $fleetGive->getScientist());
        $planetTake->setSignature($planetTake->getNbrSignatures());
        $em->remove($fleetGive);

        $em->flush();

        return $this->redirectToRoute('fleet', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/envoyer-flotte/{fleetGive}/{usePlanet}", name="send_fleet", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function sendFleetAction(Request $request, Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $moreNow = new DateTime();
        $moreNow->setTimezone(new DateTimeZone('Europe/Paris'));
        $moreNow->add(new DateInterval('PT' . 120 . 'S'));

        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            if (!$user->getAlly()) {
                return $this->redirectToRoute('home');
            } elseif ($user->getGrade()->getPlacement() != 1 || $fleetGive->getUser()->getAlly() != $user->getAlly()) {
                return $this->redirectToRoute('home');
            }
        }
        $previousDestination = $fleetGive->getDestination();
        if (!$fleetGive->getFlightTime() && $previousDestination) {
            $previousDestination->setFleet(null);
            $previousDestination->setPlanet(null);
            $em->remove($previousDestination);
            $em->flush();
        }

        $form_sendFleet = $this->createForm(FleetSendType::class, null, ["user" => $user->getId()]);
        $form_sendFleet->handleRequest($request);

        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if ($form_sendFleet->isSubmitted() && $form_sendFleet->isValid()) {
            $sectorDestroy = $em->getRepository('App:Sector')
                ->createQueryBuilder('s')
                ->where('s.position = :sector')
                ->andWhere('s.destroy = :true')
                ->setParameters(['sector' => $form_sendFleet->get('sector')->getData(), 'true' => 1])
                ->getQuery()
                ->getOneOrNullResult();

            if($sectorDestroy && $form_sendFleet->get('sector')->getData() != $fleetGive->getPlanet()->getSector()->getPosition()) { // AJOUTER LA GALAXIE
                return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
            }

            if($form_sendFleet->get('planet')->getData()) {
                $planetTake = $form_sendFleet->get('planet')->getData();
                $sector = $planetTake->getSector()->getPosition();
                $planetTakee = $planetTake->getPosition();
                $galaxy = $planetTake->getSector()->getGalaxy()->getPosition();
                if($planetTake == $fleetGive->getPlanet()) {
                    return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
                }
            } else {
                if($user->getHyperespace() == 1) {
                    $galaxy = $form_sendFleet->get('galaxy')->getData();
                } else {
                    $galaxy = $fleetGive->getPlanet()->getSector()->getGalaxy()->getPosition();
                }
                $sector = $form_sendFleet->get('sector')->getData();
                $planetTakee = $form_sendFleet->get('planete')->getData();

                if (($galaxy < 1 || $galaxy > 10) || ($sector < 1 || $sector > 100) || ($planetTakee < 1 || $planetTakee > 25)) {
                    return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
                }

                $planetTake = $em->getRepository('App:Planet')
                    ->createQueryBuilder('p')
                    ->join('p.sector', 's')
                    ->join('s.galaxy', 'g')
                    ->where('s.position = :sector')
                    ->andWhere('g.position = :galaxy')
                    ->andWhere('p.position = :planete')
                    ->setParameters(['sector' => $sector, 'galaxy' => $galaxy, 'planete' => $planetTakee])
                    ->getQuery()
                    ->getOneOrNullResult();

                if($planetTake == $fleetGive->getPlanet()) {
                    return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
                }
            }
            $sFleet = $fleetGive->getPlanet()->getSector()->getPosition();
            if($fleetGive->getPlanet()->getSector()->getGalaxy()->getPosition() != $galaxy) {
                $base = 18;  // 86400 MODE NORMAL
                $price = 25;
            } else {
                $pFleet = $fleetGive->getPlanet()->getPosition();
                if ($sFleet == $sector) {
                    $x1 = ($pFleet - 1) % 5;
                    $x2 = ($planetTakee - 1) % 5;
                    $y1 = ($pFleet - 1) / 5;
                    $y2 = ($planetTakee - 1) / 5;
                } else {
                    $x1 = (($sFleet - 1) % 10) * 3;
                    $x2 = (($sector - 1) % 10) * 3;
                    $y1 = (($sFleet - 1) / 10) * 3;
                    $y2 = (($sector - 1) / 10) * 3;
                }
                $base = sqrt(pow(($x2 - $x1), 2) + pow(($y2 - $y1), 2));
                $price = $base / 3;
            }
            $carburant = round($price * ($fleetGive->getNbrSignatures() / 200));
            if($carburant > $user->getBitcoin()) {
                return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
            }
            if($fleetGive->getMotherShip()) {
                $speed = $fleetGive->getSpeed() - ($fleetGive->getSpeed() * 0.10);
            } else {
                $speed = $fleetGive->getSpeed();
            }
            $distance = $speed * $base * 100; // 1000 MODE NORMAL
            $now->add(new DateInterval('PT' . round($distance) . 'S'));
            $destination = new Destination();
            $destination->setFleet($fleetGive);
            $destination->setPlanet($planetTake);
            $em->persist($destination);
            $fleetGive->setFlightTime($now);
            $fleetGive->setCancelFlight($moreNow);
            $fleetGive->setSignature($fleetGive->getNbrSignatures());
            if($form_sendFleet->get('flightType')->getData() == '2' && ($planetTake->getUser() || $planetTake->getMerchant() == true)) {
                $fleetGive->setFlightType(2);
                $carburant = $carburant * 2;
            } elseif($form_sendFleet->get('flightType')->getData() == '3' && $planetTake->getUser() == null) {
                $fleetGive->setFlightType(3);
            } elseif($form_sendFleet->get('flightType')->getData() == '4' && $planetTake->getUser()) {
                $fleetGive->setFlightType(4);
            } elseif($form_sendFleet->get('flightType')->getData() == '5' && $planetTake->getUser()) {
                $fleetGive->setFlightType(5);
            } else {
                $fleetGive->setFlightType(1);
            }
            $user->setBitcoin($user->getBitcoin() - $carburant);

            $em->flush();
        }
        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-niobium/{fleetGive}/{usePlanet}", name="discharge_fleet_niobium", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function dischargeNiobiumFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        
        if($planetTake->getMerchant() == true) {
            if ($user->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getNiobium() / 6) / 1000) * (1 + ($user->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getNiobium() / 6) / 1000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setUser($user);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($user->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getNiobium() * 0.10) * (1 + ($user->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getNiobium() * 0.10);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setNiobium($planetTake->getNiobium() + $fleetGive->getNiobium());
            $user->setBitcoin($user->getBitcoin() + $gainSell);
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setNiobium(0);
            $quest = $user->checkQuests('sell');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }
        if(($planetTake->getNiobium() + $fleetGive->getNiobium()) <= $planetTake->getNiobiumMax()) {
            $planetTake->setNiobium($planetTake->getNiobium() + $fleetGive->getNiobium());
            $fleetGive->setNiobium(0);
        } else {
            $planetTake->setNiobium($planetTake->getNiobiumMax());
            $fleetGive->setNiobium(($planetTake->getNiobium() + $fleetGive->getNiobium()) - $planetTake->getNiobiumMax());
        }
        $user->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-water/{fleetGive}/{usePlanet}", name="discharge_fleet_water", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function dischargeWaterFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            if ($user->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getWater() / 3) / 1000) * (1 + ($user->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getWater() / 3) / 1000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setUser($user);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($user->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getWater() * 0.25) * (1 + ($user->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getWater() * 0.25);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setWater($planetTake->getWater() + $fleetGive->getWater());
            $user->setBitcoin($user->getBitcoin() + $gainSell);
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setWater(0);
            $quest = $user->checkQuests('sell');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }
        if(($planetTake->getWater() + $fleetGive->getWater()) <= $planetTake->getWaterMax()) {
            $planetTake->setWater($planetTake->getWater() + $fleetGive->getWater());
            $fleetGive->setWater(0);
        } else {
            $planetTake->setWater($planetTake->getWaterMax());
            $fleetGive->setWater(($planetTake->getWater() + $fleetGive->getWater()) - $planetTake->getWaterMax());
        }
        $user->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-soldat/{fleetGive}/{usePlanet}", name="discharge_fleet_soldier", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function dischargeSoldierFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            if ($user->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getSoldier() * 10) / 1000) * (1 + ($user->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getSoldier() * 10) / 1000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setUser($user);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($user->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getSoldier() * 80) * (1 + ($user->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getSoldier() * 80);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setSoldier($planetTake->getSoldier() + $fleetGive->getSoldier());
            $user->setBitcoin($user->getBitcoin() + $gainSell);
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setSoldier(0);
            $quest = $user->checkQuests('sell');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }
        if(($planetTake->getSoldier() + $fleetGive->getSoldier()) <= $planetTake->getSoldierMax()) {
            $planetTake->setSoldier($planetTake->getSoldier() + $fleetGive->getSoldier());
            $fleetGive->setSoldier(0);
        } else {
            $planetTake->setSoldier($planetTake->getSoldierMax());
            $fleetGive->setSoldier(($planetTake->getSoldier() + $fleetGive->getSoldier()) - $planetTake->getSoldierMax());
        }
        $user->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-travailleurs/{fleetGive}/{usePlanet}", name="discharge_fleet_worker", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function dischargeWorkerFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            if ($user->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getWorker() * 50) / 1000) * (1 + ($user->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getWorker() * 50) / 1000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setUser($user);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($user->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getWorker() * 5) * (1 + ($user->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getWorker() * 5);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setWorker($planetTake->getWorker() + $fleetGive->getWorker());
            $user->setBitcoin($user->getBitcoin() + $gainSell);
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setWorker(0);
            $quest = $user->checkQuests('sell');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }
        if(($planetTake->getWorker() + $fleetGive->getWorker()) <= $planetTake->getWorkerMax()) {
            $planetTake->setWorker($planetTake->getWorker() + $fleetGive->getWorker());
            $fleetGive->setWorker(0);
        } else {
            $planetTake->setWorker($planetTake->getWorkerMax());
            $fleetGive->setWorker(($planetTake->getWorker() + $fleetGive->getWorker()) - $planetTake->getWorkerMax());
        }
        $user->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-scientifique/{fleetGive}/{usePlanet}", name="discharge_fleet_scientist", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function dischargeScientistFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            if ($user->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getScientist() * 100) / 1000) * (1 + ($user->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getScientist() * 100) / 1000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setUser($user);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($user->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getScientist() * 300) * (1 + ($user->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getScientist() * 300);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setScientist($planetTake->getScientist() + $fleetGive->getScientist());
            $user->setBitcoin($user->getBitcoin() + $gainSell);
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setScientist(0);
            $quest = $user->checkQuests('sell');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }
        if(($planetTake->getScientist() + $fleetGive->getScientist()) <= $planetTake->getScientistMax()) {
            $planetTake->setScientist($planetTake->getScientist() + $fleetGive->getScientist());
            $fleetGive->setScientist(0);
        } else {
            $planetTake->setScientist($planetTake->getScientistMax());
            $fleetGive->setScientist(($planetTake->getScientist() + $fleetGive->getScientist()) - $planetTake->getScientistMax());
        }
        $user->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-tout/{fleetGive}/{usePlanet}", name="discharge_fleet_all", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function dischargeAllFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setUser($user);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($user->getPoliticPdg() > 0) {
                $newWarPointS = round((((($fleetGive->getScientist() * 100) + ($fleetGive->getWorker() * 50) + ($fleetGive->getSoldier() * 10) + ($fleetGive->getWater() / 3) + ($fleetGive->getNiobium() / 6)) / 1000)) * (1 + ($user->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round((($fleetGive->getScientist() * 100) + ($fleetGive->getWorker() * 50) + ($fleetGive->getSoldier() * 10) + ($fleetGive->getWater() / 3) + ($fleetGive->getNiobium() / 6)) / 1000);
            }
            if ($user->getPoliticMerchant() > 0) {
                $gainSell = round((($fleetGive->getWater() * 0.25) + ($fleetGive->getSoldier() * 80) + ($fleetGive->getWorker() * 5) + ($fleetGive->getScientist() * 300) + ($fleetGive->getNiobium() * 0.10)) * (1 + ($user->getPoliticMerchant() / 20)));
            } else {
                $gainSell = round(($fleetGive->getWater() * 0.25) + ($fleetGive->getSoldier() * 80) + ($fleetGive->getWorker() * 5) + ($fleetGive->getScientist() * 300) + ($fleetGive->getNiobium() * 0.10));
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format($gainSell) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setScientist($planetTake->getScientist() + $fleetGive->getScientist());
            $planetTake->setWorker($planetTake->getWorker() + $fleetGive->getWorker());
            $planetTake->setSoldier($planetTake->getSoldier() + $fleetGive->getSoldier());
            $planetTake->setWater($planetTake->getWater() + $fleetGive->getWater());
            $planetTake->setNiobium($planetTake->getNiobium() + $fleetGive->getNiobium());
            $user->setBitcoin($user->getBitcoin() + $gainSell);
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setScientist(0);
            $fleetGive->setNiobium(0);
            $fleetGive->setSoldier(0);
            $fleetGive->setWorker(0);
            $fleetGive->setWater(0);
            $quest = $user->checkQuests('sell');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }
        if(($planetTake->getScientist() + $fleetGive->getScientist()) <= $planetTake->getScientistMax() &&
            ($planetTake->getWorker() + $fleetGive->getWorker()) <= $planetTake->getWorkerMax() &&
            ($planetTake->getSoldier() + $fleetGive->getSoldier()) <= $planetTake->getSoldierMax() &&
            ($planetTake->getWater() + $fleetGive->getWater()) <= $planetTake->getWaterMax() &&
            ($planetTake->getNiobium() + $fleetGive->getNiobium()) <= $planetTake->getNiobiumMax()) {
            $planetTake->setScientist($planetTake->getScientist() + $fleetGive->getScientist());
            $planetTake->setWorker($planetTake->getWorker() + $fleetGive->getWorker());
            $planetTake->setSoldier($planetTake->getSoldier() + $fleetGive->getSoldier());
            $planetTake->setWater($planetTake->getWater() + $fleetGive->getWater());
            $planetTake->setNiobium($planetTake->getNiobium() + $fleetGive->getNiobium());
            $fleetGive->setNiobium(0);
            $fleetGive->setWater(0);
            $fleetGive->setSoldier(0);
            $fleetGive->setWorker(0);
            $fleetGive->setScientist(0);
        } else {
            $planetTake->setScientist($planetTake->getScientistMax());
            $fleetGive->setScientist(($planetTake->getScientist() + $fleetGive->getScientist()) - $planetTake->getScientistMax());
            $planetTake->setNiobium($planetTake->getNiobiumMax());
            $fleetGive->setNiobium(($planetTake->getNiobium() + $fleetGive->getNiobium()) - $planetTake->getNiobiumMax());
            $planetTake->setWater($planetTake->getWaterMax());
            $fleetGive->setWater(($planetTake->getWater() + $fleetGive->getWater()) - $planetTake->getWaterMax());
            $planetTake->setSoldier($planetTake->getSoldierMax());
            $fleetGive->setSoldier(($planetTake->getSoldier() + $fleetGive->getSoldier()) - $planetTake->getSoldierMax());
            $planetTake->setWorker($planetTake->getWorkerMax());
            $fleetGive->setWorker(($planetTake->getWorker() + $fleetGive->getWorker()) - $planetTake->getWorkerMax());
        }
        $user->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/fusionner-flotte/{fleetGive}/{fleetTake}/{usePlanet}", name="fusion_fleet", requirements={"usePlanet"="\d+", "fleetGive"="\d+", "fleetTake"="\d+"})
     */
    public function fusionFleetAction(Planet $usePlanet, Fleet $fleetGive, Fleet $fleetTake)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user || $fleetTake->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $fleetTake->setColonizer($fleetTake->getColonizer() + $fleetGive->getColonizer());
        $fleetTake->setCargoI($fleetTake->getCargoI() + $fleetGive->getCargoI());
        $fleetTake->setCargoV($fleetTake->getCargoV() + $fleetGive->getCargoV());
        $fleetTake->setCargoX($fleetTake->getCargoX() + $fleetGive->getCargoX());
        $fleetTake->setRecycleur($fleetTake->getRecycleur() + $fleetGive->getRecycleur());
        $fleetTake->setBarge($fleetTake->getBarge() + $fleetGive->getBarge());
        $fleetTake->setMoonMaker($fleetTake->getMoonMaker() + $fleetGive->getMoonMaker());
        $fleetTake->setRadarShip($fleetTake->getRadarShip() + $fleetGive->getRadarShip());
        $fleetTake->setBrouilleurShip($fleetTake->getBrouilleurShip() + $fleetGive->getBrouilleurShip());
        $fleetTake->setMotherShip($fleetTake->getMotherShip() + $fleetGive->getMotherShip());
        $fleetTake->setSonde($fleetTake->getSonde() + $fleetGive->getSonde());
        $fleetTake->setHunter($fleetTake->getHunter() + $fleetGive->getHunter());
        $fleetTake->setHunterHeavy($fleetTake->getHunterHeavy() + $fleetGive->getHunterHeavy());
        $fleetTake->setHunterWar($fleetTake->getHunterWar() + $fleetGive->getHunterWar());
        $fleetTake->setCorvet($fleetTake->getCorvet() + $fleetGive->getCorvet());
        $fleetTake->setCorvetLaser($fleetTake->getCorvetLaser() + $fleetGive->getCorvetLaser());
        $fleetTake->setCorvetWar($fleetTake->getCorvetWar() + $fleetGive->getCorvetWar());
        $fleetTake->setFregate($fleetTake->getFregate() + $fleetGive->getFregate());
        $fleetTake->setFregatePlasma($fleetTake->getFregatePlasma() + $fleetGive->getFregatePlasma());
        $fleetTake->setCroiser($fleetTake->getCroiser() + $fleetGive->getCroiser());
        $fleetTake->setIronClad($fleetTake->getIronClad() + $fleetGive->getIronClad());
        $fleetTake->setDestroyer($fleetTake->getDestroyer() + $fleetGive->getDestroyer());
        $fleetTake->setNiobium($fleetTake->getNiobium() + $fleetGive->getNiobium());
        $fleetTake->setWater($fleetTake->getWater() + $fleetGive->getWater());
        $fleetTake->setSoldier($fleetTake->getSoldier() + $fleetGive->getSoldier());
        $fleetTake->setWorker($fleetTake->getWorker() + $fleetGive->getWorker());
        $fleetTake->setScientist($fleetTake->getScientist() + $fleetGive->getScientist());
        $fleetTake->setSignature($fleetTake->getNbrSignatures());
        if ($fleetGive->getRecycleAt() && $fleetTake->getRecycleAt()) {
            $bestRecycle = ($fleetGive->getRecycleAt() > $fleetTake->getRecycleAt()) ? $fleetGive->getRecycleAt() : $fleetTake->getRecycleAt();
            $fleetTake->setRecycleAt($bestRecycle);
        }
        $em->remove($fleetGive);
        $em->flush();
        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetTake->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/gerer-vaisseaux/{fleet}/{usePlanet}", name="ships_manage", requirements={"usePlanet"="\d+", "fleet"="\d+"})
     */
    public function shipsManageAction(Request $request, Planet $usePlanet, Fleet $fleet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if ($usePlanet->getUser() != $user || $fleet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if(count($user->getFleets()) >= 75) {
            $this->addFlash("fail", "Vous avez atteint la limite de flottes autorisées par l'Instance.");
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
        }

        $form_spatialShip = $this->createForm(FleetEditShipType::class);
        $form_spatialShip->handleRequest($request);

        if ($form_spatialShip->isSubmitted() && $form_spatialShip->isValid()) {
            $planetTake = $fleet->getPlanet();
            if (abs($form_spatialShip->get('moreColonizer')->getData())) {
                $colonizer = $planetTake->getColonizer() - abs($form_spatialShip->get('moreColonizer')->getData());
                $fleet->setColonizer($fleet->getColonizer() + abs($form_spatialShip->get('moreColonizer')->getData()));
            } elseif (abs($form_spatialShip->get('lessColonizer')->getData()) <= $fleet->getColonizer()) {
                $colonizer = $planetTake->getColonizer() + abs($form_spatialShip->get('lessColonizer')->getData());
                $fleet->setColonizer($fleet->getColonizer() - abs($form_spatialShip->get('lessColonizer')->getData()));
            } else {
                $colonizer = $planetTake->getColonizer();
            }
            if (abs($form_spatialShip->get('moreRecycleur')->getData())) {
                $recycleur = $planetTake->getRecycleur() - abs($form_spatialShip->get('moreRecycleur')->getData());
                $fleet->setRecycleur($fleet->getRecycleur() + abs($form_spatialShip->get('moreRecycleur')->getData()));
            } elseif (abs($form_spatialShip->get('lessRecycleur')->getData()) <= $fleet->getRecycleur()) {
                $recycleur = $planetTake->getRecycleur() + abs($form_spatialShip->get('lessRecycleur')->getData());
                $fleet->setRecycleur($fleet->getRecycleur() - abs($form_spatialShip->get('lessRecycleur')->getData()));
            }
            if (abs($form_spatialShip->get('moreCargoI')->getData())) {
                $cargoI = $planetTake->getCargoI() - abs($form_spatialShip->get('moreCargoI')->getData());
                $fleet->setCargoI($fleet->getCargoI() + abs($form_spatialShip->get('moreCargoI')->getData()));
            } elseif (abs($form_spatialShip->get('lessCargoI')->getData()) <= $fleet->getCargoI()) {
                $cargoI = $planetTake->getCargoI() + abs($form_spatialShip->get('lessCargoI')->getData());
                $fleet->setCargoI($fleet->getCargoI() - abs($form_spatialShip->get('lessCargoI')->getData()));
            }
            if (abs($form_spatialShip->get('moreCargoV')->getData())) {
                $cargoV = $planetTake->getCargoV() - abs($form_spatialShip->get('moreCargoV')->getData());
                $fleet->setCargoV($fleet->getCargoV() + abs($form_spatialShip->get('moreCargoV')->getData()));
            } elseif (abs($form_spatialShip->get('lessCargoV')->getData()) <= $fleet->getCargoV()) {
                $cargoV = $planetTake->getCargoV() + abs($form_spatialShip->get('lessCargoV')->getData());
                $fleet->setCargoV($fleet->getCargoV() - abs($form_spatialShip->get('lessCargoV')->getData()));
            }
            if (abs($form_spatialShip->get('moreCargoX')->getData())) {
                $cargoX = $planetTake->getCargoX() - abs($form_spatialShip->get('moreCargoX')->getData());
                $fleet->setCargoX($fleet->getCargoX() + abs($form_spatialShip->get('moreCargoX')->getData()));
            } elseif (abs($form_spatialShip->get('lessCargoX')->getData()) <= $fleet->getCargoX()) {
                $cargoX = $planetTake->getCargoX() + abs($form_spatialShip->get('lessCargoX')->getData());
                $fleet->setCargoX($fleet->getCargoX() - abs($form_spatialShip->get('lessCargoX')->getData()));
            }
            if (abs($form_spatialShip->get('moreBarge')->getData())) {
                $barge = $planetTake->getBarge() - abs($form_spatialShip->get('moreBarge')->getData());
                $fleet->setBarge($fleet->getBarge() + abs($form_spatialShip->get('moreBarge')->getData()));
            } elseif (abs($form_spatialShip->get('lessBarge')->getData()) <= $fleet->getBarge()) {
                $barge = $planetTake->getBarge() + abs($form_spatialShip->get('lessBarge')->getData());
                $fleet->setBarge($fleet->getBarge() - abs($form_spatialShip->get('lessBarge')->getData()));
            }
            if (abs($form_spatialShip->get('moreMotherShip')->getData())) {
                $motherShip = $planetTake->getMotherShip() - abs($form_spatialShip->get('moreMotherShip')->getData());
                $fleet->setMotherShip($fleet->getMotherShip() + abs($form_spatialShip->get('moreMotherShip')->getData()));
            } elseif (abs($form_spatialShip->get('lessMotherShip')->getData()) <= $fleet->getMotherShip()) {
                $motherShip = $planetTake->getMotherShip() + abs($form_spatialShip->get('lessMotherShip')->getData());
                $fleet->setMotherShip($fleet->getMotherShip() - abs($form_spatialShip->get('lessMotherShip')->getData()));
            }
            if (abs($form_spatialShip->get('moreMoonMaker')->getData())) {
                $moonMaker = $planetTake->getMoonMaker() - abs($form_spatialShip->get('moreMoonMaker')->getData());
                $fleet->setMoonMaker($fleet->getMoonMaker() + abs($form_spatialShip->get('moreMoonMaker')->getData()));
            } elseif (abs($form_spatialShip->get('lessMoonMaker')->getData()) <= $fleet->getMoonMaker()) {
                $moonMaker = $planetTake->getMoonMaker() + abs($form_spatialShip->get('lessMoonMaker')->getData());
                $fleet->setMoonMaker($fleet->getMoonMaker() - abs($form_spatialShip->get('lessMoonMaker')->getData()));
            } else {
                $moonMaker = $planetTake->getMoonMaker();
            }
            if (abs($form_spatialShip->get('moreRadarShip')->getData())) {
                $radarShip = $planetTake->getRadarShip() - abs($form_spatialShip->get('moreRadarShip')->getData());
                $fleet->setRadarShip($fleet->getRadarShip() + abs($form_spatialShip->get('moreRadarShip')->getData()));
            } elseif (abs($form_spatialShip->get('lessRadarShip')->getData()) <= $fleet->getRadarShip()) {
                $radarShip = $planetTake->getRadarShip() + abs($form_spatialShip->get('lessRadarShip')->getData());
                $fleet->setRadarShip($fleet->getRadarShip() - abs($form_spatialShip->get('lessRadarShip')->getData()));
            } else {
                $radarShip = $planetTake->getRadarShip();
            }
            if (abs($form_spatialShip->get('moreBrouilleurShip')->getData())) {
                $brouilleurShip = $planetTake->getBrouilleurShip() - abs($form_spatialShip->get('moreBrouilleurShip')->getData());
                $fleet->setBrouilleurShip($fleet->getBrouilleurShip() + abs($form_spatialShip->get('moreBrouilleurShip')->getData()));
            } elseif (abs($form_spatialShip->get('lessBrouilleurShip')->getData()) <= $fleet->getBrouilleurShip()) {
                $brouilleurShip = $planetTake->getBrouilleurShip() + abs($form_spatialShip->get('lessBrouilleurShip')->getData());
                $fleet->setBrouilleurShip($fleet->getBrouilleurShip() - abs($form_spatialShip->get('lessBrouilleurShip')->getData()));
            } else {
                $brouilleurShip = $planetTake->getBrouilleurShip();
            }
            if (abs($form_spatialShip->get('moreSonde')->getData())) {
                $sonde = $planetTake->getSonde() - abs($form_spatialShip->get('moreSonde')->getData());
                $fleet->setSonde($fleet->getSonde() + abs($form_spatialShip->get('moreSonde')->getData()));
            } elseif (abs($form_spatialShip->get('lessSonde')->getData()) <= $fleet->getSonde()) {
                $sonde = $planetTake->getSonde() + abs($form_spatialShip->get('lessSonde')->getData());
                $fleet->setSonde($fleet->getSonde() - abs($form_spatialShip->get('lessSonde')->getData()));
            } else {
                $sonde = $planetTake->getSonde();
            }
            if (abs($form_spatialShip->get('moreHunter')->getData())) {
                $hunter = $planetTake->getHunter() - abs($form_spatialShip->get('moreHunter')->getData());
                $fleet->setHunter($fleet->getHunter() + abs($form_spatialShip->get('moreHunter')->getData()));
            } elseif (abs($form_spatialShip->get('lessHunter')->getData()) <= $fleet->getHunter()) {
                $hunter = $planetTake->getHunter() + abs($form_spatialShip->get('lessHunter')->getData());
                $fleet->setHunter($fleet->getHunter() - abs($form_spatialShip->get('lessHunter')->getData()));
            } else {
                $hunter = $planetTake->getHunter();
            }
            if (abs($form_spatialShip->get('moreHunterHeavy')->getData())) {
                $hunterHeavy = $planetTake->getHunterHeavy() - abs($form_spatialShip->get('moreHunterHeavy')->getData());
                $fleet->setHunterHeavy($fleet->getHunterHeavy() + abs($form_spatialShip->get('moreHunterHeavy')->getData()));
            } elseif (abs($form_spatialShip->get('lessHunterHeavy')->getData()) <= $fleet->getHunterHeavy()) {
                $hunterHeavy = $planetTake->getHunterHeavy() + abs($form_spatialShip->get('lessHunterHeavy')->getData());
                $fleet->setHunterHeavy($fleet->getHunterHeavy() - abs($form_spatialShip->get('lessHunterHeavy')->getData()));
            } else {
                $hunterHeavy = $planetTake->getHunterHeavy();
            }
            if (abs($form_spatialShip->get('moreHunterWar')->getData())) {
                $hunterWar = $planetTake->getHunterWar() - abs($form_spatialShip->get('moreHunterWar')->getData());
                $fleet->setHunterWar($fleet->getHunterWar() + abs($form_spatialShip->get('moreHunterWar')->getData()));
            } elseif (abs($form_spatialShip->get('lessHunterWar')->getData()) <= $fleet->getHunterWar()) {
                $hunterWar = $planetTake->getHunterWar() + abs($form_spatialShip->get('lessHunterWar')->getData());
                $fleet->setHunterWar($fleet->getHunterWar() - abs($form_spatialShip->get('lessHunterWar')->getData()));
            } else {
                $hunterWar = $planetTake->getHunterWar();
            }
            if (abs($form_spatialShip->get('moreCorvet')->getData())) {
                $corvet = $planetTake->getCorvet() - abs($form_spatialShip->get('moreCorvet')->getData());
                $fleet->setCorvet($fleet->getCorvet() + abs($form_spatialShip->get('moreCorvet')->getData()));
            } elseif (abs($form_spatialShip->get('lessCorvet')->getData()) <= $fleet->getCorvet()) {
                $corvet = $planetTake->getCorvet() + abs($form_spatialShip->get('lessCorvet')->getData());
                $fleet->setCorvet($fleet->getCorvet() - abs($form_spatialShip->get('lessCorvet')->getData()));
            } else {
                $corvet = $planetTake->getCorvet();
            }
            if (abs($form_spatialShip->get('moreCorvetLaser')->getData())) {
                $corvetLaser = $planetTake->getCorvetLaser() - abs($form_spatialShip->get('moreCorvetLaser')->getData());
                $fleet->setCorvetLaser($fleet->getCorvetLaser() + abs($form_spatialShip->get('moreCorvetLaser')->getData()));
            } elseif (abs($form_spatialShip->get('lessCorvetLaser')->getData()) <= $fleet->getCorvetLaser()) {
                $corvetLaser = $planetTake->getCorvetLaser() + abs($form_spatialShip->get('lessCorvetLaser')->getData());
                $fleet->setCorvetLaser($fleet->getCorvetLaser() - abs($form_spatialShip->get('lessCorvetLaser')->getData()));
            } else {
                $corvetLaser = $planetTake->getCorvetLaser();
            }
            if (abs($form_spatialShip->get('moreCorvetWar')->getData())) {
                $corvetWar = $planetTake->getCorvetWar() - abs($form_spatialShip->get('moreCorvetWar')->getData());
                $fleet->setCorvetWar($fleet->getCorvetWar() + abs($form_spatialShip->get('moreCorvetWar')->getData()));
            } elseif (abs($form_spatialShip->get('lessCorvetWar')->getData()) <= $fleet->getCorvetWar()) {
                $corvetWar = $planetTake->getCorvetWar() + abs($form_spatialShip->get('lessCorvetWar')->getData());
                $fleet->setCorvetWar($fleet->getCorvetWar() - abs($form_spatialShip->get('lessCorvetWar')->getData()));
            } else {
                $corvetWar = $planetTake->getCorvetLaser();
            }
            if (abs($form_spatialShip->get('moreFregate')->getData())) {
                $fregate = $planetTake->getFregate() - abs($form_spatialShip->get('moreFregate')->getData());
                $fleet->setFregate($fleet->getFregate() + abs($form_spatialShip->get('moreFregate')->getData()));
            } elseif (abs($form_spatialShip->get('lessFregate')->getData()) <= $fleet->getFregate()) {
                $fregate = $planetTake->getFregate() + abs($form_spatialShip->get('lessFregate')->getData());
                $fleet->setFregate($fleet->getFregate() - abs($form_spatialShip->get('lessFregate')->getData()));
            } else {
                $fregate = $planetTake->getFregate();
            }
            if (abs($form_spatialShip->get('moreFregatePlasma')->getData())) {
                $fregatePlasma = $planetTake->getFregatePlasma() - abs($form_spatialShip->get('moreFregatePlasma')->getData());
                $fleet->setFregatePlasma($fleet->getFregatePlasma() + abs($form_spatialShip->get('moreFregatePlasma')->getData()));
            } elseif (abs($form_spatialShip->get('lessFregatePlasma')->getData()) <= $fleet->getFregatePlasma()) {
                $fregatePlasma = $planetTake->getFregatePlasma() + abs($form_spatialShip->get('lessFregatePlasma')->getData());
                $fleet->setFregatePlasma($fleet->getFregatePlasma() - abs($form_spatialShip->get('lessFregatePlasma')->getData()));
            } else {
                $fregatePlasma = $planetTake->getFregatePlasma();
            }
            if (abs($form_spatialShip->get('moreCroiser')->getData())) {
                $croiser = $planetTake->getCroiser() - abs($form_spatialShip->get('moreCroiser')->getData());
                $fleet->setCroiser($fleet->getCroiser() + abs($form_spatialShip->get('moreCroiser')->getData()));
            } elseif (abs($form_spatialShip->get('lessCroiser')->getData()) <= $fleet->getCroiser()) {
                $croiser = $planetTake->getCroiser() + abs($form_spatialShip->get('lessCroiser')->getData());
                $fleet->setCroiser($fleet->getCroiser() - abs($form_spatialShip->get('lessCroiser')->getData()));
            } else {
                $croiser = $planetTake->getCroiser();
            }
            if (abs($form_spatialShip->get('moreIronClad')->getData())) {
                $ironClad = $planetTake->getIronClad() - abs($form_spatialShip->get('moreIronClad')->getData());
                $fleet->setIronClad($fleet->getIronClad() + abs($form_spatialShip->get('moreIronClad')->getData()));
            } elseif (abs($form_spatialShip->get('lessIronClad')->getData()) <= $fleet->getIronClad()) {
                $ironClad = $planetTake->getIronClad() + abs($form_spatialShip->get('lessIronClad')->getData());
                $fleet->setIronClad($fleet->getIronClad() - abs($form_spatialShip->get('lessIronClad')->getData()));
            } else {
                $ironClad = $planetTake->getIronClad();
            }
            if (abs($form_spatialShip->get('moreDestroyer')->getData())) {
                $destroyer = $planetTake->getDestroyer() - abs($form_spatialShip->get('moreDestroyer')->getData());
                $fleet->setDestroyer($fleet->getDestroyer() + abs($form_spatialShip->get('moreDestroyer')->getData()));
            } elseif (abs($form_spatialShip->get('lessDestroyer')->getData()) <= $fleet->getDestroyer()) {
                $destroyer = $planetTake->getDestroyer() + abs($form_spatialShip->get('lessDestroyer')->getData());
                $fleet->setDestroyer($fleet->getDestroyer() - abs($form_spatialShip->get('lessDestroyer')->getData()));
            } else {
                $destroyer = $planetTake->getDestroyer();
            }
            $cargoTotal = $fleet->getCargoPlace() - $fleet->getCargoFull();

            if (($colonizer < 0 || $recycleur < 0) || ($barge < 0 || $sonde < 0) || ($hunter < 0 || $fregate < 0) ||
                ($cargoI < 0) || ($cargoV < 0 || $cargoX < 0) || ($hunterHeavy < 0 || $corvet < 0) ||
                ($corvetLaser < 0 || $fregatePlasma < 0) || ($croiser < 0 || $ironClad < 0) || ($destroyer < 0 || $hunterWar < 0) ||
                ($corvetWar < 0 || $moonMaker < 0) || ($radarShip < 0 || $brouilleurShip < 0) || ($motherShip < 0 || $cargoTotal <= 0)) {
                return $this->redirectToRoute('ships_manage', ['fleet' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
            }

            if($fleet->getNbrShips() == 0) {
                $em->remove($fleet);
            }
            $planetTake->setCargoI($cargoI);
            $planetTake->setCargoV($cargoV);
            $planetTake->setCargoX($cargoX);
            $planetTake->setColonizer($colonizer);
            $planetTake->setRecycleur($recycleur);
            $planetTake->setBarge($barge);
            $planetTake->setColonizer($colonizer);
            $planetTake->setMoonMaker($moonMaker);
            $planetTake->setRadarShip($radarShip);
            $planetTake->setBrouilleurShip($brouilleurShip);
            $planetTake->setSonde($sonde);
            $planetTake->setHunter($hunter);
            $planetTake->setHunterHeavy($hunterHeavy);
            $planetTake->setHunterWar($hunterWar);
            $planetTake->setCorvet($corvet);
            $planetTake->setCorvetLaser($corvetLaser);
            $planetTake->setCorvetWar($corvetWar);
            $planetTake->setFregate($fregate);
            $planetTake->setFregatePlasma($fregatePlasma);
            $planetTake->setCroiser($croiser);
            $planetTake->setIronClad($ironClad);
            $planetTake->setDestroyer($destroyer);
            $planetTake->setSignature($planetTake->getNbrSignatures());

            $fleet->setSignature($fleet->getNbrSignatures());

            $em->flush();


            return $this->redirectToRoute('ships_manage', ['fleet' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
        }

        return $this->render('connected/fleet/ships.html.twig', [
            'usePlanet' => $usePlanet,
            'fleet' => $fleet,
            'form_spatialShip' => $form_spatialShip->createView(),
        ]);
    }

    /**
     * @Route("/scinder-flotte/{oldFleet}/{usePlanet}", name="fleet_split", requirements={"usePlanet"="\d+", "oldFleet"="\d+"})
     */
    public function splitFleetAction(Request $request, Planet $usePlanet, Fleet $oldFleet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if ($usePlanet->getUser() != $user || $oldFleet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if(count($user->getFleets()) >= 75) {
            $this->addFlash("fail", "Vous avez atteint la limite de flottes autorisées par l'Instance.");
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $oldFleet->getId(), 'usePlanet' => $usePlanet->getId()]);
        }

        $form_spatialShip = $this->createForm(FleetSplitType::class);
        $form_spatialShip->handleRequest($request);

        if ($form_spatialShip->isSubmitted() && $form_spatialShip->isValid()) {
            $cargoI = $oldFleet->getCargoI() - abs($form_spatialShip->get('cargoI')->getData());
            $cargoV = $oldFleet->getCargoV() - abs($form_spatialShip->get('cargoV')->getData());
            $cargoX = $oldFleet->getCargoX() - abs($form_spatialShip->get('cargoX')->getData());
            $colonizer = $oldFleet->getColonizer() - abs($form_spatialShip->get('colonizer')->getData());
            $recycleur = $oldFleet->getRecycleur() - abs($form_spatialShip->get('recycleur')->getData());
            $barge = $oldFleet->getBarge() - abs($form_spatialShip->get('barge')->getData());
            $moonMaker = $oldFleet->getMoonMaker() - abs($form_spatialShip->get('moonMaker')->getData());
            $radarShip = $oldFleet->getRadarShip() - abs($form_spatialShip->get('radarShip')->getData());
            $brouilleurShip = $oldFleet->getBrouilleurShip() - abs($form_spatialShip->get('brouilleurShip')->getData());
            $motherShip = $oldFleet->getMotherShip() - abs($form_spatialShip->get('motherShip')->getData());
            $sonde = $oldFleet->getSonde() - abs($form_spatialShip->get('sonde')->getData());
            $hunter = $oldFleet->getHunter() - abs($form_spatialShip->get('hunter')->getData());
            $fregate = $oldFleet->getFregate() - abs($form_spatialShip->get('fregate')->getData());
            $hunterHeavy = $oldFleet->getHunterHeavy() - abs($form_spatialShip->get('hunterHeavy')->getData());
            $hunterWar = $oldFleet->getHunterWar() - abs($form_spatialShip->get('hunterWar')->getData());
            $corvet = $oldFleet->getCorvet() - abs($form_spatialShip->get('corvet')->getData());
            $corvetLaser = $oldFleet->getCorvetLaser() - abs($form_spatialShip->get('corvetLaser')->getData());
            $corvetWar = $oldFleet->getCorvetWar() - abs($form_spatialShip->get('corvetWar')->getData());
            $fregatePlasma = $oldFleet->getFregatePlasma() - abs($form_spatialShip->get('fregatePlasma')->getData());
            $croiser = $oldFleet->getCroiser() - abs($form_spatialShip->get('croiser')->getData());
            $ironClad = $oldFleet->getIronClad() - abs($form_spatialShip->get('ironClad')->getData());
            $destroyer = $oldFleet->getDestroyer() - abs($form_spatialShip->get('destroyer')->getData());
            $niobium = $oldFleet->getNiobium() - abs($form_spatialShip->get('niobium')->getData());
            $water = $oldFleet->getWater() - abs($form_spatialShip->get('water')->getData());
            $uranium = $oldFleet->getUranium() - abs($form_spatialShip->get('uranium')->getData());
            $soldier = $oldFleet->getSoldier() - abs($form_spatialShip->get('soldier')->getData());
            $tank = $oldFleet->getTank() - abs($form_spatialShip->get('tank')->getData());
            $worker = $oldFleet->getWorker() - abs($form_spatialShip->get('worker')->getData());
            $scientist = $oldFleet->getScientist() - abs($form_spatialShip->get('scientist')->getData());
            $motherBonus = $motherShip > 0 ? 1.1 : 1;
            $total = $form_spatialShip->get('moonMaker')->getData() + $form_spatialShip->get('radarShip')->getData() + $form_spatialShip->get('brouilleurShip')->getData() + $form_spatialShip->get('motherShip')->getData() + $form_spatialShip->get('corvetWar')->getData() + $form_spatialShip->get('hunterWar')->getData() + $form_spatialShip->get('cargoI')->getData() + $form_spatialShip->get('cargoV')->getData() + $form_spatialShip->get('cargoX')->getData() + $form_spatialShip->get('hunterHeavy')->getData() + $form_spatialShip->get('corvet')->getData() + $form_spatialShip->get('corvetLaser')->getData() + $form_spatialShip->get('fregatePlasma')->getData() + $form_spatialShip->get('croiser')->getData() + $form_spatialShip->get('ironClad')->getData() + $form_spatialShip->get('destroyer')->getData() + $form_spatialShip->get('colonizer')->getData() + $form_spatialShip->get('fregate')->getData() + $form_spatialShip->get('hunter')->getData() + $form_spatialShip->get('sonde')->getData() + $form_spatialShip->get('barge')->getData() + $form_spatialShip->get('recycleur')->getData();
            $cargoTotal = ((($hunterHeavy * 35) + ($fregate * 250) + ($cargoI * 25000) + ($cargoV * 100000) + ($cargoX * 250000) + ($barge * 2500) + ($recycleur * 10000)) * $motherBonus) - $oldFleet->getCargoFull();

            if (($colonizer < 0 || $recycleur < 0) || ($barge < 0 || $sonde < 0) || ($hunter < 0 || $fregate < 0) ||
                ($total == 0 || $cargoI < 0) || ($cargoV < 0 || $cargoX < 0) || ($hunterHeavy < 0 || $corvet < 0) ||
                ($corvetLaser < 0 || $fregatePlasma < 0) || ($croiser < 0 || $ironClad < 0) || ($destroyer < 0 || $hunterWar < 0) ||
                ($corvetWar < 0 || $moonMaker < 0) || ($radarShip < 0 || $brouilleurShip < 0) || ($motherShip < 0 || $cargoTotal < 0) ||
                ($niobium < 0 || $water < 0) || ($uranium < 0 || $soldier < 0) || ($tank < 0 || $worker < 0) || $scientist < 0) {
                return $this->redirectToRoute('manage_fleet', ['fleetGive' => $oldFleet->getId(), 'usePlanet' => $usePlanet->getId()]);
            }
            $eAlly = $user->getAllyEnnemy();
            $warAlly = [];
            $x = 0;
            foreach ($eAlly as $tmp) {
                $warAlly[$x] = $tmp->getAllyTag();
                $x++;
            }
            $fleets = $em->getRepository('App:Fleet')
                ->createQueryBuilder('f')
                ->join('f.user', 'u')
                ->leftJoin('u.ally', 'a')
                ->where('f.planet = :planet')
                ->andWhere('f.attack = true OR a.sigle in (:ally)')
                ->andWhere('f.user != :user')
                ->andWhere('f.flightTime is null')
                ->setParameters(['planet' => $oldFleet->getPlanet(), 'ally' => $warAlly, 'user' => $user])
                ->getQuery()
                ->getResult();

            $fleet = new Fleet();
            $fleet->setCargoI($form_spatialShip->get('cargoI')->getData());
            $fleet->setCargoV($form_spatialShip->get('cargoV')->getData());
            $fleet->setCargoX($form_spatialShip->get('cargoX')->getData());
            $fleet->setColonizer($form_spatialShip->get('colonizer')->getData());
            $fleet->setRecycleur($form_spatialShip->get('recycleur')->getData());
            $fleet->setBarge($form_spatialShip->get('barge')->getData());
            $fleet->setMoonMaker($form_spatialShip->get('moonMaker')->getData());
            $fleet->setRadarShip($form_spatialShip->get('radarShip')->getData());
            $fleet->setBrouilleurShip($form_spatialShip->get('brouilleurShip')->getData());
            $fleet->setMotherShip($form_spatialShip->get('motherShip')->getData());
            $fleet->setSonde($form_spatialShip->get('sonde')->getData());
            $fleet->setHunter($form_spatialShip->get('hunter')->getData());
            $fleet->setFregate($form_spatialShip->get('fregate')->getData());
            $fleet->setHunterHeavy($form_spatialShip->get('hunterHeavy')->getData());
            $fleet->setHunterWar($form_spatialShip->get('hunterWar')->getData());
            $fleet->setCorvet($form_spatialShip->get('corvet')->getData());
            $fleet->setCorvetLaser($form_spatialShip->get('corvetLaser')->getData());
            $fleet->setCorvetWar($form_spatialShip->get('corvetWar')->getData());
            $fleet->setFregatePlasma($form_spatialShip->get('fregatePlasma')->getData());
            $fleet->setCroiser($form_spatialShip->get('croiser')->getData());
            $fleet->setIronClad($form_spatialShip->get('ironClad')->getData());
            $fleet->setDestroyer($form_spatialShip->get('destroyer')->getData());
            $fleet->setNiobium($form_spatialShip->get('niobium')->getData());
            $fleet->setWater($form_spatialShip->get('water')->getData());
            $fleet->setUranium($form_spatialShip->get('uranium')->getData());
            $fleet->setSoldier($form_spatialShip->get('soldier')->getData());
            $fleet->setTank($form_spatialShip->get('tank')->getData());
            $fleet->setWorker($form_spatialShip->get('worker')->getData());
            $fleet->setScientist($form_spatialShip->get('scientist')->getData());
            $fleet->setSignature($fleet->getNbrSignatures());
            if($fleets) {
                $allFleets = $em->getRepository('App:Fleet')
                    ->createQueryBuilder('f')
                    ->join('f.user', 'u')
                    ->where('f.planet = :planet')
                    ->andWhere('f.user != :user')
                    ->setParameters(['planet' => $oldFleet->getPlanet()])
                    ->getQuery()
                    ->getResult();
                $now = new DateTime();
                $now->setTimezone(new DateTimeZone('Europe/Paris'));
                $now->add(new DateInterval('PT' . 300 . 'S'));
                foreach ($allFleets as $updateF) {
                    $updateF->setFightAt($now);
                }
                $fleet->setFightAt($now);
            }
            $fleet->setUser($user);
            $fleet->setPlanet($oldFleet->getPlanet());
            $fleet->setName($form_spatialShip->get('name')->getData());
            $em->persist($fleet);
            $oldFleet->setCargoI($cargoI);
            $oldFleet->setCargoV($cargoV);
            $oldFleet->setCargoX($cargoX);
            $oldFleet->setColonizer($colonizer);
            $oldFleet->setRecycleur($recycleur);
            $oldFleet->setBarge($barge);
            $oldFleet->setMoonMaker($moonMaker);
            $oldFleet->setRadarShip($radarShip);
            $oldFleet->setBrouilleurShip($brouilleurShip);
            $oldFleet->setMotherShip($motherShip);
            $oldFleet->setSonde($sonde);
            $oldFleet->setHunter($hunter);
            $oldFleet->setFregate($fregate);
            $oldFleet->setHunterHeavy($hunterHeavy);
            $oldFleet->setHunterWar($hunterWar);
            $oldFleet->setCorvet($corvet);
            $oldFleet->setCorvetLaser($corvetLaser);
            $oldFleet->setCorvetWar($corvetWar);
            $oldFleet->setFregatePlasma($fregatePlasma);
            $oldFleet->setCroiser($croiser);
            $oldFleet->setIronClad($ironClad);
            $oldFleet->setDestroyer($destroyer);
            $oldFleet->setNiobium($niobium);
            $oldFleet->setWater($water);
            $oldFleet->setUranium($uranium);
            $oldFleet->setSoldier($soldier);
            $oldFleet->setTank($tank);
            $oldFleet->setWorker($worker);
            $oldFleet->setScientist($scientist);
            $oldFleet->setSignature($oldFleet->getNbrSignatures());
            if ($oldFleet->getNbrSignatures() == 0) {
                $em->remove($oldFleet);
            }

            $em->flush();

            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
        }

        return $this->render('connected/fleet/split.html.twig', [
            'usePlanet' => $usePlanet,
            'oldFleet' => $oldFleet,
            'form_spatialShip' => $form_spatialShip->createView(),
        ]);
    }

    /**
     * @Route("/annuler-flotte/{fleet}/{usePlanet}", name="cancel_fleet", requirements={"usePlanet"="\d+", "fleet"="\d+"})
     */
    public function cancelFleetAction(Planet $usePlanet, Fleet $fleet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($fleet->getCancelFlight() > $now) {
            $fleet->setFlightTime(null);
            $em->remove($fleet->getDestination());

            $em->flush();
        }

        if ($fleet->getFleetList()) {
            return $this->redirectToRoute('fleet_list', ['usePlanet' => $usePlanet->getId()]);
        } else {
            return $this->redirectToRoute('fleet', ['usePlanet' => $usePlanet->getId()]);
        }
    }

    /**
     * @Route("/abandonner-flotte/{fleet}/{usePlanet}", name="abandon_fleet", requirements={"usePlanet"="\d+", "fleet"="\d+"})
     */
    public function abandonFleetAction(Planet $usePlanet, Fleet $fleet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($fleet->getFlightTime() == null and $fleet->getFightAt() == null) {
            $zombie = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);
            $fleet->setFleetList(null);
            $fleet->setUser($zombie);
            $em->flush();
        }

        return $this->redirectToRoute('fleet', ['usePlanet' => $usePlanet->getId()]);
    }
}