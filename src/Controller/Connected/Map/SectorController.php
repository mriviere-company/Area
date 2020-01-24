<?php

namespace App\Controller\Connected\Map;

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
use DatetimeZone;
use DateInterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class SectorController extends AbstractController
{
    /**
     * @Route("/carte-spatiale/{sector}/{gal}/{usePlanet}", name="map", requirements={"sector"="\d+", "usePlanet"="\d+", "gal"="\d+"})
     */
    public function mapAction(Request $request, Sector $sector, Planet $usePlanet, Galaxy $gal)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $form_navigate = $this->createForm(NavigateType::class, null, ["galaxy" => $gal->getPosition(), "sector" => $sector->getPosition()]);
        $form_navigate->handleRequest($request);

        if ($form_navigate->isSubmitted() && $form_navigate->isValid()) {
            if ($form_navigate->get('sector')->getData() && $form_navigate->get('galaxy')->getData()) {
                return $this->redirectToRoute('map', ['sector' => $form_navigate->get('sector')->getData(), 'gal' => $form_navigate->get('galaxy')->getData(), 'usePlanet' => $usePlanet->getId()]);
            }
            return $this->redirectToRoute('galaxy', ['id' => $form_navigate->get('galaxy')->getData(), 'usePlanet' => $usePlanet->getId()]);
        }
        if ($user->getAlly()) {
            $viewSector = $em->getRepository('App:Ally')
                ->createQueryBuilder('a')
                ->join('a.users', 'u')
                ->join('u.planets', 'p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->select('p.id, p.radar as radar, p.skyRadar as skyRadar')
                ->groupBy('p.id')
                ->where('s.position = :id')
                ->andWhere('g.position = :gal')
                ->andWhere('a.id = :alliance')
                ->setParameters(['id' => $sector->getPosition(), 'gal' => $gal->getPosition(), 'alliance' => $user->getAlly()->getId()])
                ->orderBy('p.radar', 'DESC')
                ->addOrderBy('p.skyRadar', 'DESC')
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();

            $viewFleets = $em->getRepository('App:Fleet')
                ->createQueryBuilder('f')
                ->join('f.user', 'u')
                ->join('u.ally', 'a')
                ->join('f.planet', 'p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->select('p.id as id')
                ->groupBy('f.id')
                ->where('s.position = :id')
                ->andWhere('g.position = :gal')
                ->andWhere('a.id = :alliance')
                ->setParameters(['id' => $sector->getPosition(), 'gal' => $gal->getPosition(), 'alliance' => $user->getAlly()->getId()])
                ->getQuery()
                ->getResult();
        } else {
            $viewSector = $em->getRepository('App:User')
                ->createQueryBuilder('u')
                ->join('u.planets', 'p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->select('p.id, p.radar as radar, p.skyRadar as skyRadar')
                ->groupBy('p.id')
                ->where('s.position = :id')
                ->andWhere('g.position = :gal')
                ->andWhere('u.id = :user')
                ->setParameters(['id' => $sector->getPosition(), 'gal' => $gal->getPosition(), 'user' => $user->getId()])
                ->orderBy('p.radar', 'DESC')
                ->addOrderBy('p.skyRadar', 'DESC')
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();

            $viewFleets = $em->getRepository('App:Fleet')
                ->createQueryBuilder('f')
                ->join('f.user', 'u')
                ->join('f.planet', 'p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->select('p.id as id')
                ->groupBy('f.id')
                ->where('s.position = :id')
                ->andWhere('g.position = :gal')
                ->andWhere('u.id = :user')
                ->setParameters(['id' => $sector->getPosition(), 'gal' => $gal->getPosition(), 'user' => $user->getId()])
                ->getQuery()
                ->getResult();
        }

        $planets = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.fleets', 'f')
            ->leftJoin('u.ally', 'a')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->select('p.id, p.position, p.name, p.ground, p.sky, p.nbCdr, p.wtCdr, p.signature, p.imageName, p.empty, p.merchant, p.cdr, p.skyRadar, p.radar, p.skyBrouilleur, u.id as user, u.username as username, u.zombie as zombie, a.id as alliance, a.sigle as sigle, count(DISTINCT f) as fleets') // count(DISTINCT p) as planets, sum(DISTINCT r.warPoint) as pdg
            ->groupBy('p.id')
            ->where('s.position = :id')
            ->andWhere('g.position = :gal')
            ->setParameters(['id' => $sector->getPosition(), 'gal' => $gal->getPosition()])
            ->orderBy('p.position')
            ->getQuery()
            ->getResult();

        $fleets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.user', 'u')
            ->leftJoin('u.ally', 'a')
            ->join('f.planet', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->select('p.id as planet, f.id, f.name, f.fightAt, f.signature, u.id as user, u.username as username, a.sigle as alliance, a.id as allianceId')
            ->groupBy('f.id')
            ->where('s.position = :id')
            ->andWhere('g.position = :gal')
            ->andWhere('f.flightTime is null')
            ->setParameters(['id' => $sector->getPosition(), 'gal' => $gal->getPosition()])
            ->getQuery()
            ->getResult();

        $fleetIn = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.user', 'u')
            ->leftJoin('u.ally', 'a')
            ->join('f.planet', 'p')
            ->leftJoin('p.user', 'pu')
            ->leftJoin('pu.ally', 'pa')
            ->join('p.sector', 'ps')
            ->join('ps.galaxy', 'gp')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->leftJoin('dp.user', 'du')
            ->leftJoin('du.ally', 'da')
            ->join('dp.sector', 'fs')
            ->join('fs.galaxy', 'g')
            ->select('f.id, f.name, f.flightTime, p.name as planetname, pu.id as puser, pa.id as palliance, du.id as duser, da.id as dalliance, dp.name as dname, dp.position as dposition, p.position as position, ps.position as sector, gp.position as galaxy, fs.position as dsector, g.position as dgalaxy, f.signature, u.id as user, u.username as username, a.id as alliance, a.sigle as sigle, u.merchant as merchant, a.imageName as imageName')
            ->where('fs.position = :id')
            ->andWhere('g.position = :gal')
            ->andWhere('ps.position != :id')
            ->setParameters(['id' => $sector->getPosition(), 'gal' => $gal->getPosition()])
            ->orderBy('f.flightTime')
            ->getQuery()
            ->getResult();

        $fleetOut = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.user', 'u')
            ->leftJoin('u.ally', 'a')
            ->join('f.planet', 'p')
            ->leftJoin('p.user', 'pu')
            ->leftJoin('pu.ally', 'pa')
            ->join('p.sector', 'ps')
            ->join('ps.galaxy', 'gp')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->leftJoin('dp.user', 'du')
            ->leftJoin('du.ally', 'da')
            ->join('dp.sector', 'fs')
            ->join('fs.galaxy', 'g')
            ->select('f.id, f.name, f.flightTime, p.name as planetname, pu.id as puser, pa.id as palliance, du.id as duser, da.id as dalliance, dp.name as dname, dp.position as dposition, p.position as position, ps.position as sector, gp.position as galaxy, fs.position as dsector, g.position as dgalaxy, f.signature, u.id as user, u.username as username, a.id as alliance, a.sigle as sigle, u.merchant as merchant, a.imageName as imageName')
            ->where('fs.position != :id')
            ->andWhere('ps.position = :id')
            ->andWhere('gp.position = :gal')
            ->setParameters(['id' => $sector->getPosition(), 'gal' => $gal->getPosition()])
            ->orderBy('f.flightTime')
            ->getQuery()
            ->getResult();

        $fleetCurrent = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.user', 'u')
            ->leftJoin('u.ally', 'a')
            ->join('f.planet', 'p')
            ->leftJoin('p.user', 'pu')
            ->leftJoin('pu.ally', 'pa')
            ->join('p.sector', 'ps')
            ->join('ps.galaxy', 'gp')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->leftJoin('dp.user', 'du')
            ->leftJoin('du.ally', 'da')
            ->join('dp.sector', 'fs')
            ->join('fs.galaxy', 'gs')
            ->select('f.id, f.name, f.flightTime, p.name as planetname, pu.id as puser, pa.id as palliance, du.id as duser, da.id as dalliance, dp.name as dname, dp.position as dposition, p.position as position, ps.position as sector, gp.position as galaxy, f.signature, u.id as user, u.username as username, a.id as alliance, a.sigle as sigle, u.merchant as merchant, a.imageName as imageName')
            ->where('ps.position = :id')
            ->andWhere('fs.position = :id')
            ->andWhere('gs.position = :gal')
            ->andWhere('gp.position = :gal')
            ->setParameters(['id' => $sector->getPosition(), 'gal' => $gal->getPosition()])
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
            'id' => $sector->getPosition(),
            'gal' => $gal->getPosition(),
            'viewSector' => $viewSector,
            'viewFleets' => $viewFleets,
            'fleetIn' => $fleetIn,
            'fleetOut' => $fleetOut,
            'fleetCurrent' => $fleetCurrent,
        ]);
    }

    /**
     * @Route("/flotte-orbite/{id}/{usePlanet}", name="fleet_sector", requirements={"id"="\d+", "usePlanet"="\d+"})
     */
    public function fleetAction($id, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $fleets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->where('p.id = :id')
            ->andWhere('f.flightTime is null')
            ->setParameters(['id' => $id])
            ->getQuery()
            ->getResult();

        return $this->render('connected/map/fleet.html.twig', [
            'fleets' => $fleets,
            'usePlanet' => $usePlanet,
        ]);
    }

    /**
     * @Route("/interaction-planete/{planet}/{usePlanet}", name="planet_interact", requirements={"planet"="\d+", "usePlanet"="\d+"})
     */
    public function planetInteractAction(Request $request, Planet $planet, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $form_sendFleet = $this->createForm(InteractFleetType::class, null, ["user" => $user->getId()]);
        $form_sendFleet->handleRequest($request);

        if($planet && $usePlanet) {
        } else {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId()]);
        }
        if ($form_sendFleet->isSubmitted() && $form_sendFleet->isValid()) {
            $fleet = $form_sendFleet->get('list')->getData();
            $sFleet= $fleet->getPlanet()->getSector()->getPosition();
            $sector = $planet->getSector()->getPosition();
            $planete = $planet->getPosition();
            $galaxy = $planet->getSector()->getGalaxy()->getPosition();
            if($user->getHyperespace() != 1 && $fleet->getPlanet()->getSector()->getGalaxy()->getPosition() != $galaxy) {
                return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId()]);
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
            if($carburant > $user->getBitcoin()) {
                return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId()]);
            }
            if($fleet->getMotherShip()) {
                $speed = $fleet->getSpeed() - ($fleet->getSpeed() * 0.10);
            } else {
                $speed = $fleet->getSpeed();
            }
            $distance = $speed * $base * 100;
            $now->add(new DateInterval('PT' . round($distance) . 'S'));
            $moreNow = new DateTime();
            $moreNow->setTimezone(new DateTimeZone('Europe/Paris'));
            $moreNow->add(new DateInterval('PT' . 120 . 'S'));
            $fleet->setFlightTime($now);
            $destination = new Destination();
            $destination->setFleet($fleet);
            $destination->setPlanet($planet);
            $em->persist($destination);
            if(($form_sendFleet->get('flightType')->getData() == '4' || $form_sendFleet->get('flightType')->getData() == '5') &&
                (!$planet->getUser() || $fleet->getSoldier() == 0 || $fleet->getBarge() == 0 || $fleet->getSoldier() == NULL ||
                    $fleet->getBarge() == NULL)) {
                if(!$planet->getUser()) {
                    $this->addFlash("fail", "Cette planète est inoccupée.");
                }
                if($fleet->getSoldier() == 0 || $fleet->getSoldier() == NULL) {
                    $this->addFlash("fail", "Vous n'avez pas de soldats sur votre flotte.");
                }
                if($fleet->getBarge() == 0 || $fleet->getBarge() == NULL) {
                    $this->addFlash("fail", "Vous ne disposez pas de barges d'invasions.");
                }
                return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId()]);
            }
            $fleet->setFlightType($form_sendFleet->get('flightType')->getData());
            $fleet->setCancelFlight($moreNow);
            $user->setBitcoin($user->getBitcoin() - $carburant);

            if(($user->getTutorial() == 15)) {
                $user->setTutorial(16);
            }
            $em->flush();
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId()]);
        }

        return $this->render('connected/map/interact.html.twig', [
            'usePlanet' => $usePlanet,
            'planet' => $planet,
            'form_fleetMove' => $form_sendFleet->createView(),
        ]);
    }

    /**
     * @Route("/envoyer-sonde/{planet}/{usePlanet}", name="send_sonde", requirements={"planet"="\d+", "usePlanet"="\d+"})
     */
    public function sendSondeAction(Planet $planet, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $fPlanet = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->andwhere('p.sonde > :zero')
            ->setParameters(['user' => $user, 'zero' => 0])
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if($fPlanet == null) {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId()]);
        }
        $sFleet= $fPlanet->getSector()->getPosition();
        $sector = $planet->getSector()->getPosition();
        $planete = $planet->getPosition();
        $galaxy = $planet->getSector()->getGalaxy()->getPosition();
        if($user->getHyperespace() == 0 && $fPlanet->getSector()->getGalaxy()->getPosition() != $galaxy) {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'id' => $fPlanet->getSector()->getPosition(), 'gal' => $fPlanet->getSector()->getGalaxy()->getPosition()]);
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
        if(1 > $user->getBitcoin()) {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId()]);
        }
        $fleet = new Fleet();
        $fleet->setSonde(1);
        $fleet->setUser($user);
        $fleet->setPlanet($fPlanet);
        $fleet->setName('Auto Sonde');
        $fleet->setSignature($fleet->getNbrSignatures());
        $fPlanet->setSonde($fPlanet->getSonde() - 1);
        $speed = $fleet->getSpeed();
        $distance = $speed * $base * 100;
        $now->add(new DateInterval('PT' . round($distance) . 'S'));
        $moreNow = new DateTime();
        $moreNow->setTimezone(new DateTimeZone('Europe/Paris'));
        $moreNow->add(new DateInterval('PT' . 120 . 'S'));
        $fleet->setFlightTime($now);
        $destination = new Destination();
        $destination->setFleet($fleet);
        $destination->setPlanet($planet);
        $em->persist($destination);
        $fleet->setFlightType(1);
        $fleet->setCancelFlight($moreNow);
        $user->setBitcoin($user->getBitcoin() - $carburant);
        $em->persist($fleet);
        if ($planet->getUser()) {
            $quest = $user->checkQuests('spy_planet');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }

        $em->flush();
        return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId()]);
    }

    /**
     * @Route("/envoyer-missiles/{planet}/{usePlanet}", name="send_nuclear", requirements={"planet"="\d+", "usePlanet"="\d+"})
     */
    public function sendNuclearAction(Planet $planet, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $fPlanet = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->andwhere('p.nuclearBomb > :zero')
            ->setParameters(['user' => $user, 'zero' => 0])
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if ($fPlanet == null) {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId()]);
        }
        $galaxy = $planet->getSector()->getGalaxy()->getPosition();
        if ($user->getHyperespace() == 0 && $fPlanet->getSector()->getGalaxy()->getPosition() != $galaxy) {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'id' => $fPlanet->getSector()->getPosition(), 'gal' => $fPlanet->getSector()->getGalaxy()->getPosition()]);
        }
        if (1 > $user->getBitcoin()) {
            return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId()]);
        }
        $fleet = new Fleet();
        $fleet->setNuclearBomb(1);
        $fleet->setUser($user);
        $fleet->setPlanet($fPlanet);
        $fleet->setName('Missile nucléaire');
        $fPlanet->setNuclearBomb($fPlanet->getNuclearBomb() - 1);
        $now->add(new DateInterval('PT' . round(1800) . 'S'));
        $fleet->setFlightTime($now);
        $destination = new Destination();
        $destination->setFleet($fleet);
        $destination->setPlanet($planet);
        $em->persist($destination);
        $fleet->setFlightType(6);
        $fleet->setSignature($fleet->getNbrSignatures());
        $em->persist($fleet);
        if ($planet->getUser()) {
            $quest = $user->checkQuests('nuclear_planet');
            if ($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }

        $em->flush();
        return $this->redirectToRoute('map', ['usePlanet' => $usePlanet->getId(), 'sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId()]);
    }
}