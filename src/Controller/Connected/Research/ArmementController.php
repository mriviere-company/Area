<?php

namespace App\Controller\Connected\Research;

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
     */
    public function researchArmementAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getArmement() + 1;
        $userBt = $user->getBitcoin();
        if(($userBt < ($level * 2000)) ||
            ($level == 6 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . round(($level * 370 / $user->getScientistProduction())) . 'S')); // X10 NORMAL GAME
        $user->setSearch('armement');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 2000));
        if(($user->getTutorial() == 8)) {
            $user->setTutorial(9);
        }
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-missile/{usePlanet}", name="research_missile", requirements={"usePlanet"="\d+"})
     */
    public function researchMissileAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getMissile() + 1;
        $userBt = $user->getBitcoin();
        if(($userBt < ($level * 2600) || $user->getArmement() < 0) ||
            ($level == 4 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . round(($level * 450 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('missile');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 2600));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-laser/{usePlanet}", name="research_laser", requirements={"usePlanet"="\d+"})
     */
    public function researchLaserAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getLaser() + 1;
        $userBt = $user->getBitcoin();
        if(($userBt < ($level * 13000) || $user->getArmement() < 2) ||
            ($level == 4 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . round(($level * 1800 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('laser');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 13000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-plasma/{usePlanet}", name="research_plasma", requirements={"usePlanet"="\d+"})
     */
    public function researchPlasmaAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getPlasma() + 1;
        $userBt = $user->getBitcoin();
        if(($userBt < ($level * 29000) || $user->getArmement() < 4) ||
            ($level == 4 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . round(($level * 4680 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('plasma');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 29000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }
}