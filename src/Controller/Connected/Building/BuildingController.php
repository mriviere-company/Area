<?php

namespace App\Controller\Connected\Building;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use DateTime;
use DateTimeZone;
use Dateinterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class BuildingController extends AbstractController
{
    /**
     * @Route("/batiment/{usePlanet}", name="building", requirements={"usePlanet"="\d+"})
     */
    public function buildingAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if(($user->getTutorial() == 4)) {
            $user->setTutorial(5);
            $em->flush();
        }

        return $this->render('connected/building.html.twig', [
            'usePlanet' => $usePlanet,
            'date' => $now,
        ]);
    }

    /**
     * @Route("/construire-batiment/{building}/{usePlanet}", name="build_building", requirements={"building"="\w+", "usePlanet"="\d+"})
     */
    public function buildBuildingAction(Planet $usePlanet, $building)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getWhichBuilding($building) + 1;
        $pdg = $user->getBuildingWarPoint($building);
        $cost = $user->getBuildingCost($building);
        $time = $user->getBuildingTime($building);
        $niobium = $user->getBuildingNiobium($building);
        $water = $user->getBuildingWater($building);
        $maxLevel = $user->getBuildingMaxLevel($building);
        $usePlanetNb = $usePlanet->getNiobium();
        $usePlanetWt = $usePlanet->getWater();
        $userBt = $user->getBitcoin();
        $userPdg = $user->getRank()->getWarPoint();
        $newGround = $usePlanet->getGroundPlace() + $usePlanet->getBuildingGroundPlace();
        $newSky = $usePlanet->getSkyPlace() + $usePlanet->getBuildingSkyPlace();

        if(($userBt < ($level * $cost)) ||
            ($level == $maxLevel || $user->setConstructAt() > $now) ||
            $user->getWhichBuilding($building) === 0) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round($level * $time) . 'S'));
        $usePlanet->setNiobium($usePlanetNb - ($level * $niobium));
        $usePlanet->setWater($usePlanetWt - ($level * $water));
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setSkyPlace($newSky);
        $usePlanet->setConstruct($building);
        $usePlanet->setConstructAt($now);
        $user->getRank()->setWarPoint($userPdg - ($level * $pdg));
        $user->setBitcoin($userBt - ($level * $cost));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/annuler-construction/{cancelPlanet}/{usePlanet}", name="cancel_construction", requirements={"usePlanet"="\d+", "cancelPlanet"="\d+"})
     */
    public function cancelConstructionAction(Planet $usePlanet,Planet $cancelPlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user || $cancelPlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $build = $cancelPlanet->getConstruct();
        if ($build == 'destruct') {
            return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
        } elseif ($build == 'miner') {
            $level = $cancelPlanet->getMiner() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 225));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 100));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 1);
        } elseif ($build == 'extractor') {
            $level = $cancelPlanet->getExtractor() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 100));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 250));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 1);
        } elseif ($build == 'niobiumStock') {
            $level = $cancelPlanet->getNiobiumStock() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 75000));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 50000));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 3);
        } elseif ($build == 'waterStock') {
            $level = $cancelPlanet->getWaterStock() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 55000));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 90000));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 3);
        } elseif ($build == 'city') {
            $level = $cancelPlanet->getCity() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 7500));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 5500));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 6);
        } elseif ($build == 'metropole') {
            $level = $cancelPlanet->getMetropole() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 36500));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 27500));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 6);
            $cancelPlanet->setSkyPlace($cancelPlanet->getSkyPlace() - 6);
        } elseif ($build == 'caserne') {
            $level = $cancelPlanet->getCaserne() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 6500));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 9500));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 6);
        } elseif ($build == 'bunker') {
            $level = $cancelPlanet->getBunker() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 100000));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 95000));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 10);
        } elseif ($build == 'centerSearch') {
            $level = $cancelPlanet->getCenterSearch() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 1400));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 3500));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 5);
        } elseif ($build == 'lightUsine') {
            $level = $cancelPlanet->getLightUsine() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 3500));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 1900));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 6);
        } elseif ($build == 'heavyUsine') {
            $level = $cancelPlanet->getHeavyUsine() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 41500));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 34000));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 12);
        } elseif ($build == 'spaceShip') {
            $level = $cancelPlanet->getSpaceShip() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 1500));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 1000));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 2);
            $cancelPlanet->setSkyPlace($cancelPlanet->getSkyPlace() - 1);
        } elseif ($build == 'radar') {
            $level = $cancelPlanet->getRadar() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 600));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 300));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 2);
        } elseif ($build == 'skyRadar') {
            $level = $cancelPlanet->getSkyRadar() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 10000));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 8600));
            $cancelPlanet->setSkyPlace($cancelPlanet->getSkyPlace() - 2);
        } elseif ($build == 'skyBrouilleur') {
            $level = $cancelPlanet->getSkyBrouilleur() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 25500));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 16000));
            $cancelPlanet->setSkyPlace($cancelPlanet->getSkyPlace() - 4);
        } elseif ($build == 'nuclearBase') {
            $level = $cancelPlanet->getNuclearBase() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 1000000));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 2);
        } elseif ($build == 'island') {
            $level = $cancelPlanet->getIsland() + 1;
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + ($level * 200000));
        } elseif ($build == 'orbital') {
            $level = $cancelPlanet->getOrbital() + 1;
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + ($level * 200000));
        }
        if(count($cancelPlanet->getConstructions()) > 0) {
            $constructTime = new DateTime();
            $constructTime->setTimezone(new DateTimeZone('Europe/Paris'));
            foreach ($cancelPlanet->getConstructions() as $construction) {
                $cancelPlanet->setConstruct($construction->getConstruct());
                $cancelPlanet->setConstructAt($constructTime->add(new DateInterval('PT' . $construction->getConstructTime() . 'S')));
                $em->remove($construction);
                break;
            }
        } else {
            $cancelPlanet->setConstruct(null);
            $cancelPlanet->setConstructAt(null);
        }
        $em->flush();

        if ($cancelPlanet == $cancelPlanet) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/annuler-construction-liste/{construction}/{usePlanet}", name="building_listCancel", requirements={"usePlanet"="\d+", "construction"="\d+"})
     */
    public function buildingListCancelAction(Planet $usePlanet, Planet $construction)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user || $construction->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $build = $construction->getConstruct();
        $cancelPlanet = $construction->getPlanet();
        if ($build == 'miner') {
            $level = $cancelPlanet->getMiner() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 225));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 100));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 1);
        } elseif ($build == 'extractor') {
            $level = $cancelPlanet->getExtractor() + 1;
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * 100));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * 250));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - 1);
        }

        $em->remove($construction);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }
}