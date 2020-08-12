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
class ExplorerController extends AbstractController
{
    /**
     * @Route("/rechercher-onde/{usePlanet}", name="research_onde", requirements={"usePlanet"="\d+"})
     */
    public function researchOndeAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getOnde() + 1;
        $userBt = $user->getBitcoin();

        if(($userBt < ($level * 2300)) ||
            ($level == 6 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 300 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('onde');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 2300));
        if(($user->getTutorial() == 8)) {
            $user->setTutorial(9);
        }
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-terraformation/{usePlanet}", name="research_terraformation", requirements={"usePlanet"="\d+"})
     */
    public function researchTerraformationAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getTerraformation() + 1;
        $userBt = $user->getBitcoin();

        if(($userBt < ($level * 12000) || $user->getUtility() == 0) ||
            ($level > 19 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 4200) / $user->getScientistProduction()) . 'S')); // X100 NORMAL GAME
        $user->setSearch('terraformation');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 12000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-cargo/{usePlanet}", name="research_cargo", requirements={"usePlanet"="\d+"})
     */
    public function researchCargoAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getCargo() + 1;
        $userBt = $user->getBitcoin();

        if(($userBt < ($level * 3500) || $user->getUtility() < 2) ||
            ($level == 6 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 400 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('cargo');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 3500));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-recyclage/{usePlanet}", name="research_recyclage", requirements={"usePlanet"="\d+"})
     */
    public function researchRecyclageAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getRecycleur();
        $userBt = $user->getBitcoin();

        if(($userBt < 16900 || $user->getUtility() < 3) ||
            ($level == 1 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(400 / $user->getScientistProduction()) . 'S'));
        $user->setSearch('recycleur');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - 16900);
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-barge/{usePlanet}", name="research_barge", requirements={"usePlanet"="\d+"})
     */
    public function researchBargeAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getBarge();
        $userBt = $user->getBitcoin();

        if(($userBt < 35000 || $user->getUtility() < 3) ||
            ($level == 1 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(280 / $user->getScientistProduction()) . 'S')); // X100 NORMAL GAME
        $user->setSearch('barge');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - 35000);
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-utilitaire/{usePlanet}", name="research_utility", requirements={"usePlanet"="\d+"})
     */
    public function researchUtilityAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getUtility() + 1;
        $userBt = $user->getBitcoin();

        if(($userBt < ($level * 2500)) ||
            ($level == 4 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 200 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('utility');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 2500));
        if(($user->getTutorial() == 8)) {
            $user->setTutorial(9);
        }
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-hyperespace/{usePlanet}", name="research_hyperespace", requirements={"usePlanet"="\d+"})
     */
    public function researchHyperespaceAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getHyperespace();
        $userBt = $user->getBitcoin();

        if(($userBt < 25000000 || $user->getUtility() < 3) ||
            ($level == 1 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(6048 / $user->getScientistProduction()) . 'S')); // X100 NORMAL GAME
        $user->setSearch('hyperespace');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - 25000000);
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }
}