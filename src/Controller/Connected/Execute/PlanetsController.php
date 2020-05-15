<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use DateTimeZone;
use DateInterval;
use DateTime;

class PlanetsController extends AbstractController
{
    public function buildingsAction($planets, $em)
    {
        foreach ($planets as $planet) {
            $build = $planet->getConstruct();
            if($build == 'destruct') {
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
                $planet->setNiobiumMax($planet->getNiobiumMax() + 5000000);
            } elseif ($build == 'waterStock') {
                $planet->setWaterStock($planet->getWaterStock() + 1);
                $planet->setWaterMax($planet->getWaterMax() + 5000000);
            } elseif ($build == 'silos') {
                $planet->setSilos($planet->getSilos() + 1);
                $planet->setFoodMax($planet->getFoodMax() + 8000000);
            } elseif ($build == 'city') {
                $planet->setCity($planet->getCity() + 1);
                $planet->setWorkerProduction($planet->getWorkerProduction() + 5.56);
                $planet->setWorkerMax($planet->getWorkerMax() + 125000);
                $quest = $planet->getUser()->checkQuests('build_city');
                if($quest) {
                    $planet->getUser()->getRank()->setWarPoint($planet->getUser()->getRank()->getWarPoint() + $quest->getGain());
                    $planet->getUser()->removeQuest($quest);
                }
            } elseif ($build == 'metropole') {
                $planet->setMetropole($planet->getMetropole() + 1);
                $planet->setWorkerProduction($planet->getWorkerProduction() + 8.32);
                $planet->setWorkerMax($planet->getWorkerMax() + 400000);
                $quest = $planet->getUser()->checkQuests('build_metro');
                if($quest) {
                    $planet->getUser()->getRank()->setWarPoint($planet->getUser()->getRank()->getWarPoint() + $quest->getGain());
                    $planet->getUser()->removeQuest($quest);
                }
            } elseif ($build == 'caserne') {
                $planet->setCaserne($planet->getCaserne() + 1);
                $planet->setSoldierMax($planet->getSoldierMax() + 2500);
            } elseif ($build == 'bunker') {
                $planet->setBunker($planet->getBunker() + 1);
                $planet->setSoldierMax($planet->getSoldierMax() + 20000);
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
                $planet->setScientistMax($planet->getScientistMax() + 500);
            } elseif ($build == 'lightUsine') {
                $planet->setLightUsine($planet->getLightUsine() + 1);
                $planet->setShipProduction($planet->getShipProduction() + 0.15);
            } elseif ($build == 'heavyUsine') {
                $planet->setHeavyUsine($planet->getHeavyUsine() + 1);
                $planet->setShipProduction($planet->getShipProduction() + 0.3);
                $quest = $planet->getUser()->checkQuests('build_heavy');
                if($quest) {
                    $planet->getUser()->getRank()->setWarPoint($planet->getUser()->getRank()->getWarPoint() + $quest->getGain());
                    $planet->getUser()->removeQuest($quest);
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
                $constructTime->setTimezone(new DateTimeZone('Europe/Paris'));
                foreach ($planet->getConstructions() as $construction) {
                    $planet->setConstruct($construction->getConstruct());
                    $planet->setConstructAt($constructTime->add(new DateInterval('PT' . $construction->getConstructTime() . 'S')));
                    $em->remove($construction);
                    break;
                }
            } else {
                $planet->setConstruct(null);
                $planet->setConstructAt(null);
            }
        }
        echo "Flush -> " . count($planets) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    public function soldiersAction($planetSoldiers, $em)
    {
        foreach ($planetSoldiers as $soldierAt) {
            if ($soldierAt->getSoldier() + $soldierAt->getSoldierAtNbr() <= $soldierAt->getSoldierMax()) {
                $soldierAt->setSoldier($soldierAt->getSoldier() + $soldierAt->getSoldierAtNbr());
                $soldierAt->setSoldierAt(null);
                $soldierAt->setSoldierAtNbr(null);
            } else {
                $soldierAt->setSoldier($soldierAt->getSoldierMax());
                $soldierAt->setSoldierAt(null);
                $soldierAt->setSoldierAtNbr(null);
            }
        }
        echo "Flush -> " . count($planetSoldiers) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    public function tanksAction($planetTanks, $em)
    {
        foreach ($planetTanks as $tankAt) {
            if ($tankAt->getTank() + $tankAt->getTankAtNbr() <= 500) {
                $tankAt->setTank($tankAt->getTank() + $tankAt->getTankAtNbr());
                $tankAt->setTankAt(null);
                $tankAt->setTankAtNbr(null);
            } else {
                $tankAt->setTank(500);
                $tankAt->setTankAt(null);
                $tankAt->setTankAtNbr(null);
            }
        }
        echo "Flush -> " . count($planetTanks) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    public function nuclearsAction($planetNuclears, $em)
    {
        foreach ($planetNuclears as $nuclear) {
            if ($nuclear->getNuclearBomb() + $nuclear->getNuclearAtNbr() <= $nuclear->getNuclearBase()) {
                $nuclear->setNuclearBomb($nuclear->getNuclearBomb() + $nuclear->getNuclearAtNbr());
                $nuclear->setNuclearAt(null);
                $nuclear->setNuclearAtNbr(null);
            } else {
                $nuclear->setNuclearBomb($nuclear->getNuclearBase());
                $nuclear->setNuclearAt(null);
                $nuclear->setNuclearAtNbr(null);
            }
        }
        echo "Flush -> " . count($planetNuclears) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    public function scientistsAction($planetScientists, $em)
    {
        foreach ($planetScientists as $scientistAt) {
            if ($scientistAt->getScientist() + $scientistAt->getScientistAtNbr() <= $scientistAt->getScientistMax()) {
                $scientistAt->setScientist($scientistAt->getScientist() + $scientistAt->getScientistAtNbr());
                $scientistAt->getUser()->setScientistProduction(round($scientistAt->getUser()->getScientistProduction() + ($scientistAt->getScientist() / 10000)));
                $scientistAt->setScientistAt(null);
                $scientistAt->setScientistAtNbr(null);
            } else {
                $scientistAt->setScientist($scientistAt->getScientistMax());
                $scientistAt->getUser()->setScientistProduction(round($scientistAt->getUser()->getScientistProduction() + ($scientistAt->getScientistMax() / 10000)));
                $scientistAt->setScientistAt(null);
                $scientistAt->setScientistAtNbr(null);
            }
        }
        echo "Flush -> " . count($planetScientists) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    public function productsAction($products, $em)
    {
        foreach ($products as $product) {
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
            $product->setPlanet(null);
            $em->remove($product);
        }
        echo "Flush -> " . count($products) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    public function radarsAction($radars, $now, $em)
    {
        foreach ($radars as $radar) {
            if($radar->getRadarAt() < $now && $radar->getMoon() == false) {
                if(!$radar->getRadarAt()) {
                    $radar->setUser(null);
                }
                $radar->setName('Vide');
                $radar->setSkyRadar(0);
                $radar->setRadarAt(null);
            }
            if($radar->getBrouilleurAt() < $now && $radar->getMoon() == false) {
                if(!$radar->getBrouilleurAt()) {
                    $radar->setUser(null);
                }
                $radar->setName('Vide');
                $radar->setSkyBrouilleur(0);
                $radar->setBrouilleurAt(null);
            }
            if($radar->getMoon() == true) {
                $radar->setSkyBrouilleur(0);
                $radar->setBrouilleurAt(null);
                $radar->setSkyRadar(0);
                $radar->setRadarAt(null);
            }
        }
        echo "Flush -> " . count($radars) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    public function productionDeleteAction($prods, $em)
    {
        foreach ($prods as $prod) {
            $em->remove($prod);
        }
        echo "Flush -> " . count($prods) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    public function embargoPlanetAction($embargos, $server, $em)
    {
        $nowEmbargo = new DateTime();
        $nowEmbargo->setTimezone(new DateTimeZone('Europe/Paris'));
        $nowEmbargo->add(new DateInterval('PT' . (600) . 'S'));

        foreach ($embargos as $embargo) {
            $embargo->setFood(($embargo->getFood() - (($embargo->getWorker() / 12) + 2000)) >= 0 ? ($embargo->getFood() - (($embargo->getWorker() / 12) + 2000)) : 0);
            if ($embargo->getFood() == 0) {
                $embargo->setWorker(($embargo->getWorker() - (900000 / $embargo->getFdProduction())) >= 0 ? ($embargo->getWorker() - (900000 / $embargo->getFdProduction())) : 2000);
                if ($embargo->getWorker() == 2000) {
                    $embargo->setSoldier(($embargo->getSoldier() - (300000 / $embargo->getFdProduction())) >= 0 ? ($embargo->getSoldier() - (300000 / $embargo->getFdProduction())) : 0);
                }
            }
        }
        $server->setEmbargo($nowEmbargo);

        echo "Flush -> " . count($embargos) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}