<?php

namespace App\Controller\Connected;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use App\Entity\Fleet;
use DateTime;
use DateTimeZone;
use Dateinterval;

class DeployController extends AbstractController
{
    /**
     * @Route("/deployer-radar/{fleet}/{usePlanet}", name="deploy_radar", requirements={"usePlanet"="\d+", "fleet"="\d+"})
     */
    public function deployRadarAction(Planet $usePlanet, Fleet $fleet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $now->add(new DateInterval('PT' . 7200 . 'S'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user || $fleet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }
        $planet = $fleet->getPlanet();

        if($fleet->getRadarShip() && $planet->getEmpty() == true) {
            $fleet->setRadarShip($fleet->getRadarShip() - 1);
            if($planet->getSkyRadar()) {
                $planet->setSkyRadar($planet->getSkyRadar() + 1);
            } else {
                $planet->setUser($fleet->getUser());
                $planet->setName('Radar');
                $planet->setSkyRadar(1);
                $planet->setRadarAt($now);
            }

            if($fleet->getNbrShips() == 0) {
                $em->remove($fleet);
            }
            $em->flush();
        }

        return $this->redirectToRoute('map', ['sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/deployer-brouilleur/{fleet}/{usePlanet}", name="deploy_brouilleur", requirements={"usePlanet"="\d+", "fleet"="\d+"})
     */
    public function deployBrouilleurAction(Planet $usePlanet, Fleet $fleet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $now->add(new DateInterval('PT' . 3600 . 'S'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user || $fleet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }
        $planet = $fleet->getPlanet();

        if($fleet->getBrouilleurShip() && $planet->getEmpty() == true) {
            $fleet->setBrouilleurShip($fleet->getBrouilleurShip() - 1);
            if($planet->getSkyBrouilleur()) {
                $planet->setSkyBrouilleur($planet->getSkyBrouilleur() + 1);
            } else {
                $planet->setUser($fleet->getUser());
                $planet->setName('Brouilleur');
                $planet->setSkyBrouilleur(1);
                $planet->setBrouilleurAt($now);
            }

            if($fleet->getNbrShips() == 0) {
                $em->remove($fleet);
            }
            $em->flush();
        }

        return $this->redirectToRoute('map', ['sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/deployer-lunar/{fleet}/{usePlanet}", name="deploy_moonMaker", requirements={"usePlanet"="\d+", "fleet"="\d+"})
     */
    public function deployMoonMakerAction(Planet $usePlanet, Fleet $fleet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user || $fleet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }
        $planet = $fleet->getPlanet();

        if($fleet->getMoonMaker() && $planet->getEmpty() == true &&
            $planet->getNbCdr() > 10000000 && $planet->getWtCdr() > 10000000) {
            $fleet->setMoonMaker($fleet->getMoonMaker() - 1);
            $planet->setUser($fleet->getUser());
            $planet->setEmpty(false);
            $planet->setMoon(true);
            $planet->setNbProduction(0);
            $planet->setWtProduction(0);
            $planet->setScientist(0);
            $planet->setName('Lune');
            $image = ['moon1.png', 'moon2.png', 'moon3.png', 'moon4.png', 'moon5.png'];
            $planet->setImageName($image[rand(0, 4)]);
            if ($planet->getNbCdr() > 2000000 && $planet->getWtCdr() > 2000000) {
                if ($planet->getNbCdr() < 5000000 && $planet->getWtCdr() < 5000000) {
                    $planet->setGround(rand(100, 150));
                    $planet->setSky(rand(10, 25));
                } elseif ($planet->getNbCdr() < 10000000 && $planet->getWtCdr() < 10000000) {
                    $planet->setGround(rand(150, 180));
                    $planet->setSky(rand(8, 23));
                } elseif ($planet->getNbCdr() < 20000000 && $planet->getWtCdr() < 20000000) {
                    $planet->setGround(rand(180, 210));
                    $planet->setSky(rand(6, 21));
                } elseif ($planet->getNbCdr() < 50000000 && $planet->getWtCdr() < 50000000) {
                    $planet->setGround(rand(210, 240));
                    $planet->setSky(rand(4, 19));
                } elseif ($planet->getNbCdr() < 75000000 && $planet->getWtCdr() < 75000000) {
                    $planet->setGround(rand(240, 280));
                    $planet->setSky(rand(2, 17));
                } else {
                    $planet->setGround(rand(280, 350));
                    $planet->setSky(rand(15, 50));
                }
            }
            $planet->setNbCdr(0);
            $planet->setWtCdr(0);

            if($fleet->getNbrShips() == 0) {
                $em->remove($fleet);
            }
            $em->flush();
        }

        return $this->redirectToRoute('map', ['sector' => $planet->getSector()->getId(), 'gal' => $planet->getSector()->getGalaxy()->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/relancer-recyclage/{fleet}/{usePlanet}", name="recycle_again", requirements={"usePlanet"="\d+", "fleet"="\d+"})
     */
    public function recycleAgainAction(Planet $usePlanet, Fleet $fleet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();

        if ($usePlanet->getUser() != $user || $fleet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $fleet->setRecycleAt($now);
        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
    }
}