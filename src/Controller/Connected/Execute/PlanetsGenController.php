<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PlanetsGenController extends AbstractController
{
    public function planetsGenAction($seconds, $now, $em)
    {
        $user = $this->getUser();

        if ($user->getPoliticWorker() > 0) {
            $workerBonus = (1 + ($user->getPoliticWorker() / 5));
        } else {
            $workerBonus = 1;
        }
        foreach ($user->getPlanets() as $planet) {
            if (!$planet->getRadarAt() and !$planet->getBrouilleurAt() and $planet->getMoon() == false) {
                $nbProd = ($planet->getNbProduction() * $seconds) / 60;
                $wtProd = ($planet->getWtProduction() * $seconds) / 60;
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

                if ($user->getAlly()) {
                    if ($user->getPoliticProd() > 0) {
                        $niobium = $planet->getNiobium() + ($nbProd * (1.2 + ($user->getPoliticProd() / 14)));
                        $water = $planet->getWater() + ($wtProd * (1.2 + ($user->getPoliticProd() / 14)));
                        $food = $planet->getFood() + ($fdProd * (1.2 + ($user->getPoliticProd() / 14)));

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
                $em->flush($planet);
            } elseif ($planet->getMoon() == true) {
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

                if ($user->getAlly()) {
                    if ($user->getPoliticProd() > 0) {
                        $food = $planet->getFood() + ($fdProd * (1.2 + ($user->getPoliticProd() / 14)));

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
        $user->setLastActivity($now);
        $em->flush($user);

        return new Response (null);
    }

    public function planetGenAction($planet, $seconds, $now, $em)
    {
        $user = $this->getUser();

        if ($user->getPoliticWorker() > 0) {
            $workerBonus = (1 + ($user->getPoliticWorker() / 5));
        } else {
            $workerBonus = 1;
        }

        if (!$planet->getRadarAt() and !$planet->getBrouilleurAt() and $planet->getMoon() == false) {
            $nbProd = ($planet->getNbProduction() * $seconds) / 60;
            $wtProd = ($planet->getWtProduction() * $seconds) / 60;
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

            if ($user->getAlly()) {
                if ($user->getPoliticProd() > 0) {
                    $niobium = $planet->getNiobium() + ($nbProd * (1.2 + ($user->getPoliticProd() / 14)));
                    $water = $planet->getWater() + ($wtProd * (1.2 + ($user->getPoliticProd() / 14)));
                    $food = $planet->getFood() + ($fdProd * (1.2 + ($user->getPoliticProd() / 14)));

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
            $em->flush($planet);
        } elseif ($planet->getMoon() == true) {
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

            if ($user->getAlly()) {
                if ($user->getPoliticProd() > 0) {
                    $food = $planet->getFood() + ($fdProd * (1.2 + ($user->getPoliticProd() / 14)));

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
            $em->flush($planet);
        }
        $user->setLastActivity($now);
        $em->flush($user);

        return new Response (null);
    }
}