<?php

namespace App\Controller\Connected;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     */
    public function overviewAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $server = $usePlanet->getSector()->getGalaxy()->getServer();
        $commander = $user->getCommander($server);
        $now = new DateTime();

        if($commander->getGameOver() || $commander->getAllPlanets() == 0) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $fleets = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->where('f.flightTime < :now')
            ->andWhere('f.flightType != :six or f.flightType is null')
            ->andWhere('f.commander = :commander or p.commander = :commander')
            ->setParameters(['now' => $now, 'six' => 6, 'commander' => $commander])
            ->getQuery()
            ->getResult();

        if ($fleets) {
            $this->forward('App\Controller\Connected\Execute\MoveFleetController::centralizeFleetAction', [
                'fleets'  => $fleets,
                'now'  => $now,
                'em'  => $em
            ]);
        }

        $planets = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->where('p.constructAt < :now')
            ->andWhere('p.commander = :commander')
            ->setParameters(['now' => $now, 'commander' => $commander])
            ->getQuery()
            ->getResult();

        if ($planets) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::buildingsAction', [
                'planets'  => $planets,
                'now' => $now,
                'em' => $em
            ]);
        }

        $planetSoldiers = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->where('p.soldierAt < :now')
            ->andWhere('p.commander = :commander')
            ->setParameters(['now' => $now, 'commander' => $commander])
            ->getQuery()
            ->getResult();

        if ($planetSoldiers) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::soldiersAction', [
                'planetSoldiers'  => $planetSoldiers,
                'em'  => $em
            ]);
        }

        $planetTanks = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->where('p.tankAt < :now')
            ->andWhere('p.commander = :commander')
            ->setParameters(['now' => $now, 'commander' => $commander])
            ->getQuery()
            ->getResult();

        if ($planetTanks) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::tanksAction', [
                'planetTanks'  => $planetTanks,
                'em'  => $em
            ]);
        }

        $planetScientists = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->where('p.scientistAt < :now')
            ->andWhere('p.commander = :commander')
            ->setParameters(['now' => $now, 'commander' => $commander])
            ->getQuery()
            ->getResult();

        if ($planetScientists) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::scientistsAction', [
                'planetScientists'  => $planetScientists,
                'em'  => $em
            ]);
        }

        $products = $doctrine->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->join('p.planet', 'pp')
            ->where('p.productAt < :now')
            ->andWhere('pp.commander = :commander')
            ->setParameters(['now' => $now, 'commander' => $commander])
            ->getQuery()
            ->getResult();

        if ($products) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::productsAction', [
                'products'  => $products,
                'em' => $em
            ]);
        }

        $seconds = $this->forward('App\Controller\Connected\Execute\ChronosController::userActivityAction', [
            'commander' => $commander,
            'now'  => $now,
            'em' => $em]);

        if ($seconds->getContent() >= 60) {
            $this->forward('App\Controller\Connected\Execute\PlanetsGenController::planetsGenAction', [
                'commander' => $commander,
                'seconds' => $seconds->getContent(),
                'now'  => $now,
                'em' => $em]);
        }

        if ($user->getTutorial() == 53) {
            $user->setTutorial(60);
            $em->flush();
        }

        $allTroops = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->join('c.planets', 'p')
            ->leftJoin('p.product', 'pp')
            ->select('sum(p.soldier) as soldier, sum(p.soldierAtNbr) as soldierAtNbr, sum(p.tank) as tank, sum(p.tankAtNbr) as tankAtNbr, sum(p.scientist) as scientist, sum(p.scientistAtNbr) as scientistAtNbr, sum(DISTINCT p.signature) as psignature, sum(pp.signature) as ppsignature')
            ->where('p.commander = :commander')
            ->setParameters(['commander' => $commander])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $allFleets = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->leftJoin('c.fleets', 'f')
            ->select('sum(f.soldier) as fsoldier, sum(f.scientist) as fscientist, sum(f.tank) as ftank, sum(f.signature) as fsignature')
            ->where('f.commander = :commander')
            ->setParameters(['commander' => $commander])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $allShipsProduct = $allTroops['ppsignature'] / 12;
        $allShipsPlanet = round($allTroops['psignature'] / 12);
        $allShipsFleet = $allFleets['fsignature'] / 2;
        $allShips = $allShipsProduct + $allShipsPlanet + $allShipsFleet;
        $allTroopsProduct = $commander->getPriceTroopsProduct($allTroops);
        $allTroopsPlanet = $commander->getPriceTroopsPlanet($allTroops);
        $allTroopsFleet = $commander->getPriceTroopsFleet($allFleets);
        $allTroops = $allTroopsProduct + $allTroopsPlanet + $allTroopsFleet;

        $allBuildings = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->join('c.planets', 'p')
            ->select('sum(p.miner * 1) as miner, sum(p.extractor * 1) as extractor, sum(p.aeroponicFarm * 2) as aeroponicFarm, sum(p.farm * 1) as farm, sum(p.silos * 30) as silos, sum(p.niobiumStock * 30) as niobiumStock, sum(p.waterStock * 30) as waterStock, sum(p.caserne * 66) as caserne, sum(p.bunker * 800) as bunker, sum(p.centerSearch * 53) as centerSearch, sum(p.city * 13) as city, sum(p.metropole * 26) as metropole, sum(p.lightUsine * 333) as lightUsine, sum(p.heavyUsine * 666) as heavyUsine, sum(p.spaceShip * 100) as spaceShip, sum(p.radar * 13) as radar, sum(p.skyRadar * 133) as skyRadar, sum(p.skyBrouilleur * 400) as skyBrouilleur, sum(p.nuclearBase * 3333) as nuclearBase, sum(p.orbital * 333) as orbital, sum(p.island * 333) as island, sum(p.worker) as worker, sum(p.workerProduction) as workerProd')
            ->where('p.commander = :commander')
            ->setParameters(['commander' => $commander])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $allWorkers = $allBuildings['worker'];
        $allWorkersProd = $allBuildings['workerProd'] * 60;
        $allBuilding = $allBuildings['centerSearch'] + $allBuildings['miner'] + $allBuildings['extractor'] + $allBuildings['niobiumStock'] + $allBuildings['waterStock'] + $allBuildings['city'] + $allBuildings['metropole'] + $allBuildings['bunker'] + $allBuildings['caserne'] + $allBuildings['spaceShip'] + $allBuildings['lightUsine'] + $allBuildings['heavyUsine'] + $allBuildings['radar'] + $allBuildings['skyRadar'] + $allBuildings['skyBrouilleur'] + $allBuildings['nuclearBase'] + $allBuildings['orbital'] + $allBuildings['island'];

        if ($commander->getPoliticWorker() > 0) {
            $allWorkersProd = $allWorkersProd * (1 + ($commander->getPoliticWorker() / 5));
        }

        $attackFleets = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->join('f.commander', 'c')
            ->leftJoin('c.ally', 'a')
            ->join('f.planet', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->join('dp.sector', 'ds')
            ->join('ds.galaxy', 'dg')
            ->select('f.attack, f.name, f.signature, p.name as pName, p.position as position, p.skyBrouilleur, s.position as sector, g.position as galaxy, s.id as idSector, g.id as idGalaxy, dp.name as dName, dp.position as dPosition, ds.position as dSector, dg.position as dGalaxy, ds.id as dIdSector, dg.id as dIdGalaxy, f.flightTime, c.id as commander, a.sigle as sigle, c.username as username')
            ->where('f.commander != :commander')
            ->andWhere('dp.commander = :commander')
            ->setParameters(['commander' => $commander])
            ->orderBy('f.flightTime')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();

        $oneHour = new DateTime();
        $oneHour->add(new DateInterval('PT' . 3600 . 'S'));
        $fleetMove = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->join('dp.sector', 'ds')
            ->join('ds.galaxy', 'dg')
            ->select('f.id, f.attack, f.name, f.signature, p.name as pName, p.position as position, s.position as sector, g.position as galaxy, s.id as idSector, g.id as idGalaxy, dp.name as dName, dp.position as dPosition, ds.position as dSector, dg.position as dGalaxy, ds.id as dIdSector, dg.id as dIdGalaxy, f.flightTime')
            ->where('f.commander = :commander')
            ->andWhere('f.flightTime < :time')
            ->setParameters(['commander' => $commander, 'time' => $oneHour])
            ->orderBy('f.flightTime')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();

        if ($commander->getOrderPlanet() == 'alpha') {
            $crit = 'p.name';
        } elseif ($commander->getOrderPlanet() == 'colo') {
            $crit = 'p.nbColo';
        } else {
            $crit = 'p.id';
        }
        $order = 'ASC';

        $myPlanets = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.constructions', 'c')
            ->select('p.name, p.id, p.sky, p.skyPlace, p.ground, p.groundPlace, p.construct, p.constructAt, count(c.construct) as nbrConstruct, p.moon, p.construct')
            ->groupBy('p.id')
            ->where('p.commander = :commander')
            ->setParameters(['commander' => $commander])
            ->orderBy($crit, $order)
            ->getQuery()
            ->getResult();

        if (count($fleetMove) == 0) {
            $fleetMove = null;
        }

        $form_image = $this->createForm(UserImageType::class,$commander);
        $form_image->handleRequest($request);

        if ($form_image->isSubmitted()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $quest = $commander->checkQuests('logo');
            if($quest) {
                $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + $quest->getGain());
                $commander->removeQuest($quest);
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
    public function gameOverAction(ManagerRegistry $doctrine): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getMainCommander();

        $now = new DateTime();
        $now->add(new DateInterval('PT' . 172800 . 'S'));
        if(($user->getId() === 220 && $user->getMainCommander()) || ($commander && ($commander->getGameOver() || $commander->getAllPlanets() == 0))) {
            if($commander->getColPlanets() == 0 && $commander->getGameOver() == null) {
                $commander->setGameOver($commander->getUsername());

                $em->flush();
            }
            if($commander->getRank()) {

                foreach ($commander->getFleetLists() as $list) {
                    foreach ($list->getFleets() as $fleetL) {
                        $fleetL->setFleetList(null);
                    }
                    $em->remove($list);
                }
                $ship = $commander->getShip();
                if ($ship) {
                    $commander->setShip(null);
                    $em->remove($ship);
                }
                $commander->setBitcoin(5000);
                $commander->setSearch(null);
                if ($commander->getRank()) {
                    $em->remove($commander->getRank());
                }
                $commander->setRank(null);
                $commander->setJoinAllyAt(null);
                $commander->setAllyBan(null);
                $commander->setScientistProduction(1);
                $commander->setSearchAt(null);
                $commander->setDemography(0);
                $commander->setUtility(0);
                $commander->setArmement(0);
                $commander->setIndustry(0);
                $commander->setTerraformation(round($commander->getTerraformation(0) / 2));
                $commander->setPlasma(0);
                $commander->setLaser(0);
                $commander->setMissile(0);
                $commander->setRecycleur(0);
                $commander->setCargo(0);
                $commander->setBarge(0);
                $commander->setHyperespace(0);
                $commander->setDiscipline(0);
                $commander->setHeavyShip(0);
                $commander->setLightShip(0);
                $commander->setOnde(0);
                $commander->setHyperespace(0);
                $commander->setDiscipline(0);
                $commander->setBarbed(0);
                $commander->setAeroponicFarm(0);
                $commander->setTank(0);
                $commander->setExpansion(0);
                $commander->setPoliticArmement(0);
                $commander->setPoliticCostScientist(0);
                $commander->setPoliticArmor(0);
                $commander->setPoliticBarge(0);
                $commander->setPoliticCargo(0);
                $commander->setPoliticColonisation(0);
                $commander->setPoliticCostSoldier(0);
                $commander->setPoliticCostTank(0);
                $commander->setPoliticInvade(0);
                $commander->setPoliticMerchant(0);
                $commander->setPoliticPdg(0);
                $commander->setPoliticProd(0);
                $commander->setPoliticRecycleur(0);
                $commander->setPoliticSearch(0);
                $commander->setPoliticSoldierAtt(0);
                $commander->setPoliticSoldierSale(0);
                $commander->setPoliticTankDef(0);
                $commander->setPoliticWorker(0);
                $commander->setPoliticWorkerDef(0);
                $commander->setZombieAtt(1);
                if ($commander->getAlly()) {
                    $ally = $commander->getAlly();
                    if (count($ally->getCommanders()) == 1 || ($ally->getPolitic() == 'fascism' && $commander->getGrade()->getPlacement() == 1)) {
                        foreach ($ally->getCommanders() as $commanderTmp) {
                            $commanderTmp->setAlly(null);
                            $commanderTmp->setGrade(null);
                            $commanderTmp->setAllyBan($now);
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

                        $pnas = $doctrine->getRepository(Pna::class)
                            ->createQueryBuilder('p')
                            ->where('p.allyTag = :allytag')
                            ->setParameters(['allytag' => $ally->getSigle()])
                            ->getQuery()
                            ->getResult();

                        $pacts = $doctrine->getRepository(Allied::class)
                            ->createQueryBuilder('a')
                            ->where('a.allyTag = :allytag')
                            ->setParameters(['allytag' => $ally->getSigle()])
                            ->getQuery()
                            ->getResult();

                        $wars = $doctrine->getRepository(War::class)
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
                $commander->setAlly(null);
                $commander->setGrade(null);

                foreach ($commander->getSalons() as $salon) {
                    $salon->removeCommander($commander);
                }

                $salon = $doctrine->getRepository(Salon::class)
                    ->createQueryBuilder('s')
                    ->where('s.name = :name')
                    ->setParameters(['name' => 'Public'])
                    ->getQuery()
                    ->getOneOrNullResult();

                $salon->removeCommander($commander);
                $commander->setSalons(null);

                $em->flush();
            }
            $servers = $doctrine->getRepository(Server::class)
                ->createQueryBuilder('s')
                ->select('s.id, s.open, s.pvp')
                ->groupBy('s.id')
                ->orderBy('s.id', 'ASC')
                ->getQuery()
                ->getResult();

            $galaxys = $doctrine->getRepository(Galaxy::class)
                ->createQueryBuilder('g')
                ->join('g.server', 'ss')
                ->join('g.sectors', 's')
                ->join('s.planets', 'p')
                ->leftJoin('p.commander', 'c')
                ->select('g.id, g.position, count(DISTINCT c.id) as commanders, ss.id as server')
                ->groupBy('g.id')
                ->orderBy('g.position', 'ASC')
                ->getQuery()
                ->getResult();

            if ($user->getId() === 220) {
                foreach ($commander->getPlanets() as $planet) {
                    $planet->setCommander(null);
                }
                foreach ($commander->getReports() as $report) {
                    $report->setImageName(null);
                    $em->remove($report);
                }
                $commander->setImageName(null);
                $em->remove($commander);
                $em->flush();
                return $this->redirectToRoute('server_select');
            }

            return $this->render('connected/game_over.html.twig', [
                'galaxys' => $galaxys,
                'servers' => $servers
            ]);
        } else {
            return $this->redirectToRoute('home');
        }
    }
}