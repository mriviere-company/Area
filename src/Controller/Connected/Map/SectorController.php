<?php

namespace App\Controller\Connected\Map;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Planet;
use App\Entity\Sector;
use App\Entity\Galaxy;
use App\Entity\Destination;
use App\Form\Front\NavigateType;
use App\Form\Front\InteractFleetType;
use App\Entity\Fleet;
use Datetime;
use DateInterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class SectorController extends AbstractController
{
    /**
     * @Route("/carte-spatiale/{sector}/{galaxy}/{usePlanet}", name="map", requirements={"sector"="\d+", "galaxy"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Sector $sector
     * @param Galaxy $galaxy
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     */
    public function mapAction(ManagerRegistry $doctrine, Request $request, Sector $sector, Galaxy $galaxy, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $server = $usePlanet->getSector()->getGalaxy()->getServer();
        $commander = $user->getCommander($server);
        $now = new DateTime();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $fleets = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->where('f.flightTime < :now')
            ->andWhere('f.flightType != :six or f.flightType is null')
            ->andWhere('s.id = :sector')
            ->andWhere('g.id = :galaxy')
            ->andWhere('g.server = :server')
            ->setParameters(['now' => $now, 'six' => 6, 'sector' => $sector->getId(), 'galaxy' => $galaxy->getId(), 'server' => $server])
            ->getQuery()
            ->getResult();

        if ($fleets) {
            $this->forward('App\Controller\Connected\Execute\MoveFleetController::centralizeFleetAction', [
                'fleets'  => $fleets,
                'now'  => $now,
                'em'  => $em
            ]);
        }

        $products = $doctrine->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->join('p.planet', 'pp')
            ->join('pp.sector', 's')
            ->join('s.galaxy', 'g')
            ->where('p.productAt < :now')
            ->andWhere('s.id = :sector')
            ->andWhere('g.id = :galaxy')
            ->andWhere('g.server = :server')
            ->setParameters(['now' => $now, 'sector' => $sector->getId(), 'galaxy' => $galaxy->getId(), 'server' => $server])
            ->getQuery()
            ->getResult();

        if ($products) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::productsAction', [
                'products'  => $products,
                'em' => $em
            ]);
        }

        $form_navigate = $this->createForm(NavigateType::class, null, ["galaxy" => $galaxy->getPosition(), "sector" => $sector->getPosition()]);
        $form_navigate->handleRequest($request);

        if ($form_navigate->isSubmitted() && $form_navigate->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if ($form_navigate->get('sector')->getData() && $form_navigate->get('galaxy')->getData()) {
                return $this->redirectToRoute('map', ['sector' => $form_navigate->get('sector')->getData(), 'galaxy' => $form_navigate->get('galaxy')->getData(), 'usePlanet' => $usePlanet->getId()]);
            }
            return $this->redirectToRoute('galaxy', ['sector' => $form_navigate->get('galaxy')->getData(), 'usePlanet' => $usePlanet->getId()]);
        }
        if ($commander->getAlly()) {
            $viewSector = $doctrine->getRepository(Ally::class)
                ->createQueryBuilder('a')
                ->join('a.commanders', 'c')
                ->join('c.planets', 'p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->select('p.id, p.radar as radar, p.skyRadar as skyRadar')
                ->groupBy('p.id')
                ->where('s.id = :sector')
                ->andWhere('g.id = :galaxy')
                ->andWhere('a.id = :alliance')
                ->setParameters(['sector' => $sector->getId(), 'galaxy' => $galaxy->getId(), 'alliance' => $commander->getAlly()->getId()])
                ->orderBy('p.radar', 'DESC')
                ->addOrderBy('p.skyRadar', 'DESC')
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();

            $viewFleets = $doctrine->getRepository(Fleet::class)
                ->createQueryBuilder('f')
                ->join('f.commander', 'c')
                ->join('c.ally', 'a')
                ->join('f.planet', 'p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->select('p.id as id')
                ->groupBy('f.id')
                ->where('s.id = :sector')
                ->andWhere('g.id = :galaxy')
                ->andWhere('a.id = :alliance')
                ->setParameters(['sector' => $sector->getId(), 'galaxy' => $galaxy->getId(), 'alliance' => $commander->getAlly()->getId()])
                ->getQuery()
                ->getResult();
        } else {
            $viewSector = $doctrine->getRepository(Commander::class)
                ->createQueryBuilder('c')
                ->join('c.planets', 'p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->select('p.id, p.radar as radar, p.skyRadar as skyRadar')
                ->groupBy('p.id')
                ->where('s.id = :sector')
                ->andWhere('g.id = :galaxy')
                ->andWhere('c.id = :commander')
                ->setParameters(['sector' => $sector->getId(), 'galaxy' => $galaxy->getId(), 'commander' => $commander->getId()])
                ->orderBy('p.radar', 'DESC')
                ->addOrderBy('p.skyRadar', 'DESC')
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();

            $viewFleets = $doctrine->getRepository(Fleet::class)
                ->createQueryBuilder('f')
                ->join('f.commander', 'c')
                ->join('f.planet', 'p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->select('p.id as id')
                ->groupBy('f.id')
                ->where('s.id = :sector')
                ->andWhere('g.id = :galaxy')
                ->andWhere('c.id = :commander')
                ->setParameters(['sector' => $sector->getId(), 'galaxy' => $galaxy->getId(), 'commander' => $commander->getId()])
                ->getQuery()
                ->getResult();
        }

        $planets = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.commander', 'c')
            ->leftJoin('p.fleets', 'f')
            ->leftJoin('c.ally', 'a')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->select('p.id, p.position, p.name, p.ground, p.sun, p.sky, p.nbCdr, p.wtCdr, p.signature, p.imageName, p.empty, p.merchant, p.cdr, p.skyRadar, p.radar, p.skyBrouilleur, c.id as commander, c.username as username, c.zombie as zombie, a.id as alliance, a.sigle as sigle, count(DISTINCT f) as fleets') // count(DISTINCT p) as planets, sum(DISTINCT r.warPoint) as pdg
            ->groupBy('p.id')
            ->where('s.id = :sector')
            ->andWhere('g.id = :galaxy')
            ->setParameters(['sector' => $sector->getId(), 'galaxy' => $galaxy->getId()])
            ->orderBy('p.position')
            ->getQuery()
            ->getResult();

        $fleets = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->join('f.commander', 'c')
            ->leftJoin('c.ally', 'a')
            ->join('f.planet', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->select('p.id as planet, f.id, f.name, f.fightAt, f.signature, c.id as commander, c.username as username, a.sigle as alliance, a.id as allianceId')
            ->groupBy('f.id')
            ->where('s.id = :sector')
            ->andWhere('g.id = :galaxy')
            ->andWhere('f.flightTime is null')
            ->setParameters(['sector' => $sector->getId(), 'galaxy' => $galaxy->getId()])
            ->getQuery()
            ->getResult();

        $fleetIn = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->join('f.commander', 'c')
            ->leftJoin('c.ally', 'a')
            ->join('f.planet', 'p')
            ->leftJoin('p.commander', 'pc')
            ->leftJoin('pc.ally', 'pa')
            ->join('p.sector', 'ps')
            ->join('ps.galaxy', 'gp')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->leftJoin('dp.commander', 'dc')
            ->leftJoin('dc.ally', 'da')
            ->join('dp.sector', 'fs')
            ->join('fs.galaxy', 'g')
            ->select('f.id, f.name, f.flightTime, p.name as planetname, p.skyBrouilleur, pc.id as pcommander, pa.id as palliance, dc.id as dcommander, da.id as dalliance, dp.name as dname, dp.position as dposition, p.position as position, ps.position as sector, gp.position as galaxy, fs.position as dsector, g.position as dgalaxy, f.signature, c.id as commander, c.username as username, a.id as alliance, a.sigle as sigle, c.merchant as merchant, a.imageName as imageName')
            ->where('fs.id = :sector')
            ->andWhere('g.id = :galaxy')
            ->andWhere('ps.id != :sector')
            ->setParameters(['sector' => $sector->getId(), 'galaxy' => $galaxy->getId()])
            ->orderBy('f.flightTime')
            ->getQuery()
            ->getResult();

        $fleetOut = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->join('f.commander', 'c')
            ->leftJoin('c.ally', 'a')
            ->join('f.planet', 'p')
            ->leftJoin('p.commander', 'pc')
            ->leftJoin('pc.ally', 'pa')
            ->join('p.sector', 'ps')
            ->join('ps.galaxy', 'gp')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->leftJoin('dp.commander', 'dc')
            ->leftJoin('dc.ally', 'da')
            ->join('dp.sector', 'fs')
            ->join('fs.galaxy', 'g')
            ->select('f.id, f.name, f.flightTime, p.name as planetname, p.skyBrouilleur, pc.id as pcommander, pa.id as palliance, dc.id as dcommander, da.id as dalliance, dp.name as dname, dp.position as dposition, p.position as position, ps.position as sector, gp.position as galaxy, fs.position as dsector, g.position as dgalaxy, f.signature, c.id as commander, c.username as username, a.id as alliance, a.sigle as sigle, c.merchant as merchant, a.imageName as imageName')
            ->where('fs.id != :sector')
            ->andWhere('ps.id = :sector')
            ->andWhere('gp.id = :galaxy')
            ->setParameters(['sector' => $sector->getId(), 'galaxy' => $galaxy->getId()])
            ->orderBy('f.flightTime')
            ->getQuery()
            ->getResult();

        $fleetCurrent = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->join('f.commander', 'c')
            ->leftJoin('c.ally', 'a')
            ->join('f.planet', 'p')
            ->leftJoin('p.commander', 'pc')
            ->leftJoin('pc.ally', 'pa')
            ->join('p.sector', 'ps')
            ->join('ps.galaxy', 'gp')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->leftJoin('dp.commander', 'dc')
            ->leftJoin('dc.ally', 'da')
            ->join('dp.sector', 'fs')
            ->join('fs.galaxy', 'gs')
            ->select('f.id, f.name, f.flightTime, p.name as planetname, pc.id as pcommander, pa.id as palliance, dc.id as dcommander, da.id as dalliance, dp.name as dname, dp.position as dposition, p.position as position, ps.position as sector, gp.position as galaxy, f.signature, c.id as commander, c.username as username, a.id as alliance, a.sigle as sigle, c.merchant as merchant, a.imageName as imageName')
            ->where('ps.id = :sector')
            ->andWhere('fs.id = :sector')
            ->andWhere('gs.id = :galaxy')
            ->andWhere('gp.id = :galaxy')
            ->setParameters(['sector' => $sector->getId(), 'galaxy' => $galaxy->getId()])
            ->orderBy('f.flightTime')
            ->getQuery()
            ->getResult();

        if(($user->getTutorial() == 14)) {
            $user->setTutorial(15);
            $em->flush();
        }

        return $this->render('connected/map/sector.html.twig', [
            'form_navigate' => $form_navigate->createView(),
            'planets' => $planets,
            'fleets' => $fleets,
            'usePlanet' => $usePlanet,
            'sector' => $sector,
            'galaxy' => $galaxy,
            'viewSector' => $viewSector,
            'viewFleets' => $viewFleets,
            'fleetIn' => $fleetIn,
            'fleetOut' => $fleetOut,
            'fleetCurrent' => $fleetCurrent
        ]);
    }

    /**
     * @Route("/flotte-orbite/{planet}/{usePlanet}", name="fleet_sector", requirements={"planet"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $planet
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function fleetAction(ManagerRegistry $doctrine, Planet $planet, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $fleets = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->where('p.id = :sector')
            ->andWhere('f.flightTime is null')
            ->setParameters(['sector' => $planet->getId()])
            ->getQuery()
            ->getResult();

        return $this->render('connected/map/fleet.html.twig', [
            'fleets' => $fleets,
            'usePlanet' => $usePlanet,
        ]);
    }

    /**
     * @Route("/interaction-planete/{planet}/{usePlanet}", name="planet_interact", requirements={"planet"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $planet
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function planetInteractAction(ManagerRegistry $doctrine, Request $request, Planet $planet, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $server = $usePlanet->getSector()->getGalaxy()->getServer();
        $commander = $user->getCommander($server);
        $now = new DateTime();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $form_sendFleet = $this->createForm(InteractFleetType::class, null, ["commander" => $commander->getId()]);
        $form_sendFleet->handleRequest($request);

        if ($planet && $usePlanet) {
        } else {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId()]);
        }
        if ($form_sendFleet->isSubmitted() && $form_sendFleet->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $fleet = $form_sendFleet->get('list')->getData();
            $sFleet= $fleet->getPlanet()->getSector()->getPosition();
            $sector = $planet->getSector()->getPosition();
            $planete = $planet->getPosition();
            $galaxy = $planet->getSector()->getGalaxy()->getPosition();
            if($commander->getHyperespace() != 1 && $fleet->getPlanet()->getSector()->getGalaxy()->getPosition() != $galaxy) {
                return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId()]);
            }
            if($fleet->getPlanet()->getSector()->getGalaxy()->getPosition() != $galaxy) {
                $base = 18;
                $price = 25;
            } else {
                $pFleet = $fleet->getPlanet()->getPosition();
                if ($sFleet == $sector) {
                    $x1 = ($pFleet - 1) % 5;
                    $x2 = ($planete - 1) % 5;
                    $y1 = ($pFleet - 1) / 5;
                    $y2 = ($planete - 1) / 5;
                } else {
                    $x1 = (($sFleet - 1) % 10) * 3;
                    $x2 = (($sector - 1) % 10) * 3;
                    $y1 = (($sFleet - 1) / 10) * 3;
                    $y2 = (($sector - 1) / 10) * 3;
                }
                $base = sqrt(pow(($x2 - $x1), 2) + pow(($y2 - $y1), 2));
                $price = $base / 3;
            }
            $carburant = round($price * ($fleet->getNbrSignatures() / 200));
            if($carburant > $commander->getBitcoin()) {
                return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId()]);
            }
            if($fleet->getMotherShip()) {
                $speed = $fleet->getSpeed() - ($fleet->getSpeed() * 0.10);
            } else {
                $speed = $fleet->getSpeed();
            }
            $distance = $speed * $base * 1000 * $server->getSpeed();
            $now->add(new DateInterval('PT' . round($distance) . 'S'));
            $moreNow = new DateTime();
            $moreNow->add(new DateInterval('PT' . 120 . 'S'));
            $fleet->setFlightTime($now);
            $destination = new Destination($fleet, $planet);
            $em->persist($destination);
            if(($form_sendFleet->get('flightType')->getData() == '4' || $form_sendFleet->get('flightType')->getData() == '5') &&
                (!$planet->getCommander() || $fleet->getSoldier() == 0 || $fleet->getBarge() == 0 || $fleet->getSoldier() == null ||
                    $fleet->getBarge() == null)) {
                if(!$planet->getCommander()) {
                    $this->addFlash("fail", "Cette planète est inoccupée.");
                }
                if($fleet->getSoldier() == 0 || $fleet->getSoldier() == NULL) {
                    $this->addFlash("fail", "Vous n'avez pas de soldats sur votre flotte.");
                }
                if($fleet->getBarge() == 0 || $fleet->getBarge() == NULL) {
                    $this->addFlash("fail", "Vous ne disposez pas de barges d'invasions.");
                }
                return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId()]);
            }
            $fleet->setFlightType($form_sendFleet->get('flightType')->getData());
            $fleet->setCancelFlight($moreNow);
            $commander->setBitcoin($commander->getBitcoin() - $carburant);

            if(($user->getTutorial() == 15)) {
                $user->setTutorial(16);
            }
            $em->flush();
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId()]);
        }

        return $this->render('connected/map/interact.html.twig', [
            'usePlanet' => $usePlanet,
            'planet' => $planet,
            'form_fleetMove' => $form_sendFleet->createView(),
        ]);
    }

    /**
     * @Route("/envoyer-sonde/{planet}/{usePlanet}", name="send_sonde", requirements={"planet"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $planet
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws NonUniqueResultException
     */
    public function sendSondeAction(ManagerRegistry $doctrine, Planet $planet, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $server = $usePlanet->getSector()->getGalaxy()->getServer();
        $commander = $user->getCommander($server);
        $now = new DateTime();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $fPlanet = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->andWhere('p.commander = :commander')
            ->andwhere('p.sonde > 0')
            ->setParameters(['commander' => $commander])
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if($fPlanet == null) {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId()]);
        }
        $sFleet= $fPlanet->getSector()->getPosition();
        $sector = $planet->getSector()->getPosition();
        $planete = $planet->getPosition();
        $galaxy = $planet->getSector()->getGalaxy()->getPosition();
        if($commander->getHyperespace() == 0 && $fPlanet->getSector()->getGalaxy()->getPosition() != $galaxy) {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $fPlanet->getSector()->getPosition(), 'galaxy' => $fPlanet->getSector()->getGalaxy()->getPosition()]);
        }
        if($fPlanet->getSector()->getGalaxy()->getPosition() != $galaxy) {
            $base = 18;
            $price = 25;
        } else {
            $pFleet = $fPlanet->getPosition();
            if ($sFleet == $sector) {
                $x1 = ($pFleet - 1) % 5;
                $x2 = ($planete - 1) % 5;
                $y1 = ($pFleet - 1) / 5;
                $y2 = ($planete - 1) / 5;
            } else {
                $x1 = (($sFleet - 1) % 10) * 3;
                $x2 = (($sector - 1) % 10) * 3;
                $y1 = (($sFleet - 1) / 10) * 3;
                $y2 = (($sector - 1) / 10) * 3;
            }
            $base = sqrt(pow(($x2 - $x1), 2) + pow(($y2 - $y1), 2));
            $price = $base / 3;
        }
        $carburant = round($price * ($fPlanet->getNbrSignatures() / 200));
        if(1 > $commander->getBitcoin()) {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId()]);
        }
        $fleet = new Fleet();
        $fleet->setSonde(1);
        $fleet->setCommander($commander);
        $fleet->setPlanet($fPlanet);
        $fleet->setName('Auto Sonde');
        $fleet->setSignature($fleet->getNbrSignatures());
        $fPlanet->setSonde($fPlanet->getSonde() - 1);
        $speed = $fleet->getSpeed();
        $distance = $speed * $base * 1000 * $server->getSpeed();
        $now->add(new DateInterval('PT' . round($distance) . 'S'));
        $moreNow = new DateTime();
        $moreNow->add(new DateInterval('PT' . 120 . 'S'));
        $fleet->setFlightTime($now);
        $destination = new Destination($fleet, $planet);
        $em->persist($destination);
        $fleet->setFlightType(1);
        $fleet->setCancelFlight($moreNow);
        $commander->setBitcoin($commander->getBitcoin() - $carburant);
        $em->persist($fleet);
        if ($planet->getCommander()) {
            $quest = $commander->checkQuests('spy_planet');
            if($quest) {
                $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + $quest->getGain());
                $commander->removeQuest($quest);
            }
        }

        $em->flush();
        return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId()]);
    }

    /**
     * @Route("/envoyer-missiles/{planet}/{usePlanet}", name="send_nuclear", requirements={"planet"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $planet
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws NonUniqueResultException
     */
    public function sendNuclearAction(ManagerRegistry $doctrine, Planet $planet, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $fPlanet = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->andWhere('p.commander = :commander')
            ->andwhere('p.nuclearBomb > 0')
            ->setParameters(['commander' => $commander])
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if ($fPlanet == null) {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId()]);
        }
        $galaxy = $planet->getSector()->getGalaxy()->getPosition();
        if ($commander->getHyperespace() == 0 && $fPlanet->getSector()->getGalaxy()->getPosition() != $galaxy) {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $fPlanet->getSector()->getPosition(), 'galaxy' => $fPlanet->getSector()->getGalaxy()->getPosition()]);
        }
        if (1 > $commander->getBitcoin()) {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId()]);
        }
        $fleet = new Fleet();
        $fleet->setNuclearBomb(1);
        $fleet->setCommander($commander);
        $fleet->setPlanet($fPlanet);
        $fleet->setName('Missile nucléaire');
        $fPlanet->setNuclearBomb($fPlanet->getNuclearBomb() - 1);
        $now->add(new DateInterval('PT' . round(1800) . 'S'));
        $fleet->setFlightTime($now);
        $destination = new Destination($fleet, $planet);
        $em->persist($destination);
        $fleet->setFlightType(6);
        $fleet->setSignature($fleet->getNbrSignatures());
        $em->persist($fleet);
        if ($planet->getCommander()) {
            $quest = $commander->checkQuests('nuclear_planet');
            if ($quest) {
                $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + $quest->getGain());
                $commander->removeQuest($quest);
            }
        }

        $em->flush();
        return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId()]);
    }
}