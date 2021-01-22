<?php

namespace App\Controller\Connected\Building;

use App\Entity\Construction;
use Symfony\Component\HttpFoundation\Response;
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
class BuildingController extends AbstractController
{
    /**
     * @Route("/batiment/{usePlanet}", name="building", requirements={"usePlanet"="\d+"})
     */
    public function buildingAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if ($usePlanet->getConstructAt() && $usePlanet->getConstructAt() < $now) {
            $this->forward('App\Controller\Connected\Execute\PlanetController::buildingOneAction', [
                'planet'  => $usePlanet,
                'now' => $now,
                'em' => $em
            ]);
        }

        $seconds = $this->forward('App\Controller\Connected\Execute\ChronosController::planetActivityAction', [
            'planet' => $usePlanet,
            'now'  => $now,
            'em' => $em]);

        if ($seconds->getContent() >= 60) {
            $this->forward('App\Controller\Connected\Execute\PlanetsGenController::planetGenAction', [
                'planet' => $usePlanet,
                'seconds' => $seconds->getContent(),
                'now'  => $now,
                'em' => $em]);
        }

        if(($user->getTutorial() == 4)) {
            $user->setTutorial(5);
            $em->flush();
        }

        return $this->render('connected/building.html.twig', [
            'usePlanet' => $usePlanet
        ]);
    }

    /**
     * @Route("/construire-batiment/{building}/{usePlanet}", name="build_building", requirements={"building"="\w+", "usePlanet"="\d+"})
     */
    public function buildBuildingAction(Planet $usePlanet, $building)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getWhichBuilding($building, $usePlanet) + 1;
        $pdg = $user->getBuildingWarPoint($building);
        $time = $user->getBuildingTime($building);
        $niobium = $user->getBuildingNiobium($building);
        $water = $user->getBuildingWater($building);
        $restrict = $user->getBuildingRestrict($building, $level, $usePlanet);
        $usePlanetNb = $usePlanet->getNiobium();
        $usePlanetWt = $usePlanet->getWater();
        $userPdg = $user->getRank()->getWarPoint();
        $newGround = $usePlanet->getGroundPlace() + $user->getBuildingGroundPlace($building);
        $newSky = $usePlanet->getSkyPlace() + $user->getBuildingSkyPlace($building);
        if ($user->getBot() == 1) {
            $usePlanetNb = $user->getBuildingNiobium($building) + 1;
            $usePlanetWt = $user->getBuildingWater($building) + 1;
            $restrict = 'continue';
        }

        if((!$restrict || $newGround > $usePlanet->getGround()) ||
            ($newSky > $usePlanet->getSky() || $niobium > $usePlanetNb) ||
            ($water > $usePlanetWt || $pdg > $userPdg)) {
            if ($user->getBot() == 1) {
                return new Response ('false');
            }
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        if ($usePlanet->getConstructAt() > $now) {
            $level = $level + $usePlanet->getConstructionsLike($building) + ($usePlanet->getConstruct() && $usePlanet->getConstruct() == $building ? 1 : 0);
            $construction = new Construction();
            $construction->setConstruct($building);
            $construction->setConstructTime($level * $time);
            $construction->setPlanet($usePlanet);
            $usePlanet->setGroundPlace($newGround);
            $usePlanet->setSkyPlace($newSky);
            if ($user->getBot() == 0) {
                $usePlanet->setNiobium($usePlanetNb - ($level * $niobium));
                $usePlanet->setWater($usePlanetWt - ($level * $water));
                $user->getRank()->setWarPoint($userPdg - ($level * $pdg));
            }
            $em->persist($construction);
            if(($user->getTutorial() == 6)) {
                $user->setTutorial(7);
            }
        } else {
            $now->add(new DateInterval('PT' . round($level * $time) . 'S'));
            $usePlanet->setGroundPlace($newGround);
            $usePlanet->setSkyPlace($newSky);
            $usePlanet->setConstruct($building);
            $usePlanet->setConstructAt($now);
            if ($user->getBot() == 0) {
                $usePlanet->setNiobium($usePlanetNb - ($level * $niobium));
                $usePlanet->setWater($usePlanetWt - ($level * $water));
                $user->getRank()->setWarPoint($userPdg - ($level * $pdg));
            }
            if(($user->getTutorial() == 5)) {
                $user->setTutorial(6);
            }
        }
        $em->flush();

        if ($user->getBot() == 1) {
            return new Response ('true');
        }
        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/annuler-construction/{cancelPlanet}/{usePlanet}", name="cancel_construction", requirements={"usePlanet"="\d+", "cancelPlanet"="\d+"})
     */
    public function cancelConstructionAction(Planet $usePlanet,Planet $cancelPlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user || $cancelPlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $building = $cancelPlanet->getConstruct();
        $level = $user->getWhichBuilding($building, $cancelPlanet) + 1;
        if ($building == 'destruct') {
            return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
        } else {
            $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * $user->getBuildingNiobium($building)));
            $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * $user->getBuildingWater($building)));
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + ($level * $user->getBuildingWarPoint($building)));
            $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - $user->getBuildingGroundPlace($building));
            $cancelPlanet->setSkyPlace($cancelPlanet->getSkyPlace() - $user->getBuildingSkyPlace($building));
        }
        if(count($cancelPlanet->getConstructions()) > 0) {
            $constructTime = new DateTime();
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

        if ($cancelPlanet == $usePlanet) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/annuler-construction-liste/{construction}/{usePlanet}", name="building_listCancel", requirements={"construction"="\d+", "usePlanet"="\d+"})
     */
    public function buildingListCancelAction(Construction $construction, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user || $construction->getPlanet() != $usePlanet) {
            var_dump($user->getId());
            var_dump($construction->getUser()->getId());
            exit;
            return $this->redirectToRoute('home');
        }
        $cancelPlanet = $construction->getPlanet();
        $building = $cancelPlanet->getConstruct();
        $level = $user->getWhichBuilding($building, $cancelPlanet) + 1;
        $cancelPlanet->setNiobium($cancelPlanet->getNiobium() + ($level * $user->getBuildingNiobium($building)));
        $cancelPlanet->setWater($cancelPlanet->getWater() + ($level * $user->getBuildingWater($building)));
        $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + ($level * $user->getBuildingWarPoint($building)));
        $cancelPlanet->setGroundPlace($cancelPlanet->getGroundPlace() - $user->getBuildingGroundPlace($building));
        $cancelPlanet->setSkyPlace($cancelPlanet->getSkyPlace() - $user->getBuildingSkyPlace($building));

        $em->remove($construction);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }
}