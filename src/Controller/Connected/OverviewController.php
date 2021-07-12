<?php

namespace App\Controller\Connected;

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
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function overviewAction(Request $request, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $server = $usePlanet->getSector()->getGalaxy()->getServer();
        $character = $user->getCharacter($server);
        $now = new DateTime();

        if($character->getGameOver() || $character->getAllPlanets() == 0) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getCharacter() != $character) {
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
            ->andWhere('f.character = :character or p.character = :character')
            ->setParameters(['now' => $now, 'six' => 6, 'character' => $character])
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
            ->andWhere('p.character = :character')
            ->setParameters(['now' => $now, 'character' => $character])
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
            ->andWhere('p.character = :character')
            ->setParameters(['now' => $now, 'character' => $character])
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
            ->andWhere('p.character = :character')
            ->setParameters(['now' => $now, 'character' => $character])
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
            ->andWhere('p.character = :character')
            ->setParameters(['now' => $now, 'character' => $character])
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
            ->andWhere('pp.character = :character')
            ->setParameters(['now' => $now, 'character' => $character])
            ->getQuery()
            ->getResult();

        if ($products) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::productsAction', [
                'products'  => $products,
                'em' => $em
            ]);
        }

        $seconds = $this->forward('App\Controller\Connected\Execute\ChronosController::userActivityAction', [
            'character' => $character,
            'now'  => $now,
            'em' => $em]);

        if ($seconds->getContent() >= 60) {
            $this->forward('App\Controller\Connected\Execute\PlanetsGenController::planetsGenAction', [
                'character' => $character,
                'seconds' => $seconds->getContent(),
                'now'  => $now,
                'em' => $em]);
        }

        if ($user->getTutorial() == 53) {
            $user->setTutorial(60);
            $em->flush();
        }

        $allTroops = $em->getRepository('App:Character')
            ->createQueryBuilder('c')
            ->join('c.planets', 'p')
            ->leftJoin('p.product', 'pp')
            ->select('sum(p.soldier) as soldier, sum(p.soldierAtNbr) as soldierAtNbr, sum(p.tank) as tank, sum(p.tankAtNbr) as tankAtNbr, sum(p.scientist) as scientist, sum(p.scientistAtNbr) as scientistAtNbr, sum(DISTINCT p.signature) as psignature, sum(pp.signature) as ppsignature')
            ->where('p.character = :character')
            ->setParameters(['character' => $character])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $allFleets = $em->getRepository('App:Character')
            ->createQueryBuilder('c')
            ->leftJoin('c.fleets', 'f')
            ->select('sum(f.soldier) as fsoldier, sum(f.scientist) as fscientist, sum(f.tank) as ftank, sum(f.signature) as fsignature')
            ->where('f.character = :character')
            ->setParameters(['character' => $character])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $allShipsProduct = $allTroops['ppsignature'] / 12;
        $allShipsPlanet = round($allTroops['psignature'] / 12);
        $allShipsFleet = $allFleets['fsignature'] / 2;
        $allShips = $allShipsProduct + $allShipsPlanet + $allShipsFleet;
        $allTroopsProduct = $character->getPriceTroopsProduct($allTroops);
        $allTroopsPlanet = $character->getPriceTroopsPlanet($allTroops);
        $allTroopsFleet = $character->getPriceTroopsFleet($allFleets);
        $allTroops = $allTroopsProduct + $allTroopsPlanet + $allTroopsFleet;

        $allBuildings = $em->getRepository('App:Character')
            ->createQueryBuilder('c')
            ->join('c.planets', 'p')
            ->select('sum(p.miner * 1) as miner, sum(p.extractor * 1) as extractor, sum(p.aeroponicFarm * 2) as aeroponicFarm, sum(p.farm * 1) as farm, sum(p.silos * 30) as silos, sum(p.niobiumStock * 30) as niobiumStock, sum(p.waterStock * 30) as waterStock, sum(p.caserne * 66) as caserne, sum(p.bunker * 800) as bunker, sum(p.centerSearch * 53) as centerSearch, sum(p.city * 13) as city, sum(p.metropole * 26) as metropole, sum(p.lightUsine * 333) as lightUsine, sum(p.heavyUsine * 666) as heavyUsine, sum(p.spaceShip * 100) as spaceShip, sum(p.radar * 13) as radar, sum(p.skyRadar * 133) as skyRadar, sum(p.skyBrouilleur * 400) as skyBrouilleur, sum(p.nuclearBase * 3333) as nuclearBase, sum(p.orbital * 333) as orbital, sum(p.island * 333) as island, sum(p.worker) as worker, sum(p.workerProduction) as workerProd')
            ->where('p.character = :character')
            ->setParameters(['character' => $character])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $allWorkers = $allBuildings['worker'];
        $allWorkersProd = $allBuildings['workerProd'] * 60;
        $allBuilding = $allBuildings['centerSearch'] + $allBuildings['miner'] + $allBuildings['extractor'] + $allBuildings['niobiumStock'] + $allBuildings['waterStock'] + $allBuildings['city'] + $allBuildings['metropole'] + $allBuildings['bunker'] + $allBuildings['caserne'] + $allBuildings['spaceShip'] + $allBuildings['lightUsine'] + $allBuildings['heavyUsine'] + $allBuildings['radar'] + $allBuildings['skyRadar'] + $allBuildings['skyBrouilleur'] + $allBuildings['nuclearBase'] + $allBuildings['orbital'] + $allBuildings['island'];

        if ($character->getPoliticWorker() > 0) {
            $allWorkersProd = $allWorkersProd * (1 + ($character->getPoliticWorker() / 5));
        }

        $attackFleets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.character', 'c')
            ->leftJoin('c.ally', 'a')
            ->join('f.planet', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->join('dp.sector', 'ds')
            ->join('ds.galaxy', 'dg')
            ->select('f.attack, f.name, f.signature, p.name as pName, p.position as position, p.skyBrouilleur, s.position as sector, g.position as galaxy, s.id as idSector, g.id as idGalaxy, dp.name as dName, dp.position as dPosition, ds.position as dSector, dg.position as dGalaxy, ds.id as dIdSector, dg.id as dIdGalaxy, f.flightTime, c.id as character, a.sigle as sigle, c.username as username')
            ->where('f.character != :character')
            ->andWhere('dp.character = :character')
            ->setParameters(['character' => $character])
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
            ->where('f.character = :character')
            ->andWhere('f.flightTime < :time')
            ->setParameters(['character' => $character, 'time' => $oneHour])
            ->orderBy('f.flightTime')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();


        if ($character->getOrderPlanet() == 'alpha') {
            $crit = 'p.name';
        } elseif ($character->getOrderPlanet() == 'colo') {
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
            ->where('p.character = :character')
            ->setParameters(['character' => $character])
            ->orderBy($crit, $order)
            ->getQuery()
            ->getResult();

        if (count($fleetMove) == 0) {
            $fleetMove = null;
        }

        $form_image = $this->createForm(UserImageType::class,$character);
        $form_image->handleRequest($request);

        if ($form_image->isSubmitted()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $quest = $character->checkQuests('logo');
            if($quest) {
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                $character->removeQuest($quest);
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
        $character = $user->getMainCharacter();

        $now = new DateTime();
        $now->add(new DateInterval('PT' . 172800 . 'S'));
        if(($user->getId() === 220 && $user->getMainCharacter()) || ($character && ($character->getGameOver() || $character->getAllPlanets() == 0))) {
            if($character->getColPlanets() == 0 && $character->getGameOver() == null) {
                $character->setGameOver($character->getUserName());

                $em->flush();
            }
            if($character->getRank()) {

                foreach ($character->getFleetLists() as $list) {
                    foreach ($list->getFleets() as $fleetL) {
                        $fleetL->setFleetList(null);
                    }
                    $em->remove($list);
                }
                $ship = $character->getShip();
                if ($ship) {
                    $character->setShip(null);
                    $em->remove($ship);
                }
                $character->setBitcoin(5000);
                $character->setSearch(null);
                if ($character->getRank()) {
                    $em->remove($character->getRank());
                }
                $character->setRank(null);
                $character->setJoinAllyAt(null);
                $character->setAllyBan(null);
                $character->setScientistProduction(1);
                $character->setSearchAt(null);
                $character->setDemography(0);
                $character->setUtility(0);
                $character->setArmement(0);
                $character->setIndustry(0);
                $character->setTerraformation(round($character->getTerraformation(0) / 2));
                $character->setPlasma(0);
                $character->setLaser(0);
                $character->setMissile(0);
                $character->setRecycleur(0);
                $character->setCargo(0);
                $character->setBarge(0);
                $character->setHyperespace(0);
                $character->setDiscipline(0);
                $character->setHeavyShip(0);
                $character->setLightShip(0);
                $character->setOnde(0);
                $character->setHyperespace(0);
                $character->setDiscipline(0);
                $character->setBarbed(0);
                $character->setAeroponicFarm(0);
                $character->setTank(0);
                $character->setExpansion(0);
                $character->setPoliticArmement(0);
                $character->setPoliticCostScientist(0);
                $character->setPoliticArmor(0);
                $character->setPoliticBarge(0);
                $character->setPoliticCargo(0);
                $character->setPoliticColonisation(0);
                $character->setPoliticCostSoldier(0);
                $character->setPoliticCostTank(0);
                $character->setPoliticInvade(0);
                $character->setPoliticMerchant(0);
                $character->setPoliticPdg(0);
                $character->setPoliticProd(0);
                $character->setPoliticRecycleur(0);
                $character->setPoliticSearch(0);
                $character->setPoliticSoldierAtt(0);
                $character->setPoliticSoldierSale(0);
                $character->setPoliticTankDef(0);
                $character->setPoliticWorker(0);
                $character->setPoliticWorkerDef(0);
                $character->setZombieAtt(1);
                if ($character->getAlly()) {
                    $ally = $character->getAlly();
                    if (count($ally->getCharacters()) == 1 || ($ally->getPolitic() == 'fascism' && $character->getGrade()->getPlacement() == 1)) {
                        foreach ($ally->getCharacters() as $character) {
                        $character->setAlly(null);
                        $character->setGrade(null);
                        $character->setAllyBan($now);
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
                $character->setAlly(null);
                $character->setGrade(null);

                foreach ($character->getSalons() as $salon) {
                    $salon->removeCharacter($character);
                }

                $salon = $em->getRepository('App:Salon')
                    ->createQueryBuilder('s')
                    ->where('s.name = :name')
                    ->setParameters(['name' => 'Public'])
                    ->getQuery()
                    ->getOneOrNullResult();

                $salon->removeCharacter($character);
                $character->setSalons(null);

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
                ->leftJoin('p.character', 'c')
                ->select('g.id, g.position, count(DISTINCT c.id) as characters, ss.id as server')
                ->groupBy('g.id')
                ->orderBy('g.position', 'ASC')
                ->getQuery()
                ->getResult();

            if ($user->getId() === 220) {
                foreach ($character->getPlanets() as $planet) {
                    $planet->setCharacter(null);
                }
                foreach ($character->getReports() as $report) {
                    $em->remove($report);
                }
                $em->remove($character);
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