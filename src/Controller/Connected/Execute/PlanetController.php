<?php

namespace App\Controller\Connected\Execute;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use DateInterval;
use DateTime;

/**
 * Class PlanetController
 * @package App\Controller\Connected\Execute
 */
class PlanetController extends AbstractController
{
    /**
     * @param $planet
     * @param $now
     * @param $em
     * @return Response
     * @throws Exception
     */
    public function buildingOneAction($planet, $now, $em): Response
    {
        $build = $planet->getConstruct();
        if ($build == 'destruct') {
        } elseif ($build == 'miner') {
            $planet->setMiner($planet->getMiner() + 1);
            $planet->setNbProduction(($planet->getMiner() * 22));
        } elseif ($build == 'extractor') {
            $planet->setExtractor($planet->getExtractor() + 1);
            $planet->setWtProduction(($planet->getExtractor() * 15));
        } elseif ($build == 'farm') {
            $planet->setFarm($planet->getFarm() + 1);
            $planet->setFdProduction(($planet->getFarm() * 18));
        } elseif ($build == 'aeroponicFarm') {
            $planet->setAeroponicFarm($planet->getAeroponicFarm() + 1);
            $planet->setFdProduction(($planet->getAeroponicFarm() * 25));
        } elseif ($build == 'niobiumStock') {
            $planet->setNiobiumStock($planet->getNiobiumStock() + 1);
            $planet->setNiobiumMax($planet->getNiobiumMax() + 50000);
        } elseif ($build == 'waterStock') {
            $planet->setWaterStock($planet->getWaterStock() + 1);
            $planet->setWaterMax($planet->getWaterMax() + 50000);
        } elseif ($build == 'silos') {
            $planet->setSilos($planet->getSilos() + 1);
            $planet->setFoodMax($planet->getFoodMax() + 80000);
        } elseif ($build == 'city') {
            $planet->setCity($planet->getCity() + 1);
            $planet->setWorkerProduction($planet->getWorkerProduction() + 5.56);
            $planet->setWorkerMax($planet->getWorkerMax() + 12500);
            $quest = $planet->getCharacter() ? $planet->getCharacter()->checkQuests('build_city') : null;
            if($quest) {
                $planet->getCharacter()->getRank()->setWarPoint($planet->getCharacter()->getRank()->getWarPoint() + $quest->getGain());
                $planet->getCharacter()->removeQuest($quest);
            }
        } elseif ($build == 'metropole') {
            $planet->setMetropole($planet->getMetropole() + 1);
            $planet->setWorkerProduction($planet->getWorkerProduction() + 8.32);
            $planet->setWorkerMax($planet->getWorkerMax() + 40000);
            $quest = $planet->getCharacter() ? $planet->getCharacter()->checkQuests('build_metro') : null;
            if($quest) {
                $planet->getCharacter()->getRank()->setWarPoint($planet->getCharacter()->getRank()->getWarPoint() + $quest->getGain());
                $planet->getCharacter()->removeQuest($quest);
            }
        } elseif ($build == 'caserne') {
            $planet->setCaserne($planet->getCaserne() + 1);
            $planet->setSoldierMax($planet->getSoldierMax() + 500);
        } elseif ($build == 'bunker') {
            $planet->setBunker($planet->getBunker() + 1);
            $planet->setSoldierMax($planet->getSoldierMax() + 5000);
        } elseif ($build == 'nuclearBase') {
            $planet->setNuclearBase($planet->getNuclearBase() + 1);
        } elseif ($build == 'island') {
            $planet->setIsland($planet->getIsland() + 1);
            $planet->setGround($planet->getGround() + 10);
        } elseif ($build == 'orbital') {
            $planet->setOrbital($planet->getOrbital() + 1);
            $planet->setSky($planet->getSky() + 5);
        } elseif ($build == 'centerSearch') {
            $planet->setCenterSearch($planet->getCenterSearch() + 1);
            $planet->setScientistMax($planet->getScientistMax() + 250);
        } elseif ($build == 'lightUsine') {
            $planet->setLightUsine($planet->getLightUsine() + 1);
            $planet->setShipProduction($planet->getShipProduction() + 0.15);
        } elseif ($build == 'heavyUsine') {
            $planet->setHeavyUsine($planet->getHeavyUsine() + 1);
            $planet->setShipProduction($planet->getShipProduction() + 0.3);
            $quest = $planet->getCharacter() ? $planet->getCharacter()->checkQuests('build_heavy') : null;
            if($quest) {
                $planet->getCharacter()->getRank()->setWarPoint($planet->getCharacter()->getRank()->getWarPoint() + $quest->getGain());
                $planet->getCharacter()->removeQuest($quest);
            }
        } elseif ($build == 'spaceShip') {
            $planet->setSpaceShip($planet->getSpaceShip() + 1);
            $planet->setShipProduction($planet->getShipProduction() + 0.1);
        } elseif ($build == 'radar') {
            $planet->setRadar($planet->getRadar() + 1);
        } elseif ($build == 'skyRadar') {
            $planet->setSkyRadar($planet->getSkyRadar() + 1);
        } elseif ($build == 'skyBrouilleur') {
            $planet->setSkyBrouilleur($planet->getSkyBrouilleur() + 1);
        }
        if(count($planet->getConstructions()) > 0) {
            $constructTime = new DateTime();
            foreach ($planet->getConstructions() as $construction) {
                $timeLimit = $planet->getConstructAt()->add(new DateInterval('PT' . $construction->getConstructTime() . 'S'));
                $planet->setConstruct($construction->getConstruct());
                if ($timeLimit < $now) {
                    $planet->setConstructAt($timeLimit);
                } else {
                    $planet->setConstructAt($constructTime->add(new DateInterval('PT' . $construction->getConstructTime() . 'S')));
                }
                $em->remove($construction);
                break;
            }
        } else {
            $planet->setConstruct(null);
            $planet->setConstructAt(null);
        }

        $em->flush();

        if ($timeLimit < $now) {
            self::buildingOneAction($planet, $now, $em);
        }

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    /**
     * @param $planetSoldier
     * @param $em
     * @return Response
     */
    public function soldierOneAction($planetSoldier, $em): Response
    {
        if ($planetSoldier->getSoldier() + $planetSoldier->getSoldierAtNbr() <= $planetSoldier->getSoldierMax()) {
            $planetSoldier->setSoldier($planetSoldier->getSoldier() + $planetSoldier->getSoldierAtNbr());
            $planetSoldier->setSoldierAt(null);
            $planetSoldier->setSoldierAtNbr(null);
        } else {
            $planetSoldier->setSoldier($planetSoldier->getSoldierMax());
            $planetSoldier->setSoldierAt(null);
            $planetSoldier->setSoldierAtNbr(null);
        }

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    /**
     * @param $planetTank
     * @param $em
     * @return Response
     */
    public function tankOneAction($planetTank, $em): Response
    {
        if ($planetTank->getTank() + $planetTank->getTankAtNbr() <= 500) {
            $planetTank->setTank($planetTank->getTank() + $planetTank->getTankAtNbr());
            $planetTank->setTankAt(null);
            $planetTank->setTankAtNbr(null);
        } else {
            $planetTank->setTank(500);
            $planetTank->setTankAt(null);
            $planetTank->setTankAtNbr(null);
        }

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    /**
     * @param $planetScientist
     * @param $em
     * @return Response
     */
    public function scientistOneAction($planetScientist, $em): Response
    {
        if ($planetScientist->getScientist() + $planetScientist->getScientistAtNbr() <= $planetScientist->getScientistMax()) {
            $planetScientist->setScientist($planetScientist->getScientist() + $planetScientist->getScientistAtNbr());
            $planetScientist->getUser()->setScientistProduction(round($planetScientist->getUser()->getScientistProduction() + ($planetScientist->getScientist() / 10000)));
            $planetScientist->setScientistAt(null);
            $planetScientist->setScientistAtNbr(null);
        } else {
            $planetScientist->setScientist($planetScientist->getScientistMax());
            $planetScientist->getUser()->setScientistProduction(round($planetScientist->getUser()->getScientistProduction() + ($planetScientist->getScientistMax() / 10000)));
            $planetScientist->setScientistAt(null);
            $planetScientist->setScientistAtNbr(null);
        }

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    /**
     * @param $product
     * @param $em
     * @return Response
     */
    public function productOneAction($product, $em): Response
    {
        $planetProduct = $product->getPlanet();
        $planetProduct->setCargoI($planetProduct->getCargoI() + $product->getCargoI());
        $planetProduct->setCargoV($planetProduct->getCargoV() + $product->getCargoV());
        $planetProduct->setCargoX($planetProduct->getCargoX() + $product->getCargoX());
        $planetProduct->setColonizer($planetProduct->getColonizer() + $product->getColonizer());
        $planetProduct->setRecycleur($planetProduct->getRecycleur() + $product->getRecycleur());
        $planetProduct->setBarge($planetProduct->getBarge() + $product->getBarge());
        $planetProduct->setMoonMaker($planetProduct->getMoonMaker() + $product->getMoonMaker());
        $planetProduct->setRadarShip($planetProduct->getRadarShip() + $product->getRadarShip());
        $planetProduct->setBrouilleurShip($planetProduct->getBrouilleurShip() + $product->getBrouilleurShip());
        $planetProduct->setMotherShip($planetProduct->getMotherShip() + $product->getMotherShip());
        $planetProduct->setSonde($planetProduct->getSonde() + $product->getSonde());
        $planetProduct->setHunter($planetProduct->getHunter() + $product->getHunter());
        $planetProduct->setFregate($planetProduct->getFregate() + $product->getFregate());
        $planetProduct->setHunterHeavy($planetProduct->getHunterHeavy() + $product->getHunterHeavy());
        $planetProduct->setHunterWar($planetProduct->getHunterWar() + $product->getHunterWar());
        $planetProduct->setCorvet($planetProduct->getCorvet() + $product->getCorvet());
        $planetProduct->setCorvetLaser($planetProduct->getCorvetLaser() + $product->getCorvetLaser());
        $planetProduct->setCorvetWar($planetProduct->getCorvetWar() + $product->getCorvetWar());
        $planetProduct->setFregatePlasma($planetProduct->getFregatePlasma() + $product->getFregatePlasma());
        $planetProduct->setCroiser($planetProduct->getCroiser() + $product->getCroiser());
        $planetProduct->setIronClad($planetProduct->getIronClad() + $product->getIronClad());
        $planetProduct->setDestroyer($planetProduct->getDestroyer() + $product->getDestroyer());
        $planetProduct->setSignature($planetProduct->getNbrSignatures());
        $em->remove($product);
        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}