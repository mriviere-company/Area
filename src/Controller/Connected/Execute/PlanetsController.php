<?php

namespace App\Controller\Connected\Execute;

use App\Entity\Report;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use DateInterval;
use DateTime;

/**
 * Class PlanetsController
 * @package App\Controller\Connected\Execute
 */
class PlanetsController extends AbstractController
{
    /**
     * @param $planets
     * @param $now
     * @param $em
     * @return Response
     * @throws Exception
     */
    public function buildingsAction($planets, $now, $em): Response
    {
        $nextPlanets = [];
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
                        if ($timeLimit < $now) {
                            $planet->setConstruct($construction->getConstruct());
                            $planet->setConstructAt($timeLimit);
                            $em->remove($construction);
                            $nextPlanets[] = $planet;
                    } else {
                            $planet->setConstruct($construction->getConstruct());
                            $planet->setConstructAt($constructTime->add(new DateInterval('PT' . $construction->getConstructTime() . 'S')));
                            $em->remove($construction);
                        }
                    break;
                }
            } else {
                $planet->setConstruct(null);
                $planet->setConstructAt(null);
            }
        }

        $em->flush();

        if ($nextPlanets) {
            self::buildingsAction($nextPlanets, $now, $em);
        }

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    /**
     * @param $planetSoldiers
     * @param $em
     * @return Response
     */
    public function soldiersAction($planetSoldiers, $em): Response
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

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    /**
     * @param $planetTanks
     * @param $em
     * @return Response
     */
    public function tanksAction($planetTanks, $em): Response
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

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    /**
     * @param $planetNuclears
     * @param $em
     * @return Response
     */
    public function nuclearsAction($planetNuclears, $em): Response
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

    /**
     * @param $planetScientists
     * @param $em
     * @return Response
     */
    public function scientistsAction($planetScientists, $em): Response
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

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    /**
     * @param $products
     * @param $em
     * @return Response
     */
    public function productsAction($products, $em): Response
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
            $em->remove($product);
            $em->flush();
        }

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    /**
     * @param $radars
     * @param $now
     * @param $em
     * @return Response
     */
    public function radarsAction($radars, $now, $em): Response
    {
        foreach ($radars as $radar) {
            if($radar->getRadarAt() < $now && !$radar->getMoon()) {
                if(!$radar->getRadarAt()) {
                    $radar->setCharacter(null);
                }
                $radar->setName('Vide');
                $radar->setSkyRadar(0);
                $radar->setRadarAt(null);
            }
            if($radar->getBrouilleurAt() < $now && !$radar->getMoon()) {
                if(!$radar->getBrouilleurAt()) {
                    $radar->setCharacter(null);
                }
                $radar->setName('Vide');
                $radar->setSkyBrouilleur(0);
                $radar->setBrouilleurAt(null);
            }
            if($radar->getMoon()) {
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

    /**
     * @param $prods
     * @param $em
     * @return Response
     */
    public function productionDeleteAction($prods, $em): Response
    {
        foreach ($prods as $prod) {
            $em->remove($prod);
        }
        echo "Flush -> " . count($prods) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    /**
     * @param $embargos
     * @param $now
     * @param $em
     * @return Response
     * @throws Exception
     */
    public function embargoPlanetAction($embargos, $now, $em): Response
    {
        $nowEmbargo = new DateTime();
        $nowEmbargo->add(new DateInterval('PT' . (3600) . 'S'));

        foreach ($embargos as $embargo) {
            $server = $embargo->getPlanet()->getSector()->getGalaxy()->getServer();
            $food = (($embargo->getWorker() / 5) + 200) >= 0 ? (($embargo->getWorker() / 5) + 200) : 0;
            $worker = 0;
            $soldier = 0;
            $embargo->setFood(($embargo->getFood() - (($embargo->getWorker() / 5) + 200)) >= 0 ? ($embargo->getFood() - (($embargo->getWorker() / 5) + 200)) : 0);
            if ($embargo->getFood() == 0) {
                $embargo->setWorker(($embargo->getWorker() - (4500000 / $embargo->getFdProduction())) >= 0 ? ($embargo->getWorker() - (4500000 / $embargo->getFdProduction())) : 200);
                $worker = ((4500000 / $embargo->getFdProduction())) >= 0 ? (4500000 / $embargo->getFdProduction()) : 200;
                if ($embargo->getWorker() == 200) {
                    $embargo->setSoldier(($embargo->getSoldier() - (1000000 / $embargo->getFdProduction())) >= 0 ? ($embargo->getSoldier() - (1000000 / $embargo->getFdProduction())) : 0);
                    $soldier = ((1000000 / $embargo->getFdProduction())) >= 0 ? (1000000 / $embargo->getFdProduction()) : 0;
                }
            }
            $reportEmbargo = new Report();
            $reportEmbargo->setType('fight');
            $reportEmbargo->setSendAt($now);
            $reportEmbargo->setCharacter($embargo->getUser());
            $reportEmbargo->setTitle("Votre planète est sous embargo !");
            $reportEmbargo->setImageName("embargo_report.webp");
            $reportEmbargo->setContent("Votre planète <span class='text-vert'>" . $embargo->getName() . "</span> subit actuellement l'embargo d'une flotte hostile !<br>Vous avez perdu <span class='text-rouge'>" . number_format($food) . "</span> rations, <span class='text-rouge'>" . number_format($worker) . "</span> travailleurs et <span class='text-rouge'>" . number_format($soldier) . "</span> soldats.");
            $em->persist($reportEmbargo);
        }
        $server->setEmbargo($nowEmbargo);

        echo "Flush -> " . count($embargos) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}