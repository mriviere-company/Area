<?php

namespace App\Controller\Connected\Execute;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Report;
use DateTime;

class WarPlanetController extends AbstractController
{
    public function invaderAction($usePlanet, $fleet, $user, $now, $em)
    {
        $alea = rand(4, 8);

        if ($user->getPoliticBarge() > 0) {
            $barge = $fleet->getBarge() * 2500 * (1 + ($user->getPoliticBarge() / 4));
        } else {
            $barge = $fleet->getBarge() * 2500;
        }
        if ($barge) {
            if($barge >= $fleet->getSoldier()) {
                $aMilitary = $fleet->getSoldier() * $alea;
                $soldierAtmp = $fleet->getSoldier();
                $soldierAtmpTotal = 0;
            } else {
                $aMilitary = $barge * $alea;
                $soldierAtmp = $barge;
                $soldierAtmpTotal = $fleet->getSoldier() - $barge;
            }
            if ($user->getPoliticSoldierAtt() > 0) {
                $aMilitary = $aMilitary * (1 + ($user->getPoliticSoldierAtt() / 10));
            }
        } else {
            return new Response ("nobarge");
        }

        $planetDefender = $fleet->getPlanet();
        $userDefender= $planetDefender->getUser();
        $usePlanetDef = $em->getRepository('App:Planet')->findByFirstPlanet($userDefender);
        $barbed = $userDefender->getBarbedAdv();
        $dSoldier = $planetDefender->getSoldier() > 0 ? ($planetDefender->getSoldier() * 6) * $barbed : 0;
        $dTanks = $planetDefender->getTank() > 0 ? $planetDefender->getTank() * 3000 : 0;
        $dWorker = $planetDefender->getWorker();
        $soldierDtmp = $planetDefender->getSoldier();
        $workerDtmp = $planetDefender->getWorker();
        $tankDtmp = $planetDefender->getTank();

        if ($planetDefender->getConstructAt() && $planetDefender->getConstructAt() < $now) {
            $this->forward('App\Controller\Connected\Execute\PlanetController::buildingOneAction', [
                'planet'  => $planetDefender,
                'now' => $now,
                'em' => $em
            ]);
        }

        $seconds = $this->forward('App\Controller\Connected\Execute\ChronosController::planetActivityAction', [
            'planet' => $planetDefender,
            'now'  => $now,
            'em' => $em]);

        if ($seconds->getContent() >= 60) {
            $this->forward('App\Controller\Connected\Execute\PlanetsGenController::planetGenAction', [
                'planet' => $planetDefender,
                'seconds' => $seconds->getContent(),
                'now'  => $now,
                'em' => $em]);
        }

        if ($userDefender->getPoliticSoldierAtt() > 0) {
            $dSoldier = $dSoldier * (1 + ($userDefender->getPoliticSoldierAtt() / 10));
        }
        if ($userDefender->getPoliticTankDef() > 0) {
            $dTanks = $dTanks * (1 + ($userDefender->getPoliticTankDef() / 10));
        }
        if ($userDefender->getPoliticWorkerDef() > 0) {
            $dWorker = $dWorker * (1 + ($userDefender->getPoliticWorkerDef() / 5));
        }
        if ($userDefender->getZombie() == 1) {
            $dTanks = 0;
        }
        $dMilitary = $dWorker + $dSoldier + $dTanks;

        $reportInv = new Report();
        if ($userDefender->getZombie() == 0) {
            $reportInv->setType('invade');
        } else {
            $reportInv->setType('zombie');
        }
        $reportInv->setSendAt($now);
        $reportInv->setUser($user);
        $user->setViewReport(false);

        if ($userDefender->getZombie() == 0) {
            $reportDef = new Report();
            $reportDef->setType('invade');
            $reportDef->setSendAt($now);
            $reportDef->setUser($userDefender);
        }
        $userDefender->setViewReport(false);
        $dSigle = null;
        if($userDefender->getAlly()) {
            $dSigle = $userDefender->getAlly()->getSigle();
        }

        if($fleet->getPlanet()->getUser() && $fleet->getAllianceUser() && $fleet->getFightAt() == null && $fleet->getFlightTime() == null && $user->getSigleAllied($dSigle) == NULL) {
            if($dMilitary >= $aMilitary) {
                if ($userDefender->getZombie() == 0) {
                    $warPointDef = round($aMilitary);
                    if ($user->getPoliticPdg() > 0) {
                        $warPointDef = round(($warPointDef * (1 + ($user->getPoliticPdg() / 10))) / 500);
                    }
                    $userDefender->getRank()->setWarPoint($userDefender->getRank()->getWarPoint() + $warPointDef);
                }
                $aMilitary = $aMilitary - $dSoldier;
                if($barge < $fleet->getSoldier()) {
                    $fleet->setSoldier($fleet->getSoldier() - $barge);
                } else {
                    $fleet->setSoldier(0);
                }
                $planetDefender->setBarge($planetDefender->getBarge() + $fleet->getBarge());
                $fleet->setBarge(0);
                if($aMilitary > 0) {
                    $planetDefender->setSoldier(0);
                    $aMilitary = $aMilitary - $dTanks;
                    if($aMilitary >= 0) {
                        $planetDefender->setTank(0);
                        $aMilitary = $aMilitary - $dWorker;
                        $diviser = (1 + ($userDefender->getPoliticWorkerDef() / 5));
                        $planetDefender->setWorker(round(abs($aMilitary / $diviser)));
                        $tankDtmp = $tankDtmp - $planetDefender->getTank();
                        $soldierDtmp = $soldierDtmp - $planetDefender->getSoldier();
                        $workerDtmp = $workerDtmp - $planetDefender->getWorker();
                    } else {
                        $diviser = (1 + ($userDefender->getPoliticTankDef() / 10)) * 3000;
                        $planetDefender->setTank(round(abs($aMilitary / $diviser)));
                        $tankDtmp = $tankDtmp - $planetDefender->getTank();
                        $soldierDtmp = $soldierDtmp - $planetDefender->getSoldier();
                    }
                } else {
                    $dMilitary = $dMilitary - $aMilitary - $dTanks -$dWorker;
                    $diviser = (1 + ($userDefender->getPoliticSoldierAtt() / 10)) * ($alea * $userDefender->getBarbedAdv()) * 6;
                    $planetDefender->setSoldier(round(abs($dMilitary / $diviser)));
                    $soldierDtmp = round(abs($dMilitary / $diviser));
                }
                if ($userDefender->getZombie() == 1) {
                    $reportInv->setTitle("Rapport contre attaque : Défaite");
                    $reportInv->setImageName("zombie_lose_report.jpg");
                    $reportInv->setContent("Vous pensiez partir pour une promenade de santé mais la réalité vous rattrape vite... Vous avez envoyé tout vos soldats au casse-pipe.<br>Pire, vous avez attirer l'attention des zombies et fait monter la menace de 10 points ! Vous avez interêt a prendre vite" .
                        $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $planetDefender, 'usePlanet' => $usePlanet])->getContent() .
                        "sinon votre Empire ne tiendra pas longtemps. Vous avez tué <span class='text-vert'>" .
                        number_format(round($soldierDtmp + ($workerDtmp / 6) + ($tankDtmp * 3000))) . "</span> zombies. Tous vos soldats sont morts et vos barges se sont égarées sur la planète.<br>N'abandonnez pas et sortez vos tripes !");

                    $user->setZombieAtt($user->getZombieAtt() + 10);
                } else {
                    $reportDef->setTitle("Rapport d'invasion : Victoire (défense)");
                    $reportDef->setImageName("defend_win_report.jpg");
                    $reportDef->setContent("Bien joué ! Vos travailleurs et soldats ont repoussé l'invasion du joueur" .
                        $this->forward('App\Controller\FacilitiesController::userReportAction', ['user' => $user, 'usePlanet' => $usePlanetDef])->getContent() .
                        "sur votre planète" . $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $planetDefender, 'usePlanet' => $usePlanetDef])->getContent() .
                        "<span class='text-vert'>" . number_format($soldierAtmp) .
                        "</span> soldats vous ont attaqué, tous ont été tués. Vous avez ainsi pris le contrôle des barges de l'attaquant.<br>Et vous remportez <span class='text-vert'>+" .
                        number_format($warPointDef) . "</span> points de Guerre.");

                    $reportInv->setTitle("Rapport d'invasion : Défaite (attaque)");
                    $reportInv->setImageName("invade_lose_report.jpg");
                    $reportInv->setContent("'AH AH AH AH' le rire de " .
                        $this->forward('App\Controller\FacilitiesController::userReportAction', ['user' => $userDefender, 'usePlanet' => $usePlanet])->getContent() .
                        " résonne à vos oreilles d'un curieuse façon. Votre sang bouillonne vous l'a vouliez cette planète. Qu'il rigole donc, vous reviendrez prendre " .
                        $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $planetDefender, 'usePlanet' => $usePlanet])->getContent() .
                        "et ferez effacer des livres d'histoires son ridicule nom. Vous avez tout de même tué <span class='text-vert'>" . number_format($soldierDtmp) .
                        "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) .
                        "</span> travailleurs à l'ennemi. Tous vos soldats sont morts et vos barges sont restées sur la planète.<br>Courage commandant.");
                }
            } else {
                $warPointAtt = round(($soldierDtmp?$soldierDtmp:1 + ($workerDtmp / 10)) * 1);
                if ($user->getPoliticPdg() > 0) {
                    $warPointAtt = round($warPointAtt * (1 + ($user->getPoliticPdg() / 10)));
                }
                $warPointAtt = round($warPointAtt / 600);
                $diviser = (1 + ($user->getPoliticSoldierAtt() / 10)) * $alea;
                $aMilitary = $aMilitary - $dMilitary;
                $fleet->setSoldier(abs($soldierAtmpTotal + round($aMilitary / $diviser)));
                $soldierAtmp = $fleet->getSoldier() - round($soldierAtmpTotal + $soldierAtmp);
                $planetDefender->setSoldier(0);
                $planetDefender->setTank(0);
                $planetDefender->setWorker(2000);

                if($fleet->getUser()->getColPlanets() <= ($fleet->getUser()->getTerraformation() + 1 + $user->getPoliticInvade()) && $userDefender->getZombie() == 0) {
                    $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $warPointAtt);
                    $planetDefender->setUser($user);
                    $em->flush();
                    if ($user->getNbrInvade()) {
                        $user->setNbrInvade($user->getNbrInvade() + 1);
                    } else {
                        $user->setNbrInvade(1);
                    }
                    $reportDef->setTitle("Rapport d'invasion : Défaite (défense)");
                    $reportDef->setImageName("defend_lose_report.jpg");

                    $reportDef->setContent("Mais QUI ? QUI !!! Vous as donné un commandant si médiocre" .
                        $this->forward('App\Controller\FacilitiesController::userReportAction', ['user' => $user, 'usePlanet' => $usePlanetDef])->getContent() .
                        "n'a pas eu à faire grand chose pour prendre votre planète" .
                        $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $planetDefender, 'usePlanet' => $usePlanetDef])->getContent() .
                        number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminés. C'est toujours ça de gagné. Vos <span class='text-rouge'>-" .
                        number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>-" . number_format($tankDtmp) ."</span> tanks et <span class='text-rouge'>-" .
                        number_format($workerDtmp) . "</span> travailleurs sont tous mort. Votre empire en a pris un coup, mais il vous reste des planètes, il est l'heure de la revanche !");

                    $reportInv->setTitle("Rapport d'invasion : Victoire (attaque)");
                    $reportInv->setImageName("invade_win_report.jpg");
                    $reportInv->setContent("Vous débarquez après que la planète ait été prise et vous installez sur le trône de" .
                        $this->forward('App\Controller\FacilitiesController::userReportAction', ['user' => $userDefender, 'usePlanet' => $usePlanet])->getContent() .
                        ". Qu'il est bon d'entendre ses pleurs lointains... La planète" .
                        $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $planetDefender, 'usePlanet' => $usePlanet])->getContent() .
                        "est désormais votre! Il est temps de remettre de l'ordre dans la galaxie. <span class='text-rouge'>" .
                        number_format(round($soldierAtmp)) . "</span> de vos soldats ont péri dans l'invasion. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" .
                        number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" .
                        number_format($workerDtmp) . "</span> travailleurs ont péri. Cependant vous épargnez 2000 travailleurs dans votre bonté (surtout pour faire tourner la planète).<br>Et vous remportez <span class='text-vert'>+" .
                        number_format($warPointAtt) . "</span> points de Guerre.");

                } else {
                    $warPointAtt = $warPointAtt / 50;
                    $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $warPointAtt);
                    $hydra = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);
                    if ($userDefender->getZombie() == 0) {
                        if ($user->getNbrInvade()) {
                            $user->setNbrInvade($user->getNbrInvade() + 1);
                        } else {
                            $user->setNbrInvade(1);
                        }
                        $usePlanetDef = $em->getRepository('App:Planet')->findByFirstPlanet($userDefender);
                        $reportDef->setTitle("Rapport d'invasion : Défaite (défense)");
                        $reportDef->setImageName("defend_lose_report.jpg");
                        $reportDef->setContent("Mais QUI ? QUI !!! Vous as donné un commandant si médiocre" .
                            $this->forward('App\Controller\FacilitiesController::userReportAction', ['user' => $user, 'usePlanet' => $usePlanetDef])->getContent() .
                            "n'a pas eu à faire grand chose pour prendre votre planète" .
                            $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $planetDefender, 'usePlanet' => $usePlanetDef])->getContent() .
                            number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminés. C'est toujours ça de gagné. Vos <span class='text-rouge'>-" .
                            number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>-" . number_format($tankDtmp) ."</span> tanks et <span class='text-rouge'>-" .
                            number_format($workerDtmp) . "</span> travailleurs sont tous mort. Votre empire en a pris un coup, mais il vous reste des planètes, il est l'heure de la revanche !");

                        $reportInv->setTitle("Rapport d'invasion : Victoire (attaque)");
                        $reportInv->setImageName("invade_win_report.jpg");
                        $reportInv->setContent("Vous débarquez après que la planète ait été prise et vous installez sur le trône de" .
                            $this->forward('App\Controller\FacilitiesController::userReportAction', ['user' => $userDefender, 'usePlanet' => $usePlanet])->getContent() .
                            ". Qu'il est bon d'entendre ses pleurs lointains... La planète " .
                            $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $planetDefender, 'usePlanet' => $usePlanet])->getContent() .
                            "est désormais votre! Il est temps de remettre de l'ordre dans la galaxie. <span class='text-rouge'>" . number_format(round($soldierAtmp)) .
                            "</span> de vos soldats ont péri dans l'invasion. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) .
                            "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) .
                            "</span> travailleurs ont péri. Cependant vous épargnez 2000 travailleurs dans votre bonté (surtout pour faire tourner la planète).<br>Et vous remportez <span class='text-vert'>+" .
                            number_format($warPointAtt) . "</span> points de Guerre.");
                    } else {
                        $reportInv->setTitle("Rapport contre attaque : Victoire");
                        $reportInv->setImageName("zombie_win_report.jpg");
                        $reportInv->setContent("Vos soldats débarquent sur la planète zombie et sortent l'artillerie lourde ! Les rues s'emplissent de morts mais l'entraînement prévaut sur la peur et vous purgez cette planète de cette peste macabre.<br> La planète " . $planetDefender->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $planetDefender->getSector()->getPosition() . "/" . $planetDefender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $planetDefender->getSector()->getGalaxy()->getPosition() . ":" . $planetDefender->getSector()->getPosition() . ":" . $planetDefender->getPosition() . "</a></span>) est désormais libre. Et votre indice d'attaque zombie est divisé par 10. Lors de l'assaut vous dénombrez <span class='text-rouge'>" . number_format(round($soldierAtmp)) . "</span> pertes parmis vos soldats. Mais vous avez exterminé <span class='text-vert'>" . number_format(round($soldierDtmp + ($workerDtmp / 6) + ($tankDtmp * 3000))) . "</span> zombies ! <br>Et vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre ainsi que <span class='text-vert'>+10</span> uraniums.");
                    }
                    if ($userDefender->getZombie() == 1) {
                        $image = [
                            'planet1.png', 'planet2.png', 'planet3.png', 'planet4.png', 'planet5.png', 'planet6.png',
                            'planet7.png', 'planet8.png', 'planet9.png', 'planet10.png', 'planet11.png', 'planet12.png',
                            'planet13.png', 'planet14.png', 'planet15.png', 'planet16.png', 'planet17.png', 'planet18.png',
                            'planet19.png', 'planet20.png', 'planet21.png', 'planet22.png', 'planet23.png', 'planet24.png',
                            'planet25.png', 'planet26.png', 'planet27.png', 'planet28.png', 'planet29.png', 'planet30.png',
                            'planet31.png', 'planet32.png', 'planet33.png'
                        ];
                        $planetDefender->setUser(null);
                        $em->flush();
                        if ($user->getZombieAtt() > 9) {
                            $user->setZombieAtt(round($user->getZombieAtt() / 10));
                        }
                        if($fleet->getCargoPlace() > $fleet->getCargoFull()) {
                            $place = $fleet->getCargoPlace() - $fleet->getCargoFull();
                            if ($place > 10) {
                                $fleet->setUranium($fleet->getUranium() + 10);
                            } else {
                                $fleet->setUranium($fleet->getUranium() + $place);
                            }
                        }
                        $planetDefender->setRestartAll();
                        $planetDefender->setImageName($image[rand(0, 32)]);
                    } else {
                        $planetDefender->setUser($hydra);
                        $planetDefender->setWorker(125000);
                        if ($planetDefender->getSoldierMax() >= 2500) {
                            $planetDefender->setSoldier($planetDefender->getSoldierMax());
                        } else {
                            $planetDefender->setCaserne(1);
                            $planetDefender->setSoldier(500);
                            $planetDefender->setSoldierMax(500);
                        }
                        $planetDefender->setName('Base Zombie');
                        $planetDefender->setImageName('hydra_planet.png');
                        $em->flush();
                    }
                }
                if($userDefender->getAllPlanets() == 0) {
                    $userDefender->setGameOver($user->getUserName());
                    $userDefender->setGrade(null);
                    if ($user->getExecution()) {
                        $user->setExecution($user->getExecution() . ', ' . $userDefender->getUsername());
                    } else {
                        $user->setExecution($userDefender->getUsername());
                    }
                    $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $userDefender->getRank()->getWarPoint());
                    $user->setBitcoin($user->getBitcoin() + $userDefender->getBitcoin());
                    $reportInv->setContent($reportInv->getContent() . "<br>Vous avez totalement anéanti l'Empire de" .
                        $this->forward('App\Controller\FacilitiesController::userReportAction', ['user' => $userDefender, 'usePlanet' => $usePlanet])->getContent() .
                        "et gagnez ses PDG : <span class='text-vert'>+" . number_format($userDefender->getRank()->getWarPoint()) .
                        "</span>, ainsi que ses Bitcoins : <span class='text-vert'>+" . number_format($userDefender->getBitcoin()) . " .</span>");
                    $userDefender->getRank()->setWarPoint(1);
                    $userDefender->setBitcoin(1);
                    foreach($userDefender->getFleets() as $tmpFleet) {
                        $tmpFleet->setUser($user);
                        $tmpFleet->setFleetList(null);
                    }
                }
                $quest = $user->checkQuests('invade');
                if($quest) {
                    $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                    $user->removeQuest($quest);
                }
            }
            if($fleet->getNbrShips() == 0) {
                $em->remove($fleet);
            }
            $em->persist($reportInv);
            if ($userDefender->getZombie() == 0) {
                $em->persist($reportDef);
            }
            $em->flush();
        } else {
            if (!$fleet->getAllianceUser() || $user->getSigleAllied($dSigle)) {
                return new Response ("ally");
            } elseif (!$fleet->getPlanet()->getUser()) {
                return new Response ("noplayer");
            }
        }
        return new Response (null);
    }

    public function raidAction($usePlanet, $fleet, $user, $now, $em)
    {
        if ($fleet->getUser()->getPoliticBarge() > 0) {
            $barge = $fleet->getBarge() * 2500 * (1 + ($fleet->getUser()->getPoliticBarge() / 4));
        } else {
            $barge = $fleet->getBarge() * 2500;
        }
        if ($barge) {
            if($barge >= $fleet->getSoldier()) {
                $aMilitary = $fleet->getSoldier() * 6;
                $soldierAtmp = $fleet->getSoldier();
                $soldierAtmpTotal = 0;
            } else {
                $aMilitary = $barge * 6;
                $soldierAtmp = $barge;
                $soldierAtmpTotal = $fleet->getSoldier() - $barge;
            }
            if ($fleet->getUser()->getPoliticSoldierAtt() > 0) {
                $aMilitary = $aMilitary * (1 + ($user->getPoliticSoldierAtt() / 10));
            }
        } else {
            return new Response ("nobarge");
        }

        $planetDefender = $fleet->getPlanet();
        $userDefender = $fleet->getPlanet()->getUser();
        $barbed = $userDefender->getBarbedAdv();
        $dSoldier = $planetDefender->getSoldier() > 0 ? ($planetDefender->getSoldier() * 6) * $barbed : 0;
        $dTanks = $planetDefender->getTank() > 0 ? $planetDefender->getTank() * 3000 : 0;
        $soldierDtmp = $planetDefender->getSoldier();
        $tankDtmp = $planetDefender->getTank();

        $seconds = $this->forward('App\Controller\Connected\Execute\ChronosController::planetActivityAction', [
            'planet' => $planetDefender,
            'now'  => $now,
            'em' => $em]);

        if ($seconds->getContent() >= 60) {
            $this->forward('App\Controller\Connected\Execute\PlanetsGenController::planetGenAction', [
                'planet' => $planetDefender,
                'seconds' => $seconds->getContent(),
                'now'  => $now,
                'em' => $em]);
        }

        if ($userDefender->getPoliticSoldierAtt() > 0) {
            $dSoldier = $dSoldier * (1 + ($userDefender->getPoliticSoldierAtt() / 10));
        }
        if ($userDefender->getPoliticTankDef() > 0) {
            $dTanks = $dTanks * (1 + ($userDefender->getPoliticTankDef() / 10));
        }
        $dMilitary = $dSoldier + $dTanks;

        $reportLoot = new Report();
        $reportLoot->setType('invade');
        $reportLoot->setSendAt($now);
        $reportLoot->setUser($fleet->getUser());
        $fleet->getUser()->setViewReport(false);
        $reportDef = new Report();
        $reportDef->setType('invade');
        $reportDef->setSendAt($now);
        $reportDef->setUser($userDefender);
        $userDefender->setViewReport(false);
        $dSigle = null;
        if ($userDefender->getAlly()) {
            $dSigle = $userDefender->getAlly()->getSigle();
        }
        $usePlanetDef = $em->getRepository('App:Planet')->findByFirstPlanet($userDefender);

        if ($fleet->getPlanet()->getUser() && $fleet->getAllianceUser() && $fleet->getUser()->getSigleAllied($dSigle) == NULL && $fleet->getFightAt() == null && $fleet->getFlightTime() == null && $userDefender->getZombie() == 0) {
            if($dMilitary >= $aMilitary) {
                $warPointDef = round($aMilitary);
                if ($userDefender->getPoliticPdg() > 0) {
                    $warPointDef = round(($warPointDef * (1 + ($userDefender->getPoliticPdg() / 10))) / 500);
                }
                $userDefender->getRank()->setWarPoint($userDefender->getRank()->getWarPoint() + $warPointDef);
                if($barge < $fleet->getSoldier()) {
                    $fleet->setSoldier($fleet->getSoldier() - $barge);
                } else {
                    $fleet->setSoldier(0);
                }
                $planetDefender->setBarge($planetDefender->getBarge() + $fleet->getBarge());
                $fleet->setBarge(0);
                $aMilitary = $aMilitary - $dSoldier;
                if($aMilitary >= 0) {
                    $planetDefender->setSoldier(0);
                    $aMilitary = $aMilitary - $dTanks;
                    $diviser = (1 + ($userDefender->getPoliticTankDef() / 10)) * 3000;
                    $planetDefender->setTank(round(abs($aMilitary / $diviser)));
                    $tankDtmp = $tankDtmp - $planetDefender->getTank();
                } else {
                    $diviser = (1 + ($userDefender->getPoliticSoldierAtt() / 10)) * (5 * $userDefender->getBarbedAdv()) * 6;
                    $dMilitary = $dMilitary - $aMilitary - $dTanks;
                    $planetDefender->setSoldier(round(abs($dMilitary / $diviser)));
                    $soldierDtmp = round(abs($dMilitary / $diviser));
                }

                $reportDef->setTitle("Rapport de pillage : Victoire (défense)");
                $reportDef->setImageName("defend_win_report.jpg");
                $reportDef->setContent("Le dirigeant" . $this->forward('App\Controller\FacilitiesController::userReportAction', ['user' => $fleet->getUser(), 'usePlanet' => $usePlanetDef])->getContent() .
                    "a tenté de piller votre planète" .
                    $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $planetDefender, 'usePlanet' => $usePlanetDef])->getContent() .
                    ". Il a échoué grâce a vos solides défenses. Vous avez éliminé <span class='text-vert'>" . number_format($soldierAtmp) .
                    "</span> soldats et pris le contrôle des barges de l'attaquant.<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointDef)->getContent() .
                    "</span> points de Guerre.");

                $reportLoot->setTitle("Rapport de pillage : Défaite (attaque)");
                $reportLoot->setImageName("invade_lose_report.jpg");
                $reportLoot->setContent("Le dirigeant" . $this->forward('App\Controller\FacilitiesController::userReportAction', ['user' => $userDefender, 'usePlanet' => $usePlanet])->getContent() .
                    " vous attendait de pieds fermes. Sa planète " .
                    $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $planetDefender, 'usePlanet' => $usePlanet])->getContent() .
                    "était trop renforcée pour vous. Vous tué tout de même <span class='text-vert'>" . number_format($soldierDtmp) .
                    "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) .
                    "</span> tanks. Tous vos soldats sont morts et vos barges sont restées sur la planète.<br>La prochaine fois, préparez votre attaque commandant.");

            } else {
                $warPointAtt = round($soldierDtmp ? $soldierDtmp : 1 + $tankDtmp);
                if ($fleet->getUser()->getPoliticPdg() > 0) {
                    $warPointAtt = round(($warPointAtt * (1 + ($fleet->getUser()->getPoliticPdg() / 10))) / 600);
                }
                $diviser = (1 + ($user->getPoliticSoldierAtt() / 10)) * 6;
                $aMilitary = $aMilitary - $dMilitary;
                $fleet->setSoldier(abs($soldierAtmpTotal + round($aMilitary / $diviser)));
                $soldierAtmp = $fleet->getSoldier() - round($soldierAtmpTotal + $soldierAtmp);
                $planetDefender->setSoldier(0);
                $planetDefender->setTank(0);
                if($fleet->getCargoPlace() > $fleet->getCargoFull()) {
                    $place = $fleet->getCargoPlace() - $fleet->getCargoFull();
                    if ($place > $planetDefender->getNiobium()) {
                        $fleet->setNiobium($fleet->getNiobium() + $planetDefender->getNiobium());
                        $place = $place - $planetDefender->getNiobium();
                        $niobium = $planetDefender->getNiobium();
                        $planetDefender->setNiobium(0);
                    } else {
                        $fleet->setNiobium($fleet->getNiobium() + $place);
                        $planetDefender->setNiobium($fleet->getNiobium() - $place);
                        $niobium = $place;
                        $place = 0;
                    }
                    if ($place > $planetDefender->getWater()) {
                        $fleet->setWater($fleet->getWater() + $planetDefender->getWater());
                        $place = $place - $planetDefender->getWater();
                        $water = $planetDefender->getWater();
                        $planetDefender->setWater(0);
                    } else {
                        $fleet->setWater($fleet->getWater() + $place);
                        $planetDefender->setWater($fleet->getWater() - $place);
                        $water = $place;
                        $place = 0;
                    }
                    if ($place > $planetDefender->getUranium()) {
                        $fleet->setUranium($fleet->getUranium() + $planetDefender->getUranium());
                        $uranium = $planetDefender->getUranium();
                        $planetDefender->setUranium(0);
                    } else {
                        $fleet->setUranium($fleet->getUranium() + $place);
                        $planetDefender->setUranium($fleet->getUranium() - $place);
                        $uranium = $place;
                    }
                }
                $fleet->getUser()->getRank()->setWarPoint($fleet->getUser()->getRank()->getWarPoint() + $warPointAtt);
                $reportDef->setTitle("Rapport de pillage : Défaite (défense)");
                $reportDef->setImageName("defend_lose_report.jpg");
                $reportDef->setContent("Le dirigeant" . $this->forward('App\Controller\FacilitiesController::userReportAction', ['user' => $fleet->getUser(), 'usePlanet' => $usePlanetDef])->getContent() .
                    " vient de piller (" . number_format(round($niobium)) . " niobiums" . number_format(round($water)) . " eaux" . number_format(round($uranium)) . " uraniums) votre planète" .
                    $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $planetDefender, 'usePlanet' => $usePlanetDef])->getContent() .
                    ". " . number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminé. C'est toujours ça de gagner. Vos <span class='text-rouge'>-" .
                    number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>-" . number_format($tankDtmp) .
                    "</span> tanks. Votre économie en a pris un coup, mais si vous étiez là pour planter des choux ça se serait ! Préparez la contre-attaque !");

                $reportLoot->setTitle("Rapport de pillage : Victoire (attaque)");
                $reportLoot->setImageName("invade_win_report.jpg");
                $reportLoot->setContent("Vos soldats ont fini de charger vos cargos ( <span class='text-vert'>" . number_format(round($niobium)) . " niobiums - " . number_format(round($water)) .
                    " eaux - " . number_format(round($uranium)) . " uraniums </span>) et remontent dans les barges, le pillage de la planète " .
                    $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $planetDefender, 'usePlanet' => $usePlanet])->getContent() .
                    "s'est bien passé. Vos pertes sont de <span class='text-rouge'>" . number_format(round($soldierAtmp)) .
                    "</span> soldats. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) .
                    "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks.<br>Vous remportez <span class='text-vert'>+" .
                    number_format($warPointAtt) . "</span> points de Guerre.");

                $quest = $fleet->getUser()->checkQuests('loot');
                if($quest) {
                    $fleet->getUser()->getRank()->setWarPoint($fleet->getUser()->getRank()->getWarPoint() + $quest->getGain());
                    $fleet->getUser()->removeQuest($quest);
                }
            }
            $em->persist($reportLoot);
            $em->persist($reportDef);

            $em->flush();
        } else {
            if ($userDefender->getZombie() == 1) {
                return new Response ("zombie");
            } elseif (!$fleet->getAllianceUser() || $fleet->getUser()->getSigleAllied($dSigle)) {
                return new Response ("ally");
            } elseif (!$fleet->getPlanet()->getUser()) {
                return new Response ("noplayer");
            }
        }
        return new Response (null);
    }
}