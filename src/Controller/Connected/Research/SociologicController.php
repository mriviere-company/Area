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
class SociologicController extends AbstractController
{
    /**
     * @Route("/rechercher-aeroponique/{usePlanet}", name="research_aeroponic", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchAeroponicAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getAeroponicFarm() + 1;
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < ($level * 16000)) ||
            ($level == 1 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 480 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('aeroponicFarm');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 16000));
        if(($user->getTutorial() == 8)) {
            $user->setTutorial(9);
        }
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-demographie/{usePlanet}", name="research_demography", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchDemographyAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getDemography() + 1;
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < ($level * 8000)) ||
            ($level == 6 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 480 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('demography');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 8000));
        if(($user->getTutorial() == 8)) {
            $user->setTutorial(9);
        }
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-discipline/{usePlanet}", name="research_discipline", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchDisciplineAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getDiscipline() + 1;
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < ($level * 11700) || $commander->getDemography() == 0) ||
            ($level == 4 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 930 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('discipline');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 11700));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-barbele/{usePlanet}", name="research_barbed", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchBarbedAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getBarbed() + 1;
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < ($level * 200000) || $commander->getDiscipline() != 3) ||
            ($level == 6 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 1200 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('barbed');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 200000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-tank/{usePlanet}", name="research_tank", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchTankAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getTank() + 1;
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < ($level * 40000) || $commander->getDiscipline() != 3) ||
            ($level == 2 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 2000 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('tank');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 40000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-expansion/{usePlanet}", name="research_expansion", requirements={"usePlanet"="\d+"})
     */
    public function researchExpansionAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getExpansion() + 1;
        $userPdg = $commander->getRank()->getWarPoint();

        if(($userPdg < ($level * 75000) || $commander->getTerraformation() <= 17) ||
            ($level == 3 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 6000 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('expansion');
        $commander->setSearchAt($now);
        $commander->getRank()->setWarPoint($userPdg - ($level * 75000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }
}