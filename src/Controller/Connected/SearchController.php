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
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if($character->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if($user->getTutorial() == 7) {
            $user->setTutorial(8);
            $em->flush();
        }

        if($user->getTutorial() == 8 && $character->getSearchAt()) {
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
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $research = $character->getSearch();
        if ($research == 'onde') {
            $level = $character->getOnde() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 2300));
        } elseif ($research == 'industry') {
            $level = $character->getIndustry() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 1500));
        } elseif ($research == 'discipline') {
            $level = $character->getDiscipline() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 11700));
        } elseif ($research == 'hyperespace') {
            $level = $character->getHyperespace() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 25000000));
        } elseif ($research == 'barge') {
            $level = $character->getBarge() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 35000));
        } elseif ($research == 'utility') {
            $level = $character->getUtility() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 2500));
        } elseif ($research == 'demography') {
            $level = $character->getDemography() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 8000));
        } elseif ($research == 'aeroponicFarm') {
            $level = $character->getAeroponicFarm() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 16000));
        } elseif ($research == 'terraformation') {
            $level = $character->getTerraformation() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 12000));
        } elseif ($research == 'cargo') {
            $level = $character->getCargo() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 3500));
        } elseif ($research == 'recycleur') {
            $level = $character->getRecycleur() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 16900));
        } elseif ($research == 'armement') {
            $level = $character->getArmement() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 2000));
        } elseif ($research == 'missile') {
            $level = $character->getMissile() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 2600));
        } elseif ($research == 'laser') {
            $level = $character->getLaser() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 13000));
        } elseif ($research == 'plasma') {
            $level = $character->getPlasma() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 29000));
        } elseif ($research == 'lightShip') {
            $level = $character->getLightShip() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 9000));
        } elseif ($research == 'heavyShip') {
            $level = $character->getHeavyShip() + 1;
            $character->setBitcoin($character->getBitcoin() + ($level * 42000));
        } elseif ($character->getWhichResearch($research) === 0 || $character->getWhichResearch($research) === -1) {
            $character->setBitcoin($character->getBitcoin() + (($character->getWhichResearch($research) + 1) * $character->getResearchCost($research)));
        }
        $character->setSearch(null);
        $character->setSearchAt(null);

        $em->flush();

        return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
    }
}