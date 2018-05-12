<?php

namespace App\Controller\CronController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Report;
use DateTime;
use DateTimeZone;
use Dateinterval;

class InstantController extends Controller
{
    /**
     * @Route("/resources/", name="ressources_load")
     */
    public function minuteLoadAction()
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));

        $users = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->getQuery()
            ->getResult();

        foreach ($users as $user) {
            foreach ($user->getPlanets() as $planet) {
                $niobium = $planet->getNiobium();
                $water = $planet->getWater();
                $niobium = $niobium + ($planet->getNbProduction());
                $water = $water + ($planet->getWtProduction());
                $planet->setNiobium($niobium);
                $planet->setWater($water);
                $em->persist($planet);
            }
            $em->persist($user);
        }
        $em->flush();

        exit;
    }

    /**
     * @Route("/construction/", name="build_fleet_load")
     */
    public function buildFleetAction()
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));

        $users = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->where('u.searchAt < :now')
            ->setParameters(array('now' => $now))
            ->getQuery()
            ->getResult();

        $userSoldiers = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.soldierAt < :now')
            ->setParameters(array('now' => $now))
            ->getQuery()
            ->getResult();

        $userScientists = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.scientistAt < :now')
            ->setParameters(array('now' => $now))
            ->getQuery()
            ->getResult();

        $fleets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->where('f.flightTime < :now')
            ->setParameters(array('now' => $now))
            ->getQuery()
            ->getResult();

        $planets = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.constructAt < :now')
            ->setParameters(array('now' => $now))
            ->getQuery()
            ->getResult();

        foreach ($userSoldiers as $soldierAt) {
            $soldierAt->setSoldier($soldierAt->getSoldierAtNbr());
            $soldierAt->setSoldierAt(null);
            $soldierAt->setSoldierAtNbr(null);
            $em->persist($soldierAt);
        }
        foreach ($userScientists as $scientistAt) {
            $scientistAt->setScientist($scientistAt->GetScientistAtNbr());
            $scientistAt->setScientistAt(null);
            $scientistAt->setScientistAtNbr(null);
            $em->persist($scientistAt);
        }

        foreach ($planets as $planet) {
            $build = $planet->getConstruct();
            if($build == 'destruct') {
            } elseif ($build == 'miner') {
                $planet->setMiner($planet->getMiner() + 1);
                $planet->setNbProduction($planet->getNbProduction() + ($planet->getMiner() * 1.1));
            } elseif ($build == 'extractor') {
                $planet->setExtractor($planet->getExtractor() + 1);
                $planet->setWtProduction($planet->getWtProduction() + ($planet->getExtractor() * 1.09));
            } elseif ($build == 'city') {
                $planet->setCity($planet->getCity() + 1);
                $planet->setWorkerProduction($planet->getWorkerProduction() + 2000);
                $planet->setWorkerMax($planet->getWorkerMax() + 25000);
            } elseif ($build == 'metropole') {
                $planet->setMetropole($planet->getMetropole() + 1);
                $planet->setWorkerProduction($planet->getWorkerProduction() + 5000);
                $planet->setWorkerMax($planet->getWorkerMax() + 75000);
            } elseif ($build == 'caserne') {
                $planet->setCaserne($planet->getCaserne() + 1);
                $planet->setSoldierMax($planet->getSoldierMax() + 2500);
            } elseif ($build == 'centerSearch') {
                $planet->setCenterSearch($planet->getCenterSearch() + 1);
                $planet->getUser()->setScientistProduction($planet->getUser()->getScientistProduction() + 0.1);
                $planet->setScientistMax($planet->getScientistMax() + 500);
            } elseif ($build == 'lightUsine') {
                $planet->setLightUsine($planet->getLightUsine() + 1);
                $planet->setShipProduction($planet->getShipProduction() + 0.15);
            } elseif ($build == 'heavyUsine') {
                $planet->setHeavyUsine($planet->getHeavyUsine() + 1);
                $planet->setShipProduction($planet->getShipProduction() + 0.3);
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
            $planet->setConstruct(null);
            $planet->setConstructAt(null);
            $em->persist($planet);
        }

        foreach ($users as $user) {
            $research = $user->getSearch();
            if ($research == 'onde') {
                $user->setOnde($user->getOnde() + 1);
            } elseif ($research == 'industry') {
                $user->setIndustry($user->getIndustry() + 1);
            } elseif ($research == 'discipline') {
                $user->setDiscipline($user->getDiscipline() + 1);
            } elseif ($research == 'hyperespace') {
                $user->setHyperespace(1);
            } elseif ($research == 'barge') {
                $user->setBarge(1);
            } elseif ($research == 'utility') {
                $user->setUtility($user->getUtility() + 1);
            } elseif ($research == 'demography') {
                $user->setDemography($user->getDemography() + 1);
            } elseif ($research == 'terraformation') {
                $user->setTerraformation($user->getTerraformation() + 1);
            } elseif ($research == 'cargo') {
                $user->setCargo($user->getCargo() + 1);
            } elseif ($research == 'recycleur') {
                $user->setRecycleur(1);
            } elseif ($research == 'armement') {
                $user->setArmement($user->getArmement() + 1);
            } elseif ($research == 'missile') {
                $user->setMissile($user->getMissile() + 1);
            } elseif ($research == 'laser') {
                $user->setLaser($user->getLaser() + 1);
            } elseif ($research == 'plasma') {
                $user->setPlasma($user->getPlasma() + 1);
            } elseif ($research == 'lightShip') {
                $user->setLightShip($user->getLightShip() + 1);
            } elseif ($research == 'heavyShip') {
                $user->setHeavyShip($user->getHeavyShip() + 1);
            }
            $user->setSearch(null);
            $user->setSearchAt(null);
            $em->persist($user);
        }

        foreach ($fleets as $fleet) {
            $allFleets = $em->getRepository('App:Fleet')
                ->createQueryBuilder('f')
                ->join('f.user', 'u')
                ->where('f.planet = :planet')
                ->andWhere('f.user != :user')
                ->setParameters(array('planet' => $fleet->getPlanet(), 'user' => $fleet->getUser()))
                ->getQuery()
                ->getResult();

            $eAlly = $fleet->getUser()->getAllyEnnemy();
            $warAlly = [];
            $x = 0;
            foreach ($eAlly as $tmp) {
                $warAlly[$x] = $tmp->getAllyTag();
                $x++;
            }

            $newHome = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->where('p.position = :planete')
                ->andWhere('s.position = :sector')
                ->andWhere('g.position = :galaxy')
                ->setParameters(array('planete' => $fleet->getPlanete(), 'sector' => $fleet->getSector()->getPosition(), 'galaxy' => $fleet->getSector()->getGalaxy()->getPosition()))
                ->getQuery()
                ->getOneOrNullResult();


            $userFleet = $fleet->getUser();
            $report = new Report();
            $report->setTitle("Votre flotte " . $fleet->getName() . " est arrivée");
            $report->setSendAt($now);
            $report->setUser($userFleet);
            $report->setContent("Bonjour dirigeant " . $userFleet->getUserName() . " votre flotte " . $fleet->getName() . " vient d'arriver en " . $newHome->getSector()->getGalaxy()->getPosition() . ":" . $newHome->getSector()->getPosition() . ":" . $newHome->getPosition() . ".");
            $userFleet->setViewReport(false);
            $em->persist($userFleet);
            $oldPlanet = $fleet->getPlanet();
            $fleet->setPlanet($newHome);
            $fleet->setPlanete(null);
            $fleet->setFlightTime(null);
            $fleet->setNewPlanet(null);
            $fleet->setSector(null);
            $attackFleets = $em->getRepository('App:Fleet')
                ->createQueryBuilder('f')
                ->join('f.user', 'u')
                ->leftJoin('u.ally', 'a')
                ->where('f.planet = :planet')
                ->andWhere('f.attack = :true OR a.sigle in (:ally)')
                ->andWhere('f.user != :user')
                ->setParameters(array('planet' => $newHome, 'true' => true, 'ally' => $warAlly, 'user' => $fleet->getUser()))
                ->getQuery()
                ->getResult();

            if ($fleet->getUser()->getAlly()) {
                $ally = $em->getRepository('App:Fleet')
                    ->createQueryBuilder('f')
                    ->join('f.user', 'u')
                    ->where('f.planet = :planet')
                    ->andWhere('u.ally != :ally')
                    ->setParameters(array('planet' => $newHome, 'ally' => $fleet->getUser()->getAlly()))
                    ->getQuery()
                    ->getResult();
            } else {
                $ally = 'war';
            }

            if ($attackFleets || ($fleet->getAttack() == true && $ally)) {
                $now = new DateTime();
                $now->setTimezone(new DateTimeZone('Europe/Paris'));
                $now->add(new DateInterval('PT' . 300 . 'S'));
                foreach ($allFleets as $updateF) {
                    $updateF->setFightAt($now);
                    $em->persist($updateF);
                }
                $fleet->setFightAt($now);
                $report->setContent($report->getContent() . " Attention votre flotte est rentrée en combat !");
            }
            $em->persist($report);
            $em->persist($fleet);
            if ($fleet->getFightAt() == null) {
                $user = $fleet->getUser();
                $newPlanet = $fleet->getPlanet();
                
                if ($fleet->getFlightType() == '2') {
                    $newPlanet->setNiobium($newPlanet->getNiobium() + $fleet->getNiobium());
                    $newPlanet->setWater($newPlanet->getWater() + $fleet->getWater());
                    $newPlanet->setSoldier($newPlanet->getSoldier() + $fleet->getSoldier());
                    $newPlanet->setWorker($newPlanet->getWorker() + $fleet->getWorker());
                    $newPlanet->setScientist($newPlanet->getScientist() + $fleet->getScientist());
                    if($newPlanet->getMerchant() == true) {
                        $user->setBitcoin($user->getBitcoin() + ($fleet->getWater() * 2) + ($fleet->getSoldier() * 7.5) + ($fleet->getWorker() / 4) + ($fleet->getScientist() * 75) + ($fleet->getNiobium() / 1.5));
                    }
                    $fleet->setNiobium(0);
                    $fleet->setWater(0);
                    $fleet->setSoldier(0);
                    $fleet->setWorker(0);
                    $fleet->setScientist(0);
                    $sFleet= $fleet->getPlanet()->getSector()->getPosition();
                    $sector = $oldPlanet->getSector()->getPosition();
                    if ($sFleet == $sector) {
                        $base= 2000;
                    } elseif (strpos('0 -1 1 -10 10 -9 9', (strval($sFleet - $sector)) ) != false) {
                        $base= 3000;
                    } elseif (strpos('-20 20 12 11 8 2 -12 -11 -8 -2', (strval($sFleet - $sector)) ) != false) {
                        $base= 6800;
                    } elseif (strpos('-28 28 29 30 31 32 33 22 12 3 7 -29 -30 -31 -32 -33 -22 -13 -3 -7', (strval($sFleet - $sector)) ) != false) {
                        $base= 8000;
                    } else {
                        $base= 15000;
                    }

                    $now->add(new DateInterval('PT' . ($fleet->getSpeed() * $base) . 'S'));
                    $fleet->setNewPlanet($oldPlanet->getId());
                    $fleet->setFlightTime($now);
                    $fleet->setFlightType(1);
                    $fleet->setSector($oldPlanet->getSector());
                    $fleet->setPlanete($oldPlanet->getPosition());
                    $em->persist($fleet);
                    $em->persist($newPlanet);
                    $em->flush();
                } elseif ($fleet->getFlightType() == '3') {
                    if ($fleet->getColonizer() && $newPlanet->getUser() == null &&
                        $newPlanet->getEmpty() == false && $newPlanet->getMerchant() == false &&
                        $newPlanet->getCdr() == false && count($fleet->getUser()->getPlanets()) < 21 &&
                        count($fleet->getUser()->getPlanets()) <= ($user->getTerraformation() + 2)) {
                        $fleet->setColonizer($fleet->getColonizer() - 1);
                        $newPlanet->setUser($fleet->getUser());
                        $newPlanet->setName('Colonie');
                        $em->persist($fleet);
                        if ($fleet->getNbrShips() == 0) {
                            $em->remove($fleet);
                        }
                        $em->persist($newPlanet);
                        $em->flush();
                    }
                } elseif ($fleet->getFlightType() == '4') {
                    $barge = $fleet->getBarge() * 2500;
                    $defenser = $fleet->getPlanet();
                    $userDefender= $fleet->getPlanet()->getUser();
                    $dMilitary = $defenser->getWorker() + ($defenser->getSoldier() * 6);
                    $alea = rand(5, 9);

                    $reportInv = new Report();
                    $reportInv->setSendAt($now);
                    $reportInv->setUser($user);
                    $user->setViewReport(false);

                    $reportDef = new Report();
                    $reportDef->setSendAt($now);
                    $reportDef->setUser($userDefender);
                    $userDefender->setViewReport(false);

                    if($barge and $fleet->getPlanet()->getUser() and $fleet->getAllianceUser() == null) {
                        if($barge >= $fleet->getSoldier()) {
                            $aMilitary = $fleet->getSoldier() * $alea;
                            $soldierAtmp = $fleet->getSoldier();
                        } else {
                            $aMilitary = $barge * $alea;
                            $soldierAtmp = $barge;
                        }
                        if($dMilitary > $aMilitary) {
                            $aMilitary = ($defenser->getSoldier() * 6) - $aMilitary;
                            if($barge < $fleet->getSoldier()) {
                                $fleet->setSoldier($fleet->getSoldier() - $barge);
                            }
                            $defenser->setBarge($defenser->getBarge() + $fleet->getBarge());
                            $fleet->setBarge(0);
                            if($aMilitary < 0) {
                                $soldierDtmp = $defenser->getSoldier();
                                $workerDtmp = $defenser->getWorker();
                                $defenser->setSoldier(0);
                                $defenser->setWorker($defenser->getWorker() + $aMilitary);
                                $soldierDtmp = $soldierDtmp - $defenser->getSoldier();
                                $workerDtmp = $workerDtmp - $defenser->getWorker();
                            } else {
                                $defenser->setSoldier($aMilitary / 6);
                            }
                            $reportDef->setTitle("Rapport d'invasion : Victoire (défense)");
                            $reportDef->setContent("Bien joué ! Vos travailleurs et soldats ont repoussé l'invasion du joueur " . $user->getUserName() . " sur votre planète " . $defenser->getName() . " - " . $defenser->getSector()->getgalaxy()->getPosition() . ":" . $defenser->getSector()->getPosition() . ":" . $defenser->getPosition() . ".  " . $soldierAtmp . " soldats vous ont attaqué, tous ont été tué. Vous avez ainsi prit le contrôle des barges de l'attaquant.");
                            $reportInv->setTitle("Rapport d'invasion : Défaite (attaque)");
                            $reportInv->setContent("'AH AH AH AH' le rire de " . $userDefender->getUserName() . " résonne à vos oreilles d'un curieuse façon. Votre sang bouillonne vous l'a vouliez cette planète. Qu'il rigole donc, vous reviendrez prendre " . $defenser->getName() . " - " . $defenser->getSector()->getgalaxy()->getPosition() . ":" . $defenser->getSector()->getPosition() . ":" . $defenser->getPosition() . " et ferez effacer des livres d'histoires son ridicule nom. Vous avez tout de même tué " . $soldierDtmp . " soldats et " . $workerDtmp . " travailleurs à l'ennemi. Tous vos soldats sont morts et vos barges sont resté sur la planète. Courage commandant.");
                        } else {
                            $soldierDtmp = $defenser->getSoldier();
                            $workerDtmp = $defenser->getWorker();
                            $soldierAtmp = $fleet->getSoldier();
                            $fleet->setSoldier(($aMilitary - $dMilitary) / $alea);
                            $soldierAtmp = $soldierAtmp - $fleet->getSoldier();
                            $defenser->setSoldier(0);
                            $defenser->setWorker(2000);
                            if(count($fleet->getUser()->getPlanets()) < ($fleet->getUser()->getTerraformation() + 2)) {
                                $defenser->setUser($user);
                            } else {
                                $defenser->setUser(null);
                                $defenser->setName('Abandonnée');
                            }
                            if(count($userDefender->getPlanets()) == 1) {
                                $userDefender->setGameOver($user->getUserName());
                                $userDefender->setAlly(null);
                                $userDefender->setGrade(null);
                                foreach($userDefender->getFleets() as $tmpFleet) {
                                    $tmpFleet->setUser($user);
                                    $em->persist($tmpFleet);
                                }
                                $em->persist($userDefender);
                            }
                            $reportDef->setTitle("Rapport d'invasion : Défaite (défense)");
                            $reportDef->setContent("Mais QUI ? QUI !!! Vous as donné un commandant si médiocre " . $user->getUserName() . " n'a pas eu a faire grand chose pour prendre votre planète " . $defenser->getName() . " - " . $defenser->getSector()->getgalaxy()->getPosition() . ":" . $defenser->getSector()->getPosition() . ":" . $defenser->getPosition() . ".  " . round($soldierAtmp) . " soldats ennemis sont tout de même éliminé. C'est toujours ça de gagner. Vos " . $soldierDtmp . " soldats et " . $workerDtmp . " travailleurs sont tous mort. Votre empire en a prit un coup, mais il vous reste des planètes, il est l'heure de la revanche !");
                            $reportInv->setTitle("Rapport d'invasion : Victoire (attaque)");
                            $reportInv->setContent("Vous débarquez après que la planète ait été prise et vous installez sur le trône de " . $userDefender->getUserName() . ". Qu'il est bon d'entendre ses pleures lointain... La planète " . $defenser->getName() . " - " . $defenser->getSector()->getgalaxy()->getPosition() . ":" . $defenser->getSector()->getPosition() . ":" . $defenser->getPosition() . " est désormais votre! Il est temps de remettre de l'ordre dans la galaxie. " . round($soldierAtmp) . " de vos soldats ont péri dans l'invasion. Mais les défenseurs ont aussi leurs pertes : " . $soldierDtmp . " soldats et " . $workerDtmp . " travailleurs ont péri. Cependant vous épargnez 2000 travailleurs dans votre bonté (surtout pour faire tourner la planète).");
                        }
                        $em->persist($fleet);
                        if($fleet->getNbrShips() == 0) {
                            $em->remove($fleet);
                        }
                        $em->persist($reportInv);
                        $em->persist($reportDef);
                        $em->persist($defenser);
                        $em->flush();
                    }
                }
            }
        }
        $em->flush();
        exit;
    }
}
