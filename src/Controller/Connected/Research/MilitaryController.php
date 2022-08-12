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
class MilitaryController extends AbstractController
{
    /**
     * @Route("/rechercher-industrie/{usePlanet}", name="research_industry", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchIndustryAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getIndustry() + 1;
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < ($level * 1500)) ||
            ($level == 6 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 150 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('industry');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 1500));
        if(($user->getTutorial() == 8)) {
            $user->setTutorial(9);
        }
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-vaisseaux-leger/{usePlanet}", name="research_light_ship", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchLightShipAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getLightShip() + 1;
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < ($level * 9000) || $commander->getIndustry() < 3) ||
            ($level == 4 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 860 / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch('lightShip');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 9000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-vaisseaux-lourd/{usePlanet}", name="research_heavy_ship", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchHeavyShipAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getHeavyShip() + 1;
        $commanderBt = $commander->getBitcoin();

        if(($commanderBt < ($level * 42000) || $commander->getIndustry() < 5) ||
            ($level == 4 || $commander->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 1200 / $commander->getScientistProduction())) . 'S')); // X100 NORMAL GAME
        $commander->setSearch('heavyShip');
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * 42000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }
}