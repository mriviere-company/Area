<?php

namespace App\Controller\Connected\Execute;

use App\Entity\Fleet;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Report;
use DateInterval;
use DateTime;

/**
 * Class MoveFleetController
 * @package App\Controller\Connected\Execute
 */
class MoveFleetController extends AbstractController
{
    /**
     * @param $fleets
     * @param $now
     * @param $em
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function centralizeFleetAction($fleets, $now, $em)
    {
        $nowReport = new DateTime();
        foreach ($fleets as $fleet) {
            $character = $fleet->getCharacter();
            $server = $fleet->getDestination()->getPlanet()->getSector()->getGalaxy()->getServer();
            if (!$character || $character->getMerchant() == 1) {
                $em->remove($fleet->getDestination());
                $em->remove($fleet);
            } else {
                $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($character);
                if (!$usePlanet) {
                    $em->remove($fleet);
                } else {
                    $newHome = $fleet->getDestination()->getPlanet();

                    $report = new Report();
                    $report->setType('move');
                    $report->setTitle("Votre flotte " . $fleet->getName() . " est arrivée");
                    $report->setImageName("travel_report.jpg");
                    $report->setSendAt($now);
                    $report->setCharacter($character);
                    $report->setContent("Bonjour dirigeant " . $character->getUsername() . " votre flotte " . "<span><a href='/connect/gerer-flotte/" . $fleet->getId() . "/" . $usePlanet->getId() . "'>" . $fleet->getName() . "</a></span>" . " vient d'arriver en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() . "/" . $newHome->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $newHome->getSector()->getGalaxy()->getPosition() . ":" . $newHome->getSector()->getPosition() . ":" . $newHome->getPosition() . "</a></span>.");
                    $character->setViewReport(false);
                    $oldPlanet = $fleet->getPlanet();
                    $fleet->setFlightTime(null);
                    $fleet->setPlanet($newHome);
                    $previousDestination = $fleet->getDestination();
                    if ($fleet->getFlightType() != '2') {
                        $em->remove($previousDestination);
                        if ($newHome->getNbCdr() || $newHome->getWtCdr()) {
                            $fleet->setRecycleAt($nowReport);
                        } else {
                            $fleet->setRecycleAt(null);
                        }
                    }

                    $eAlly = $character->getAllyEnnemy();
                    $warAlly = [];
                    $x = 0;
                    foreach ($eAlly as $tmp) {
                        $warAlly[$x] = $tmp->getAllyTag();
                        $x++;
                    }

                    $fAlly = $character->getAllyFriends();
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

                    if ($character->getAlly()) {
                        $allyF = $character->getAlly();
                    } else {
                        $allyF = 'wedontexistsok';
                    }

                    $warFleets = $em->getRepository('App:Fleet')
                        ->createQueryBuilder('f')
                        ->join('f.character', 'c')
                        ->leftJoin('c.ally', 'a')
                        ->where('f.planet = :planet')
                        ->andWhere('f.attack = true OR a.sigle in (:ally)')
                        ->andWhere('f.character != :character')
                        ->andWhere('f.flightTime is null')
                        ->andWhere('c.ally is null OR a.sigle not in (:friend)')
                        ->andWhere('c.ally is null OR c.ally != :myAlly')
                        ->setParameters(['planet' => $newHome, 'ally' => $warAlly, 'character' => $character, 'friend' => $friendAlly, 'myAlly' => $allyF])
                        ->getQuery()
                        ->getResult();

                    $neutralFleets = $em->getRepository('App:Fleet')
                        ->createQueryBuilder('f')
                        ->join('f.character', 'c')
                        ->leftJoin('c.ally', 'a')
                        ->where('f.planet = :planet')
                        ->andWhere('f.character != :character')
                        ->andWhere('f.flightTime is null')
                        ->andWhere('c.ally is null OR a.sigle not in (:friend)')
                        ->setParameters(['planet' => $newHome, 'character' => $character, 'friend' => $friendAlly])
                        ->getQuery()
                        ->getResult();

                    $fleetFight = $em->getRepository('App:Fleet')
                        ->createQueryBuilder('f')
                        ->where('f.planet = :planet')
                        ->andWhere('f.character != :character')
                        ->andWhere('f.fightAt is not null')
                        ->andWhere('f.flightTime is null')
                        ->setParameters(['planet' => $newHome, 'character' => $character])
                        ->getQuery()
                        ->setMaxResults(1)
                        ->getOneOrNullResult();

                    if ($fleetFight) {
                        $fleet->setFightAt($fleetFight->getFightAt());
                    } elseif ($warFleets) {
                        foreach ($warFleets as $setWar) {
                            if ($setWar->getCharacter()->getAlly()) {
                                $fleetArm = $fleet->getMissile() + $fleet->getLaser() + $fleet->getPlasma();
                                if ($fleetArm > 0) {
                                    $fleet->setAttack(1);
                                }
                                foreach ($eAlly as $tmp) {
                                    if ($setWar->getCharacter()->getAlly()->getSigle() == $tmp->getAllyTag()) {
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
                            ->join('f.character', 'c')
                            ->where('f.planet = :planet')
                            ->andWhere('f.flightTime is null')
                            ->setParameters(['planet' => $newHome])
                            ->getQuery()
                            ->getResult();

                        $nowWar = new DateTime();
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

                        if ($character->getZombie() == 1) {
                            $zbRegroups = $em->getRepository('App:Fleet')
                                ->createQueryBuilder('f')
                                ->where('f.planet = :planet')
                                ->andWhere('f.flightTime is null')
                                ->andWhere('f.character = :character')
                                ->andWhere('f.id != :fleet')
                                ->setParameters(['planet' => $newHome, 'character' => $character, 'fleet' => $fleet->getId()])
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
                        if ($fleet->getFlightType() == '1' && $character->getZombie() == 0) {
                            $em->persist($report);
                        } elseif ($fleet->getFlightType() == '2') {
                            if ($newPlanet->getMerchant() == true) {
                                $reportSell = new Report();
                                $reportSell->setType('economic');
                                $reportSell->setSendAt($nowReport);
                                $reportSell->setCharacter($character);
                                $reportSell->setTitle("Vente aux marchands");
                                $reportSell->setImageName("sell_report.jpg");
                                if ($character->getPoliticPdg() > 0) {
                                    $newWarPointS = round((((($fleet->getScientist() * 100) + ($fleet->getWorker() * 50) + ($fleet->getSoldier() * 10) + ($fleet->getWater() / 3) + ($fleet->getNiobium() / 6) + ($fleet->getTank() * 5) + ($fleet->getUranium() * 10)) / 50000)) * (1 + ($character->getPoliticPdg() / 10)));
                                } else {
                                    $newWarPointS = round((($fleet->getScientist() * 100) + ($fleet->getWorker() * 50) + ($fleet->getSoldier() * 10) + ($fleet->getWater() / 3) + ($fleet->getNiobium() / 6) + ($fleet->getTank() * 5) + ($fleet->getUranium() * 10)) / 50000);
                                }
                                if ($character->getPoliticMerchant() > 0) {
                                    $gainSell = (($fleet->getWater() * 0.25) + ($fleet->getSoldier() * 80) + ($fleet->getWorker() * 5) + ($fleet->getScientist() * 300) + ($fleet->getNiobium() * 0.10) + ($fleet->getTank() * 2500) + ($fleet->getUranium() * 5000)) * (1 + ($character->getPoliticMerchant() / 20));
                                } else {
                                    $gainSell = ($fleet->getWater() * 0.25) + ($fleet->getSoldier() * 80) + ($fleet->getWorker() * 5) + ($fleet->getScientist() * 300) + ($fleet->getNiobium() * 0.10) + ($fleet->getTank() * 2500) + ($fleet->getUranium() * 5000);
                                }
                                $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre. Votre flotte " . $fleet->getName() . " est sur le chemin du retour.");
                                $em->persist($reportSell);
                                $character->setBitcoin($character->getBitcoin() + $gainSell);
                                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $newWarPointS);
                                $fleet->setNiobium(0);
                                $fleet->setWater(0);
                                $fleet->setUranium(0);
                                $fleet->setSoldier(0);
                                $fleet->setTank(0);
                                $fleet->setWorker(0);
                                $fleet->setScientist(0);
                                $quest = $character->checkQuests('sell');
                                if ($quest) {
                                    $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                                    $character->removeQuest($quest);
                                }
                            } else {
                                if ($newPlanet->getCharacter() && $character != $newPlanet->getCharacter()) {
                                    $reportSell = new Report();
                                    $reportSell->setType('move');
                                    $reportSell->setSendAt($nowReport);
                                    $reportSell->setCharacter($newPlanet->getCharacter());
                                    $reportSell->setTitle("Dépôt de ressources");
                                    $reportSell->setImageName("depot_report.jpg");
                                    $reportSell->setContent("Le joueur " . $newPlanet->getCharacter()->getUsername() . " vient de déposer des ressources sur votre planète " . $newPlanet->getSector()->getgalaxy()->getPosition() . ":" . $newPlanet->getSector()->getPosition() . ":" . $newPlanet->getPosition() . " " . number_format($fleet->getNiobium()) . " Niobium, " . number_format($fleet->getWater()) . " Eau, " . number_format($fleet->getWorker()) . " Travailleurs, " . number_format($fleet->getSoldier()) . " Soldats, " . number_format($fleet->getScientist()) . " Scientifiques.");
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
                            if ($carburant <= $character->getBitcoin()) {
                                if ($fleet->getMotherShip()) {
                                    $speed = $fleet->getSpeed() - ($fleet->getSpeed() * 0.10);
                                } else {
                                    $speed = $fleet->getSpeed();
                                }
                                $distance = $speed * $base * 1000 * $server->getSpeed();
                                $moreNow = new DateTime();
                                $moreNow->add(new DateInterval('PT' . 120 . 'S'));
                                $nowFlight = new DateTime();
                                $nowFlight->add(new DateInterval('PT' . round($distance) . 'S'));
                                $fleet->setFlightTime($nowFlight);
                                $fleet->setFlightType(1);
                                $fleet->getDestination()->setPlanet($oldPlanet);
                                $fleet->setCancelFlight($moreNow);
                                $character->setBitcoin($character->getBitcoin() - $carburant);
                            }
                        } elseif ($fleet->getFlightType() == '3') {
                            if ($fleet->getColonizer() && $newPlanet->getCharacter() == null &&
                                $newPlanet->getEmpty() == false && $newPlanet->getMerchant() == false &&
                                $newPlanet->getCdr() == false && $character->getColPlanets() < 26 &&
                                $character->getColPlanets() <= ($character->getTerraformation() + 1 + $character->getPoliticColonisation())) {

                                $fleet->setColonizer($fleet->getColonizer() - 1);
                                $newPlanet->setCharacter($character);
                                if ($character->getZombie() == 1) {
                                    $newPlanet->setName('Base Zombie');
                                    $newPlanet->setWorker(125000);
                                    $newPlanet->setSoldier(500);
                                    $newPlanet->setSoldierMax(500);
                                    $newPlanet->setCaserne(1);
                                } else {
                                    $newPlanet->setName('Colonie');
                                    $newPlanet->setSoldier(20);
                                }
                                $newPlanet->setNbColo(count($character->getPlanets()) + 1);
                                $quest = $character->checkQuests('colonize');
                                if ($quest) {
                                    $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                                    $character->removeQuest($quest);
                                }
                                if ($fleet->getNbrShips() == 0) {
                                    $em->remove($fleet);
                                }
                                $reportColo = new Report();
                                $reportColo->setSendAt($nowReport);
                                $reportColo->setCharacter($character);
                                $reportColo->setTitle("Colonisation de planète");
                                $reportColo->setImageName("colonize_report.jpg");
                                $reportColo->setContent("Vous venez de coloniser une planète inhabitée en : " . "<span><a href='/connect/carte-spatiale/" . $newPlanet->getSector()->getPosition() . "/" . $newPlanet->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $newPlanet->getSector()->getGalaxy()->getPosition() . ":" . $newPlanet->getSector()->getPosition() . ":" . $newPlanet->getPosition() . "</a></span>" . ". Cette planète fait désormais partie de votre Empire, pensez à la renommer sur la page Planètes.");
                                $character->setViewReport(false);
                                $em->persist($reportColo);
                            }
                        } elseif ($fleet->getFlightType() == '4') {
                            if ($character->getPoliticBarge() > 0) {
                                $barge = $fleet->getBarge() * 2500 * (1 + ($character->getPoliticBarge() / 4));
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
                                if ($character->getPoliticSoldierAtt() > 0) {
                                    $aMilitary = $aMilitary * (1 + ($character->getPoliticSoldierAtt() / 10));
                                }
                            } else {
                                $this->addFlash("fail", "Vous ne disposez pas de barges d'invasions.");
                                return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
                            }

                            $defender = $fleet->getPlanet();
                            $characterDefender = $fleet->getPlanet()->getUser();
                            $barbed = $characterDefender->getBarbedAdv();
                            $dSoldier = $defender->getSoldier() > 0 ? ($defender->getSoldier() * 6) * $barbed : 0;
                            $dTanks = $defender->getTank() > 0 ? $defender->getTank() * 3000 : 0;
                            $soldierDtmp = $defender->getSoldier();
                            $tankDtmp = $defender->getTank();

                            $seconds = $this->forward('App\Controller\Connected\Execute\ChronosController::planetActivityAction', [
                                'planet' => $defender,
                                'now'  => $now,
                                'em' => $em]);

                            if ($seconds->getContent() >= 60) {
                                $this->forward('App\Controller\Connected\Execute\PlanetsGenController::planetGenAction', [
                                    'planet' => $defender,
                                    'seconds' => $seconds->getContent(),
                                    'now'  => $now,
                                    'em' => $em]);
                            }

                            if ($characterDefender->getPoliticSoldierAtt() > 0) {
                                $dSoldier = $dSoldier * (1 + ($characterDefender->getPoliticSoldierAtt() / 10));
                            }
                            if ($characterDefender->getPoliticTankDef() > 0) {
                                $dTanks = $dTanks * (1 + ($characterDefender->getPoliticTankDef() / 10));
                            }
                            $dMilitary = $dSoldier + $dTanks;
                            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($character);
                            $usePlanetDef = $em->getRepository('App:Planet')->findByFirstPlanet($defender->getUser());

                            $reportLoot = new Report();
                            $reportLoot->setType('invade');
                            $reportLoot->setSendAt($now);
                            $reportLoot->setCharacter($character);
                            $character->setViewReport(false);
                            $reportDef = new Report();
                            $reportDef->setType('invade');
                            $reportDef->setSendAt($now);
                            $reportDef->setCharacter($characterDefender);
                            $characterDefender->setViewReport(false);
                            $dSigle = null;
                            if ($characterDefender->getAlly()) {
                                $dSigle = $characterDefender->getAlly()->getSigle();
                            }

                            if ($fleet->getPlanet()->getUser() && $fleet->getAllianceCharacter() && $character->getSigleAllied($dSigle) == null && $characterDefender->getZombie() == 0) {
                                if($dMilitary >= $aMilitary) {
                                    $warPointDef = round($aMilitary);
                                    if ($characterDefender->getPoliticPdg() > 0) {
                                        $warPointDef = round(($warPointDef * (1 + ($characterDefender->getPoliticPdg() / 10))) / 500);
                                    }
                                    $characterDefender->getRank()->setWarPoint($characterDefender->getRank()->getWarPoint() + $warPointDef);
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
                                        $diviser = (1 + ($characterDefender->getPoliticTankDef() / 10)) * 3000;
                                        $defender->setTank(round(abs($aMilitary / $diviser)));
                                        $tankDtmp = $tankDtmp - $defender->getTank();
                                    } else {
                                        $diviser = (1 + ($characterDefender->getPoliticSoldierAtt() / 10)) * (5 * $characterDefender->getBarbedAdv()) * 6;
                                        $dMilitary = $dMilitary - $aMilitary - $dTanks;
                                        $defender->setSoldier(round(abs($dMilitary / $diviser)));
                                        $soldierDtmp = round(abs($dMilitary / $diviser));
                                    }

                                    $reportDef->setTitle("Rapport de pillage : Victoire (défense)");
                                    $reportDef->setImageName("defend_win_report.jpg");
                                    $reportDef->setContent("Le dirigeant " . $character->getUsername() . " a tenté de piller votre planète " . $defender->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanetDef->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>). Il a échoué grâce a vos solides défenses. Vous avez éliminé <span class='text-vert'>" . number_format($soldierAtmp) . "</span> soldats et prit le contrôle des barges de l'attaquant.<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointDef) . "</span> points de Guerre.");
                                    $reportLoot->setTitle("Rapport de pillage : Défaite (attaque)");
                                    $reportLoot->setImageName("invade_lose_report.jpg");
                                    $reportLoot->setContent("Le dirigeant " . $characterDefender->getUsername() . " vous attendait de pieds fermes. Sa planète " . $defender->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>) était trop renforcée pour vous. Vous tué tout de même <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks. Tous vos soldats sont morts et vos barges sont restées sur la planète.<br>La prochaine fois, préparez votre attaque commandant.");
                                } else {
                                    $warPointAtt = round($soldierDtmp?$soldierDtmp:1 + $tankDtmp);
                                    if ($character->getPoliticPdg() > 0) {
                                        $warPointAtt = round(($warPointAtt * (1 + ($character->getPoliticPdg() / 10))) / 60);
                                    }
                                    $diviser = (1 + ($character->getPoliticSoldierAtt() / 10)) * 6;
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
                                    $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $warPointAtt);
                                    $reportDef->setTitle("Rapport de pillage : Défaite (défense)");
                                    $reportDef->setImageName("defend_lose_report.jpg");
                                    $reportDef->setContent("Le dirigeant " . $character->getUsername() . " vient de piller (" . number_format(round($niobium)) . " niobiums" . number_format(round($water)) . " eaux" . number_format(round($uranium)) . " uraniums) votre planète " . $defender->getName() . " - (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanetDef->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>).  " . number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminé. C'est toujours ça de gagner. Vos <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) ."</span> tanks. Votre économie en a prit un coup, mais si vous étiez là pour planter des choux ça se serait ! Préparez la contre-attaque !");
                                    $reportLoot->setTitle("Rapport de pillage : Victoire (attaque)");
                                    $reportLoot->setImageName("invade_win_report.jpg");
                                    $reportLoot->setContent("Vos soldats ont fini de charger vos cargos ( <span class='text-vert'>" . number_format(round($niobium)) . " niobiums - " . number_format(round($water)) . " eaux - " . number_format(round($uranium)) . " uraniums </span>) et remontent dans les barges, le pillage de la planète " . $defender->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>) s'est bien passé. Vos pertes sont de <span class='text-rouge'>" . number_format(round($soldierAtmp)) . "</span> soldats. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks.<br>Vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre.");
                                    $quest = $character->checkQuests('loot');
                                    if($quest) {
                                        $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                                        $character->removeQuest($quest);
                                    }
                                }
                                $em->persist($reportLoot);
                                $em->persist($reportDef);
                            }
                        } elseif ($fleet->getFlightType() == '5' && $fleet->getPlanet()->getUser()) {
                            $alea = rand(4, 8);
                            if ($character->getPoliticBarge() > 0) {
                                $barge = $fleet->getBarge() * 2500 * (1 + ($character->getPoliticBarge() / 4));
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
                                if ($character->getPoliticSoldierAtt() > 0) {
                                    $aMilitary = $aMilitary * (1 + ($character->getPoliticSoldierAtt() / 10));
                                }
                            } else {
                                $this->addFlash("fail", "Vous ne disposez pas de barges d'invasions.");
                                return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
                            }

                            $defender = $fleet->getPlanet();
                            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($character);
                            $usePlanetDef = $em->getRepository('App:Planet')->findByFirstPlanet($defender->getUser());
                            $characterDefender= $fleet->getPlanet()->getUser();
                            $barbed = $characterDefender->getBarbedAdv();
                            $dSoldier = $defender->getSoldier() > 0 ? ($defender->getSoldier() * 6) * $barbed : 0;
                            $dTanks = $defender->getTank() > 0 ? $defender->getTank() * 3000 : 0;
                            $dWorker = $defender->getWorker();
                            $soldierDtmp = $defender->getSoldier();
                            $workerDtmp = $defender->getWorker();
                            $tankDtmp = $defender->getTank();

                            $seconds = $this->forward('App\Controller\Connected\Execute\ChronosController::planetActivityAction', [
                                'planet' => $defender,
                                'now'  => $now,
                                'em' => $em]);

                            if ($seconds->getContent() >= 60) {
                                $this->forward('App\Controller\Connected\Execute\PlanetsGenController::planetGenAction', [
                                    'planet' => $defender,
                                    'seconds' => $seconds->getContent(),
                                    'now'  => $now,
                                    'em' => $em]);
                            }

                            if ($characterDefender->getPoliticSoldierAtt() > 0) {
                                $dSoldier = $dSoldier * (1 + ($characterDefender->getPoliticSoldierAtt() / 10));
                            }
                            if ($characterDefender->getPoliticTankDef() > 0) {
                                $dTanks = $dTanks * (1 + ($characterDefender->getPoliticTankDef() / 10));
                            }
                            if ($characterDefender->getPoliticWorkerDef() > 0) {
                                $dWorker = $dWorker * (1 + ($characterDefender->getPoliticWorkerDef() / 5));
                            }
                            if ($characterDefender->getZombie() == 1) {
                                $dTanks = 0;
                            }
                            $dMilitary = $dWorker + $dSoldier + $dTanks;

                            $reportInv = new Report();
                            if ($characterDefender->getZombie() == 0) {
                                $reportInv->setType('invade');
                            } else {
                                $reportInv->setType('zombie');
                            }
                            $reportInv->setSendAt($now);
                            $reportInv->setCharacter($character);
                            $character->setViewReport(false);

                            if ($characterDefender->getZombie() == 0) {
                                $reportDef = new Report();
                                $reportDef->setType('invade');
                                $reportDef->setSendAt($now);
                                $reportDef->setCharacter($characterDefender);
                            }
                            $characterDefender->setViewReport(false);
                            $dSigle = null;
                            if($characterDefender->getAlly()) {
                                $dSigle = $characterDefender->getAlly()->getSigle();
                            }

                            if($fleet->getPlanet()->getUser() && $fleet->getAllianceCharacter() && $fleet->getFightAt() == null && $fleet->getFlightTime() == null && $character->getSigleAllied($dSigle) == null) {
                                if($dMilitary >= $aMilitary) {
                                    if ($characterDefender->getZombie() == 0) {
                                        $warPointDef = round($aMilitary);
                                        if ($character->getPoliticPdg() > 0) {
                                            $warPointDef = round(($warPointDef * (1 + ($character->getPoliticPdg() / 10))) / 500);
                                        }
                                        $characterDefender->getRank()->setWarPoint($characterDefender->getRank()->getWarPoint() + $warPointDef);
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
                                            $diviser = (1 + ($characterDefender->getPoliticWorkerDef() / 5));
                                            $defender->setWorker(round(abs($aMilitary / $diviser)));
                                            $tankDtmp = $tankDtmp - $defender->getTank();
                                            $soldierDtmp = $soldierDtmp - $defender->getSoldier();
                                            $workerDtmp = $workerDtmp - $defender->getWorker();
                                        } else {
                                            $diviser = (1 + ($characterDefender->getPoliticTankDef() / 10)) * 3000;
                                            $defender->setTank(round(abs($aMilitary / $diviser)));
                                            $tankDtmp = $tankDtmp - $defender->getTank();
                                            $soldierDtmp = $soldierDtmp - $defender->getSoldier();
                                        }
                                    } else {
                                        $dMilitary = $dMilitary - $aMilitary - $dTanks -$dWorker;
                                        $diviser = (1 + ($characterDefender->getPoliticSoldierAtt() / 10)) * ($alea * $characterDefender->getBarbedAdv()) * 6;
                                        $defender->setSoldier(round(abs($dMilitary / $diviser)));
                                        $soldierDtmp = round(abs($dMilitary / $diviser));
                                    }
                                    if ($characterDefender->getZombie() == 1) {
                                        $reportInv->setTitle("Rapport contre attaque : Défaite");
                                        $reportInv->setImageName("zombie_lose_report.jpg");
                                        $reportInv->setContent("Vous pensiez partir pour une promenade de santé mais la réalité vous rattrape vite... Vous avez envoyé tout vos soldats au casse-pipe.<br>Pire, vous avez attirer l'attention des zombies et fait monter la menace de 10 points ! Vous avez interêt a prendre vite" .
                                            $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanet])->getContent() .
                                            "sinon votre Empire ne tiendra pas longtemps. Vous avez tué <span class='text-vert'>" . number_format(round($soldierDtmp + ($workerDtmp / 6) + ($tankDtmp * 3000))) .
                                            "</span> zombies. Tous vos soldats sont morts et vos barges se sont égarées sur la planète.<br>N'abandonnez pas et sortez vos tripes !");

                                        $character->ZombieAtt($character->getZombieAtt() + 10);
                                    } else {
                                        $reportDef->setTitle("Rapport d'invasion : Victoire (défense)");
                                        $reportDef->setImageName("defend_win_report.jpg");
                                        $reportDef->setContent("Bien joué ! Vos travailleurs et soldats ont repoussé l'invasion du joueur" .
                                            $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $character, 'usePlanet' => $usePlanetDef])->getContent() .
                                            "sur votre planète" .
                                            $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanetDef])->getContent() .
                                            ".  <span class='text-vert'>" . number_format($soldierAtmp) .
                                            "</span> soldats vous ont attaqué, tous ont été tués. Vous avez ainsi pris le contrôle des barges de l'attaquant.<br>Et vous remportez <span class='text-vert'>+" .
                                            number_format($warPointDef) . "</span> points de Guerre.");

                                        $reportInv->setTitle("Rapport d'invasion : Défaite (attaque)");
                                        $reportInv->setImageName("invade_lose_report.jpg");
                                        $reportInv->setContent("'AH AH AH AH' le rire de" . $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $characterDefender, 'usePlanet' => $usePlanet])->getContent() .
                                            "résonne à vos oreilles d'un curieuse façon. Votre sang bouillonne vous l'a vouliez cette planète. Qu'il rigole donc, vous reviendrez prendre " .
                                            $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanet])->getContent() .
                                            "et ferez effacer des livres d'histoires son ridicule nom. Vous avez tout de même tué <span class='text-vert'>" .
                                            number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" .
                                            number_format($workerDtmp) . "</span> travailleurs à l'ennemi. Tous vos soldats sont morts et vos barges sont restées sur la planète.<br>Courage commandant.");
                                    }
                                } else {
                                    $warPointAtt = round(($soldierDtmp?$soldierDtmp:1 + ($workerDtmp / 10)) * 1);
                                    if ($character->getPoliticPdg() > 0) {
                                        $warPointAtt = round($warPointAtt * (1 + ($character->getPoliticPdg() / 10)));
                                    }
                                    $warPointAtt = round($warPointAtt / 600);
                                    $diviser = (1 + ($character->getPoliticSoldierAtt() / 10)) * $alea;
                                    $aMilitary = $aMilitary - $dMilitary;
                                    $fleet->setSoldier(abs($soldierAtmpTotal + round($aMilitary / $diviser)));
                                    $soldierAtmp = $fleet->getSoldier() - round($soldierAtmpTotal + $soldierAtmp);
                                    $defender->setSoldier(0);
                                    $defender->setTank(0);
                                    $defender->setWorker(2000);

                                    if($character->getColPlanets() <= ($character->getTerraformation() + 1 + $character->getPoliticInvade()) && $characterDefender->getZombie() == 0) {
                                        $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $warPointAtt);
                                        $defender->setCharacter($character);
                                        $em->flush();
                                        if ($character->getNbrInvade()) {
                                            $character->NbrInvade($character->getNbrInvade() + 1);
                                        } else {
                                            $character->NbrInvade(1);
                                        }
                                        $reportDef->setTitle("Rapport d'invasion : Défaite (défense)");
                                        $reportDef->setImageName("defend_lose_report.jpg");
                                        $reportDef->setContent("Mais QUI ? QUI !!! Vous as donné un commandant si médiocre" .
                                            $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $character, 'usePlanet' => $usePlanetDef])->getContent() .
                                            "n'a pas eu à faire grand chose pour prendre votre planète" .
                                            $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanetDef])->getContent() .
                                            number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminés. C'est toujours ça de gagné. Vos <span class='text-rouge'>" . number_format($soldierDtmp) .
                                            "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-rouge'>" . number_format($workerDtmp) .
                                            "</span> travailleurs sont tous mort. Votre empire en a pris un coup, mais il vous reste des planètes, il est l'heure de la revanche !");

                                        $reportInv->setTitle("Rapport d'invasion : Victoire (attaque)");
                                        $reportInv->setImageName("invade_win_report.jpg");
                                        $reportInv->setContent("Vous débarquez après que la planète ait été prise et vous installez sur le trône de" .
                                            $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $characterDefender, 'usePlanet' => $usePlanet])->getContent() .
                                            ". Qu'il est bon d'entendre ses pleurs lointains... La planète" .
                                            $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanet])->getContent() .
                                            "est désormais votre! Il est temps de remettre de l'ordre dans la galaxie. <span class='text-rouge'>" . number_format(round($soldierAtmp)) .
                                            "</span> de vos soldats ont péri dans l'invasion. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) .
                                            "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) .
                                            "</span> travailleurs ont péri. Cependant vous épargnez 2000 travailleurs dans votre bonté (surtout pour faire tourner la planète).<br>Et vous remportez <span class='text-vert'>+" .
                                            number_format($warPointAtt) . "</span> points de Guerre.");

                                    } else {
                                        $warPointAtt = $warPointAtt / 50;
                                        $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $warPointAtt);
                                        $hydra = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);
                                        if ($characterDefender->getZombie() == 0) {
                                            if ($character->getNbrInvade()) {
                                                $character->setNbrInvade($character->getNbrInvade() + 1);
                                            } else {
                                                $character->setNbrInvade(1);
                                            }
                                            $reportDef->setTitle("Rapport d'invasion : Défaite (défense)");
                                            $reportDef->setImageName("defend_lose_report.jpg");
                                            $reportDef->setContent("Mais QUI ? QUI !!! Vous as donné un commandant si médiocre" .
                                                $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $character, 'usePlanet' => $usePlanetDef])->getContent() .
                                                "n'a pas eu à faire grand chose pour prendre votre planète" .
                                                $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanetDef])->getContent() .
                                                number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminés. C'est toujours ça de gagné. Vos <span class='text-rouge'>" .
                                                number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-rouge'>" .
                                                number_format($workerDtmp) . "</span> travailleurs sont tous mort. Votre empire en a pris un coup, mais il vous reste des planètes, il est l'heure de la revanche !");

                                            $reportInv->setTitle("Rapport d'invasion : Victoire (attaque)");
                                            $reportInv->setImageName("invade_win_report.jpg");
                                            $reportInv->setContent("Vous débarquez après que la planète ait été prise et vous installez sur le trône de" .
                                                $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $characterDefender, 'usePlanet' => $usePlanet])->getContent() .
                                                ". Qu'il est bon d'entendre ses pleurs lointains... La planète" .
                                                $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanet])->getContent() .
                                                "est désormais votre! Il est temps de remettre de l'ordre dans la galaxie. <span class='text-rouge'>" . number_format(round($soldierAtmp)) .
                                                "</span> de vos soldats ont péri dans l'invasion. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) .
                                                "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) .
                                                "</span> travailleurs ont péri. Cependant vous épargnez 2000 travailleurs dans votre bonté (surtout pour faire tourner la planète).<br>Et vous remportez <span class='text-vert'>+" .
                                                number_format($warPointAtt) . "</span> points de Guerre.");

                                        } else {
                                            $reportInv->setTitle("Rapport contre attaque : Victoire");
                                            $reportInv->setImageName("zombie_win_report.jpg");
                                            $reportInv->setContent("Vos soldats débarquent sur la planète zombie et sortent l'artillerie lourde ! Les rues s'emplissent de morts mais l'entraînement prévaut sur la peur et vous purgez cette planète de cette peste macabre.<br> La planète" .
                                                $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanet])->getContent() .
                                                "est désormais libre. Et votre indice d'attaque zombie est divisé par 10. Lors de l'assaut vous dénombrez <span class='text-rouge'>" .
                                                number_format(round($soldierAtmp)) . "</span> pertes parmis vos soldats. Mais vous avez exterminé <span class='text-vert'>" .
                                                number_format(round($soldierDtmp + ($workerDtmp / 6) + ($tankDtmp * 3000))) . "</span> zombies ! <br>Et vous remportez <span class='text-vert'>+" .
                                                number_format($warPointAtt) . "</span> points de Guerre ainsi que <span class='text-vert'>+10</span> uraniums.");
                                        }
                                        if ($characterDefender->getZombie() == 1) {
                                            $image = [
                                                'planet1.png', 'planet2.png', 'planet3.png', 'planet4.png', 'planet5.png', 'planet6.png',
                                                'planet7.png', 'planet8.png', 'planet9.png', 'planet10.png', 'planet11.png', 'planet12.png',
                                                'planet13.png', 'planet14.png', 'planet15.png', 'planet16.png', 'planet17.png', 'planet18.png',
                                                'planet19.png', 'planet20.png', 'planet21.png', 'planet22.png', 'planet23.png', 'planet24.png',
                                                'planet25.png', 'planet26.png', 'planet27.png', 'planet28.png', 'planet29.png', 'planet30.png',
                                                'planet31.png', 'planet32.png', 'planet33.png'
                                            ];
                                            $defender->setCharacter(null);
                                            $em->flush();
                                            if ($character->getZombieAtt() > 9) {
                                                $character->setZombieAtt(round($character->getZombieAtt() / 10));
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
                                            $defender->setCharacter($hydra);
                                            $defender->setWorker(125000);
                                            if ($defender->getSoldierMax() >= 2500) {
                                                $defender->setSoldier($defender->getSoldierMax());
                                            } else {
                                                $defender->setCaserne(1);
                                                $defender->setSoldier(500);
                                                $defender->setSoldierMax(500);
                                            }
                                            $defender->setName('Base Zombie');
                                            $defender->setImageName('hydra_planet.png');
                                            $em->flush();
                                        }
                                    }
                                    if($characterDefender->getAllPlanets() == 0) {
                                        $characterDefender->setGameOver($character->getUsername());
                                        $characterDefender->setGrade(null);
                                        if ($character->getExecution()) {
                                            $character->setExecution($character->getExecution() . ', ' . $characterDefender->getUsername());
                                        } else {
                                            $character->setExecution($characterDefender->getUsername());
                                        }
                                        $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $characterDefender->getRank()->getWarPoint());
                                        $character->setBitcoin($character->getBitcoin() + $characterDefender->getBitcoin());
                                        $reportInv->setContent($reportInv->getContent() . "<br>Vous avez totalement anéanti l'Empire de" .
                                            $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $characterDefender, 'usePlanet' => $usePlanet])->getContent() .
                                            "et gagnez ses PDG : <span class='text-vert'>+" . number_format($characterDefender->getRank()->getWarPoint()) .
                                            "</span>, ainsi que ses Bitcoins : <span class='text-vert'>+" . number_format($characterDefender->getBitcoin()) . " .</span>");

                                        $characterDefender->getRank()->setWarPoint(1);
                                        $characterDefender->setBitcoin(1);
                                        foreach($characterDefender->getFleets() as $tmpFleet) {
                                            $tmpFleet->setCharacter($character);
                                            $tmpFleet->setFleetList(null);
                                        }
                                    }
                                    $quest = $character->checkQuests('invade');
                                    if($quest) {
                                        $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                                        $character->removeQuest($quest);
                                    }
                                }
                                if($fleet->getNbrShips() == 0) {
                                    $em->remove($fleet);
                                }
                                $em->persist($reportInv);
                                if ($characterDefender->getZombie() == 0) {
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
                            $fleet->setCharacter(null);
                            $em->remove($fleet);
                            $planetGround->setSignature($planetGround->getNbrSignatures());
                        }
                    } else {
                        if ($character->getZombie() == 0) {
                            $em->persist($report);
                        }
                    }
                }
            }
        }

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    /**
     * @param $fleet
     * @param $now
     * @param $em
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function centralizeOneFleetAction($fleet, $server, $now, $em)
    {
        $nowReport = new DateTime();
        $character = $fleet->getCharacter();

        if (!$character || $character->getMerchant() == 1) {
            $em->remove($fleet->getDestination());
            $em->remove($fleet);
        } else {
            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($character);
            if (!$usePlanet) {
                $em->remove($fleet);
            } else {
                $newHome = $fleet->getDestination()->getPlanet();

                $report = new Report();
                $report->setType('move');
                $report->setTitle("Votre flotte " . $fleet->getName() . " est arrivée");
                $report->setImageName("travel_report.jpg");
                $report->setSendAt($now);
                $report->setCharacter($character);
                $report->setContent("Bonjour dirigeant " . $character->getUsername() . " votre flotte " . "<span><a href='/connect/gerer-flotte/" . $fleet->getId() . "/" . $usePlanet->getId() . "'>" . $fleet->getName() . "</a></span>" . " vient d'arriver en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() . "/" . $newHome->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $newHome->getSector()->getGalaxy()->getPosition() . ":" . $newHome->getSector()->getPosition() . ":" . $newHome->getPosition() . "</a></span>.");
                $character->setViewReport(false);
                $oldPlanet = $fleet->getPlanet();
                $fleet->setFlightTime(null);
                $fleet->setPlanet($newHome);
                $previousDestination = $fleet->getDestination();
                if ($fleet->getFlightType() != '2') {
                    $em->remove($previousDestination);
                    if ($newHome->getNbCdr() || $newHome->getWtCdr()) {
                        $fleet->setRecycleAt($nowReport);
                    } else {
                        $fleet->setRecycleAt(null);
                    }
                }

                $eAlly = $character->getAllyEnnemy();
                $warAlly = [];
                $x = 0;
                foreach ($eAlly as $tmp) {
                    $warAlly[$x] = $tmp->getAllyTag();
                    $x++;
                }

                $fAlly = $character->getAllyFriends();
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

                if ($character->getAlly()) {
                    $allyF = $character->getAlly();
                } else {
                    $allyF = 'wedontexistsok';
                }

                $warFleets = $em->getRepository('App:Fleet')
                    ->createQueryBuilder('f')
                    ->join('f.character', 'c')
                    ->leftJoin('c.ally', 'a')
                    ->where('f.planet = :planet')
                    ->andWhere('f.attack = true OR a.sigle in (:ally)')
                    ->andWhere('f.character != :character')
                    ->andWhere('f.flightTime is null')
                    ->andWhere('c.ally is null OR a.sigle not in (:friend)')
                    ->andWhere('c.ally is null OR c.ally != :myAlly')
                    ->setParameters(['planet' => $newHome, 'ally' => $warAlly, 'character' => $character, 'friend' => $friendAlly, 'myAlly' => $allyF])
                    ->getQuery()
                    ->getResult();

                $neutralFleets = $em->getRepository('App:Fleet')
                    ->createQueryBuilder('f')
                    ->join('f.character', 'c')
                    ->leftJoin('c.ally', 'a')
                    ->where('f.planet = :planet')
                    ->andWhere('f.character != :character')
                    ->andWhere('f.flightTime is null')
                    ->andWhere('c.ally is null OR a.sigle not in (:friend)')
                    ->setParameters(['planet' => $newHome, 'character' => $character, 'friend' => $friendAlly])
                    ->getQuery()
                    ->getResult();

                $fleetFight = $em->getRepository('App:Fleet')
                    ->createQueryBuilder('f')
                    ->where('f.planet = :planet')
                    ->andWhere('f.character != :character')
                    ->andWhere('f.fightAt is not null')
                    ->andWhere('f.flightTime is null')
                    ->setParameters(['planet' => $newHome, 'character' => $character])
                    ->getQuery()
                    ->setMaxResults(1)
                    ->getOneOrNullResult();

                if ($fleetFight) {
                    $fleet->setFightAt($fleetFight->getFightAt());
                } elseif ($warFleets) {
                    foreach ($warFleets as $setWar) {
                        if ($setWar->getCharacter()->getAlly()) {
                            $fleetArm = $fleet->getMissile() + $fleet->getLaser() + $fleet->getPlasma();
                            if ($fleetArm > 0) {
                                $fleet->setAttack(1);
                            }
                            foreach ($eAlly as $tmp) {
                                if ($setWar->getCharacter()->getAlly()->getSigle() == $tmp->getAllyTag()) {
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
                        ->join('f.character', 'c')
                        ->where('f.planet = :planet')
                        ->andWhere('f.flightTime is null')
                        ->setParameters(['planet' => $newHome])
                        ->getQuery()
                        ->getResult();

                    $nowWar = new DateTime();
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

                    if ($character->getZombie() == 1) {
                        $zbRegroups = $em->getRepository('App:Fleet')
                            ->createQueryBuilder('f')
                            ->where('f.planet = :planet')
                            ->andWhere('f.flightTime is null')
                            ->andWhere('f.character = :character')
                            ->andWhere('f.id != :fleet')
                            ->setParameters(['planet' => $newHome, 'character' => $character, 'fleet' => $fleet->getId()])
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
                    if ($fleet->getFlightType() == '1' && $character->getZombie() == 0) {
                        $em->persist($report);
                    }
                    if ($fleet->getFlightType() == '2') {
                        if ($newPlanet->getMerchant() == true) {
                            $reportSell = new Report();
                            $reportSell->setType('economic');
                            $reportSell->setSendAt($nowReport);
                            $reportSell->setCharacter($character);
                            $reportSell->setTitle("Vente aux marchands");
                            $reportSell->setImageName("sell_report.jpg");
                            if ($character->getPoliticPdg() > 0) {
                                $newWarPointS = round((((($fleet->getScientist() * 100) + ($fleet->getWorker() * 50) + ($fleet->getSoldier() * 10) + ($fleet->getWater() / 3) + ($fleet->getNiobium() / 6) + ($fleet->getTank() * 5) + ($fleet->getUranium() * 10)) / 50000)) * (1 + ($character->getPoliticPdg() / 10)));
                            } else {
                                $newWarPointS = round((($fleet->getScientist() * 100) + ($fleet->getWorker() * 50) + ($fleet->getSoldier() * 10) + ($fleet->getWater() / 3) + ($fleet->getNiobium() / 6) + ($fleet->getTank() * 5) + ($fleet->getUranium() * 10)) / 50000);
                            }
                            if ($character->getPoliticMerchant() > 0) {
                                $gainSell = (($fleet->getWater() * 0.25) + ($fleet->getSoldier() * 80) + ($fleet->getWorker() * 5) + ($fleet->getScientist() * 300) + ($fleet->getNiobium() * 0.10) + ($fleet->getTank() * 2500) + ($fleet->getUranium() * 5000)) * (1 + ($character->getPoliticMerchant() / 20));
                            } else {
                                $gainSell = ($fleet->getWater() * 0.25) + ($fleet->getSoldier() * 80) + ($fleet->getWorker() * 5) + ($fleet->getScientist() * 300) + ($fleet->getNiobium() * 0.10) + ($fleet->getTank() * 2500) + ($fleet->getUranium() * 5000);
                            }
                            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre. Votre flotte " . $fleet->getName() . " est sur le chemin du retour.");
                            $em->persist($reportSell);
                            $character->setBitcoin($character->getBitcoin() + $gainSell);
                            $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $newWarPointS);
                            $fleet->setNiobium(0);
                            $fleet->setWater(0);
                            $fleet->setUranium(0);
                            $fleet->setSoldier(0);
                            $fleet->setTank(0);
                            $fleet->setWorker(0);
                            $fleet->setScientist(0);
                            $quest = $character->checkQuests('sell');
                            if ($quest) {
                                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                                $character->removeQuest($quest);
                            }
                        } else {
                            if ($newPlanet->getCharacter() && $character != $newPlanet->getCharacter()) {
                                $reportSell = new Report();
                                $reportSell->setType('move');
                                $reportSell->setSendAt($nowReport);
                                $reportSell->setCharacter($newPlanet->getCharacter());
                                $reportSell->setTitle("Dépôt de ressources");
                                $reportSell->setImageName("depot_report.jpg");
                                $reportSell->setContent("Le joueur " . $newPlanet->getCharacter()->getUsername() . " vient de déposer des ressources sur votre planète " . $newPlanet->getSector()->getgalaxy()->getPosition() . ":" . $newPlanet->getSector()->getPosition() . ":" . $newPlanet->getPosition() . " " . number_format($fleet->getNiobium()) . " Niobium, " . number_format($fleet->getWater()) . " Eau, " . number_format($fleet->getWorker()) . " Travailleurs, " . number_format($fleet->getSoldier()) . " Soldats, " . number_format($fleet->getScientist()) . " Scientifiques.");
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
                        if ($carburant <= $character->getBitcoin()) {
                            if ($fleet->getMotherShip()) {
                                $speed = $fleet->getSpeed() - ($fleet->getSpeed() * 0.10);
                            } else {
                                $speed = $fleet->getSpeed();
                            }
                            $distance = $speed * $base * 1000 * $server->getSpeed();
                            $moreNow = new DateTime();
                            $moreNow->add(new DateInterval('PT' . 120 . 'S'));
                            $nowFlight = new DateTime();
                            $nowFlight->add(new DateInterval('PT' . round($distance) . 'S'));
                            $fleet->setFlightTime($nowFlight);
                            $fleet->setFlightType(1);
                            $fleet->getDestination()->setPlanet($oldPlanet);
                            $fleet->setCancelFlight($moreNow);
                            $character->setBitcoin($character->getBitcoin() - $carburant);
                        }
                    } elseif ($fleet->getFlightType() == '3') {
                        if ($fleet->getColonizer() && $newPlanet->getCharacter() == null &&
                            $newPlanet->getEmpty() == false && $newPlanet->getMerchant() == false &&
                            $newPlanet->getCdr() == false && $character->getColPlanets() < 26 &&
                            $character->getColPlanets() <= ($character->getTerraformation() + 1 + $character->getPoliticColonisation())) {

                            $fleet->setColonizer($fleet->getColonizer() - 1);
                            $newPlanet->setCharacter($character);
                            if ($character->getZombie() == 1) {
                                $newPlanet->setName('Base Zombie');
                                $newPlanet->setWorker(125000);
                                $newPlanet->setSoldier(500);
                                $newPlanet->setSoldierMax(500);
                                $newPlanet->setCaserne(1);
                            } else {
                                $newPlanet->setName('Colonie');
                                $newPlanet->setSoldier(20);
                            }
                            $newPlanet->setNbColo(count($character->getPlanets()) + 1);
                            $quest = $character->checkQuests('colonize');
                            if ($quest) {
                                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                                $character->removeQuest($quest);
                            }
                            if ($fleet->getNbrShips() == 0) {
                                $em->remove($fleet);
                            }
                            $reportColo = new Report();
                            $reportColo->setSendAt($nowReport);
                            $reportColo->setCharacter($character);
                            $reportColo->setTitle("Colonisation de planète");
                            $reportColo->setImageName("colonize_report.jpg");
                            $reportColo->setContent("Vous venez de coloniser une planète inhabitée en : " . "<span><a href='/connect/carte-spatiale/" . $newPlanet->getSector()->getPosition() . "/" . $newPlanet->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $newPlanet->getSector()->getGalaxy()->getPosition() . ":" . $newPlanet->getSector()->getPosition() . ":" . $newPlanet->getPosition() . "</a></span>" . ". Cette planète fait désormais partie de votre Empire, pensez à la renommer sur la page Planètes.");
                            $character->setViewReport(false);
                            $em->persist($reportColo);
                        }
                    } elseif ($fleet->getFlightType() == '4') {
                        if ($character->getPoliticBarge() > 0) {
                            $barge = $fleet->getBarge() * 2500 * (1 + ($character->getPoliticBarge() / 4));
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
                            if ($character->getPoliticSoldierAtt() > 0) {
                                $aMilitary = $aMilitary * (1 + ($character->getPoliticSoldierAtt() / 10));
                            }
                        } else {
                            $this->addFlash("fail", "Vous ne disposez pas de barges d'invasions.");
                            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
                        }

                        $defender = $fleet->getPlanet();
                        $characterDefender = $fleet->getPlanet()->getUser();
                        $barbed = $characterDefender->getBarbedAdv();
                        $dSoldier = $defender->getSoldier() > 0 ? ($defender->getSoldier() * 6) * $barbed : 0;
                        $dTanks = $defender->getTank() > 0 ? $defender->getTank() * 3000 : 0;
                        $soldierDtmp = $defender->getSoldier();
                        $tankDtmp = $defender->getTank();

                        $seconds = $this->forward('App\Controller\Connected\Execute\ChronosController::planetActivityAction', [
                            'planet' => $defender,
                            'now'  => $now,
                            'em' => $em]);

                        if ($seconds->getContent() >= 60) {
                            $this->forward('App\Controller\Connected\Execute\PlanetsGenController::planetGenAction', [
                                'planet' => $defender,
                                'seconds' => $seconds->getContent(),
                                'now'  => $now,
                                'em' => $em]);
                        }

                        if ($characterDefender->getPoliticSoldierAtt() > 0) {
                            $dSoldier = $dSoldier * (1 + ($characterDefender->getPoliticSoldierAtt() / 10));
                        }
                        if ($characterDefender->getPoliticTankDef() > 0) {
                            $dTanks = $dTanks * (1 + ($characterDefender->getPoliticTankDef() / 10));
                        }
                        $dMilitary = $dSoldier + $dTanks;
                        $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($character);
                        $usePlanetDef = $em->getRepository('App:Planet')->findByFirstPlanet($defender->getUser());

                        $reportLoot = new Report();
                        $reportLoot->setType('invade');
                        $reportLoot->setSendAt($now);
                        $reportLoot->setCharacter($character);
                        $character->setViewReport(false);
                        $reportDef = new Report();
                        $reportDef->setType('invade');
                        $reportDef->setSendAt($now);
                        $reportDef->setCharacter($characterDefender);
                        $characterDefender->setViewReport(false);
                        $dSigle = null;
                        if ($characterDefender->getAlly()) {
                            $dSigle = $characterDefender->getAlly()->getSigle();
                        }

                        if ($fleet->getPlanet()->getUser() && $fleet->getAllianceCharacter() && $character->getSigleAllied($dSigle) == null && $characterDefender->getZombie() == 0) {
                            if($dMilitary >= $aMilitary) {
                                $warPointDef = round($aMilitary);
                                if ($characterDefender->getPoliticPdg() > 0) {
                                    $warPointDef = round(($warPointDef * (1 + ($characterDefender->getPoliticPdg() / 10))) / 500);
                                }
                                $characterDefender->getRank()->setWarPoint($characterDefender->getRank()->getWarPoint() + $warPointDef);
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
                                    $diviser = (1 + ($characterDefender->getPoliticTankDef() / 10)) * 3000;
                                    $defender->setTank(round(abs($aMilitary / $diviser)));
                                    $tankDtmp = $tankDtmp - $defender->getTank();
                                } else {
                                    $diviser = (1 + ($characterDefender->getPoliticSoldierAtt() / 10)) * (5 * $characterDefender->getBarbedAdv()) * 6;
                                    $dMilitary = $dMilitary - $aMilitary - $dTanks;
                                    $defender->setSoldier(round(abs($dMilitary / $diviser)));
                                    $soldierDtmp = round(abs($dMilitary / $diviser));
                                }

                                $reportDef->setTitle("Rapport de pillage : Victoire (défense)");
                                $reportDef->setImageName("defend_win_report.jpg");
                                $reportDef->setContent("Le dirigeant " . $character->getUsername() . " a tenté de piller votre planète " . $defender->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanetDef->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>). Il a échoué grâce a vos solides défenses. Vous avez éliminé <span class='text-vert'>" . number_format($soldierAtmp) . "</span> soldats et prit le contrôle des barges de l'attaquant.<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointDef) . "</span> points de Guerre.");
                                $reportLoot->setTitle("Rapport de pillage : Défaite (attaque)");
                                $reportLoot->setImageName("invade_lose_report.jpg");
                                $reportLoot->setContent("Le dirigeant " . $characterDefender->getUsername() . " vous attendait de pieds fermes. Sa planète " . $defender->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>) était trop renforcée pour vous. Vous tué tout de même <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks. Tous vos soldats sont morts et vos barges sont restées sur la planète.<br>La prochaine fois, préparez votre attaque commandant.");
                            } else {
                                $warPointAtt = round($soldierDtmp?$soldierDtmp:1 + $tankDtmp);
                                if ($character->getPoliticPdg() > 0) {
                                    $warPointAtt = round(($warPointAtt * (1 + ($character->getPoliticPdg() / 10))) / 60);
                                }
                                $diviser = (1 + ($character->getPoliticSoldierAtt() / 10)) * 6;
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
                                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $warPointAtt);
                                $reportDef->setTitle("Rapport de pillage : Défaite (défense)");
                                $reportDef->setImageName("defend_lose_report.jpg");
                                $reportDef->setContent("Le dirigeant " . $character->getUsername() . " vient de piller (" . number_format(round($niobium)) . " niobiums" . number_format(round($water)) . " eaux" . number_format(round($uranium)) . " uraniums) votre planète " . $defender->getName() . " - (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanetDef->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>).  " . number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminé. C'est toujours ça de gagner. Vos <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) ."</span> tanks. Votre économie en a prit un coup, mais si vous étiez là pour planter des choux ça se serait ! Préparez la contre-attaque !");
                                $reportLoot->setTitle("Rapport de pillage : Victoire (attaque)");
                                $reportLoot->setImageName("invade_win_report.jpg");
                                $reportLoot->setContent("Vos soldats ont fini de charger vos cargos ( <span class='text-vert'>" . number_format(round($niobium)) . " niobiums - " . number_format(round($water)) . " eaux - " . number_format(round($uranium)) . " uraniums </span>) et remontent dans les barges, le pillage de la planète " . $defender->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $defender->getSector()->getPosition() . "/" . $defender->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $defender->getSector()->getGalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . "</a></span>) s'est bien passé. Vos pertes sont de <span class='text-rouge'>" . number_format(round($soldierAtmp)) . "</span> soldats. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks.<br>Vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre.");
                                $quest = $character->checkQuests('loot');
                                if($quest) {
                                    $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                                    $character->removeQuest($quest);
                                }
                            }
                            $em->persist($reportLoot);
                            $em->persist($reportDef);
                        }
                    } elseif ($fleet->getFlightType() == '5' && $fleet->getPlanet()->getUser()) {
                        $alea = rand(4, 8);
                        if ($character->getPoliticBarge() > 0) {
                            $barge = $fleet->getBarge() * 2500 * (1 + ($character->getPoliticBarge() / 4));
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
                            if ($character->getPoliticSoldierAtt() > 0) {
                                $aMilitary = $aMilitary * (1 + ($character->getPoliticSoldierAtt() / 10));
                            }
                        } else {
                            $this->addFlash("fail", "Vous ne disposez pas de barges d'invasions.");
                            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
                        }

                        $defender = $fleet->getPlanet();
                        $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($character);
                        $usePlanetDef = $em->getRepository('App:Planet')->findByFirstPlanet($defender->getUser());
                        $characterDefender= $fleet->getPlanet()->getUser();
                        $barbed = $characterDefender->getBarbedAdv();
                        $dSoldier = $defender->getSoldier() > 0 ? ($defender->getSoldier() * 6) * $barbed : 0;
                        $dTanks = $defender->getTank() > 0 ? $defender->getTank() * 3000 : 0;
                        $dWorker = $defender->getWorker();
                        $soldierDtmp = $defender->getSoldier();
                        $workerDtmp = $defender->getWorker();
                        $tankDtmp = $defender->getTank();

                        $seconds = $this->forward('App\Controller\Connected\Execute\ChronosController::planetActivityAction', [
                            'planet' => $defender,
                            'now'  => $now,
                            'em' => $em]);

                        if ($seconds->getContent() >= 60) {
                            $this->forward('App\Controller\Connected\Execute\PlanetsGenController::planetGenAction', [
                                'planet' => $defender,
                                'seconds' => $seconds->getContent(),
                                'now'  => $now,
                                'em' => $em]);
                        }

                        if ($characterDefender->getPoliticSoldierAtt() > 0) {
                            $dSoldier = $dSoldier * (1 + ($characterDefender->getPoliticSoldierAtt() / 10));
                        }
                        if ($characterDefender->getPoliticTankDef() > 0) {
                            $dTanks = $dTanks * (1 + ($characterDefender->getPoliticTankDef() / 10));
                        }
                        if ($characterDefender->getPoliticWorkerDef() > 0) {
                            $dWorker = $dWorker * (1 + ($characterDefender->getPoliticWorkerDef() / 5));
                        }
                        if ($characterDefender->getZombie() == 1) {
                            $dTanks = 0;
                        }
                        $dMilitary = $dWorker + $dSoldier + $dTanks;

                        $reportInv = new Report();
                        if ($characterDefender->getZombie() == 0) {
                            $reportInv->setType('invade');
                        } else {
                            $reportInv->setType('zombie');
                        }
                        $reportInv->setSendAt($now);
                        $reportInv->setCharacter($character);
                        $character->setViewReport(false);

                        if ($characterDefender->getZombie() == 0) {
                            $reportDef = new Report();
                            $reportDef->setType('invade');
                            $reportDef->setSendAt($now);
                            $reportDef->setCharacter($characterDefender);
                        }
                        $characterDefender->setViewReport(false);
                        $dSigle = null;
                        if($characterDefender->getAlly()) {
                            $dSigle = $characterDefender->getAlly()->getSigle();
                        }

                        if($fleet->getPlanet()->getUser() && $fleet->getAllianceCharacter() && $fleet->getFightAt() == null && $fleet->getFlightTime() == null && $character->getSigleAllied($dSigle) == null) {
                            if($dMilitary >= $aMilitary) {
                                if ($characterDefender->getZombie() == 0) {
                                    $warPointDef = round($aMilitary);
                                    if ($character->getPoliticPdg() > 0) {
                                        $warPointDef = round(($warPointDef * (1 + ($character->getPoliticPdg() / 10))) / 500);
                                    }
                                    $characterDefender->getRank()->setWarPoint($characterDefender->getRank()->getWarPoint() + $warPointDef);
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
                                        $diviser = (1 + ($characterDefender->getPoliticWorkerDef() / 5));
                                        $defender->setWorker(round(abs($aMilitary / $diviser)));
                                        $tankDtmp = $tankDtmp - $defender->getTank();
                                        $soldierDtmp = $soldierDtmp - $defender->getSoldier();
                                        $workerDtmp = $workerDtmp - $defender->getWorker();
                                    } else {
                                        $diviser = (1 + ($characterDefender->getPoliticTankDef() / 10)) * 3000;
                                        $defender->setTank(round(abs($aMilitary / $diviser)));
                                        $tankDtmp = $tankDtmp - $defender->getTank();
                                        $soldierDtmp = $soldierDtmp - $defender->getSoldier();
                                    }
                                } else {
                                    $dMilitary = $dMilitary - $aMilitary - $dTanks -$dWorker;
                                    $diviser = (1 + ($characterDefender->getPoliticSoldierAtt() / 10)) * ($alea * $characterDefender->getBarbedAdv()) * 6;
                                    $defender->setSoldier(round(abs($dMilitary / $diviser)));
                                    $soldierDtmp = round(abs($dMilitary / $diviser));
                                }
                                if ($characterDefender->getZombie() == 1) {
                                    $reportInv->setTitle("Rapport contre attaque : Défaite");
                                    $reportInv->setImageName("zombie_lose_report.jpg");
                                    $reportInv->setContent("Vous pensiez partir pour une promenade de santé mais la réalité vous rattrape vite... Vous avez envoyé tout vos soldats au casse-pipe.<br>Pire, vous avez attirer l'attention des zombies et fait monter la menace de 10 points ! Vous avez interêt a prendre vite" .
                                        $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanet])->getContent() .
                                        "sinon votre Empire ne tiendra pas longtemps. Vous avez tué <span class='text-vert'>" . number_format(round($soldierDtmp + ($workerDtmp / 6) + ($tankDtmp * 3000))) .
                                        "</span> zombies. Tous vos soldats sont morts et vos barges se sont égarées sur la planète.<br>N'abandonnez pas et sortez vos tripes !");

                                    $character->setZombieAtt($character->getZombieAtt() + 10);
                                } else {
                                    $reportDef->setTitle("Rapport d'invasion : Victoire (défense)");
                                    $reportDef->setImageName("defend_win_report.jpg");
                                    $reportDef->setContent("Bien joué ! Vos travailleurs et soldats ont repoussé l'invasion du joueur" .
                                        $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $character, 'usePlanet' => $usePlanetDef])->getContent() .
                                        "sur votre planète" .
                                        $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanetDef])->getContent() .
                                        ".  <span class='text-vert'>" . number_format($soldierAtmp) .
                                        "</span> soldats vous ont attaqué, tous ont été tués. Vous avez ainsi pris le contrôle des barges de l'attaquant.<br>Et vous remportez <span class='text-vert'>+" .
                                        number_format($warPointDef) . "</span> points de Guerre.");

                                    $reportInv->setTitle("Rapport d'invasion : Défaite (attaque)");
                                    $reportInv->setImageName("invade_lose_report.jpg");
                                    $reportInv->setContent("'AH AH AH AH' le rire de" . $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $characterDefender, 'usePlanet' => $usePlanet])->getContent() .
                                        "résonne à vos oreilles d'un curieuse façon. Votre sang bouillonne vous l'a vouliez cette planète. Qu'il rigole donc, vous reviendrez prendre " .
                                        $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanet])->getContent() .
                                        "et ferez effacer des livres d'histoires son ridicule nom. Vous avez tout de même tué <span class='text-vert'>" .
                                        number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" .
                                        number_format($workerDtmp) . "</span> travailleurs à l'ennemi. Tous vos soldats sont morts et vos barges sont restées sur la planète.<br>Courage commandant.");
                                }
                            } else {
                                $warPointAtt = round(($soldierDtmp?$soldierDtmp:1 + ($workerDtmp / 10)) * 1);
                                if ($character->getPoliticPdg() > 0) {
                                    $warPointAtt = round($warPointAtt * (1 + ($character->getPoliticPdg() / 10)));
                                }
                                $warPointAtt = round($warPointAtt / 600);
                                $diviser = (1 + ($character->getPoliticSoldierAtt() / 10)) * $alea;
                                $aMilitary = $aMilitary - $dMilitary;
                                $fleet->setSoldier(abs($soldierAtmpTotal + round($aMilitary / $diviser)));
                                $soldierAtmp = $fleet->getSoldier() - round($soldierAtmpTotal + $soldierAtmp);
                                $defender->setSoldier(0);
                                $defender->setTank(0);
                                $defender->setWorker(2000);

                                if($character->getColPlanets() <= ($character->getTerraformation() + 1 + $character->getPoliticInvade()) && $characterDefender->getZombie() == 0) {
                                    $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $warPointAtt);
                                    $defender->setCharacter($character);
                                    $em->flush();
                                    if ($character->getNbrInvade()) {
                                        $character->setNbrInvade($character->getNbrInvade() + 1);
                                    } else {
                                        $character->setNbrInvade(1);
                                    }
                                    $reportDef->setTitle("Rapport d'invasion : Défaite (défense)");
                                    $reportDef->setImageName("defend_lose_report.jpg");
                                    $reportDef->setContent("Mais QUI ? QUI !!! Vous as donné un commandant si médiocre" .
                                        $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $character, 'usePlanet' => $usePlanetDef])->getContent() .
                                        "n'a pas eu à faire grand chose pour prendre votre planète" .
                                        $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanetDef])->getContent() .
                                        number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminés. C'est toujours ça de gagné. Vos <span class='text-rouge'>" . number_format($soldierDtmp) .
                                        "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-rouge'>" . number_format($workerDtmp) .
                                        "</span> travailleurs sont tous mort. Votre empire en a pris un coup, mais il vous reste des planètes, il est l'heure de la revanche !");

                                    $reportInv->setTitle("Rapport d'invasion : Victoire (attaque)");
                                    $reportInv->setImageName("invade_win_report.jpg");
                                    $reportInv->setContent("Vous débarquez après que la planète ait été prise et vous installez sur le trône de" .
                                        $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $characterDefender, 'usePlanet' => $usePlanet])->getContent() .
                                        ". Qu'il est bon d'entendre ses pleurs lointains... La planète" .
                                        $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanet])->getContent() .
                                        "est désormais votre! Il est temps de remettre de l'ordre dans la galaxie. <span class='text-rouge'>" . number_format(round($soldierAtmp)) .
                                        "</span> de vos soldats ont péri dans l'invasion. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) .
                                        "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) .
                                        "</span> travailleurs ont péri. Cependant vous épargnez 2000 travailleurs dans votre bonté (surtout pour faire tourner la planète).<br>Et vous remportez <span class='text-vert'>+" .
                                        number_format($warPointAtt) . "</span> points de Guerre.");

                                } else {
                                    $warPointAtt = $warPointAtt / 50;
                                    $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $warPointAtt);
                                    $hydra = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);
                                    if ($characterDefender->getZombie() == 0) {
                                        if ($character->getNbrInvade()) {
                                            $character->setNbrInvade($character->getNbrInvade() + 1);
                                        } else {
                                            $character->setNbrInvade(1);
                                        }
                                        $reportDef->setTitle("Rapport d'invasion : Défaite (défense)");
                                        $reportDef->setImageName("defend_lose_report.jpg");
                                        $reportDef->setContent("Mais QUI ? QUI !!! Vous as donné un commandant si médiocre" .
                                            $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $character, 'usePlanet' => $usePlanetDef])->getContent() .
                                            "n'a pas eu à faire grand chose pour prendre votre planète" .
                                            $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanetDef])->getContent() .
                                            number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminés. C'est toujours ça de gagné. Vos <span class='text-rouge'>" .
                                            number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-rouge'>" .
                                            number_format($workerDtmp) . "</span> travailleurs sont tous mort. Votre empire en a pris un coup, mais il vous reste des planètes, il est l'heure de la revanche !");

                                        $reportInv->setTitle("Rapport d'invasion : Victoire (attaque)");
                                        $reportInv->setImageName("invade_win_report.jpg");
                                        $reportInv->setContent("Vous débarquez après que la planète ait été prise et vous installez sur le trône de" .
                                            $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $characterDefender, 'usePlanet' => $usePlanet])->getContent() .
                                            ". Qu'il est bon d'entendre ses pleurs lointains... La planète" .
                                            $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanet])->getContent() .
                                            "est désormais votre! Il est temps de remettre de l'ordre dans la galaxie. <span class='text-rouge'>" . number_format(round($soldierAtmp)) .
                                            "</span> de vos soldats ont péri dans l'invasion. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) .
                                            "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) .
                                            "</span> travailleurs ont péri. Cependant vous épargnez 2000 travailleurs dans votre bonté (surtout pour faire tourner la planète).<br>Et vous remportez <span class='text-vert'>+" .
                                            number_format($warPointAtt) . "</span> points de Guerre.");

                                    } else {
                                        $reportInv->setTitle("Rapport contre attaque : Victoire");
                                        $reportInv->setImageName("zombie_win_report.jpg");
                                        $reportInv->setContent("Vos soldats débarquent sur la planète zombie et sortent l'artillerie lourde ! Les rues s'emplissent de morts mais l'entraînement prévaut sur la peur et vous purgez cette planète de cette peste macabre.<br> La planète" .
                                            $this->forward('App\Controller\FacilitiesController::coordinatesAction', ['planet' => $defender, 'usePlanet' => $usePlanet])->getContent() .
                                            "est désormais libre. Et votre indice d'attaque zombie est divisé par 10. Lors de l'assaut vous dénombrez <span class='text-rouge'>" .
                                            number_format(round($soldierAtmp)) . "</span> pertes parmis vos soldats. Mais vous avez exterminé <span class='text-vert'>" .
                                            number_format(round($soldierDtmp + ($workerDtmp / 6) + ($tankDtmp * 3000))) . "</span> zombies ! <br>Et vous remportez <span class='text-vert'>+" .
                                            number_format($warPointAtt) . "</span> points de Guerre ainsi que <span class='text-vert'>+10</span> uraniums.");
                                    }
                                    if ($characterDefender->getZombie() == 1) {
                                        $image = [
                                            'planet1.png', 'planet2.png', 'planet3.png', 'planet4.png', 'planet5.png', 'planet6.png',
                                            'planet7.png', 'planet8.png', 'planet9.png', 'planet10.png', 'planet11.png', 'planet12.png',
                                            'planet13.png', 'planet14.png', 'planet15.png', 'planet16.png', 'planet17.png', 'planet18.png',
                                            'planet19.png', 'planet20.png', 'planet21.png', 'planet22.png', 'planet23.png', 'planet24.png',
                                            'planet25.png', 'planet26.png', 'planet27.png', 'planet28.png', 'planet29.png', 'planet30.png',
                                            'planet31.png', 'planet32.png', 'planet33.png'
                                        ];
                                        $defender->setCharacter(null);
                                        $em->flush();
                                        if ($character->getZombieAtt() > 9) {
                                            $character->setZombieAtt(round($character->getZombieAtt() / 10));
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
                                        $defender->setCharacter($hydra);
                                        $defender->setWorker(125000);
                                        if ($defender->getSoldierMax() >= 2500) {
                                            $defender->setSoldier($defender->getSoldierMax());
                                        } else {
                                            $defender->setCaserne(1);
                                            $defender->setSoldier(500);
                                            $defender->setSoldierMax(500);
                                        }
                                        $defender->setName('Base Zombie');
                                        $defender->setImageName('hydra_planet.png');
                                        $em->flush();
                                    }
                                }
                                if($characterDefender->getAllPlanets() == 0) {
                                    $characterDefender->setGameOver($character->getUsername());
                                    $characterDefender->setGrade(null);
                                    if ($character->getExecution()) {
                                        $character->setExecution($character->getExecution() . ', ' . $characterDefender->getUsername());
                                    } else {
                                        $character->setExecution($characterDefender->getUsername());
                                    }
                                    $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $characterDefender->getRank()->getWarPoint());
                                    $character->setBitcoin($character->getBitcoin() + $characterDefender->getBitcoin());
                                    $reportInv->setContent($reportInv->getContent() . "<br>Vous avez totalement anéanti l'Empire de" .
                                        $this->forward('App\Controller\FacilitiesController::userReportAction', ['character' => $characterDefender, 'usePlanet' => $usePlanet])->getContent() .
                                        "et gagnez ses PDG : <span class='text-vert'>+" . number_format($characterDefender->getRank()->getWarPoint()) .
                                        "</span>, ainsi que ses Bitcoins : <span class='text-vert'>+" . number_format($characterDefender->getBitcoin()) . " .</span>");

                                    $characterDefender->getRank()->setWarPoint(1);
                                    $characterDefender->setBitcoin(1);
                                    foreach($characterDefender->getFleets() as $tmpFleet) {
                                        $tmpFleet->setCharacter($character);
                                        $tmpFleet->setFleetList(null);
                                    }
                                }
                                $quest = $character->checkQuests('invade');
                                if($quest) {
                                    $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                                    $character->removeQuest($quest);
                                }
                            }
                            if($fleet->getNbrShips() == 0) {
                                $em->remove($fleet);
                            }
                            $em->persist($reportInv);
                            if ($characterDefender->getZombie() == 0) {
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
                        $fleet->setCharacter(null);
                        $em->remove($fleet);
                        $planetGround->setSignature($planetGround->getNbrSignatures());
                    }
                } else {
                    if ($character->getZombie() == 0) {
                        $em->persist($report);
                    }
                }
            }
        }

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}