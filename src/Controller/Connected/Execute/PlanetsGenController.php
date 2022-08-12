<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PlanetsGenController
 * @package App\Controller\Connected\Execute
 */
class PlanetsGenController extends AbstractController
{
    /**
     * @param $commander
     * @param $seconds
     * @param $now
     * @param $em
     * @return Response
     */
    public function planetsGenAction($commander, $seconds, $now, $em): Response
    {
        if ($commander->getPoliticWorker() > 0) {
            $workerBonus = (1 + ($commander->getPoliticWorker() / 5));
        } else {
            $workerBonus = 1;
        }
        foreach ($commander->getPlanets() as $planet) {
            if (!$planet->getRadarAt() and !$planet->getBrouilleurAt() and $planet->getMoon()) {
                $nbProd = ($planet->getNbProduction() * $seconds) / 600;
                $wtProd = ($planet->getWtProduction() * $seconds) / 600;
                $fdProd = ($planet->getFdProduction() * $seconds) / 600;
                $workerMore = (($planet->getWorkerProduction() * $workerBonus * $seconds) / 60);

                if ($seconds > 129600) {
                    $secondsLvl = $seconds % 86400 + 1;
                    $workerMore = $workerMore / $secondsLvl;
                    if (($planet->getWorker() + $workerMore >= $planet->getWorkerMax())) {
                        $planet->setWorker($planet->getWorkerMax());
                    } else {
                        $planet->setWorker($planet->getWorker() + (($planet->getWorkerProduction() * $workerBonus * $seconds) / 60));
                    }
                } else {
                    if (($planet->getWorker() + $workerMore >= $planet->getWorkerMax())) {
                        $planet->setWorker($planet->getWorkerMax());
                    } else {
                        $planet->setWorker($planet->getWorker() + (($planet->getWorkerProduction() * $workerBonus * $seconds) / 60));
                    }
                }

                if ($commander->getAlly()) {
                    if ($commander->getPoliticProd() > 0) {
                        $niobium = $planet->getNiobium() + ($nbProd * (1.2 + ($commander->getPoliticProd() / 14)));
                        $water = $planet->getWater() + ($wtProd * (1.2 + ($commander->getPoliticProd() / 14)));
                        $food = $planet->getFood() + ($fdProd * (1.2 + ($commander->getPoliticProd() / 14)));

                    } else {
                        $niobium = $planet->getNiobium() + ($nbProd * 1.2);
                        $water = $planet->getWater() + ($wtProd * 1.2);
                        $food = $planet->getFood() + ($fdProd * 1.2);
                    }
                } else {
                    $niobium = $planet->getNiobium() + $nbProd;
                    $water = $planet->getWater() + $wtProd;
                    $food = $planet->getFood() + $fdProd;
                }
                if ($planet->getNiobiumMax() > $niobium) {
                    $planet->setNiobium($niobium);
                } else {
                    $planet->setNiobium($planet->getNiobiumMax());
                }
                if ($planet->getWaterMax() > $water) {
                    $planet->setWater($water);
                } else {
                    $planet->setWater($planet->getWaterMax());
                }
                if ($planet->getFoodMax() > $food) {
                    $planet->setFood($food);
                } else {
                    $planet->setFood($planet->getFoodMax());
                }
                $planet->setLastActivity($now);
                $em->flush($planet);
            } elseif ($planet->getMoon()) {
                $fdProd = ($planet->getFdProduction() * $seconds) / 60;
                $workerMore = (($planet->getWorkerProduction() * $workerBonus * $seconds) / 60);

                if ($seconds > 129600) {
                    $secondsLvl = $seconds % 86400 + 1;
                    $workerMore = $workerMore / $secondsLvl;
                    if (($planet->getWorker() + $workerMore >= $planet->getWorkerMax())) {
                        $planet->setWorker($planet->getWorkerMax());
                    } else {
                        $planet->setWorker($planet->getWorker() + (($planet->getWorkerProduction() * $workerBonus * $seconds) / 60));
                    }
                } else {
                    if (($planet->getWorker() + $workerMore >= $planet->getWorkerMax())) {
                        $planet->setWorker($planet->getWorkerMax());
                    } else {
                        $planet->setWorker($planet->getWorker() + (($planet->getWorkerProduction() * $workerBonus * $seconds) / 60));
                    }
                }

                if ($commander->getAlly()) {
                    if ($commander->getPoliticProd() > 0) {
                        $food = $planet->getFood() + ($fdProd * (1.2 + ($commander->getPoliticProd() / 14)));

                    } else {
                        $food = $planet->getFood() + ($fdProd * 1.2);
                    }
                } else {
                    $food = $planet->getFood() + $fdProd;
                }
                if ($planet->getFoodMax() > ($planet->getFood() + $food)) {
                    $planet->setFood($food);
                } else {
                    $planet->setFood($planet->getFoodMax());
                }
                $planet->setLastActivity($now);
                $em->flush($planet);
            }
        }
        $commander->setLastActivity($now);
        $em->flush($commander);

        return new Response (null);
    }

    /**
     * @param $commander
     * @param $planet
     * @param $seconds
     * @param $now
     * @param $em
     * @return Response
     */
    public function planetGenAction($commander, $planet, $seconds, $now, $em): Response
    {
        if ($commander->getPoliticWorker() > 0) {
            $workerBonus = (1 + ($commander->getPoliticWorker() / 5));
        } else {
            $workerBonus = 1;
        }

        if (!$planet->getRadarAt() and !$planet->getBrouilleurAt() and !$planet->getMoon()) {
            $nbProd = ($planet->getNbProduction() * $seconds) / 600;
            $wtProd = ($planet->getWtProduction() * $seconds) / 600;
            $fdProd = ($planet->getFdProduction() * $seconds) / 600;
            $workerMore = (($planet->getWorkerProduction() * $workerBonus * $seconds) / 60);

            if ($seconds > 129600) {
                $secondsLvl = $seconds % 86400 + 1;
                $workerMore = $workerMore / $secondsLvl;
                if (($planet->getWorker() + $workerMore >= $planet->getWorkerMax())) {
                    $planet->setWorker($planet->getWorkerMax());
                } else {
                    $planet->setWorker($planet->getWorker() + (($planet->getWorkerProduction() * $workerBonus * $seconds) / 60));
                }
            } else {
                if (($planet->getWorker() + $workerMore >= $planet->getWorkerMax())) {
                    $planet->setWorker($planet->getWorkerMax());
                } else {
                    $planet->setWorker($planet->getWorker() + (($planet->getWorkerProduction() * $workerBonus * $seconds) / 60));
                }
            }

            if ($commander->getAlly()) {
                if ($commander->getPoliticProd() > 0) {
                    $niobium = $planet->getNiobium() + ($nbProd * (1.2 + ($commander->getPoliticProd() / 14)));
                    $water = $planet->getWater() + ($wtProd * (1.2 + ($commander->getPoliticProd() / 14)));
                    $food = $planet->getFood() + ($fdProd * (1.2 + ($commander->getPoliticProd() / 14)));

                } else {
                    $niobium = $planet->getNiobium() + ($nbProd * 1.2);
                    $water = $planet->getWater() + ($wtProd * 1.2);
                    $food = $planet->getFood() + ($fdProd * 1.2);
                }
            } else {
                $niobium = $planet->getNiobium() + $nbProd;
                $water = $planet->getWater() + $wtProd;
                $food = $planet->getFood() + $fdProd;
            }
            if ($planet->getNiobiumMax() > $niobium) {
                $planet->setNiobium($niobium);
            } else {
                $planet->setNiobium($planet->getNiobiumMax());
            }
            if ($planet->getWaterMax() > $water) {
                $planet->setWater($water);
            } else {
                $planet->setWater($planet->getWaterMax());
            }
            if ($planet->getFoodMax() > $food) {
                $planet->setFood($food);
            } else {
                $planet->setFood($planet->getFoodMax());
            }
            $planet->setLastActivity($now);
            $em->flush($planet);
        } elseif ($planet->getMoon()) {
            $fdProd = ($planet->getFdProduction() * $seconds) / 60;
            $workerMore = (($planet->getWorkerProduction() * $workerBonus * $seconds) / 60);

            if ($seconds > 129600) {
                $secondsLvl = $seconds % 86400 + 1;
                $workerMore = $workerMore / $secondsLvl;
                if (($planet->getWorker() + $workerMore >= $planet->getWorkerMax())) {
                    $planet->setWorker($planet->getWorkerMax());
                } else {
                    $planet->setWorker($planet->getWorker() + (($planet->getWorkerProduction() * $workerBonus * $seconds) / 60));
                }
            } else {
                if (($planet->getWorker() + $workerMore >= $planet->getWorkerMax())) {
                    $planet->setWorker($planet->getWorkerMax());
                } else {
                    $planet->setWorker($planet->getWorker() + (($planet->getWorkerProduction() * $workerBonus * $seconds) / 60));
                }
            }

            if ($commander->getAlly()) {
                if ($commander->getPoliticProd() > 0) {
                    $food = $planet->getFood() + ($fdProd * (1.2 + ($commander->getPoliticProd() / 14)));

                } else {
                    $food = $planet->getFood() + ($fdProd * 1.2);
                }
            } else {
                $food = $planet->getFood() + $fdProd;
            }
            if ($planet->getFoodMax() > ($planet->getFood() + $food)) {
                $planet->setFood($food);
            } else {
                $planet->setFood($planet->getFoodMax());
            }
            $planet->setLastActivity($now);
            $em->flush($planet);
        }
        $commander->setLastActivity($now);
        $em->flush($commander);

        return new Response (null);
    }
}