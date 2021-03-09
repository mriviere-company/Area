<?php

namespace App\Controller\Connected;

use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Fleet;
use App\Entity\Report;
use App\Entity\Planet;
use App\Entity\Destination;
use Datetime;
use DateInterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class FleetInteractController  extends AbstractController
{
    /**
     * @Route("/regroupement-flottes/{usePlanet}", name="fleets_regroup", requirements={"usePlanet"="\d+"})
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function fleetsRegroupAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $server = $usePlanet->getSector()->getGalaxy()->getServer();
        $character = $user->getCharacter($server);
        $moreNow = new DateTime();
        $moreNow->add(new DateInterval('PT' . 120 . 'S'));

        if($character->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $allFleets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->where('f.character = :character')
            ->andWhere('f.flightTime is null')
            ->andWhere('f.fightAt is null')
            ->andWhere('f.planet != :planet')
            ->setParameters(['character' => $character, 'planet' => $usePlanet])
            ->getQuery()
            ->getResult();

        foreach ($allFleets as $allFleet) {
            $now = new DateTime();

            $galaxy = $usePlanet->getSector()->getGalaxy()->getPosition();
            $sector = $usePlanet->getSector()->getPosition();
            $planetTakee = $usePlanet->getPosition();

            $sFleet = $allFleet->getPlanet()->getSector()->getPosition();
            if($allFleet->getPlanet()->getSector()->getGalaxy()->getPosition() != $galaxy) {
                $base = 18;  // 86400 MODE NORMAL
                $price = 25;
            } else {
                $pFleet = $allFleet->getPlanet()->getPosition();
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
            $carburant = round($price * ($allFleet->getNbrSignatures() / 200));
            if($carburant > $character->getBitcoin() && $character->getId() != 1) {
                return $this->redirectToRoute('fleet', ['usePlanet' => $usePlanet->getId()]);
            }
            if($allFleet->getMotherShip()) {
                $speed = $allFleet->getSpeed() - ($allFleet->getSpeed() * 0.10);
            } else {
                $speed = $allFleet->getSpeed();
            }
            $distance = $speed * $base * 1000 * $server->getSpeed();
            $now->add(new DateInterval('PT' . round($distance) . 'S'));
            $destination = new Destination($allFleet, $usePlanet);
            $em->persist($destination);
            $allFleet->setDestination($destination);
            $allFleet->setFlightTime($now);
            $allFleet->setCancelFlight($moreNow);
            $allFleet->setSignature($allFleet->getNbrSignatures());

            $allFleet->setFlightType(7);
            if($character->getId() != 1) {
                $character->setBitcoin($character->getBitcoin() - $carburant);
            }
        }
        $em->flush();

        return $this->redirectToRoute('fleet', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/regroupement-vaisseaux/{usePlanet}", name="ships_regroup", requirements={"usePlanet"="\d+"})
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function shipsRegroupAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $server = $usePlanet->getSector()->getGalaxy()->getServer();
        $character = $user->getCharacter($server);
        $moreNow = new DateTime();
        $moreNow->add(new DateInterval('PT' . 120 . 'S'));

        if($character->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $allPlanets = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.character = :character')
            ->andWhere('p.signature > 0')
            ->andWhere('p.id != :planet')
            ->setParameters(['character' => $character, 'planet' => $usePlanet->getId()])
            ->getQuery()
            ->getResult();

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
            if($tmp->getAccepted() == 1) {
                $friendAlly[$x] = $tmp->getAllyTag();
                $x++;
            }
        }
        if(!$friendAlly) {
            $friendAlly = ['impossible', 'personne'];
        }

        if($character->getAlly()) {
            $allyF = $character->getAlly();
        } else {
            $allyF = 'wedontexistsok';
        }

        foreach ($allPlanets as $allPlanet) {

            $fleets = $em->getRepository('App:Fleet')
                ->createQueryBuilder('f')
                ->join('f.character', 'c')
                ->leftJoin('c.ally', 'a')
                ->where('f.planet = :planet')
                ->andWhere('f.attack = true OR a.sigle in (:ally)')
                ->andWhere('f.character != :character')
                ->andWhere('f.flightTime is null')
                ->andWhere('c.ally is null OR a.sigle not in (:friend)')
                ->andWhere('c.ally is null OR c.ally != :myAlly')
                ->setParameters(['planet' => $allPlanet, 'ally' => $warAlly, 'character' => $character, 'friend' => $friendAlly, 'myAlly' => $allyF])
                ->getQuery()
                ->getResult();

            $fleetFight = $em->getRepository('App:Fleet')
                ->createQueryBuilder('f')
                ->where('f.planet = :planet')
                ->andWhere('f.character != :character')
                ->andWhere('f.fightAt is not null')
                ->andWhere('f.flightTime is null')
                ->setParameters(['planet' => $allPlanet, 'character' => $character])
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();

            if ($allPlanet->getNbrSignaturesRegroup() > 0 and !$fleetFight and !$fleets) {
                $one = new Fleet();
                $one->setCharacter($character);
                $one->setPlanet($allPlanet);
                $one->setName('Ralliement');
                $one->setAttack(0);
                $one->setSonde($one->getSonde() + $allPlanet->getSonde());
                $one->setCargoI($one->getCargoI() + $allPlanet->getCargoI());
                $one->setCargoV($one->getCargoV() + $allPlanet->getCargoV());
                $one->setCargoX($one->getCargoX() + $allPlanet->getCargoX());
                $one->setColonizer($one->getColonizer() + $allPlanet->getColonizer());
                $one->setRecycleur($one->getRecycleur() + $allPlanet->getRecycleur());
                $one->setBarge($one->getBarge() + $allPlanet->getBarge());
                $one->setMoonMaker($one->getMoonMaker() + $allPlanet->getMoonMaker());
                $one->setRadarShip($one->getRadarShip() + $allPlanet->getRadarShip());
                $one->setBrouilleurShip($one->getBrouilleurShip() + $allPlanet->getBrouilleurShip());
                $one->setMotherShip($one->getMotherShip() + $allPlanet->getMotherShip());
                $one->setHunter($one->getHunter() + $allPlanet->getHunter());
                $one->setHunterHeavy($one->getHunterHeavy() + $allPlanet->getHunterHeavy());
                $one->setHunterWar($one->getHunterWar() + $allPlanet->getHunterWar());
                $one->setCorvet($one->getCorvet() + $allPlanet->getCorvet());
                $one->setCorvetLaser($one->getCorvetLaser() + $allPlanet->getCorvetLaser());
                $one->setCorvetWar($one->getCorvetWar() + $allPlanet->getCorvetWar());
                $one->setFregate($one->getFregate() + $allPlanet->getFregate());
                $one->setFregatePlasma($one->getFregatePlasma() + $allPlanet->getFregatePlasma());
                $one->setCroiser($one->getCroiser() + $allPlanet->getCroiser());
                $one->setIronClad($one->getIronClad() + $allPlanet->getIronClad());
                $one->setDestroyer($one->getDestroyer() + $allPlanet->getDestroyer());
                $one->setSignature($one->getNbrSignatures());
                $allPlanet->setSonde(0);
                $allPlanet->setCargoI(0);
                $allPlanet->setCargoV(0);
                $allPlanet->setCargoX(0);
                $allPlanet->setColonizer(0);
                $allPlanet->setRecycleur(0);
                $allPlanet->setBarge(0);
                $allPlanet->setMoonMaker(0);
                $allPlanet->setRadarShip(0);
                $allPlanet->setBrouilleurShip(0);
                $allPlanet->setMotherShip(0);
                $allPlanet->setHunter(0);
                $allPlanet->setHunterHeavy(0);
                $allPlanet->setHunterWar(0);
                $allPlanet->setCorvet(0);
                $allPlanet->setCorvetLaser(0);
                $allPlanet->setCorvetWar(0);
                $allPlanet->setFregate(0);
                $allPlanet->setFregatePlasma(0);
                $allPlanet->setCroiser(0);
                $allPlanet->setIronClad(0);
                $allPlanet->setDestroyer(0);
                $allPlanet->setSignature(0);
                $em->persist($one);

                $now = new DateTime();

                $galaxy = $usePlanet->getSector()->getGalaxy()->getPosition();
                $sector = $usePlanet->getSector()->getPosition();
                $planetTakee = $usePlanet->getPosition();

                $sFleet = $one->getPlanet()->getSector()->getPosition();
                if($one->getPlanet()->getSector()->getGalaxy()->getPosition() != $galaxy) {
                    $base = 18;  // 86400 MODE NORMAL
                    $price = 25;
                } else {
                    $pFleet = $one->getPlanet()->getPosition();
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
                $carburant = round($price * ($one->getNbrSignatures() / 200));
                if($carburant > $character->getBitcoin() && $character->getZombie() != 1) {
                    return $this->redirectToRoute('fleet', ['usePlanet' => $usePlanet->getId()]);
                }
                if($one->getMotherShip()) {
                    $speed = $one->getSpeed() - ($one->getSpeed() * 0.10);
                } else {
                    $speed = $one->getSpeed();
                }
                $distance = $speed * $base * 1000 * $server->getSpeed();
                $now->add(new DateInterval('PT' . round($distance) . 'S'));
                $destination = new Destination($one, $usePlanet);
                $em->persist($destination);
                $one->setDestination($destination);
                $one->setFlightTime($now);
                $one->setCancelFlight($moreNow);
                $one->setSignature($one->getNbrSignatures());

                $one->setFlightType(7);
                if($character->getZombie() != 1) {
                    $character->setBitcoin($character->getBitcoin() - $carburant);
                }
            }
        }
        $em->flush();

        return $this->redirectToRoute('fleet', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-niobium/{fleetGive}/{usePlanet}", name="discharge_fleet_niobium", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     * @param Planet $usePlanet
     * @param Fleet $fleetGive
     * @return RedirectResponse
     */
    public function dischargeNiobiumFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();

        if ($usePlanet->getCharacter() != $character || $fleetGive->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        
        if($planetTake->getMerchant() == true) {
            if ($character->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getNiobium() / 6) / 50000) * (1 + ($character->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getNiobium() / 6) / 50000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setCharacter($character);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($character->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getNiobium() * 0.10) * (1 + ($character->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getNiobium() * 0.10);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setNiobium($planetTake->getNiobium() + $fleetGive->getNiobium());
            $character->setBitcoin($character->getBitcoin() + $gainSell);
            $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setNiobium(0);
            $quest = $character->checkQuests('sell');
            if($quest) {
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                $character->removeQuest($quest);
            }
        } else {
            if(($planetTake->getNiobium() + $fleetGive->getNiobium()) <= $planetTake->getNiobiumMax()) {
                $planetTake->setNiobium($planetTake->getNiobium() + $fleetGive->getNiobium());
                $fleetGive->setNiobium(0);
            } else {
                $planetTake->setNiobium($planetTake->getNiobiumMax());
                $fleetGive->setNiobium(($planetTake->getNiobium() + $fleetGive->getNiobium()) - $planetTake->getNiobiumMax());
            }
        }
        $character->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-water/{fleetGive}/{usePlanet}", name="discharge_fleet_water", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     * @param Planet $usePlanet
     * @param Fleet $fleetGive
     * @return RedirectResponse
     */
    public function dischargeWaterFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();

        if ($usePlanet->getCharacter() != $character || $fleetGive->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            if ($character->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getWater() / 3) / 50000) * (1 + ($character->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getWater() / 3) / 50000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setCharacter($character);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($character->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getWater() * 0.25) * (1 + ($character->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getWater() * 0.25);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setWater($planetTake->getWater() + $fleetGive->getWater());
            $character->setBitcoin($character->getBitcoin() + $gainSell);
            $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setWater(0);
            $quest = $character->checkQuests('sell');
            if($quest) {
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                $character->removeQuest($quest);
            }
        } else {
            if(($planetTake->getWater() + $fleetGive->getWater()) <= $planetTake->getWaterMax()) {
                $planetTake->setWater($planetTake->getWater() + $fleetGive->getWater());
                $fleetGive->setWater(0);
            } else {
                $planetTake->setWater($planetTake->getWaterMax());
                $fleetGive->setWater(($planetTake->getWater() + $fleetGive->getWater()) - $planetTake->getWaterMax());
            }
        }
        $character->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-soldat/{fleetGive}/{usePlanet}", name="discharge_fleet_soldier", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     * @param Planet $usePlanet
     * @param Fleet $fleetGive
     * @return RedirectResponse
     */
    public function dischargeSoldierFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();

        if ($usePlanet->getCharacter() != $character || $fleetGive->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            if ($character->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getSoldier() * 10) / 50000) * (1 + ($character->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getSoldier() * 10) / 50000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setCharacter($character);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($character->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getSoldier() * 80) * (1 + ($character->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getSoldier() * 80);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setSoldier($planetTake->getSoldier() + $fleetGive->getSoldier());
            $character->setBitcoin($character->getBitcoin() + $gainSell);
            $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setSoldier(0);
            $quest = $character->checkQuests('sell');
            if($quest) {
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                $character->removeQuest($quest);
            }
        } else {
            if(($planetTake->getSoldier() + $fleetGive->getSoldier()) <= $planetTake->getSoldierMax()) {
                $planetTake->setSoldier($planetTake->getSoldier() + $fleetGive->getSoldier());
                $fleetGive->setSoldier(0);
            } else {
                $planetTake->setSoldier($planetTake->getSoldierMax());
                $fleetGive->setSoldier(($planetTake->getSoldier() + $fleetGive->getSoldier()) - $planetTake->getSoldierMax());
            }
        }
        $character->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-travailleurs/{fleetGive}/{usePlanet}", name="discharge_fleet_worker", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     * @param Planet $usePlanet
     * @param Fleet $fleetGive
     * @return RedirectResponse
     */
    public function dischargeWorkerFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();

        if ($usePlanet->getCharacter() != $character || $fleetGive->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            if ($character->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getWorker() * 50) / 50000) * (1 + ($character->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getWorker() * 50) / 50000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setCharacter($character);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($character->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getWorker() * 5) * (1 + ($character->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getWorker() * 5);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setWorker($planetTake->getWorker() + $fleetGive->getWorker());
            $character->setBitcoin($character->getBitcoin() + $gainSell);
            $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setWorker(0);
            $quest = $character->checkQuests('sell');
            if($quest) {
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                $character->removeQuest($quest);
            }
        } else {
            if(($planetTake->getWorker() + $fleetGive->getWorker()) <= $planetTake->getWorkerMax()) {
                $planetTake->setWorker($planetTake->getWorker() + $fleetGive->getWorker());
                $fleetGive->setWorker(0);
            } else {
                $planetTake->setWorker($planetTake->getWorkerMax());
                $fleetGive->setWorker(($planetTake->getWorker() + $fleetGive->getWorker()) - $planetTake->getWorkerMax());
            }
        }
        $character->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-scientifique/{fleetGive}/{usePlanet}", name="discharge_fleet_scientist", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     * @param Planet $usePlanet
     * @param Fleet $fleetGive
     * @return RedirectResponse
     */
    public function dischargeScientistFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();

        if ($usePlanet->getCharacter() != $character || $fleetGive->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            if ($character->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getScientist() * 100) / 50000) * (1 + ($character->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getScientist() * 100) / 50000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setCharacter($character);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($character->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getScientist() * 300) * (1 + ($character->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getScientist() * 300);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setScientist($planetTake->getScientist() + $fleetGive->getScientist());
            $character->setBitcoin($character->getBitcoin() + $gainSell);
            $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setScientist(0);
            $quest = $character->checkQuests('sell');
            if($quest) {
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                $character->removeQuest($quest);
            }
        } else {
            if(($planetTake->getScientist() + $fleetGive->getScientist()) <= $planetTake->getScientistMax()) {
                $planetTake->setScientist($planetTake->getScientist() + $fleetGive->getScientist());
                $fleetGive->setScientist(0);
            } else {
                $planetTake->setScientist($planetTake->getScientistMax());
                $fleetGive->setScientist(($planetTake->getScientist() + $fleetGive->getScientist()) - $planetTake->getScientistMax());
            }
        }
        $character->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-tout/{fleetGive}/{usePlanet}", name="discharge_fleet_all", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     * @param Planet $usePlanet
     * @param Fleet $fleetGive
     * @return RedirectResponse
     */
    public function dischargeAllFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();

        if ($usePlanet->getCharacter() != $character || $fleetGive->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setCharacter($character);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($character->getPoliticPdg() > 0) {
                $newWarPointS = round((((($fleetGive->getScientist() * 100) + ($fleetGive->getWorker() * 50) + ($fleetGive->getSoldier() * 10) + ($fleetGive->getWater() / 3) + ($fleetGive->getNiobium() / 6) + ($fleetGive->getTank() * 5) + ($fleetGive->getUranium() * 10)) / 50000)) * (1 + ($character->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round((($fleetGive->getScientist() * 100) + ($fleetGive->getWorker() * 50) + ($fleetGive->getSoldier() * 10) + ($fleetGive->getWater() / 3) + ($fleetGive->getNiobium() / 6) + ($fleetGive->getTank() * 5) + ($fleetGive->getUranium() * 10)) / 50000);
            }
            if ($character->getPoliticMerchant() > 0) {
                $gainSell = round((($fleetGive->getWater() * 0.25) + ($fleetGive->getSoldier() * 80) + ($fleetGive->getWorker() * 5) + ($fleetGive->getScientist() * 300) + ($fleetGive->getNiobium() * 0.10) + ($fleetGive->getTank() * 2500) + ($fleetGive->getUranium() * 5000)) * (1 + ($character->getPoliticMerchant() / 20)));
            } else {
                $gainSell = round(($fleetGive->getWater() * 0.25) + ($fleetGive->getSoldier() * 80) + ($fleetGive->getWorker() * 5) + ($fleetGive->getScientist() * 300) + ($fleetGive->getNiobium() * 0.10) + ($fleetGive->getTank() * 2500) + ($fleetGive->getUranium() * 5000));
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format($gainSell) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setScientist($planetTake->getScientist() + $fleetGive->getScientist());
            $planetTake->setWorker($planetTake->getWorker() + $fleetGive->getWorker());
            $planetTake->setSoldier($planetTake->getSoldier() + $fleetGive->getSoldier());
            $planetTake->setWater($planetTake->getWater() + $fleetGive->getWater());
            $planetTake->setNiobium($planetTake->getNiobium() + $fleetGive->getNiobium());
            $planetTake->setUranium($planetTake->getUranium() + $fleetGive->getUranium());
            $character->setBitcoin($character->getBitcoin() + $gainSell);
            $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setScientist(0);
            $fleetGive->setNiobium(0);
            $fleetGive->setWater(0);
            $fleetGive->setUranium(0);
            $fleetGive->setSoldier(0);
            $fleetGive->setTank(0);
            $fleetGive->setWorker(0);
            $quest = $character->checkQuests('sell');
            if($quest) {
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                $character->removeQuest($quest);
            }
        } else {
            if ($planetTake->getNiobium() + $fleetGive->getNiobium() <= $planetTake->getNiobiumMax()) {
                $planetTake->setNiobium($planetTake->getNiobium() + $fleetGive->getNiobium());
                $fleetGive->setNiobium(0);
            } else {
                $fleetGive->setNiobium($fleetGive->getNiobium() - ($planetTake->getNiobiumMax() - $planetTake->getNiobium()));
                $planetTake->setNiobium($planetTake->getNiobiumMax());
            }
            if ($planetTake->getWater() + $fleetGive->getWater() <= $planetTake->getWaterMax()) {
                $planetTake->setWater($planetTake->getWater() + $fleetGive->getWater());
                $fleetGive->setWater(0);
            } else {
                $fleetGive->setWater($fleetGive->getWater() - ($planetTake->getWaterMax() - $planetTake->getWater()));
                $planetTake->setWater($planetTake->getWaterMax());
            }
            $planetTake->setUranium($fleetGive->getUranium());
            $fleetGive->setUranium(0);
            if ($planetTake->getSoldier() + $fleetGive->getSoldier() <= $planetTake->getSoldierMax()) {
                $planetTake->setSoldier($planetTake->getSoldier() + $fleetGive->getSoldier());
                $fleetGive->setSoldier(0);
            } else {
                $fleetGive->setSoldier($fleetGive->getSoldier() - ($planetTake->getSoldierMax() - $planetTake->getSoldier()));
                $planetTake->setSoldier($planetTake->getSoldierMax());
            }
            if ($planetTake->getTank() + $fleetGive->getTank() <= 500) {
                $planetTake->setTank($planetTake->getTank() + $fleetGive->getTank());
                $fleetGive->setTank(0);
            } else {
                $fleetGive->setTank($fleetGive->getTank() - (500 - $planetTake->getTank()));
                $planetTake->setTank(500);
            }
            if ($planetTake->getWorker() + $fleetGive->getWorker() <= $planetTake->getWorkerMax()) {
                $planetTake->setWorker($planetTake->getWorker() + $fleetGive->getWorker());
                $fleetGive->setWorker(0);
            } else {
                $fleetGive->setWorker($fleetGive->getWorker() - ($planetTake->getWorkerMax() - $planetTake->getWorker()));
                $planetTake->setWorker($planetTake->getWorkerMax());
            }
            if ($planetTake->getScientist() + $fleetGive->getScientist() <= $planetTake->getScientistMax()) {
                $planetTake->setScientist($planetTake->getScientist() + $fleetGive->getScientist());
                $fleetGive->setScientist(0);
            } else {
                $fleetGive->setScientist($fleetGive->getScientist() - ($planetTake->getScientistMax() - $planetTake->getScientist()));
                $planetTake->setScientist($planetTake->getScientistMax());
            }
        }
        $character->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }
}