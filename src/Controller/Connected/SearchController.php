<?php

namespace App\Controller\Connected;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class SearchController extends AbstractController
{
    /**
     * @Route("/recherche/{usePlanet}", name="search", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function searchAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if($commander->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if($user->getTutorial() == 7) {
            $user->setTutorial(8);
            $em->flush();
        }

        if($user->getTutorial() == 8 && $commander->getSearchAt()) {
            $user->setTutorial(9);
            $em->flush();
        }

        return $this->render('connected/search.html.twig', [
            'usePlanet' => $usePlanet,
        ]);
    }

    /**
     * @Route("/annuler-rechercher/{usePlanet}", name="research_cancel", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function researchCancelAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $research = $commander->getSearch();
        if ($research == 'onde') {
            $level = $commander->getOnde() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 2300));
        } elseif ($research == 'industry') {
            $level = $commander->getIndustry() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 1500));
        } elseif ($research == 'discipline') {
            $level = $commander->getDiscipline() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 11700));
        } elseif ($research == 'hyperespace') {
            $level = $commander->getHyperespace() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 25000000));
        } elseif ($research == 'barge') {
            $level = $commander->getBarge() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 35000));
        } elseif ($research == 'utility') {
            $level = $commander->getUtility() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 2500));
        } elseif ($research == 'demography') {
            $level = $commander->getDemography() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 8000));
        } elseif ($research == 'aeroponicFarm') {
            $level = $commander->getAeroponicFarm() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 16000));
        } elseif ($research == 'terraformation') {
            $level = $commander->getTerraformation() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 12000));
        } elseif ($research == 'cargo') {
            $level = $commander->getCargo() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 3500));
        } elseif ($research == 'recycleur') {
            $level = $commander->getRecycleur() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 16900));
        } elseif ($research == 'armement') {
            $level = $commander->getArmement() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 2000));
        } elseif ($research == 'missile') {
            $level = $commander->getMissile() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 2600));
        } elseif ($research == 'laser') {
            $level = $commander->getLaser() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 13000));
        } elseif ($research == 'plasma') {
            $level = $commander->getPlasma() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 29000));
        } elseif ($research == 'lightShip') {
            $level = $commander->getLightShip() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 9000));
        } elseif ($research == 'heavyShip') {
            $level = $commander->getHeavyShip() + 1;
            $commander->setBitcoin($commander->getBitcoin() + ($level * 42000));
        } elseif ($commander->getWhichResearch($research) === 0 || $commander->getWhichResearch($research) === -1) {
            $commander->setBitcoin($commander->getBitcoin() + (($commander->getWhichResearch($research) + 1) * $commander->getResearchCost($research)));
        }
        $commander->setSearch(null);
        $commander->setSearchAt(null);

        $em->flush();

        return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
    }
}