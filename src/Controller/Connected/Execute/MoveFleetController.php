<?php

namespace App\Controller\Connected\Execute;

use App\Entity\Fleet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Report;
use DateTimeZone;
use DateInterval;
use DateTime;

class MoveFleetController extends AbstractController
{
    public function centralizeFleetAction($fleets, $now, $em)
    {
        $nowReport = new DateTime();
        $nowReport->setTimezone(new DateTimeZone('Europe/Paris'));
        foreach ($fleets as $fleet) {
            $user = $fleet->getUser();
            if ($user->getMerchant() == 1) {
                $em->remove($fleet->getDestination());
                $em->remove($fleet);
            } else {
                $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($user);
                if (!$usePlanet) {
                    $em->remove($fleet);
                } else {
                    $newHome = $fleet->getDestination()->getPlanet();

                    $report = new Report();
                    $report->setType('move');
                    $report->setTitle("Votre flotte " . $fleet->getName() . " est arrivée");
                    $report->setImageName("travel_report.jpg");
                    $report->setSendAt($now);
                    $report->setUser($user);
                    $report->setContent("Bonjour dirigeant " . $user->getUserName() . " votre flotte " . "<span><a href='/connect/gerer-flotte/" . $fleet->getId() . "/" . $usePlanet->getId() . "'>" . $fleet->getName() . "</a></span>" . " vient d'arriver en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() . "/" . $newHome->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $newHome->getSector()->getGalaxy()->getPosition() . ":" . $newHome->getSector()->getPosition() . ":" . $newHome->getPosition() . "</a></span>.");
                    $user->setViewReport(false);
                    $oldPlanet = $fleet->getPlanet();
                    $fleet->setFlightTime(null);
                    $fleet->setPlanet($newHome);
                    $previousDestination = $fleet->getDestination();
                    if ($fleet->getFlightType() != '2') {
                        $previousDestination->setFleet(null);
                        $previousDestination->setPlanet(null);
                        $em->remove($previousDestination);
                        if ($newHome->getNbCdr() || $newHome->getWtCdr()) {
                            $fleet->setRecycleAt($nowReport);
                        } else {
                            $fleet->setRecycleAt(null);
                        }
                    }

                    $eAlly = $user->getAllyEnnemy();
                    $warAlly = [];
                    $x = 0;
                    foreach ($eAlly as $tmp) {
                        $warAlly[$x] = $tmp->getAllyTag();
                        $x++;
                    }

                    $fAlly = $user->getAllyFriends();
                    $friendAlly = [];
                    $x = 0;
                    foreach ($fAlly as $tmp) {
                        if ($tmp->getAccepted() == 1) {
                            $friendAlly[$x] = $tmp->getAllyTag();
                            $x++;
                        }
                    }
                    if (!$friendAlly) {
                        $friendAlly = ['impossible', 'personne'];
                    }

                    if ($user->getAlly()) {
                        $allyF = $user->getAlly();
                    } else {
                        $allyF = 'wedontexistsok';
                    }

                    $warFleets = $em->getRepository('App:Fleet')
                        ->createQueryBuilder('f')
                        ->join('f.user', 'u')
                        ->leftJoin('u.ally', 'a')
                        ->where('f.planet = :planet')
                        ->andWhere('f.attack = true OR a.sigle in (:ally)')
                        ->andWhere('f.user != :user')
                        ->andWhere('f.flightTime is null')
                        ->andWhere('u.ally is null OR a.sigle not in (:friend)')
                        ->andWhere('u.ally is null OR u.ally != :myAlly')
                        ->setParameters(['planet' => $newHome, 'ally' => $warAlly, 'user' => $user, 'friend' => $friendAlly, 'myAlly' => $allyF])
                        ->getQuery()
                        ->getResult();

                    $neutralFleets = $em->getRepository('App:Fleet')
                        ->createQueryBuilder('f')
                        ->join('f.user', 'u')
                        ->leftJoin('u.ally', 'a')
                        ->where('f.planet = :planet')
                        ->andWhere('f.user != :user')
                        ->andWhere('f.flightTime is null')
                        ->andWhere('u.ally is null OR a.sigle not in (:friend)')
                        ->setParameters(['planet' => $newHome, 'user' => $user, 'friend' => $friendAlly])
                        ->getQuery()
                        ->getResult();

                    $fleetFight = $em->getRepository('App:Fleet')
                        ->createQueryBuilder('f')
                        ->where('f.planet = :planet')
                        ->andWhere('f.user != :user')
                        ->andWhere('f.fightAt is not null')
                        ->andWhere('f.flightTime is null')
                        ->setParameters(['planet' => $newHome, 'user' => $user])
                        ->getQuery()
                        ->setMaxResults(1)
                        ->getOneOrNullResult();

                    if ($fleetFight) {
                        $fleet->setFightAt($fleetFight->getFightAt());
                    } elseif ($warFleets) {
                        foreach ($warFleets as $setWar) {
                            if ($setWar->getUser()->getAlly()) {
                                $fleetArm = $fleet->getMissile() + $fleet->getLaser() + $fleet->getPlasma();
                                if ($fleetArm > 0) {
                                    $fleet->setAttack(1);
                                }
                                foreach ($eAlly as $tmp) {
                                    if ($setWar->getUser()->getAlly()->getSigle() == $tmp->getAllyTag()) {
                                        $fleetArm = $setWar->getMissile() + $setWar->getLaser() + $setWar->getPlasma();
                                        if ($fleetArm > 0) {
                                            $setWar->setAttack(1);
                                        }
                                    }
                                }
                            }
                        }
                        $allFleets = $em->getRepository('App:Fleet')
                            ->createQueryBuilder('f')
                            ->where('f.planet = :planet')
                            ->andWhere('f.flightTime is null')
                            ->setParameters(['planet' => $newHome])
                            ->getQuery()
                            ->getResult();

                        $nowWar = new DateTime();
                        $nowWar->setTimezone(new DateTimeZone('Europe/Paris'));
                        $nowWar->add(new DateInterval('PT' . 300 . 'S'));

                        foreach ($allFleets as $updateF) {
                            $updateF->setFightAt($nowWar);
                        }
                        $fleet->setFightAt($nowWar);
                        $report->setContent($report->getContent() . " Attention votre flotte est rentrée en combat !");
                        $report->setImageName("war_report.jpg");
                    } elseif ($neutralFleets && $fleet->getAttack() == 1) {
                        $allFleets = $em->getRepository('App:Fleet')
                            ->createQueryBuilder('f')
                            ->join('f.user', 'u')
                            ->where('f.planet = :planet')
                            ->andWhere('f.flightTime is null')
                            ->setParameters(['planet' => $newHome])
                            ->getQuery()
                            ->getResult();

                        $nowWar = new DateTime();
                        $nowWar->setTimezone(new DateTimeZone('Europe/Paris'));
                        $nowWar->add(new DateInterval('PT' . 300 . 'S'));

                        foreach ($allFleets as $updateF) {
                            $updateF->setFightAt($nowWar);
                        }
                        $fleet->setFightAt($nowWar);
                        $report->setContent($report->getContent() . " Votre flotte vient d''engager le combat !");
                        $report->setImageName("war_report.jpg");
                    }
                    if ($fleet->getFightAt() == null) {
                        $newPlanet = $fleet->getPlanet();

                        if ($user->getZombie() == 1) {
                            $zbRegroups = $em->getRepository('App:Fleet')
                                ->createQueryBuilder('f')
                                ->where('f.planet = :planet')
                                ->andWhere('f.flightTime is null')
                                ->andWhere('f.user = :user')
                                ->andWhere('f.id != :fleet')
                                ->setParameters(['planet' => $newHome, 'user' => $user, 'fleet' => $fleet->getId()])
                                ->getQuery()
                                ->getResult();

                            foreach ($zbRegroups as $zbRegroup) {
                                $fleet->setHunter($fleet->getHunter() + $zbRegroup->getHunter());
                                $fleet->setHunterWar($fleet->getHunterWar() + $zbRegroup->getHunterWar());
                                $fleet->setCorvet($fleet->getCorvet() + $zbRegroup->getCorvet());
                                $fleet->setCorvetLaser($fleet->getCorvetLaser() + $zbRegroup->getCorvetLaser());
                                $fleet->setCorvetWar($fleet->getCorvetWar() + $zbRegroup->getCorvetWar());
                                $fleet->setFregate($fleet->getFregate() + $zbRegroup->getFregate());
                                $em->remove($zbRegroup);
                            }
                            $fleet->setSignature($fleet->getNbrSignatures());
                        }
                        if ($fleet->getFlightType() == '1' && $user->getZombie() == 0) {
                            $em->persist($report);
                        }
                        if ($fleet->getFlightType() == '2') {
                            if ($newPlanet->getMerchant() == true) {
                                $reportSell = new Report();
                                $reportSell->setType('economic');
                                $reportSell->setSendAt($nowReport);
                                $reportSell->setUser($user);
                                $reportSell->setTitle("Vente aux marchands");
                                $reportSell->setImageName("sell_report.jpg");
                                if ($user->getPoliticPdg() > 0) {
                                    $newWarPointS = round((((($fleet->getScientist() * 100) + ($fleet->getWorker() * 50) + ($fleet->getSoldier() * 10) + ($fleet->getWater() / 3) + ($fleet->getNiobium() / 6) + ($fleet->getTank() * 5) + ($fleet->getUranium() * 10)) / 5000)) * (1 + ($user->getPoliticPdg() / 10)));
                                } else {
                                    $newWarPointS = round((($fleet->getScientist() * 100) + ($fleet->getWorker() * 50) + ($fleet->getSoldier() * 10) + ($fleet->getWater() / 3) + ($fleet->getNiobium() / 6) + ($fleet->getTank() * 5) + ($fleet->getUranium() * 10)) / 5000);
                                }
                                if ($user->getPoliticMerchant() > 0) {
                                    $gainSell = (($fleet->getWater() * 0.25) + ($fleet->getSoldier() * 80) + ($fleet->getWorker() * 5) + ($fleet->getScientist() * 300) + ($fleet->getNiobium() * 0.10) + ($fleet->getTank() * 2500) + ($fleet->getUranium() * 5000)) * (1 + ($user->getPoliticMerchant() / 20));
                                } else {
                                    $gainSell = ($fleet->getWater() * 0.25) + ($fleet->getSoldier() * 80) + ($fleet->getWorker() * 5) + ($fleet->getScientist() * 300) + ($fleet->getNiobium() * 0.10) + ($fleet->getTank() * 2500) + ($fleet->getUranium() * 5000);
                                }
                                $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre. Votre flotte " . $fleet->getName() . " est sur le chemin du retour.");
                                $em->persist($reportSell);
                                $user->setBitcoin($user->getBitcoin() + $gainSell);
                                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
                                $fleet->setNiobium(0);
                                $fleet->setWater(0);
                                $fleet->setUranium(0);
                                $fleet->setSoldier(0);
                                $fleet->setTank(0);
                                $fleet->setWorker(0);
                                $fleet->setScientist(0);
                                $quest = $user->checkQuests('sell');
                                if ($quest) {
                                    $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                                    $user->removeQuest($quest);
                                }
                            } else {
                                if ($user != $newPlanet->getUser() && $newPlanet->getUser()) {
                                    $reportSell = new Report();
                                    $reportSell->setType('move');
                                    $reportSell->setSendAt($nowReport);
                                    $reportSell->setUser($newPlanet->getUser());
                                    $reportSell->setTitle("Dépôt de ressources");
                                    $reportSell->setImageName("depot_report.jpg");
                                    $reportSell->setContent("Le joueur " . $newPlanet->getUser()->getUserName() . " vient de déposer des ressources sur votre planète " . $newPlanet->getSector()->getgalaxy()->getPosition() . ":" . $newPlanet->getSector()->getPosition() . ":" . $newPlanet->getPosition() . " " . number_format($fleet->getNiobium()) . " Niobium, " . number_format($fleet->getWater()) . " Eau, " . number_format($fleet->getWorker()) . " Travailleurs, " . number_format($fleet->getSoldier()) . " Soldats, " . number_format($fleet->getScientist()) . " Scientifiques.");
                                    $em->persist($reportSell);
                                }
                                if ($newPlanet->getNiobium() + $fleet->getNiobium() <= $newPlanet->getNiobiumMax()) {
                                    $newPlanet->setNiobium($newPlanet->getNiobium() + $fleet->getNiobium());
                                    $fleet->setNiobium(0);
                                } else {
                                    $fleet->setNiobium($fleet->getNiobium() - ($newPlanet->getNiobiumMax() - $newPlanet->getNiobium()));
                                    $newPlanet->setNiobium($newPlanet->getNiobiumMax());
                                }
                                if ($newPlanet->getWater() + $fleet->getWater() <= $newPlanet->getWaterMax()) {
                                    $newPlanet->setWater($newPlanet->getWater() + $fleet->getWater());
                                    $fleet->setWater(0);
                                } else {
                                    $fleet->setWater($fleet->getWater() - ($newPlanet->getWaterMax() - $newPlanet->getWater()));
                                    $newPlanet->setWater($newPlanet->getWaterMax());
                                }
                                $newPlanet->setUranium($newPlanet->getUranium() + $fleet->getUranium());
                                $fleet->setUranium(0);
                                if ($newPlanet->getSoldier() + $fleet->getSoldier() <= $newPlanet->getSoldierMax()) {
                                    $newPlanet->setSoldier($newPlanet->getSoldier() + $fleet->getSoldier());
                                    $fleet->setSoldier(0);
                                } else {
                                    $fleet->setSoldier($fleet->getSoldier() - ($newPlanet->getSoldierMax() - $newPlanet->getSoldier()));
                                    $newPlanet->setSoldier($newPlanet->getSoldierMax());
                                }
                                if ($newPlanet->getTank() + $fleet->getTank() <= 500) {
                                    $newPlanet->setTank($newPlanet->getTank() + $fleet->getTank());
                                    $fleet->setTank(0);
                                } else {
                                    $fleet->setTank($fleet->getTank() - (500 - $newPlanet->getTank()));
                                    $newPlanet->setTank(500);
                                }
                                if ($newPlanet->getWorker() + $fleet->getWorker() <= $newPlanet->getWorkerMax()) {
                                    $newPlanet->setWorker($newPlanet->getWorker() + $fleet->getWorker());
                                    $fleet->setWorker(0);
                                } else {
                                    $fleet->setWorker($fleet->getWorker() - ($newPlanet->getWorkerMax() - $newPlanet->getWorker()));
                                    $newPlanet->setWorker($newPlanet->getWorkerMax());
                                }
                                if ($newPlanet->getScientist() + $fleet->getScientist() <= $newPlanet->getScientistMax()) {
                                    $newPlanet->setScientist($newPlanet->getScientist() + $fleet->getScientist());
                                    $fleet->setScientist(0);
                                } else {
                                    $fleet->setScientist($fleet->getScientist() - ($newPlanet->getScientistMax() - $newPlanet->getScientist()));
                                    $newPlanet->setScientist($newPlanet->getScientistMax());
                                }
                            }

                            $planetTakee = $newPlanet->getPosition();
                            $sFleet = $newPlanet->getSector()->getPosition();
                            $sector = $oldPlanet->getSector()->getPosition();
                            $galaxy = $oldPlanet->getSector()->getGalaxy()->getPosition();
                            if ($newPlanet->getSector()->getGalaxy()->getPosition() != $galaxy) {
                                $base = 18;
                                $price = 25;
                            } else {
                                $pFleet = $fleet->getPlanet()->getPosition();
                                if ($sFleet == $sector) {
                                    $x1 = ($pFleet - 1) % 5;
                                    $x2 = ($planetTakee - 1) % 5;
                                    $y1 = ($pFleet - 1) / 5;
                                    $y2 = ($planetTakee - 1) / 5;
                                } else {
                                    $x1 = (($sFleet - 1) % 10) * 3;
                                    $x2 = (($sector - 1) % 10) * 3;
                                    $y1 = (($sFleet - 1) / 10) * 3;
                                    $y2 = (($sector - 1) / 10) * 3;
                                }
                                $base = sqrt(pow(($x2 - $x1), 2) + pow(($y2 - $y1), 2));
                                $price = $base / 3;
                            }
                            $carburant = round($price * ($fleet->getNbrSignatures() / 200));
                            if ($carburant <= $user->getBitcoin()) {
                                if ($fleet->getMotherShip()) {
                                    $speed = $fleet->getSpeed() - ($fleet->getSpeed() * 0.10);
                                } else {
                                    $speed = $fleet->getSpeed();
                                }
                                $server = $em->getRepository('App:Server')->find(['id' => 1]);
                                $distance = $speed * $base * 1000 * $server->getSpeed();
                                $moreNow = new DateTime();
                                $moreNow->setTimezone(new DateTimeZone('Europe/Paris'));
                                $moreNow->add(new DateInterval('PT' . 120 . 'S'));
                                $nowFlight = new DateTime();
                                $nowFlight->setTimezone(new DateTimeZone('Europe/Paris'));
                                $nowFlight->add(new DateInterval('PT' . round($distance) . 'S'));
                                $fleet->setFlightTime($nowFlight);
                                $fleet->setFlightType(1);
                                $fleet->getDestination()->setPlanet($oldPlanet);
                                $fleet->setCancelFlight($moreNow);
                                $user->setBitcoin($user->getBitcoin() - $carburant);
                            }
                        } elseif ($fleet->getFlightType() == '3') {
                            if ($fleet->getColonizer() && $newPlanet->getUser() == null &&
                                $newPlanet->getEmpty() == false && $newPlanet->getMerchant() == false &&
                                $newPlanet->getCdr() == false && $user->getColPlanets() < 26 &&
                                $user->getColPlanets() <= ($user->getTerraformation() + 1 + $user->getPoliticColonisation())) {

                                $fleet->setColonizer($fleet->getColonizer() - 1);
                                $newPlanet->setUser($user);
                                if ($user->getZombie() == 1) {
                                    $newPlanet->setName('Base Zombie');
                                    $newPlanet->setWorker(125000);
                                    $newPlanet->setSoldier(2500);
                                    $newPlanet->setSoldierMax(2500);
                                    $newPlanet->setCaserne(1);
                                } else {
                                    $newPlanet->setName('Colonie');
                                    $newPlanet->setSoldier(50);
                                }
                                $newPlanet->setNbColo(count($user->getPlanets()) + 1);
                                $quest = $user->checkQuests('colonize');
                                if ($quest) {
                                    $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                                    $user->removeQuest($quest);
                                }
                                if ($fleet->getNbrShips() == 0) {
                                    $em->remove($fleet);
                                }
                                $reportColo = new Report();
                                $reportColo->setSendAt($nowReport);
                                $reportColo->setUser($user);
                                $reportColo->setTitle("Colonisation de planète");
                                $reportColo->setImageName("colonize_report.jpg");
                                $reportColo->setContent("Vous venez de coloniser une planète inhabitée en : " . "<span><a href='/connect/carte-spatiale/" . $newPlanet->getSector()->getPosition() . "/" . $newPlanet->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $newPlanet->getSector()->getGalaxy()->getPosition() . ":" . $newPlanet->getSector()->getPosition() . ":" . $newPlanet->getPosition() . "</a></span>" . ". Cette planète fait désormais partit de votre Empire, pensez a la renommer sur la page Planètes.");
                                $user->setViewReport(false);
                                $em->persist($reportColo);
                            }
                        } elseif ($fleet->getFlightType() == '4') {
                            if ($user->getPoliticBarge() > 0) {
                                $barge = $fleet->getBarge() * 2500 * (1 + ($user->getPoliticBarge() / 4));
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
                                if ($user->getPoliticSoldierAtt() > 0) {
                                    $aMilitary = $aMilitary * (1 + ($user->getPoliticSoldierAtt() / 10));
                                }
                            } else {
                                $this->addFlash("fail", "Vous ne disposez pas de barges d'invasions.");
                                return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
                            }

                            $defender = $fleet->getPlanet();
                            $userDefender = $fleet->getPlanet()->getUser();
                            $barbed = $userDefender->getBarbedAdv();
                            $dSoldier = $defender->getSoldier() > 0 ? ($defender->getSoldier() * 6) * $barbed : 0;
                            $dTanks = $defender->getTank() > 0 ? $defender->getTank() * 3000 : 0;
                            $soldierDtmp = $defender->getSoldier();
                            $tankDtmp = $defender->getTank();
                            if ($userDefender->getPoliticSoldierAtt() > 0) {
                                $dSoldier = $dSoldier * (1 + ($userDefender->getPoliticSoldierAtt() / 10));
                            }
                            if ($userDefender->getPoliticTankDef() > 0) {
                                $dTanks = $dTanks * (1 + ($userDefender->getPoliticTankDef() / 10));
                            }
                            $dMilitary = $dSoldier + $dTanks;
                            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($user);
                            $usePlanetDef = $em->getRepository('App:Planet')->findByFirstPlanet($defender->getUser());

                            $reportLoot = new Report();
                            $reportLoot->setType('invade');
                            $reportLoot->setSendAt($now);
                            $reportLoot->setUser($user);
                            $user->setViewReport(false);
                            $reportDef = new Report();
                            $reportDef->setType('invade');
                            $reportDef->setSendAt($now);
                            $reportDef->setUser($userDefender);
                            $userDefender->setViewReport(false);
                            $dSigle = null;
                            if ($userDefender->getAlly()) {
                                $dSigle = $userDefender->getAlly()->getSigle();
                            }

                            if ($fleet->getPlanet()->getUser() && $fleet->getAllianceUser() && $user->getSigleAllied($dSigle) == NULL && $userDefender->getZombie() == 0) {
                                if($dMilitary >= $aMilitary) {
                                    $warPointDef = round($aMilitary);
                                    if ($userDefender->getPoliticPdg() > 0) {
                                        $warPointDef = round(($warPointDef * (1 + ($userDefender->getPoliticPdg() / 10))) / 50);
                                    }
                                    $userDefender->getRank()->setWarPoint($userDefender->getRank()->getWarPoint() + $warPointDef);
                                    if($barge < $fleet->getSoldier()) {
                                        $fleet->setSoldier($fleet->getSoldier() - $barge);
                                    } else {
                                        $fleet->setSoldier(0);
                                    }
                                    $defender->setBarge($defender->getBarge() + $fleet->getBarge());
                                    $fleet->setBarge(0);
                                    $aMilitary = $aMilitary - $dSoldier;
                                    if($aMilitary >= 0) {
                                        $defender->setSoldier(0);
                                        $aMilitary = $aMilitary - $dTanks;
                                        $diviser = (1 + ($userDefender->getPoliticTankDef() / 10)) * 3000;
                                        $defender->setTank(round(abs($aMilitary / $diviser)));
                                        $tankDtmp = $tankDtmp - $defender->getTank();
                                    } else {
                                        $diviser = (1 + ($userDefender->getPoliticSoldierAtt() / 10)) * (5 * $userDefender->getBarbedAdv()) * 6;
                                        $dMilitary = $dMilitary - $aMilitary - $dTanks;
                                        $defender->setSoldier(round(abs($dMilitary / $diviser)));
                                        $soldierDtmp = round(abs($dMilitary / $diviser));
                                    }

                                    $reportDef->setTitle("Rapport de pillage : Victoire (défense)");
                                    $reportDef->setImageName("defend_win_report.jpg");
                                    $reportDef->setContent("Le dirigeant " . $user->getUserName() . " a tenté de piller votre planète " . $defender->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanetDef->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>). Il a échoué grâce a vos solides défenses. Vous avez éliminé <span class='text-vert'>" . number_format($soldierAtmp) . "</span> soldats et prit le contrôle des barges de l'attaquant.<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointDef) . "</span> points de Guerre.");
                                    $reportLoot->setTitle("Rapport de pillage : Défaite (attaque)");
                                    $reportLoot->setImageName("invade_lose_report.jpg");
                                    $reportLoot->setContent("Le dirigeant " . $userDefender->getUserName() . " vous attendait de pieds fermes. Sa planète " . $defender->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>) était trop renforcée pour vous. Vous tué tout de même <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks. Tous vos soldats sont morts et vos barges sont restées sur la planète.<br>La prochaine fois, préparez votre attaque commandant.");
                                } else {
                                    $warPointAtt = round($soldierDtmp?$soldierDtmp:1 + $tankDtmp);
                                    if ($user->getPoliticPdg() > 0) {
                                        $warPointAtt = round(($warPointAtt * (1 + ($user->getPoliticPdg() / 10))) / 60);
                                    }
                                    $diviser = (1 + ($user->getPoliticSoldierAtt() / 10)) * 6;
                                    $aMilitary = $aMilitary - $dMilitary;
                                    $fleet->setSoldier(abs($soldierAtmpTotal + round($aMilitary / $diviser)));
                                    $soldierAtmp = $fleet->getSoldier() - round($soldierAtmpTotal + $soldierAtmp);
                                    $defender->setSoldier(0);
                                    $defender->setTank(0);
                                    if($fleet->getCargoPlace() > $fleet->getCargoFull()) {
                                        $place = $fleet->getCargoPlace() - $fleet->getCargoFull();
                                        if ($place > $defender->getNiobium()) {
                                            $fleet->setNiobium($fleet->getNiobium() + $defender->getNiobium());
                                            $place = $place - $defender->getNiobium();
                                            $niobium = $defender->getNiobium();
                                            $defender->setNiobium(0);
                                        } else {
                                            $fleet->setNiobium($fleet->getNiobium() + $place);
                                            $defender->setNiobium($fleet->getNiobium() - $place);
                                            $niobium = $place;
                                            $place = 0;
                                        }
                                        if ($place > $defender->getWater()) {
                                            $fleet->setWater($fleet->getWater() + $defender->getWater());
                                            $place = $place - $defender->getWater();
                                            $water = $defender->getWater();
                                            $defender->setWater(0);
                                        } else {
                                            $fleet->setWater($fleet->getWater() + $place);
                                            $defender->setWater($fleet->getWater() - $place);
                                            $water = $place;
                                            $place = 0;
                                        }
                                        if ($place > $defender->getUranium()) {
                                            $fleet->setUranium($fleet->getUranium() + $defender->getUranium());
                                            $uranium = $defender->getUranium();
                                            $defender->setUranium(0);
                                        } else {
                                            $fleet->setUranium($fleet->getUranium() + $place);
                                            $defender->setUranium($fleet->getUranium() - $place);
                                            $uranium = $place;
                                        }
                                    }
                                    $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $warPointAtt);
                                    $reportDef->setTitle("Rapport de pillage : Défaite (défense)");
                                    $reportDef->setImageName("defend_lose_report.jpg");
                                    $reportDef->setContent("Le dirigeant " . $user->getUserName() . " vient de piller (" . number_format(round($niobium)) . " niobiums" . number_format(round($water)) . " eaux" . number_format(round($uranium)) . " uraniums) votre planète " . $defender->getName() . " - (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanetDef->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>).  " . number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminé. C'est toujours ça de gagner. Vos <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) ."</span> tanks. Votre économie en a prit un coup, mais si vous étiez là pour planter des choux ça se serait ! Préparez la contre-attaque !");
                                    $reportLoot->setTitle("Rapport de pillage : Victoire (attaque)");
                                    $reportLoot->setImageName("invade_win_report.jpg");
                                    $reportLoot->setContent("Vos soldats ont fini de charger vos cargos ( <span class='text-vert'>" . number_format(round($niobium)) . " niobiums - " . number_format(round($water)) . " eaux - " . number_format(round($uranium)) . " uraniums </span>) et remontent dans les barges, le pillage de la planète " . $defender->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>) s'est bien passé. Vos pertes sont de <span class='text-rouge'>" . number_format(round($soldierAtmp)) . "</span> soldats. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks.<br>Vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre.");
                                    $quest = $user->checkQuests('loot');
                                    if($quest) {
                                        $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                                        $user->removeQuest($quest);
                                    }
                                }
                                $em->persist($reportLoot);
                                $em->persist($reportDef);
                            }
                        } elseif ($fleet->getFlightType() == '5' && $fleet->getPlanet()->getUser()) {
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
                                $this->addFlash("fail", "Vous ne disposez pas de barges d'invasions.");
                                return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
                            }

                            $defender = $fleet->getPlanet();
                            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($user);
                            $usePlanetDef = $em->getRepository('App:Planet')->findByFirstPlanet($defender->getUser());
                            $userDefender= $fleet->getPlanet()->getUser();
                            $barbed = $userDefender->getBarbedAdv();
                            $dSoldier = $defender->getSoldier() > 0 ? ($defender->getSoldier() * 6) * $barbed : 0;
                            $dTanks = $defender->getTank() > 0 ? $defender->getTank() * 3000 : 0;
                            $dWorker = $defender->getWorker();
                            $soldierDtmp = $defender->getSoldier();
                            $workerDtmp = $defender->getWorker();
                            $tankDtmp = $defender->getTank();
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
                                            $warPointDef = round(($warPointDef * (1 + ($user->getPoliticPdg() / 10))) / 50);
                                        }
                                        $userDefender->getRank()->setWarPoint($userDefender->getRank()->getWarPoint() + $warPointDef);
                                    }
                                    $aMilitary = $aMilitary - $dSoldier;
                                    if($barge < $fleet->getSoldier()) {
                                        $fleet->setSoldier($fleet->getSoldier() - $barge);
                                    } else {
                                        $fleet->setSoldier(0);
                                    }
                                    $defender->setBarge($defender->getBarge() + $fleet->getBarge());
                                    $fleet->setBarge(0);
                                    if($aMilitary > 0) {
                                        $defender->setSoldier(0);
                                        $aMilitary = $aMilitary - $dTanks;
                                        if($aMilitary >= 0) {
                                            $defender->setTank(0);
                                            $aMilitary = $aMilitary - $dWorker;
                                            $diviser = (1 + ($userDefender->getPoliticWorkerDef() / 5));
                                            $defender->setWorker(round(abs($aMilitary / $diviser)));
                                            $tankDtmp = $tankDtmp - $defender->getTank();
                                            $soldierDtmp = $soldierDtmp - $defender->getSoldier();
                                            $workerDtmp = $workerDtmp - $defender->getWorker();
                                        } else {
                                            $diviser = (1 + ($userDefender->getPoliticTankDef() / 10)) * 3000;
                                            $defender->setTank(round(abs($aMilitary / $diviser)));
                                            $tankDtmp = $tankDtmp - $defender->getTank();
                                            $soldierDtmp = $soldierDtmp - $defender->getSoldier();
                                        }
                                    } else {
                                        $dMilitary = $dMilitary - $aMilitary - $dTanks -$dWorker;
                                        $diviser = (1 + ($userDefender->getPoliticSoldierAtt() / 10)) * ($alea * $userDefender->getBarbedAdv()) * 6;
                                        $defender->setSoldier(round(abs($dMilitary / $diviser)));
                                        $soldierDtmp = round(abs($dMilitary / $diviser));
                                    }
                                    if ($userDefender->getZombie() == 1) {
                                        $reportInv->setTitle("Rapport contre attaque : Défaite");
                                        $reportInv->setImageName("zombie_lose_report.jpg");
                                        $reportInv->setContent("Vous pensiez partir pour une promenade de santé mais la réalité vous rattrape vite... Vous avez envoyé tout vos soldats au casse-pipe.<br>Pire, vous avez attirer l'attention des zombies et fait monter la menace de 10 points ! Vous avez interêt a prendre vite " . $defender->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>) sinon votre Empire ne tiendra pas longtemps. Vous avez tué <span class='text-vert'>" . number_format(round($soldierDtmp + ($workerDtmp / 6) + ($tankDtmp * 3000))) . "</span> zombies. Tous vos soldats sont morts et vos barges se sont égarées sur la planète.<br>N'abandonnez pas et sortez vos tripes !");
                                        $user->setZombieAtt($user->getZombieAtt() + 10);
                                    } else {
                                        $reportDef->setTitle("Rapport d'invasion : Victoire (défense)");
                                        $reportDef->setImageName("defend_win_report.jpg");
                                        $reportDef->setContent("Bien joué ! Vos travailleurs et soldats ont repoussé l'invasion du joueur " . $user->getUserName() . " sur votre planète " . $defender->getName() . " - (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanetDef->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>) .  <span class='text-vert'>" . number_format($soldierAtmp) . "</span> soldats vous ont attaqué, tous ont été tué. Vous avez ainsi prit le contrôle des barges de l'attaquant.<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointDef) . "</span> points de Guerre.");
                                        $reportInv->setTitle("Rapport d'invasion : Défaite (attaque)");
                                        $reportInv->setImageName("invade_lose_report.jpg");
                                        $reportInv->setContent("'AH AH AH AH' le rire de " . $userDefender->getUserName() . " résonne à vos oreilles d'un curieuse façon. Votre sang bouillonne vous l'a vouliez cette planète. Qu'il rigole donc, vous reviendrez prendre " . $defender->getName() . " - (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>) et ferez effacer des livres d'histoires son ridicule nom. Vous avez tout de même tué <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) . "</span> travailleurs à l'ennemi. Tous vos soldats sont morts et vos barges sont restées sur la planète.<br>Courage commandant.");
                                    }
                                } else {
                                    $warPointAtt = round(($soldierDtmp?$soldierDtmp:1 + ($workerDtmp / 10)) * 1);
                                    if ($user->getPoliticPdg() > 0) {
                                        $warPointAtt = round($warPointAtt * (1 + ($user->getPoliticPdg() / 10)));
                                    }
                                    $warPointAtt = round($warPointAtt / 60);
                                    $diviser = (1 + ($user->getPoliticSoldierAtt() / 10)) * $alea;
                                    $aMilitary = $aMilitary - $dMilitary;
                                    $fleet->setSoldier(abs($soldierAtmpTotal + round($aMilitary / $diviser)));
                                    $soldierAtmp = $fleet->getSoldier() - round($soldierAtmpTotal + $soldierAtmp);
                                    $defender->setSoldier(0);
                                    $defender->setTank(0);
                                    $defender->setWorker(2000);

                                    if($user->getColPlanets() <= ($user->getTerraformation() + 1 + $user->getPoliticInvade()) && $userDefender->getZombie() == 0) {
                                        $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $warPointAtt);
                                        $defender->setUser($user);
                                        $em->flush();
                                        if ($user->getNbrInvade()) {
                                            $user->setNbrInvade($user->getNbrInvade() + 1);
                                        } else {
                                            $user->setNbrInvade(1);
                                        }
                                        $reportDef->setTitle("Rapport d'invasion : Défaite (défense)");
                                        $reportDef->setImageName("defend_lose_report.jpg");
                                        $reportDef->setContent("Mais QUI ? QUI !!! Vous as donné un commandant si médiocre " . $defender->getUser()->getUserName() . " n'a pas eu a faire grand chose pour prendre votre planète " . $defender->getName() . " - (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanetDef->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>)  " . number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminé. C'est toujours ça de gagner. Vos <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-rouge'>" . number_format($workerDtmp) . "</span> travailleurs sont tous mort. Votre empire en a prit un coup, mais il vous reste des planètes, il est l'heure de la revanche !");
                                        $reportInv->setTitle("Rapport d'invasion : Victoire (attaque)");
                                        $reportInv->setImageName("invade_win_report.jpg");
                                        $reportInv->setContent("Vous débarquez après que la planète ait été prise et vous installez sur le trône de " . $userDefender->getUserName() . ". Qu'il est bon d'entendre ses pleurs lointains... La planète " . $defender->getName() . " - (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>) est désormais votre! Il est temps de remettre de l'ordre dans la galaxie. <span class='text-rouge'>" . number_format(round($soldierAtmp)) . "</span> de vos soldats ont péri dans l'invasion. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) . "</span> travailleurs ont péri. Cependant vous épargnez 2000 travailleurs dans votre bonté (surtout pour faire tourner la planète).<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre.");
                                    } else {
                                        $warPointAtt = $warPointAtt / 5;
                                        $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $warPointAtt);
                                        $hydra = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);
                                        if ($userDefender->getZombie() == 0) {
                                            if ($user->getNbrInvade()) {
                                                $user->setNbrInvade($user->getNbrInvade() + 1);
                                            } else {
                                                $user->setNbrInvade(1);
                                            }
                                            $reportDef->setTitle("Rapport d'invasion : Défaite (défense)");
                                            $reportDef->setImageName("defend_lose_report.jpg");
                                            $reportDef->setContent("Mais QUI ? QUI !!! Vous as donné un commandant si médiocre " . $defender->getUser()->getUserName() . " n'a pas eu a faire grand chose pour prendre votre planète " . $defender->getName() . " - (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanetDef->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>)  " . number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminé. C'est toujours ça de gagner. Vos <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-rouge'>" . number_format($workerDtmp) . "</span> travailleurs sont tous mort. Votre empire en a prit un coup, mais il vous reste des planètes, il est l'heure de la revanche !");
                                            $reportInv->setTitle("Rapport d'invasion : Victoire (attaque)");
                                            $reportInv->setImageName("invade_win_report.jpg");
                                            $reportInv->setContent("Vous débarquez après que la planète ait été prise et vous installez sur le trône de " . $userDefender->getUserName() . ". Qu'il est bon d'entendre ses pleurs lointains... La planète " . $defender->getName() . " - (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>) est désormais votre! Il est temps de remettre de l'ordre dans la galaxie. <span class='text-rouge'>" . number_format(round($soldierAtmp)) . "</span> de vos soldats ont péri dans l'invasion. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) . "</span> travailleurs ont péri. Cependant vous épargnez 2000 travailleurs dans votre bonté (surtout pour faire tourner la planète).<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre.");
                                        } else {
                                            $reportInv->setTitle("Rapport contre attaque : Victoire");
                                            $reportInv->setImageName("zombie_win_report.jpg");
                                            $reportInv->setContent("Vos soldats débarquent sur la planète zombie et sortent l'artillerie lourde ! Les rues s'emplissent de morts mais l'entraînement prévaut sur la peur et vous purgez cette planète de cette peste macabre.<br> La planète " . $defender->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>) est désormais libre. Et votre indice d'attaque zombie est divisé par 10. Lors de l'assaut vous dénombrez <span class='text-rouge'>" . number_format(round($soldierAtmp)) . "</span> pertes parmis vos soldats. Mais vous avez exterminé <span class='text-vert'>" . number_format(round($soldierDtmp + ($workerDtmp / 6) + ($tankDtmp * 3000))) . "</span> zombies ! <br>Et vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre.");
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
                                            $defender->setUser(null);
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
                                            $defender->setRestartAll();
                                            $defender->setImageName($image[rand(0, 32)]);
                                        } else {
                                            $defender->setUser($hydra);
                                            $defender->setWorker(125000);
                                            if ($defender->getSoldierMax() >= 2500) {
                                                $defender->setSoldier($defender->getSoldierMax());
                                            } else {
                                                $defender->setCaserne(1);
                                                $defender->setSoldier(2500);
                                                $defender->setSoldierMax(2500);
                                            }
                                            $defender->setName('Base Zombie');
                                            $defender->setImageName('hydra_planet.png');
                                            $em->flush();
                                        }
                                    }
                                    if($userDefender->getColPlanets() == 0) {
                                        $userDefender->setGameOver($user->getUserName());
                                        $userDefender->setGrade(null);
                                        if ($user->getExecution()) {
                                            $user->setExecution($user->getExecution() . ', ' . $userDefender->getUsername());
                                        } else {
                                            $user->setExecution($userDefender->getUsername());
                                        }
                                        $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $userDefender->getRank()->getWarPoint());
                                        $user->setBitcoin($user->getBitcoin() + $userDefender->getBitcoin());
                                        $reportInv->setContent($reportInv->getContent() . "<br>Vous avez totalement anéanti l'Empire de " . $userDefender->getUsername() . " et gagnez ses PDG : <span class='text-vert'>+" . number_format($userDefender->getRank()->getWarPoint()) . "</span>, ainsi que ses Bitcoins : <span class='text-vert'>+" . number_format($userDefender->getBitcoin()) . " .</span>");
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
                            }
                        } elseif ($fleet->getFlightType() == '7') {
                            $planetGround = $fleet->getPlanet();
                            $planetGround->setSonde($planetGround->getSonde() + $fleet->getSonde());
                            $planetGround->setCargoI($planetGround->getCargoI() + $fleet->getCargoI());
                            $planetGround->setCargoV($planetGround->getCargoV() + $fleet->getCargoV());
                            $planetGround->setCargoX($planetGround->getCargoX() + $fleet->getCargoX());
                            $planetGround->setColonizer($planetGround->getColonizer() + $fleet->getColonizer());
                            $planetGround->setRecycleur($planetGround->getRecycleur() + $fleet->getRecycleur());
                            $planetGround->setBarge($planetGround->getBarge() + $fleet->getBarge());
                            $planetGround->setMoonMaker($planetGround->getMoonMaker() + $fleet->getMoonMaker());
                            $planetGround->setRadarShip($planetGround->getRadarShip() + $fleet->getRadarShip());
                            $planetGround->setBrouilleurShip($planetGround->getBrouilleurShip() + $fleet->getBrouilleurShip());
                            $planetGround->setMotherShip($planetGround->getMotherShip() + $fleet->getMotherShip());
                            $planetGround->setHunter($planetGround->getHunter() + $fleet->getHunter());
                            $planetGround->setHunterHeavy($planetGround->getHunterHeavy() + $fleet->getHunterHeavy());
                            $planetGround->setHunterWar($planetGround->getHunterWar() + $fleet->getHunterWar());
                            $planetGround->setCorvet($planetGround->getCorvet() + $fleet->getCorvet());
                            $planetGround->setCorvetLaser($planetGround->getCorvetLaser() + $fleet->getCorvetLaser());
                            $planetGround->setCorvetWar($planetGround->getCorvetWar() + $fleet->getCorvetWar());
                            $planetGround->setFregate($planetGround->getFregate() + $fleet->getFregate());
                            $planetGround->setFregatePlasma($planetGround->getFregatePlasma() + $fleet->getFregatePlasma());
                            $planetGround->setCroiser($planetGround->getCroiser() + $fleet->getCroiser());
                            $planetGround->setIronClad($planetGround->getIronClad() + $fleet->getIronClad());
                            $planetGround->setDestroyer($planetGround->getDestroyer() + $fleet->getDestroyer());
                            $planetGround->setSoldier($planetGround->getSoldier() + $fleet->getSoldier());
                            $planetGround->setTank($planetGround->getTank() + $fleet->getTank());
                            $planetGround->setWorker($planetGround->getWorker() + $fleet->getWorker());
                            $planetGround->setScientist($planetGround->getScientist() + $fleet->getScientist());
                            $planetGround->setNiobium($planetGround->getNiobium() + $fleet->getNiobium());
                            $planetGround->setWater($planetGround->getWater() + $fleet->getWater());
                            $planetGround->setFood($planetGround->getFood() + $fleet->getFood());
                            $planetGround->setUranium($planetGround->getUranium() + $fleet->getUranium());
                            $planetGround->setNuclearBomb($planetGround->getNuclearBomb() + $fleet->getNuclearBomb());
                            $fleet->setUser(null);
                            $em->remove($fleet);
                            $planetGround->setSignature($planetGround->getNbrSignatures());
                        }
                    } else {
                        if ($user->getZombie() == 0) {
                            $em->persist($report);
                        }
                    }
                }
            }
        }
        echo "Flush ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}