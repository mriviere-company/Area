<?php

namespace App\Controller\Connected;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use App\Entity\Fleet;
use DateTime;
use Dateinterval;

class DeployController extends AbstractController
{
    /**
     * @Route("/deployer-radar/{fleet}/{usePlanet}", name="deploy_radar", requirements={"usePlanet"="\d+", "fleet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @param Fleet $fleet
     * @return RedirectResponse
     * @throws Exception
     */
    public function deployRadarAction(ManagerRegistry $doctrine, Planet $usePlanet, Fleet $fleet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $now->add(new DateInterval('PT' . 7200 . 'S'));
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander || $fleet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }
        $planet = $fleet->getPlanet();

        if($fleet->getRadarShip() && $planet->getEmpty()) {
            $fleet->setRadarShip($fleet->getRadarShip() - 1);
            if($planet->getSkyRadar()) {
                $planet->setSkyRadar($planet->getSkyRadar() + 1);
            } else {
                $planet->setCommander($fleet->getCommander());
                $planet->setName('Radar');
                $planet->setSkyRadar(1);
                $planet->setRadarAt($now);
            }

            if($fleet->getNbrShips() == 0) {
                $em->remove($fleet);
            }
            $em->flush();
        }

        return $this->redirectToRoute('map', ['sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/deployer-brouilleur/{fleet}/{usePlanet}", name="deploy_brouilleur", requirements={"usePlanet"="\d+", "fleet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @param Fleet $fleet
     * @return RedirectResponse
     * @throws Exception
     */
    public function deployBrouilleurAction(ManagerRegistry $doctrine, Planet $usePlanet, Fleet $fleet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $now->add(new DateInterval('PT' . 3600 . 'S'));
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander || $fleet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }
        $planet = $fleet->getPlanet();

        if($fleet->getBrouilleurShip() && $planet->getEmpty()) {
            $fleet->setBrouilleurShip($fleet->getBrouilleurShip() - 1);
            if($planet->getSkyBrouilleur()) {
                $planet->setSkyBrouilleur($planet->getSkyBrouilleur() + 1);
            } else {
                $planet->setCommander($fleet->getCommander());
                $planet->setName('Brouilleur');
                $planet->setSkyBrouilleur(1);
                $planet->setBrouilleurAt($now);
            }

            if($fleet->getNbrShips() == 0) {
                $em->remove($fleet);
            }
            $em->flush();
        }

        return $this->redirectToRoute('map', ['sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/deployer-lunar/{fleet}/{usePlanet}", name="deploy_moonMaker", requirements={"usePlanet"="\d+", "fleet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @param Fleet $fleet
     * @return RedirectResponse
     */
    public function deployMoonMakerAction(ManagerRegistry $doctrine, Planet $usePlanet, Fleet $fleet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander || $fleet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }
        $planet = $fleet->getPlanet();

        if($fleet->getMoonMaker() && $planet->getEmpty() && !$planet->getCdr() &&
            $planet->getNbCdr() > 750000 && $planet->getWtCdr() > 750000) {
            $fleet->setMoonMaker($fleet->getMoonMaker() - 1);
            $planet->setCommander($fleet->getCommander());
            $planet->setEmpty(false);
            $planet->setMoon(true);
            $planet->setNbProduction(0);
            $planet->setWtProduction(0);
            $planet->setScientist(0);
            $planet->setName('Lune');
            $image = ['moon1.webp', 'moon2.webp', 'moon3.webp', 'moon4.webp', 'moon5.webp'];
            $planet->setImageName($image[rand(0, 4)]);
            if ($planet->getNbCdr() < 750000 && $planet->getWtCdr() < 750000) {
                $planet->setGround(rand(100, 150));
                $planet->setSky(rand(10, 25));
            } elseif ($planet->getNbCdr() < 1000000 && $planet->getWtCdr() < 1000000) {
                $planet->setGround(rand(150, 180));
                $planet->setSky(rand(8, 23));
            } elseif ($planet->getNbCdr() < 2000000 && $planet->getWtCdr() < 2000000) {
                $planet->setGround(rand(180, 210));
                $planet->setSky(rand(6, 21));
            } elseif ($planet->getNbCdr() < 4000000 && $planet->getWtCdr() < 4000000) {
                $planet->setGround(rand(210, 240));
                $planet->setSky(rand(4, 19));
            } elseif ($planet->getNbCdr() < 8000000 && $planet->getWtCdr() < 8000000) {
                $planet->setGround(rand(240, 280));
                $planet->setSky(rand(2, 17));
            } else {
                $planet->setGround(rand(280, 350));
                $planet->setSky(rand(15, 50));
            }
            $planet->setNbCdr(0);
            $planet->setWtCdr(0);

            if($fleet->getNbrShips() == 0) {
                $em->remove($fleet);
            }
            $em->flush();
        }

        return $this->redirectToRoute('map', ['sector' => $planet->getSector()->getId(), 'galaxy' => $planet->getSector()->getGalaxy()->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/relancer-recyclage/{fleet}/{usePlanet}", name="recycle_again", requirements={"usePlanet"="\d+", "fleet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @param Fleet $fleet
     * @return RedirectResponse
     * @throws Exception
     */
    public function recycleAgainAction(ManagerRegistry $doctrine, Planet $usePlanet, Fleet $fleet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $now->add(new DateInterval('PT' . 300 . 'S'));
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander || $fleet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }
        if (($fleet->getPlanet()->getNbCdr() > 0 || $fleet->getPlanet()->getWtCdr()) > 0 && $fleet->getCargoPlace() != $fleet->getCargoFull()) {
            $fleet->setRecycleAt($now);
        }
        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
    }
}