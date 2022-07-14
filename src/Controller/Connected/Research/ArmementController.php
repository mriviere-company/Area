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
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $level = $character->getArmement() + 1;
        $characterBt = $character->getBitcoin();
        if(($characterBt < ($level * 2000)) ||
            ($level == 6 || $character->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . round(($level * 370 / $character->getScientistProduction())) . 'S')); // X10 NORMAL GAME
        $character->setSearch('armement');
        $character->setSearchAt($now);
        $character->setBitcoin($characterBt - ($level * 2000));
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
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $level = $character->getMissile() + 1;
        $characterBt = $character->getBitcoin();
        if(($characterBt < ($level * 2600) || $character->getArmement() < 0) ||
            ($level == 4 || $character->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . round(($level * 450 / $character->getScientistProduction())) . 'S'));
        $character->setSearch('missile');
        $character->setSearchAt($now);
        $character->setBitcoin($characterBt - ($level * 2600));
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
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $level = $character->getLaser() + 1;
        $characterBt = $character->getBitcoin();
        if(($characterBt < ($level * 13000) || $character->getArmement() < 2) ||
            ($level == 4 || $character->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . round(($level * 1800 / $character->getScientistProduction())) . 'S'));
        $character->setSearch('laser');
        $character->setSearchAt($now);
        $character->setBitcoin($characterBt - ($level * 13000));
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
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $level = $character->getPlasma() + 1;
        $characterBt = $character->getBitcoin();
        if(($characterBt < ($level * 29000) || $character->getArmement() < 4) ||
            ($level == 4 || $character->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . round(($level * 4680 / $character->getScientistProduction())) . 'S'));
        $character->setSearch('plasma');
        $character->setSearchAt($now);
        $character->setBitcoin($characterBt - ($level * 29000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }
}