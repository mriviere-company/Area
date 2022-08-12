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
class ExplorerController extends AbstractController
{
    /**
     * @Route("/rechercher-onde/{usePlanet}", name="research_onde", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchOndeAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getOnde() + 1;
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < ($level * 2300)) ||
            ($level == 6 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 300 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('onde');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 2300));
        if(($user->getTutorial() == 8)) {
            $user->setTutorial(9);
        }
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-terraformation/{usePlanet}", name="research_terraformation", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchTerraformationAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getTerraformation() + 1;
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < ($level * 12000) || $commander->getUtility() == 0) ||
            ($level > 19 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 4200) / $commander->getScientistProduction()) . 'S')); // X100 NORMAL GAME
        $commander->setSearch('terraformation');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 12000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-cargo/{usePlanet}", name="research_cargo", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchCargoAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getCargo() + 1;
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < ($level * 3500) || $commander->getUtility() < 2) ||
            ($level == 6 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 400 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('cargo');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 3500));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-recyclage/{usePlanet}", name="research_recyclage", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchRecyclageAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getRecycleur();
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < 16900 || $commander->getUtility() < 3) ||
            ($level == 1 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(400 / $commander->getScientistProduction()) . 'S'));
        $commander->setSearch('recycleur');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - 16900);
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-barge/{usePlanet}", name="research_barge", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchBargeAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getBarge();
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < 35000 || $commander->getUtility() < 3) ||
            ($level == 1 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(280 / $commander->getScientistProduction()) . 'S')); // X100 NORMAL GAME
        $commander->setSearch('barge');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - 35000);
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-utilitaire/{usePlanet}", name="research_utility", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchUtilityAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getUtility() + 1;
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < ($level * 2500)) ||
            ($level == 4 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 200 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('utility');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 2500));
        if(($user->getTutorial() == 8)) {
            $user->setTutorial(9);
        }
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-hyperespace/{usePlanet}", name="research_hyperespace", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchHyperespaceAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getHyperespace();
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < 25000000 || $commander->getUtility() < 3) ||
            ($level == 1 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(6048 / $commander->getScientistProduction()) . 'S')); // X100 NORMAL GAME
        $commander->setSearch('hyperespace');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - 25000000);
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }
}