<?php

namespace App\Controller\Connected;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\UserImageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use DateTime;
use Dateinterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class OverviewController extends AbstractController
{
    /**
     * @Route("/empire/{usePlanet}", name="overview", requirements={"usePlanet"="\d+"})
     */
    public function overviewAction(Request $request, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $server = $em->getRepository('App:Server')->find(['id' => 1]);

        if($user->getGameOver() || $user->getAllPlanets() == 0) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $fleets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->where('f.flightTime < :now')
            ->andWhere('f.flightType != :six or f.flightType is null')
            ->andWhere('f.user = :user or p.user = :user')
            ->setParameters(['now' => $now, 'six' => 6, 'user' => $user])
            ->getQuery()
            ->getResult();

        if ($fleets) {
            $this->forward('App\Controller\Connected\Execute\MoveFleetController::centralizeFleetAction', [
                'fleets'  => $fleets,
                'server' => $server,
                'now'  => $now,
                'em'  => $em
            ]);
        }

        $planets = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.constructAt < :now')
            ->andWhere('p.user = :user')
            ->setParameters(['now' => $now, 'user' => $user])
            ->getQuery()
            ->getResult();

        if ($planets) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::buildingsAction', [
                'planets'  => $planets,
                'now' => $now,
                'em' => $em
            ]);
        }

        $planetSoldiers = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.soldierAt < :now')
            ->andWhere('p.user = :user')
            ->setParameters(['now' => $now, 'user' => $user])
            ->getQuery()
            ->getResult();

        if ($planetSoldiers) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::soldiersAction', [
                'planetSoldiers'  => $planetSoldiers,
                'em'  => $em
            ]);
        }

        $planetTanks = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.tankAt < :now')
            ->andWhere('p.user = :user')
            ->setParameters(['now' => $now, 'user' => $user])
            ->getQuery()
            ->getResult();

        if ($planetTanks) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::tanksAction', [
                'planetTanks'  => $planetTanks,
                'em'  => $em
            ]);
        }

        $planetScientists = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.scientistAt < :now')
            ->andWhere('p.user = :user')
            ->setParameters(['now' => $now, 'user' => $user])
            ->getQuery()
            ->getResult();

        if ($planetScientists) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::scientistsAction', [
                'planetScientists'  => $planetScientists,
                'em'  => $em
            ]);
        }

        $products = $em->getRepository('App:Product')
            ->createQueryBuilder('p')
            ->join('p.planet', 'pp')
            ->where('p.productAt < :now')
            ->andWhere('pp.user = :user')
            ->setParameters(['now' => $now, 'user' => $user])
            ->getQuery()
            ->getResult();

        if ($products) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::productsAction', [
                'products'  => $products,
                'em' => $em
            ]);
        }

        $seconds = $this->forward('App\Controller\Connected\Execute\ChronosController::userActivityAction', [
            'now'  => $now,
            'em' => $em]);

        if ($seconds->getContent() >= 60) {
            $this->forward('App\Controller\Connected\Execute\PlanetsGenController::planetsGenAction', [
                'seconds' => $seconds->getContent(),
                'now'  => $now,
                'em' => $em]);
        }

        if ($user->getTutorial() == 53) {
            $user->setTutorial(60);
            $em->flush();
        }

        $allTroops = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.planets', 'p')
            ->leftJoin('p.product', 'pp')
            ->select('sum(p.soldier) as soldier, sum(p.soldierAtNbr) as soldierAtNbr, sum(p.tank) as tank, sum(p.tankAtNbr) as tankAtNbr, sum(p.scientist) as scientist, sum(p.scientistAtNbr) as scientistAtNbr, sum(DISTINCT p.signature) as psignature, sum(pp.signature) as ppsignature')
            ->where('p.user = :user')
            ->setParameters(['user' => $user])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $allFleets = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->leftJoin('u.fleets', 'f')
            ->select('sum(f.soldier) as fsoldier, sum(f.scientist) as fscientist, sum(f.tank) as ftank, sum(f.signature) as fsignature')
            ->where('f.user = :user')
            ->setParameters(['user' => $user])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $allShipsProduct = $allTroops['ppsignature'] / 12;
        $allShipsPlanet = round($allTroops['psignature'] / 12);
        $allShipsFleet = $allFleets['fsignature'] / 2;
        $allShips = $allShipsProduct + $allShipsPlanet + $allShipsFleet;
        $allTroopsProduct = $user->getPriceTroopsProduct($allTroops);
        $allTroopsPlanet = $user->getPriceTroopsPlanet($allTroops);
        $allTroopsFleet = $user->getPriceTroopsFleet($allFleets);
        $allTroops = $allTroopsProduct + $allTroopsPlanet + $allTroopsFleet;

        $allBuildings = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.planets', 'p')
            ->select('sum(p.miner * 1) as miner, sum(p.extractor * 1) as extractor, sum(p.aeroponicFarm * 2) as aeroponicFarm, sum(p.farm * 1) as farm, sum(p.silos * 30) as silos, sum(p.niobiumStock * 30) as niobiumStock, sum(p.waterStock * 30) as waterStock, sum(p.caserne * 66) as caserne, sum(p.bunker * 800) as bunker, sum(p.centerSearch * 53) as centerSearch, sum(p.city * 13) as city, sum(p.metropole * 26) as metropole, sum(p.lightUsine * 333) as lightUsine, sum(p.heavyUsine * 666) as heavyUsine, sum(p.spaceShip * 100) as spaceShip, sum(p.radar * 13) as radar, sum(p.skyRadar * 133) as skyRadar, sum(p.skyBrouilleur * 400) as skyBrouilleur, sum(p.nuclearBase * 3333) as nuclearBase, sum(p.orbital * 333) as orbital, sum(p.island * 333) as island, sum(p.worker) as worker, sum(p.workerProduction) as workerProd')
            ->where('p.user = :user')
            ->setParameters(['user' => $user])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $allWorkers = $allBuildings['worker'];
        $allWorkersProd = $allBuildings['workerProd'] * 60;
        $allBuilding = $allBuildings['centerSearch'] + $allBuildings['miner'] + $allBuildings['extractor'] + $allBuildings['niobiumStock'] + $allBuildings['waterStock'] + $allBuildings['city'] + $allBuildings['metropole'] + $allBuildings['bunker'] + $allBuildings['caserne'] + $allBuildings['spaceShip'] + $allBuildings['lightUsine'] + $allBuildings['heavyUsine'] + $allBuildings['radar'] + $allBuildings['skyRadar'] + $allBuildings['skyBrouilleur'] + $allBuildings['nuclearBase'] + $allBuildings['orbital'] + $allBuildings['island'];

        if ($user->getPoliticWorker() > 0) {
            $allWorkersProd = $allWorkersProd * (1 + ($user->getPoliticWorker() / 5));
        }

        $attackFleets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.user', 'u')
            ->leftJoin('u.ally', 'a')
            ->join('f.planet', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->join('dp.sector', 'ds')
            ->join('ds.galaxy', 'dg')
            ->select('f.attack, f.name, f.signature, p.name as pName, p.position as position, p.skyBrouilleur, s.position as sector, g.position as galaxy, s.id as idSector, g.id as idGalaxy, dp.name as dName, dp.position as dPosition, ds.position as dSector, dg.position as dGalaxy, ds.id as dIdSector, dg.id as dIdGalaxy, f.flightTime, u.id as user, a.sigle as sigle, u.username as username')
            ->where('f.user != :user')
            ->andWhere('dp.user = :user')
            ->setParameters(['user' => $user])
            ->orderBy('f.flightTime')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();


        $oneHour = new DateTime();
        $oneHour->add(new DateInterval('PT' . 3600 . 'S'));
        $fleetMove = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->join('dp.sector', 'ds')
            ->join('ds.galaxy', 'dg')
            ->select('f.id, f.attack, f.name, f.signature, p.name as pName, p.position as position, s.position as sector, g.position as galaxy, s.id as idSector, g.id as idGalaxy, dp.name as dName, dp.position as dPosition, ds.position as dSector, dg.position as dGalaxy, ds.id as dIdSector, dg.id as dIdGalaxy, f.flightTime')
            ->where('f.user = :user')
            ->andWhere('f.flightTime < :time')
            ->setParameters(['user' => $user, 'time' => $oneHour])
            ->orderBy('f.flightTime')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();


        if ($user->getOrderPlanet() == 'alpha') {
            $crit = 'p.name';
        } elseif ($user->getOrderPlanet() == 'colo') {
            $crit = 'p.nbColo';
        } else {
            $crit = 'p.id';
        }
        $order = 'ASC';

        $myPlanets = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->leftJoin('p.constructions', 'c')
            ->select('p.name, p.id, p.sky, p.skyPlace, p.ground, p.groundPlace, p.construct, p.constructAt, count(c.construct) as nbrConstruct, p.moon, p.construct')
            ->groupBy('p.id')
            ->where('p.user = :user')
            ->setParameters(['user' => $user])
            ->orderBy($crit, $order)
            ->getQuery()
            ->getResult();

        if (count($fleetMove) == 0) {
            $fleetMove = null;
        }

        $user = $this->getUser();
        $form_image = $this->createForm(UserImageType::class,$user);
        $form_image->handleRequest($request);

        if ($form_image->isSubmitted()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $quest = $user->checkQuests('logo');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
            $em->flush();
        }

        return $this->render('connected/overview.html.twig', [
            'form_image' => $form_image->createView(),
            'usePlanet' => $usePlanet,
            'date' => $now,
            'attackFleets' => $attackFleets,
            'fleetMove' => $fleetMove,
            'allTroops' => $allTroops,
            'allShips' => $allShips,
            'allBuildings' => $allBuilding,
            'allWorkers' => $allWorkers,
            'allShipsProduct' => $allShipsProduct,
            'allShipsPlanet' => $allShipsPlanet,
            'allShipsFleet' => $allShipsFleet,
            'allTroopsProduct' => $allTroopsProduct,
            'allTroopsPlanet' => $allTroopsPlanet,
            'allTroopsFleet' => $allTroopsFleet,
            'myPlanets' => $myPlanets,
            'allWorkersProd' => $allWorkersProd
        ]);
    }

    /**
     * @Route("/game-over/", name="game_over")
     */
    public function gameOverAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->add(new DateInterval('PT' . 172800 . 'S'));
        if($user->getGameOver() || $user->getAllPlanets() == 0) {
            if($user->getColPlanets() == 0 && $user->getGameOver() == null) {
                $user->setGameOver($user->getUserName());

                $em->flush();
            }
            if($user->getRank()) {

                foreach ($user->getFleetLists() as $list) {
                    foreach ($list->getFleets() as $fleetL) {
                        $fleetL->setFleetList(null);
                    }
                    $em->remove($list);
                }
                $ship = $user->getShip();
                if ($ship) {
                    $user->setShip(null);
                    $em->remove($ship);
                }
                $user->setBitcoin(25000);
                $user->setSearch(null);
                if ($user->getRank()) {
                    $em->remove($user->getRank());
                }
                $user->setRank(null);
                $user->setJoinAllyAt(null);
                $user->setAllyBan(null);
                $user->setScientistProduction(1);
                $user->setSearchAt(null);
                $user->setDemography(0);
                $user->setUtility(0);
                $user->setArmement(0);
                $user->setIndustry(0);
                $user->setTerraformation(round($user->getTerraformation(0) / 2));
                $user->setPlasma(0);
                $user->setLaser(0);
                $user->setMissile(0);
                $user->setRecycleur(0);
                $user->setCargo(0);
                $user->setBarge(0);
                $user->setHyperespace(0);
                $user->setDiscipline(0);
                $user->setHeavyShip(0);
                $user->setLightShip(0);
                $user->setOnde(0);
                $user->setHyperespace(0);
                $user->setDiscipline(0);
                $user->setBarbed(0);
                $user->setAeroponicFarm(0);
                $user->setTank(0);
                $user->setExpansion(0);
                $user->setPoliticArmement(0);
                $user->setPoliticCostScientist(0);
                $user->setPoliticArmor(0);
                $user->setPoliticBarge(0);
                $user->setPoliticCargo(0);
                $user->setPoliticColonisation(0);
                $user->setPoliticCostSoldier(0);
                $user->setPoliticCostTank(0);
                $user->setPoliticInvade(0);
                $user->setPoliticMerchant(0);
                $user->setPoliticPdg(0);
                $user->setPoliticProd(0);
                $user->setPoliticRecycleur(0);
                $user->setPoliticSearch(0);
                $user->setPoliticSoldierAtt(0);
                $user->setPoliticSoldierSale(0);
                $user->setPoliticTankDef(0);
                $user->setPoliticWorker(0);
                $user->setPoliticWorkerDef(0);
                $user->setZombieAtt(1);
                if ($user->getAlly()) {
                    $ally = $user->getAlly();
                    if (count($ally->getUsers()) == 1 || ($ally->getPolitic() == 'fascism' && $user->getGrade()->getPlacement() == 1)) {
                        foreach ($ally->getUsers() as $user) {
                        $user->setAlly(null);
                        $user->setGrade(null);
                        $user->setAllyBan($now);
                    }
                        foreach ($ally->getFleets() as $fleet) {
                            $fleet->setAlly(null);
                        }
                        foreach ($ally->getGrades() as $grade) {
                            $em->remove($grade);
                        }
                        foreach ($ally->getSalons() as $salon) {
                            foreach ($salon->getContents() as $content) {
                                $em->remove($content);
                            }
                            foreach ($salon->getViews() as $view) {
                                $em->remove($view);
                            }
                            $em->remove($salon);
                        }
                        foreach ($ally->getExchanges() as $exchange) {
                            $em->remove($exchange);
                        }

                        foreach ($ally->getPnas() as $pna) {
                            $em->remove($pna);
                        }

                        foreach ($ally->getWars() as $war) {
                            $em->remove($war);
                        }

                        foreach ($ally->getAllieds() as $allied) {
                            $em->remove($allied);
                        }

                        foreach ($ally->getProposals() as $proposal) {
                            $em->remove($proposal);
                        }
                        $em->flush();

                        $pnas = $em->getRepository('App:Pna')
                            ->createQueryBuilder('p')
                            ->where('p.allyTag = :allytag')
                            ->setParameters(['allytag' => $ally->getSigle()])
                            ->getQuery()
                            ->getResult();

                        $pacts = $em->getRepository('App:Allied')
                            ->createQueryBuilder('a')
                            ->where('a.allyTag = :allytag')
                            ->setParameters(['allytag' => $ally->getSigle()])
                            ->getQuery()
                            ->getResult();

                        $wars = $em->getRepository('App:War')
                            ->createQueryBuilder('w')
                            ->where('w.allyTag = :allytag')
                            ->setParameters(['allytag' => $ally->getSigle()])
                            ->getQuery()
                            ->getResult();

                        foreach ($pnas as $pna) {
                            $em->remove($pna);
                        }

                        foreach ($pacts as $pact) {
                            $em->remove($pact);
                        }

                        foreach ($wars as $war) {
                            $em->remove($war);
                        }

                        $ally->setImageName(null);
                        $em->remove($ally);
                    }
                }
                $user->setAlly(null);
                $user->setGrade(null);

                foreach ($user->getSalons() as $salon) {
                    $salon->removeUser($user);
                }

                $salon = $em->getRepository('App:Salon')
                    ->createQueryBuilder('s')
                    ->where('s.name = :name')
                    ->setParameters(['name' => 'Public'])
                    ->getQuery()
                    ->getOneOrNullResult();

                $salon->removeUser($user);
                $user->setSalons(null);

                $em->flush();
            }
            $servers = $em->getRepository('App:Server')
                ->createQueryBuilder('s')
                ->select('s.id, s.open, s.pvp')
                ->groupBy('s.id')
                ->orderBy('s.id', 'ASC')
                ->getQuery()
                ->getResult();

            $galaxys = $em->getRepository('App:Galaxy')
                ->createQueryBuilder('g')
                ->join('g.server', 'ss')
                ->join('g.sectors', 's')
                ->join('s.planets', 'p')
                ->leftJoin('p.user', 'u')
                ->select('g.id, g.position, count(DISTINCT u.id) as users, ss.id as server')
                ->groupBy('g.id')
                ->orderBy('g.position', 'ASC')
                ->getQuery()
                ->getResult();

            return $this->render('connected/game_over.html.twig', [
                'galaxys' => $galaxys,
                'servers' => $servers
            ]);
        } else {
            return $this->redirectToRoute('home');
        }
    }
}