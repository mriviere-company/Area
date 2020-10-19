<?php

namespace App\Controller\Connected\Building;

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
class DestroyController extends AbstractController
{
    /**
     * @Route("/detruire-mine/{usePlanet}", name="building_remove_mine", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveMineAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getMiner();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('miner');
        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 60 . 'S'));
        $usePlanet->setMiner($level - 1);
        $usePlanet->setNbProduction($usePlanet->getMiner() * 22);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-puit/{usePlanet}", name="building_remove_extract", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveExtractAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getExtractor();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('extractor');
        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 60 . 'S'));
        $usePlanet->setExtractor($level - 1);
        $usePlanet->setWtProduction($usePlanet->getExtractor() * 15);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-ferme/{usePlanet}", name="building_remove_farm", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveFarmAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getFarm();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('farm');
        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 60 . 'S'));
        $usePlanet->setFarm($level - 1);
        $usePlanet->setFdProduction($usePlanet->getFarm() * 18);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-ferme-aeroponique/{usePlanet}", name="building_remove_aeroponic_farm", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveAeroponicFarmAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getAeroponicFarm();
        $newSky = $usePlanet->getSkyPlace() - $user->getBuildingSkyPlace('aeroponicFarm');
        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 60 . 'S'));
        $usePlanet->setAeroponicFarm($level - 1);
        $usePlanet->setFdProduction($usePlanet->getAeroponicFarm() * 25);
        $usePlanet->setSkyPlace($newSky);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-stockage-niobium/{usePlanet}", name="building_remove_niobiumStock", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveNiobiumStockAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getNiobiumStock();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('niobiumStock');
        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setNiobiumStock($level - 1);
        $usePlanet->setNiobiumMax($usePlanet->getNiobiumMax() - 5000000);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-stockage-eau/{usePlanet}", name="building_remove_waterStock", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveWaterStockAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getWaterStock();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('waterStock');
        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setWaterMax($usePlanet->getWaterMax() - 5000000);
        $usePlanet->setWaterStock($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-silos/{usePlanet}", name="building_remove_silos", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveSilosAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getSilos();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('silos');
        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setFoodMax($usePlanet->getFoodMax() - 5000000);
        $usePlanet->setSilos($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-laboratoire/{usePlanet}", name="building_remove_search", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveSearchAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getCenterSearch();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('centerSearch');

        if(($level == 0 || $usePlanet->getConstructAt() > $now) ||
            ($usePlanet->getScientist() > $usePlanet->getScientistMax() - 500)) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setCenterSearch($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setScientistMax($usePlanet->getScientistMax() - 500);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-ville/{usePlanet}", name="building_remove_city", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveCityAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getCity();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('city');

        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setCity($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setWorkerMax($usePlanet->getWorkerMax() - 125000);
        $usePlanet->setWorkerProduction($usePlanet->getWorkerProduction() - 5.56);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-metropole/{usePlanet}", name="building_remove_metropole", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveMetropoleAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getMetropole();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('metropole');
        $newSky = $usePlanet->getSkyPlace() - $user->getBuildingSkyPlace('metropole');

        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setMetropole($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setWorkerMax($usePlanet->getWorkerMax() - 400000);
        $usePlanet->setWorkerProduction($usePlanet->getWorkerProduction() - 8.32);
        $usePlanet->setSkyPlace($newSky);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-chantier-spatiale/{usePlanet}", name="building_remove_spaceShipyard", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveSpaceShipyardAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getSpaceShip();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('spaceShip');
        $newSky = $usePlanet->getSkyPlace() - $user->getBuildingSkyPlace('spaceShip');

        if(($level == 0 || $usePlanet->getConstructAt() > $now)
            || ($usePlanet->getProduct() && $level < 1)) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setSpaceShip($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setShipProduction($usePlanet->getShipProduction() - 0.1);
        $usePlanet->setSkyPlace($newSky);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-usine-legere/{usePlanet}", name="building_remove_lightUsine", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveLightUsineAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getLightUsine();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('lightUsine');

        if(($level == 0 || $usePlanet->getConstructAt() > $now)
            || $usePlanet->getProduct()) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setLightUsine($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setShipProduction($usePlanet->getShipProduction() - 0.15);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-usine-lourde/{usePlanet}", name="building_remove_heavyUsine", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveHeavyUsineAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getHeavyUsine();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('heavyUsine');

        if(($level == 0 || $usePlanet->getConstructAt() > $now)
            || $usePlanet->getProduct()) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setHeavyUsine($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setShipProduction($usePlanet->getShipProduction() - 0.3);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-caserne/{usePlanet}", name="building_remove_caserne", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveCaserneAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getCaserne();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('caserne');

        if(($level == 0 || $usePlanet->getConstructAt() > $now) ||
            ($usePlanet->getSoldier() > $usePlanet->getSoldierMax() - 2500 || $usePlanet->getSoldierAt())) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setCaserne($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setSoldierMax($usePlanet->getSoldierMax() - 2500);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-bunker/{usePlanet}", name="building_remove_bunker", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveBunkerAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getBunker();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('bunker');

        if(($level == 0 || $usePlanet->getConstructAt() > $now) ||
            ($usePlanet->getSoldier() > $usePlanet->getSoldierMax() - 20000 || $usePlanet->getSoldierAt())) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setBunker($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setSoldierMax($usePlanet->getSoldierMax() - 20000);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-nucleaire/{usePlanet}", name="building_remove_nuclear", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveNuclearAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getNuclearBase();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('nuclearBase');

        if(($level == 0 || $usePlanet->getConstructAt() > $now) ||
            $usePlanet->getNuclearBomb() > $usePlanet->getNuclearBase() - 1) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setNuclearBase($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-radar/{usePlanet}", name="building_remove_radar", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveRadarAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getRadar();
        $newGround = $usePlanet->getGroundPlace() - $user->getBuildingGroundPlace('radar');

        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setRadar($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-radar-espace/{usePlanet}", name="building_remove_skyRadar", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveSkyRadarAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getSkyRadar();
        $newSky = $usePlanet->getSkyPlace() - $user->getBuildingSkyPlace('skyRadar');

        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setSkyRadar($level - 1);
        $usePlanet->setSkyPlace($newSky);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-brouilleur/{usePlanet}", name="building_remove_brouilleur", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveBrouilleurAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getSkyBrouilleur();
        $newSky = $usePlanet->getSkyPlace() - $user->getBuildingSkyPlace('skyBrouilleur');

        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setSkyBrouilleur($level - 1);
        $usePlanet->setSkyPlace($newSky);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }
}