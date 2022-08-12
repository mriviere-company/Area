<?php

namespace App\Controller\Connected\Research;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use DateTime;
use Dateinterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class ArmementController extends AbstractController
{
    /**
     * @Route("/rechercher-armement/{usePlanet}", name="research_armement", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchArmementAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getArmement() + 1;
        $commanderBt = $commander->getBitcoin();
        if(($commanderBt < ($level * 2000)) ||
            ($level == 6 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . round(($level * 370 / $commander->getScientistProduction())) . 'S')); // X10 NORMAL GAME
        $commander->setSearch('armement');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 2000));
        if(($user->getTutorial() == 8)) {
            $user->setTutorial(9);
        }
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-missile/{usePlanet}", name="research_missile", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchMissileAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getMissile() + 1;
        $commanderBt = $commander->getBitcoin();
        if(($commanderBt < ($level * 2600) || $commander->getArmement() < 0) ||
            ($level == 4 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . round(($level * 450 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('missile');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 2600));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-laser/{usePlanet}", name="research_laser", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchLaserAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getLaser() + 1;
        $commanderBt = $commander->getBitcoin();
        if(($commanderBt < ($level * 13000) || $commander->getArmement() < 2) ||
            ($level == 4 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . round(($level * 1800 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('laser');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 13000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-plasma/{usePlanet}", name="research_plasma", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchPlasmaAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getPlasma() + 1;
        $commanderBt = $commander->getBitcoin();
        if(($commanderBt < ($level * 29000) || $commander->getArmement() < 4) ||
            ($level == 4 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . round(($level * 4680 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('plasma');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 29000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }
}