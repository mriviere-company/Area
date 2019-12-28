<?php

namespace App\Controller\CronController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Report;
use App\Entity\Fleet;
use App\Entity\Destination;
use App\Entity\Exchange;
use DateTime;
use DateTimeZone;
use Dateinterval;

class InstantController extends AbstractController
{
    /**
     * @Route("/construction/", name="build_fleet_load")
     */
    public function buildFleetAction()
    {
        $em = $this->getDoctrine()->getManager();
        $servers = $em->getRepository('App:Server')->findAll();
        $nowAste = new DateTime();
        $nowAste->setTimezone(new DateTimeZone('Europe/Paris'));

        $asteroides = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.cdr = true')
            ->andWhere('p.recycleAt < :nowAste OR p.recycleAt IS NULL')
            ->setParameters(['nowAste' => $nowAste])
            ->getQuery()
            ->getResult();

        if($asteroides) {
            $nowAste->add(new DateInterval('PT' . (25000 * rand(1, 4)) . 'S'));
            foreach ($asteroides as $asteroide) {

                $asteroide->setRecycleAt($nowAste);
                $asteroide->setNbCdr($asteroide->getNbCdr() + rand(50000, 500000));
                $asteroide->setWtCdr($asteroide->getWtCdr() + rand(40000, 400000));


                if(rand(1, 50) == 50) {
                    $asteroide->setCdr(false);
                    $asteroide->setEmpty(true);
                    $asteroide->setImageName(null);
                    $asteroide->setRecycleAt(null);
                    $asteroide->setName('Vide');
                    $newAsteroides = $em->getRepository('App:Planet')
                        ->createQueryBuilder('p')
                        ->join('p.sector', 's')
                        ->join('s.galaxy', 'g')
                        ->where('p.empty = true')
                        ->andWhere('s.position = :rand')
                        ->andWhere('g.position = :galaxy')
                        ->setParameters(['rand' => rand(1, 100), 'galaxy' => $asteroide->getSector()->getGalaxy()->getPosition()])
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();

                    if ($newAsteroides) {
                        $newAsteroides->setEmpty(false);
                        $newAsteroides->setCdr(true);
                        $newAsteroides->setImageName('cdr.png');
                        $newAsteroides->setName('Astéroïdes');
                        $iaPlayer = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);
                        $planetZb = $em->getRepository('App:Planet')
                            ->createQueryBuilder('p')
                            ->where('p.user = :user')
                            ->andWhere('p.radarAt is null and p.brouilleurAt is null')
                            ->setParameters(['user' => $iaPlayer])
                            ->orderBy('p.ground', 'ASC')
                            ->getQuery()
                            ->setMaxresults(1)
                            ->getOneOrNullResult();

                        $planetBis = $em->getRepository('App:Planet')
                            ->createQueryBuilder('p')
                            ->leftJoin('p.missions', 'm')
                            ->where('p.user = :user')
                            ->andWhere('p.radarAt is null and p.brouilleurAt is null and m.soldier is not null')
                            ->setParameters(['user' => $iaPlayer])
                            ->orderBy('p.ground', 'ASC')
                            ->getQuery()
                            ->setMaxresults(1)
                            ->getOneOrNullResult();

                        if ($planetBis) {
                            $planetZb = $planetBis;
                        }

                        $timeAttAst = new DateTime();
                        $timeAttAst->setTimezone(new DateTimeZone('Europe/Paris'));
                        $timeAttAst->add(new DateInterval('PT' . 24 . 'H'));
                        $fleet = new Fleet();
                        $alea = rand(10, 100);
                        $fleet->setHunterWar(300 * $alea);
                        $fleet->setCorvetWar(50 * $alea);
                        $fleet->setFregatePlasma(3 * $alea);
                        $fleet->setDestroyer(1 * $alea);
                        $fleet->setUser($iaPlayer);
                        $fleet->setPlanet($planetZb);
                        $destination = new Destination();
                        $destination->setFleet($fleet);
                        $destination->setPlanet($newAsteroides);
                        $em->persist($destination);
                        $fleet->setFlightTime($timeAttAst);
                        $fleet->setAttack(1);
                        $fleet->setName('Horde');
                        $fleet->setSignature($fleet->getNbrSignatures());
                        $em->persist($fleet);
                    }
                }
            }
            $em->flush();
        }

        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $now->add(new DateInterval('PT' . 1 . 'S'));

        $planetSoldiers = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.soldierAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        $planetTanks = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.tankAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        $planetNuclear = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.nuclearAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        $planetScientists = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.scientistAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        $fleets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->where('f.flightTime < :now')
            ->andWhere('f.flightType != :six or f.flightType is null')
            ->setParameters(['now' => $now, 'six' => 6])
            ->getQuery()
            ->getResult();

        $nuclears = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->where('f.flightTime < :now')
            ->andWhere('f.flightType = :six')
            ->setParameters(['now' => $now, 'six' => 6])
            ->getQuery()
            ->getResult();

        $zUsers = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.planets', 'p')
            ->where('u.zombieAt < :now')
            ->andWhere('u.rank is not null')
            ->andWhere('u.bot = false')
            ->andWhere('p.id is not null')
            ->groupBy('u.id')
            ->having('count(p.id) >= 3')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        $fleetCdrs = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->where('f.recycleAt < :now or f.recycleAt is null')
            ->andWhere('f.recycleur > :zero')
            ->andWhere('f.flightTime is null')
            ->andWhere('p.nbCdr > :zero or p.wtCdr > :zero')
            ->setParameters(['now' => $now, 'zero' => 0])
            ->getQuery()
            ->getResult();

        $planets = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.constructAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        $products = $em->getRepository('App:Product')
            ->createQueryBuilder('p')
            ->where('p.productAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        $radars = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.radarAt < :now or p.brouilleurAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        $pacts = $em->getRepository('App:Allied')
            ->createQueryBuilder('al')
            ->where('al.dismissAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        $peaces = $em->getRepository('App:Peace')
            ->createQueryBuilder('p')
            ->where('p.signedAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        $userGOs = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->where('u.gameOver is not null')
            ->andWhere('u.rank is not null')
            ->getQuery()
            ->getResult();

        $dailyReport = $em->getRepository('App:Server')
            ->createQueryBuilder('s')
            ->where('s.dailyReport < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getOneOrNullResult();

        if ($dailyReport) {
            $users = $em->getRepository('App:User')
                ->createQueryBuilder('u')
                ->join('u.rank', 'r')
                ->where('u.id != :one')
                ->setParameters(['one' => 1])
                ->orderBy('r.point', 'DESC')
                ->getQuery()
                ->getResult();

            $x = 1;
            foreach ($users as $user) {

                $maxQuest = count($user->getWhichQuest()) - 1;
                $first = rand(0, $maxQuest);
                $second = $first;
                $third = $second;
                $economicGO = 0;
                while ($second == $first) {
                    $second = rand(0, $maxQuest);
                }
                while ($third == $first or $third == $second) {
                    $third = rand(0, $maxQuest);
                }
                $questOne = $em->getRepository('App:Quest')->findOneByName($user->getWhichQuest()[$first]);
                $questTwo = $em->getRepository('App:Quest')->findOneByName($user->getWhichQuest()[$second]);
                $questTree = $em->getRepository('App:Quest')->findOneByName($user->getWhichQuest()[$third]);
                $report = new Report();
                $report->setType('economic');
                $report->setTitle("Rapport de l'empire");
                $report->setImageName("daily_report.jpg");
                $report->setSendAt($now);
                $report->setUser($user);
                $ally = $user->getAlly();
                $nbrQuests = count($user->getQuests());
                foreach ($user->getQuests() as $quest) {
                    $user->removeQuest($quest);
                }
                $user->addQuest($questOne);
                $worker = 0;
                $planetPoint= 0;
                $buildingCost = 0;
                foreach ($user->getPlanets() as $planet) {
                    $worker = $worker + $planet->getWorker();
                    $planetPoint = $planetPoint + $planet->getBuildingPoint();
                    $buildingCost = $buildingCost + $planet->getBuildingCost();
                }
                $gain = round($worker / 3);
                $lose = null;
                if($ally) {
                    $user->addQuest($questTwo);
                    if($ally->getPeaces()) {
                        foreach($ally->getPeaces() as $peace) {
                            if($peace->getType() == false && $peace->getAccepted() == 1) {
                                $otherAlly = $em->getRepository('App:Ally')
                                    ->createQueryBuilder('a')
                                    ->where('a.sigle = :sigle')
                                    ->setParameter('sigle', $peace->getAllyTag())
                                    ->getQuery()
                                    ->getOneOrNullResult();

                                $lose = (($peace->getTaxe() / 100) * $gain);
                                if($lose < 0) {
                                    $lose = (($peace->getTaxe() / 100) * $user->getBitcoin());
                                }
                                $gain = $gain - $lose;
                                $user->setBitcoin($user->getBitcoin() - $lose);
                                $otherAlly->setBitcoin($otherAlly->getBitcoin() + $lose);
                                $exchange = new Exchange();
                                $exchange->setAlly($otherAlly);
                                $exchange->setCreatedAt($now);
                                $exchange->setType(0);
                                $exchange->setAccepted(1);
                                $exchange->setContent("Taxe liée à la paix.");
                                $exchange->setAmount($lose);
                                $exchange->setName($user->getUserName());
                                $em->persist($exchange);
                                $report->setContent($report->getContent() . " La paix que vous avez signé envoi directement <span class='text-rouge'>" . number_format(round($lose)) . "</span> bitcoins à l'aliance [" . $otherAlly->getSigle() . "].<br>");
                            }
                        }
                    }
                    $userBitcoin = $user->getBitcoin();
                    $taxe = (($ally->getTaxe() / 100) * $gain);
                    $gain = $gain - $taxe;
                    $user->setBitcoin($userBitcoin - $taxe);
                    $report->setContent(" Le montant envoyé dans les fonds de votre alliance s'élève à <span class='text-rouge'>" . number_format(round($taxe)) . "</span> bitcoins.<br>");
                    $allyBitcoin = $ally->getBitcoin();
                    $allyBitcoin = $allyBitcoin + $taxe;
                    $ally->setBitcoin($allyBitcoin);
                } else {
                    $questAlly = $em->getRepository('App:Quest')->findOneById(50);
                    $user->addQuest($questAlly);
                }
                $user->addQuest($questTree);
                $troops = $user->getAllTroops();
                $ship = $user->getAllShipsCost();
                $cost = $user->getBitcoin();
                $report->setContent($report->getContent() . " Le travaille fournit par vos travailleurs vous rapporte <span class='text-vert'>+" . number_format(round($gain)) . "</span> bitcoins.");
                $empireCost = $troops + $ship + $buildingCost;
                $cost = $cost - $empireCost + ($gain);
                $report->setContent($report->getContent() . " L'entretien de votre empire vous coûte cependant <span class='text-rouge'>" . number_format(round($empireCost)) . "</span> bitcoins.<br>");
                $point = round(round($worker / 100) + round($user->getAllShipsPoint() / 75) + round($troops / 75) + $planetPoint);
                $user->setBitcoin($cost);
                if ($user->getBitcoin() < 0) {
                    foreach ($user->getFleetLists() as $list) {
                        foreach ($list->getFleets() as $fleetL) {
                            $fleetL->setFleetList(null);
                        }
                        $em->remove($list);
                    }
                    foreach($user->getFleets() as $fleet) {
                        if ($fleet->getDestination()) {
                            $em->remove($fleet->getDestination());
                        }
                        $em->remove($fleet);
                    }
                    foreach ($user->getPlanets() as $planet) {
                        $planet->setSignature(0);
                        $planet->setSoldier(0);
                        $planet->setScientist(0);
                    }
                    $economicGO = 1;
                    $user->setBitcoin(5000);
                }
                if ($gain - $empireCost > 0) {
                    $color = '<span class="text-vert">+';
                } else {
                    $color = '<span class="text-rouge">';
                }
                if ($nbrQuests == 0) {
                    $report->setContent($report->getContent() . " Ce qui vous donne un revenu de " . $color . number_format(round($gain - $empireCost)) . "</span> bitcoins. Comme vous avez terminé toutes les quêtes vous recevez un bonus de 20.000 PDG ! Bonne journée suprême Commandant.");
                    $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + 20000);
                } else {
                    $report->setContent($report->getContent() . " Ce qui vous donne un revenu de " . $color . number_format(round($gain - $empireCost)) . "</span> bitcoins.<br>Bonne journée Commandant.");
                }
                if ($point - $user->getRank()->getOldPoint() > 0) {
                    $user->setExperience($user->getExperience() + ($point - $user->getRank()->getOldPoint()));
                }
                $user->getRank()->setOldPoint($user->getRank()->getPoint());
                $user->getRank()->setPoint($point);
                $user->setViewReport(false);

                if ($economicGO == 1) {
                    $report->setContent($report->getContent() . "<br>Votre réserve de Bitcoins passe en négatif et vous n'êtes plus en mesure d'entretenir votre armada.<br>Vous perdez tout les vaisseaux que contenait votre Empire et redémarrez avec 5.000 Bitcoins.");
                }
                $em->persist($report);
                $x++;
            }

            $em->flush();

            $users = $em->getRepository('App:User')
                ->createQueryBuilder('u')
                ->join('u.rank', 'r')
                ->where('u.id != :one')
                ->setParameters(['one' => 1])
                ->orderBy('r.point', 'DESC')
                ->getQuery()
                ->getResult();

            $x = 1;
            foreach ($users as $user) {
                $user->getRank()->setOldPosition($user->getRank()->getPosition());
                $user->getRank()->setPosition($x);
                $x++;
            }

            $allys = $em->getRepository('App:Ally')->findAll();

            foreach ($allys as $ally) {
                $ally->setRank($ally->getUsersPoint());
            }

            $nowDaily = new DateTime();
            $nowDaily->setTimezone(new DateTimeZone('Europe/Paris'));
            $nowDaily->add(new DateInterval('PT' . round(86397 + rand(1,2)) . 'S'));
            foreach ($servers as $server) {
                $server->setDailyReport($nowDaily);
            }
            $em->flush();
        }

        foreach ($userGOs as $userGO) {
            foreach ($userGO->getFleetLists() as $list) {
                foreach ($list->getFleets() as $fleetL) {
                    $fleetL->setFleetList(null);
                }
                $em->remove($list);
            }
            $ship = $userGO->getShip();
            $userGO->setShip(null);
            $em->remove($ship);
            $userGO->setBitcoin(25000);
            $userGO->setSearch(null);
            $em->remove($userGO->getRank(null));
            $userGO->setRank(null);
            $userGO->setGrade(null);
            $userGO->setJoinAllyAt(null);
            $userGO->setAllyBan(null);
            $userGO->setScientistProduction(1);
            $userGO->setSearchAt(null);
            $userGO->setDemography(0);
            $userGO->setUtility(0);
            $userGO->setArmement(0);
            $userGO->setIndustry(0);
            $userGO->setTerraformation(round($userGO->getTerraformation(0) / 2));
            /*$userGO->setPlasma(0);
            $userGO->setLaser(0);
            $userGO->setMissile(0);
            $userGO->setRecycleur(0);
            $userGO->setCargo(0);
            $userGO->setBarge(0);
            $userGO->setHyperespace(0);
            $userGO->setDiscipline(0);
            $userGO->setHeavyShip(0);
            $userGO->setLightShip(0);
            $userGO->setOnde(0);
            $userGO->setHyperespace(0);
            $userGO->setDiscipline(0);
            $userGO->setBarbed(0);
            $userGO->setTank(0);
            $userGO->setExpansion(0);*/
            $userGO->setPoliticArmement(0);
            $userGO->setPoliticCostScientist(0);
            $userGO->setPoliticArmor(0);
            $userGO->setPoliticBarge(0);
            $userGO->setPoliticCargo(0);
            $userGO->setPoliticColonisation(0);
            $userGO->setPoliticCostSoldier(0);
            $userGO->setPoliticCostTank(0);
            $userGO->setPoliticInvade(0);
            $userGO->setPoliticMerchant(0);
            $userGO->setPoliticPdg(0);
            $userGO->setPoliticProd(0);
            $userGO->setPoliticRecycleur(0);
            $userGO->setPoliticSearch(0);
            $userGO->setPoliticSoldierAtt(0);
            $userGO->setPoliticSoldierSale(0);
            $userGO->setPoliticTankDef(0);
            $userGO->setPoliticWorker(0);
            $userGO->setPoliticWorkerDef(0);
            $userGO->setZombieAtt(1);
            if ($userGO->getAlly()) {
                $ally = $userGO->getAlly();
                if (count($ally->getUsers()) == 1 || ($ally->getPolitic() == 'fascism' && $userGO->getGrade()->getPlacement() == 1)) {
                    foreach ($ally->getUsers() as $userGO) {
                        $userGO->setAlly(null);
                        $userGO->setGrade(null);
                        $userGO->setAllyBan($now);
                    }
                    foreach ($ally->getFleets() as $fleet) {
                        $fleet->setAlly(null);
                    }
                    foreach ($ally->getGrades() as $grade) {
                        $em->remove($grade);
                    }
                    foreach ($ally->getSalons() as $salon) {
                        foreach ($salon->getContents() as $content) {
                            $em->remove($content);
                        }
                        $em->remove($salon);
                    }
                    foreach ($ally->getExchanges() as $exchange) {
                        $em->remove($exchange);
                    }

                    foreach ($ally->getPnas() as $pna) {
                        $em->remove($pna);
                    }

                    foreach ($ally->getWars() as $war) {
                        $em->remove($war);
                    }

                    foreach ($ally->getAllieds() as $allied) {
                        $em->remove($allied);
                    }

                    foreach ($ally->getProposals() as $proposal) {
                        $em->remove($proposal);
                    }
                    $em->flush();

                    $pnas = $em->getRepository('App:Pna')
                        ->createQueryBuilder('p')
                        ->where('p.allyTag = :allytag')
                        ->setParameters(['allytag' => $ally->getSigle()])
                        ->getQuery()
                        ->getResult();

                    $pacts = $em->getRepository('App:Allied')
                        ->createQueryBuilder('a')
                        ->where('a.allyTag = :allytag')
                        ->setParameters(['allytag' => $ally->getSigle()])
                        ->getQuery()
                        ->getResult();

                    $wars = $em->getRepository('App:War')
                        ->createQueryBuilder('w')
                        ->where('w.allyTag = :allytag')
                        ->setParameters(['allytag' => $ally->getSigle()])
                        ->getQuery()
                        ->getResult();

                    foreach ($pnas as $pna) {
                        $em->remove($pna);
                    }

                    foreach ($pacts as $pact) {
                        $em->remove($pact);
                    }

                    foreach ($wars as $war) {
                        $em->remove($war);
                    }

                    $ally->setImageName(null);
                    $em->remove($ally);
                }
            }
            $userGO->setAlly(null);

            foreach ($userGO->getSalons() as $salon) {
                $salon->removeUser($userGO);
            }

            $salon = $em->getRepository('App:Salon')
                ->createQueryBuilder('s')
                ->where('s.name = :name')
                ->setParameters(['name' => 'Public'])
                ->getQuery()
                ->getOneOrNullResult();

            $salon->removeUser($userGO);
            $userGO->setSalons(null);

        }
        $now->sub(new DateInterval('PT' . 1 . 'S'));

        foreach ($zUsers as $zUser) {
            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($zUser->getUsername());
            $zombie = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);
            $planetAtt = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->where('p.user = :user')
                ->andWhere('p.radarAt is null and p.brouilleurAt is null')
                ->setParameters(['user' => $zUser])
                ->orderBy('p.ground', 'ASC')
                ->getQuery()
                ->setMaxresults(1)
                ->getOneOrNullResult();

            $planetBis = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->leftJoin('p.missions', 'm')
                ->where('p.user = :user')
                ->andWhere('p.radarAt is null and p.brouilleurAt is null and m.soldier is not null')
                ->setParameters(['user' => $zUser])
                ->orderBy('p.ground', 'ASC')
                ->getQuery()
                ->setMaxresults(1)
                ->getOneOrNullResult();

            if ($planetBis) {
                $planetAtt = $planetBis;
            }

            if (!$planetAtt) {
                // stopper serveur
            }

            $planetZb = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->where('p.user = :user')
                ->setParameters(['user' => $zombie])
                ->orderBy('p.ground', 'ASC')
                ->andWhere('p.radarAt is null and p.brouilleurAt is null')
                ->getQuery()
                ->setMaxresults(1)
                ->getOneOrNullResult();

            $planetBis = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->leftJoin('p.missions', 'm')
                ->where('p.user = :user')
                ->andWhere('p.radarAt is null and p.brouilleurAt is null and m.soldier is not null')
                ->setParameters(['user' => $zombie])
                ->orderBy('p.ground', 'ASC')
                ->getQuery()
                ->setMaxresults(1)
                ->getOneOrNullResult();

            if ($planetBis) {
                $planetZb = $planetBis;
            }

            if ($zUser->getZombieAtt() > 0) {
                $reportDef = new Report();
                $reportDef->setType('invade');
                $reportDef->setSendAt($now);
                $reportDef->setUser($zUser);
                if ($zUser->getZombieAtt() == 1 && $zUser->getTutorial() == 50) {
                    $zUser->setTutorial(51);
                }

                $barbed = $zUser->getBarbedAdv();
                $dSoldier = $planetAtt->getSoldier() > 0 ? ($planetAtt->getSoldier() * 6) * $barbed : 0;
                $dTanks = $planetAtt->getTank() > 0 ? $planetAtt->getTank() * 900 : 0;
                $dWorker = $planetAtt->getWorker();
                if ($zUser->getPoliticSoldierAtt() > 0) {
                    $dSoldier = $dSoldier * (1 + ($zUser->getPoliticSoldierAtt() / 10));
                }
                if ($zUser->getPoliticTankDef() > 0) {
                    $dTanks = $dTanks * (1 + ($zUser->getPoliticTankDef() / 10));
                }
                if ($zUser->getPoliticWorkerDef() > 0) {
                    $dWorker = $dWorker * (1 + ($zUser->getPoliticWorkerDef() / 5));
                }
                $dMilitary = $dWorker + $dSoldier + $dTanks;
                $aMilitary = (500 * (($zUser->getZombieAtt() / 6) + 1) * 2 * round(1 + ($zUser->getTerraformation()) / 5));
                $soldierAtmp = (500 * (($zUser->getZombieAtt() / 6) + 1));

                if ($dMilitary > $aMilitary) {
                    if ($zUser->getAlly()) {
                        if ($zUser->getAlly()->getPolitic() == 'fascism') {
                            $zUser->setZombieAtt($zUser->getZombieAtt() + 5);
                        } else {
                            $zUser->setZombieAtt($zUser->getZombieAtt() + 10);
                        }
                    } else {
                        $zUser->setZombieAtt($zUser->getZombieAtt() + 1);
                    }
                    $warPointDef = round($aMilitary);
                    $zUser->getRank()->setWarPoint($zUser->getRank()->getWarPoint() + $warPointDef);
                    $aMilitary = $dSoldier - $aMilitary;
                    $reportDef->setType("zombie");
                    $reportDef->setTitle("Rapport invasion zombies : Victoire");
                    $reportDef->setImageName("zombie_win_report.jpg");
                    $soldierDtmp = $planetAtt->getSoldier();
                    $workerDtmp = $planetAtt->getWorker();
                    $tankDtmp = $planetAtt->getTank();
                    if ($aMilitary <= 0) {
                        $planetAtt->setSoldier(0);
                        $aMilitary = $dTanks - abs($aMilitary);
                        if ($aMilitary <= 0) {
                            $planetAtt->setTank(0);
                            $planetAtt->setWorker($planetAtt->getWorker() + ($aMilitary / (1 + ($zUser->getPoliticWorkerDef() / 5))));
                            $workerDtmp = $workerDtmp - $planetAtt->getWorker();
                            $reportDef->setContent("«Au secours !» des civils crient et cours dans tout les sens sur " . $planetAtt->getName() . " en <span><a href='/connect/carte-spatiale/" . $planetAtt->getSector()->getPosition() . "/" . $planetAtt->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $planetAtt->getSector()->getGalaxy()->getPosition() . ":" . $planetAtt->getSector()->getPosition() . ":" . $planetAtt->getPosition() . "</a></span>.<br>Vous n'aviez pas prévu suffisament de soldats et tanks pour faire face a la menace et des zombies envahissent les villes. Heureusement pour vous les travailleurs se réunissent et parviennent exterminer les zombies mais ce n'est pas grâce a vous.<br>" . number_format($soldierAtmp) . " zombies sont tués. <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> de vos soldats succombent aux mâchoires de ces infamies et <span class='text-rouge'>" . number_format($tankDtmp) . "</span> tanks sont mit hors de service. <span class='text-rouge'>" . number_format($workerDtmp) . "</span> de vos travailleurs sont retrouvés morts.<br>Vous ne remportez aucun points de Guerre pour avoir sacrifié vos civils.");
                            $em->persist($reportDef);
                        } else {
                            $diviser = (1 + ($zUser->getPoliticTankDef() / 10)) * 900;
                            $planetAtt->setTank(round($aMilitary / $diviser));
                            $tankDtmp = $tankDtmp - $planetAtt->getTank();
                            $reportDef->setContent("Vos tanks ont suffit a arrêter les zombies pour cette fois-ci sur la planète " . $planetAtt->getName() . " en <span><a href='/connect/carte-spatiale/" . $planetAtt->getSector()->getPosition() . "/" . $planetAtt->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $planetAtt->getSector()->getGalaxy()->getPosition() . ":" . $planetAtt->getSector()->getPosition() . ":" . $planetAtt->getPosition() . "</a></span>.br>Mais pensez a rester sur vos gardes. Votre armée extermine " . number_format($soldierAtmp) . " zombies. <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> de vos soldats succombent aux mâchoires de ces infamies et <span class='text-rouge'>" . number_format($tankDtmp) . "</span> tanks sont mit hors de service.<br>Vous remportez <span class='text-vert'>+" . number_format($warPointDef) . "</span> points de Guerre.");
                            $em->persist($reportDef);
                        }
                    } else {
                        $diviser = (1 + ($zUser->getPoliticSoldierAtt() / 10)) * (6 * $zUser->getBarbedAdv());
                        $planetAtt->setSoldier(round($aMilitary / $diviser));
                        $soldierDtmp = $soldierDtmp - $planetAtt->getSoldier();
                        $reportDef->setContent("Une attaque de zombie est déclarée sur " . $planetAtt->getName() . " en <span><a href='/connect/carte-spatiale/" . $planetAtt->getSector()->getPosition() . "/" . $planetAtt->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $planetAtt->getSector()->getGalaxy()->getPosition() . ":" . $planetAtt->getSector()->getPosition() . ":" . $planetAtt->getPosition() . "</a></span>.<br>Vous étiez préparé a cette éventualité et vos soldats exterminent " . number_format($soldierAtmp) . " zombies. <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> de vos soldats succombent aux mâchoires de ces infamies.<br>Vous remportez <span class='text-vert'>+" . number_format($warPointDef) . "</span> points de Guerre.");
                        $em->persist($reportDef);
                    }
                } else {
                    $zUser->setZombieAtt((round($zUser->getZombieAtt() / 2)));
                    $soldierDtmp = $planetAtt->getSoldier() != 0 ? $planetAtt->getSoldier() : 1;
                    $workerDtmp = $planetAtt->getWorker();
                    $tankDtmp = $planetAtt->getTank();
                    $reportDef->setTitle("Rapport invasion zombies : Défaite");
                    $reportDef->setType("zombie");
                    $reportDef->setImageName("zombie_lose_report.jpg");
                    $reportDef->setContent("Vous recevez des rapports de toutes parts vous signalant des zombies sur la planète " . $planetAtt->getName() . " en <span><a href='/connect/carte-spatiale/" . $planetAtt->getSector()->getPosition() . "/" . $planetAtt->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $planetAtt->getSector()->getGalaxy()->getPosition() . ":" . $planetAtt->getSector()->getPosition() . ":" . $planetAtt->getPosition() . "</a></span>.<br>Mais vous tardez a réagir et le manque de préparation lui est fatale.<br>Vous recevez ces derniers mots de votre Gouverneur local «Salopard! Vous étiez censé nous protéger, ou est l'armée !»<br> Les dernières images de la planète vous montre des zombies envahissant le moindre recoin de la planète.<br>Vos <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) . "</span> tanks et <span class='text-rouge'>" . number_format($workerDtmp) . "</span> travailleurs sont tous mort. Votre empire en a prit un coup, mais il vous reste des planètes, remettez vous en question! Consolidez vos positions et allez détuire les nids de zombies!");
                    $em->persist($reportDef);
                    $planetAtt->setWorker(125000);
                    if ($planetAtt->getGround() != 25 && $planetAtt->getSky() != 5) {
                        if ($planetAtt->getSoldierMax() >= 2500) {
                            $planetAtt->setSoldier($planetAtt->getSoldierMax());
                        } else {
                            $planetAtt->setCaserne(1);
                            $planetAtt->setSoldier(2500);
                            $planetAtt->setSoldierMax(2500);
                        }
                        $planetAtt->setName('Base Zombie');
                        $planetAtt->setImageName('hydra_planet.png');
                        $planetAtt->setUser($zombie);
                    } else {
                        $planetAtt->setName('Inhabitée');
                        $planetAtt->setUser(null);
                    }
                    $em->flush();
                    if ($zUser->getColPlanets() == 0) {
                        $zUser->setGameOver($zombie->getUserName());
                        $zUser->setGrade(null);
                        foreach ($zUser->getFleets() as $tmpFleet) {
                            $tmpFleet->setUser($zombie);
                            $tmpFleet->setFleetList(null);
                        }
                    }
                }
            } else {
                if ($zUser->getAlly()) {
                    if ($zUser->getAlly()->getPolitic() == 'fascism') {
                        $zUser->setZombieAtt($zUser->getZombieAtt() + 5);
                    } else {
                        $zUser->setZombieAtt($zUser->getZombieAtt() + 10);
                    }
                } else {
                    $zUser->setZombieAtt($zUser->getZombieAtt() + 1);
                }
            }
            $zUser->setViewReport(false);
            $timeAtt = new DateTime();
            $timeAtt->setTimezone(new DateTimeZone('Europe/Paris'));
            $timeAtt->add(new DateInterval('PT' . round(86400 * rand(1,3)) . 'S'));
            $nextZombie = new DateTime();
            $nextZombie->setTimezone(new DateTimeZone('Europe/Paris'));
            $nextZombie->add(new DateInterval('PT' . round(12 * rand(1,5)) . 'H'));
            $zUser->setZombieAt($nextZombie);
            $fleetZb = new Fleet();
            $fleetZb->setName('Horde');
            $fleetZb->setHunter(1 + round(($zUser->getAllShipsPoint() / (3 * rand(1, 5))) / 5));
            $fleetZb->setHunterWar(1 + round(($zUser->getAllShipsPoint() / (4 * rand(1, 5))) / 5));
            $fleetZb->setCorvet(1 + round(($zUser->getAllShipsPoint() / (5 * rand(1, 5))) / 5));
            $fleetZb->setCorvetLaser(1 + round(($zUser->getAllShipsPoint() / (6 * rand(1, 5))) / 5));
            $fleetZb->setCorvetWar(1 + round(($zUser->getAllShipsPoint() / (7 * rand(1, 5))) / 5));
            $fleetZb->setFregate(1 + round(($zUser->getAllShipsPoint() / (8 * rand(1, 5))) / 5));
            $fleetZb->setUser($zombie);
            $fleetZb->setPlanet($planetZb);
            $fleetZb->setSignature($fleetZb->getNbrSignatures());
            $destination = new Destination();
            $destination->setFleet($fleetZb);
            $destination->setPlanet($planetAtt);
            $em->persist($destination);
            $fleetZb->setFlightTime($timeAtt);
            $fleetZb->setAttack(1);
            $fleetZb->setFlightType(1);
            $em->persist($fleetZb);
        }

        foreach ($peaces as $peace) {
            $em->remove($peace);
        }

        foreach ($pacts as $pact) {
            $otherAlly = $em->getRepository('App:Ally')
                ->createQueryBuilder('a')
                ->where('a.sigle = :sigle')
                ->setParameter('sigle', $pact->getAllyTag())
                ->getQuery()
                ->getOneOrNullResult();

            $pact2 = $em->getRepository('App:Allied')
                ->createQueryBuilder('al')
                ->where('al.allyTag = :allytag')
                ->andWhere('al.ally = :ally')
                ->setParameters([
                    'allytag' => $pact->getDismissBy(),
                    'ally' => $otherAlly])
                ->getQuery()
                ->getOneOrNullResult();

            $salons = $em->getRepository('App:Salon')
                ->createQueryBuilder('s')
                ->where('s.name = :sigle1')
                ->orWhere('s.name = :sigle2')
                ->setParameters(['sigle1' => $otherAlly->getSigle() . " - " . $pact->getDismissBy(), 'sigle2' => $pact->getDismissBy() . " - " . $otherAlly->getSigle()])
                ->getQuery()
                ->getResult();

            foreach($salons as $salon) {
                foreach($salon->getContents() as $content) {
                    $em->remove($content);
                }
                $em->remove($salon);
            }

            if($pact2) {
                $em->remove($pact2);
            }
            $em->remove($pact);
        }

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

        foreach ($planetNuclear as $nuclear) {
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

        foreach ($nuclears as $nuclear) {

            $newHome = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->where('p.position = :planete')
                ->andWhere('s.position = :sector')
                ->andWhere('g.position = :galaxy')
                ->setParameters(['planete' => $nuclear->getDestination()->getPlanet()->getPosition(), 'sector' => $nuclear->getDestination()->getPlanet()->getSector()->getPosition(), 'galaxy' => $nuclear->getDestination()->getPlanet()->getSector()->getGalaxy()->getPosition()])
                ->getQuery()
                ->getOneOrNullResult();

            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($newHome->getUser()->getUsername());
            $reportNuclearAtt = new Report();
            $reportNuclearAtt->setType('fight');
            $reportNuclearAtt->setTitle("Votre missile nucléaire a touché sa cible !");
            $reportNuclearAtt->setImageName("nuclear_attack.png");
            $reportNuclearAtt->setSendAt($now);
            $reportNuclearAtt->setUser($nuclear->getUser());
            $reportNuclearDef = new Report();
            $reportNuclearDef->setType('fight');
            $reportNuclearDef->setTitle("Un missile nucléaire vous a frapper !");
            $reportNuclearDef->setImageName("nuclear_attack.png");
            $reportNuclearDef->setSendAt($now);
            $reportNuclearDef->setUser($newHome->getUser());
            $em->remove($nuclear);
            if ($newHome->getMetropole() > 0) {
                $newHome->setMetropole($newHome->getMetropole() - 1);
                $newHome->setWorkerMax($newHome->getWorkerMax() - 75000);
                $newHome->setWorkerProduction($newHome->getWorkerProduction() - 8.32);
                if ($newHome->getWorker() > $newHome->getWorkerMax()) {
                    $newHome->setWorker($newHome->getWorkerMax());
                }
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Une métropole a été détruite. Il provenait du Dirigeant " . $nuclear->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Une métropole a été détruite.");
            } elseif ($newHome->getCity() > 0) {
                $newHome->setCity($newHome->getCity() - 1);
                $newHome->setWorkerMax($newHome->getWorkerMax() - 25000);
                $newHome->setWorkerProduction($newHome->getWorkerProduction() - 5.56);
                if ($newHome->getWorker() > $newHome->getWorkerMax()) {
                    $newHome->setWorker($newHome->getWorkerMax());
                }
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Une ville a été détruite. Il provenait du Dirigeant " . $nuclear->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Une ville a été détruite.");
            } elseif ($newHome->getBunker() > 0) {
                $newHome->setBunker($newHome->getBunker() - 1);
                $newHome->setSoldierMax($newHome->getSoldierMax() - 20000);
                if ($newHome->getSoldier() > $newHome->getSoldierMax()) {
                    $newHome->setSoldier($newHome->getSoldierMax());
                }
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Un bunker a été détruit. Il provenait du Dirigeant " . $nuclear->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Un bunker a été détruit.");
            } elseif ($newHome->getCaserne() > 0) {
                $newHome->setCaserne($newHome->getCaserne() - 1);
                $newHome->setSoldierMax($newHome->getSoldierMax() - 2500);
                if ($newHome->getSoldier() > $newHome->getSoldierMax()) {
                    $newHome->setSoldier($newHome->getSoldierMax());
                }
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span> Une caserne a été détruite. Il provenait du Dirigeant " . $nuclear->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Une caserne a été détruite.");
            } else {
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span> Par chance votre planète n'avait aucune infrastructures ciblées. Il provenait du Dirigeant " . $nuclear->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Aucune infrastructure n'a été détruite.");
            }
            $em->persist($reportNuclearAtt);
            $em->persist($reportNuclearDef);
        }

        $tmpNoCdr = new DateTime();
        $tmpNoCdr->setTimezone(new DateTimeZone('Europe/Paris'));
        $tmpNoCdr->add(new DateInterval('PT' . 300 . 'S'));
        foreach ($fleetCdrs as $fleetCdr) {
            if ($fleetCdr->getUser()->getPoliticRecycleur() > 0) {
                $recycle = $fleetCdr->getRecycleur() * (1000 + ($fleetCdr->getUser()->getPoliticRecycleur() * 400));
            } else {
                $recycle = $fleetCdr->getRecycleur() * 1000;
            }
            $planetCdr = $fleetCdr->getPlanet();
            if ($fleetCdr->getCargoPlace() > ($fleetCdr->getCargoFull() + ($recycle * 2))) {
                if($planetCdr->getNbCdr() == 0 || $planetCdr->getWtCdr() == 0) {
                    $recycle = $recycle * 2;
                }
                if ($planetCdr->getNbCdr() > $recycle) {
                    $fleetCdr->setNiobium($fleetCdr->getNiobium() + $recycle);
                    $planetCdr->setNbCdr($planetCdr->getNbCdr() - $recycle);
                } else {
                    $fleetCdr->setNiobium($fleetCdr->getNiobium() + $planetCdr->getNbCdr());
                    $planetCdr->setNbCdr(0);
                }
                if ($planetCdr->getWtCdr() > $recycle) {
                    $fleetCdr->setWater($fleetCdr->getWater() + $recycle);
                    $planetCdr->setWtCdr($planetCdr->getWtCdr() - $recycle);
                } else {
                    $fleetCdr->setWater($fleetCdr->getWater() + $planetCdr->getWtCdr());
                    $planetCdr->setWtCdr(0);
                }
                if($planetCdr->getNbCdr() == 0 && $planetCdr->getWtCdr() == 0) {
                    $fleetCdr->setRecycleAt(null);
                    if ($fleetCdr->getCargoPlace() > ($fleetCdr->getCargoFull() + ($recycle * 2))) {
                        $reportRec = new Report();
                        $reportRec->setType('move');
                        $reportRec->setTitle("Votre flotte " . $fleetCdr->getName() . " a arrêté de recycler!");
                        $reportRec->setImageName("recycle_report.jpg");
                        $reportRec->setSendAt($now);
                        $reportRec->setUser($fleetCdr->getUser());
                        $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($fleetCdr->getUser()->getUsername());
                        $reportRec->setContent("Bonjour dirigeant " . $fleetCdr->getUser()->getUserName() . " votre flotte " . "<span><a href='/connect/gerer-flotte/" . $fleetCdr->getId() ."/" . $usePlanet->getId() . "'>" . $fleetCdr->getName() . "</a></span>" . " vient de terminer de recycler en " . "<span><a href='/connect/carte-spatiale/" . $fleetCdr->getPlanet()->getSector()->getPosition() ."/" . $fleetCdr->getPlanet()->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>" . $fleetCdr->getPlanet()->getSector()->getGalaxy()->getPosition() . ":" . $fleetCdr->getPlanet()->getSector()->getPosition() . ":" . $fleetCdr->getPlanet()->getPosition() . "</a></span>.");
                        $em->persist($reportRec);
                        $fleetCdr->getUser()->setViewReport(false);
                    }
                } else {
                    $fleetCdr->setRecycleAt($tmpNoCdr);
                }
            } else {
                if($planetCdr->getNbCdr() == 0 || $planetCdr->getWtCdr() == 0) {
                    $recycle = ($fleetCdr->getCargoPlace() >= ($fleetCdr->getCargoFull() + ($recycle * 2))) ? $recycle * 2 : (($fleetCdr->getCargoPlace() - $fleetCdr->getCargoFull()));
                } else {
                    $recycle = ($fleetCdr->getCargoPlace() >= ($fleetCdr->getCargoFull() + ($recycle * 2))) ? $recycle : (($fleetCdr->getCargoPlace() - $fleetCdr->getCargoFull()) / 2);
                }
                if ($planetCdr->getNbCdr() > $recycle) {
                    $fleetCdr->setNiobium($fleetCdr->getNiobium() + $recycle);
                    $planetCdr->setNbCdr($planetCdr->getNbCdr() - $recycle);
                } else {
                    $fleetCdr->setNiobium($fleetCdr->getNiobium() + $planetCdr->getNbCdr());
                    $planetCdr->setNbCdr(0);
                }
                if ($planetCdr->getWtCdr() > $recycle) {
                    $fleetCdr->setWater($fleetCdr->getWater() + $recycle);
                    $planetCdr->setWtCdr($planetCdr->getWtCdr() - $recycle);
                } else {
                    $fleetCdr->setWater($fleetCdr->getWater() + $planetCdr->getWtCdr());
                    $planetCdr->setWtCdr(0);
                }
                if ($planetCdr->getNbCdr() == 0 && $planetCdr->getWtCdr() == 0 || $fleetCdr->getCargoPlace() == $fleetCdr->getCargoFull()) {
                    $fleetCdr->setRecycleAt(null);
                } else {
                    $fleetCdr->setRecycleAt($tmpNoCdr);
                }
            }
            $quest = $fleetCdr->getUser()->checkQuests('recycle');
            if($quest) {
                $fleetCdr->getUser()->getRank()->setWarPoint($fleetCdr->getUser()->getRank()->getWarPoint() + $quest->getGain());
                $fleetCdr->getUser()->removeQuest($quest);
            }
        }

        foreach ($planets as $planet) {
            $build = $planet->getConstruct();
            if($build == 'destruct') {
            } elseif ($build == 'miner') {
                $planet->setMiner($planet->getMiner() + 1);
                $planet->setNbProduction($planet->getNbProduction() + ($planet->getMiner() * 1.06));
            } elseif ($build == 'extractor') {
                $planet->setExtractor($planet->getExtractor() + 1);
                $planet->setWtProduction($planet->getWtProduction() + ($planet->getExtractor() * 1.05));
            } elseif ($build == 'farm') {
                $planet->setFarm($planet->getFarm() + 1);
                $planet->setFdProduction($planet->getFdProduction() + ($planet->getFarm() * 1.15));
            } elseif ($build == 'aeroponicFarm') {
                $planet->setAeroponicFarm($planet->getAeroponicFarm() + 1);
                $planet->setFdProduction($planet->getFdProduction() + ($planet->getAeroponicFarm() * 1.20));
            } elseif ($build == 'niobiumStock') {
                $planet->setNiobiumStock($planet->getNiobiumStock() + 1);
                $planet->setNiobiumMax($planet->getNiobiumMax() + 5000000);
            } elseif ($build == 'waterStock') {
                $planet->setWaterStock($planet->getWaterStock() + 1);
                $planet->setWaterMax($planet->getWaterMax() + 5000000);
            } elseif ($build == 'silos') {
                $planet->setSilos($planet->getSilos() + 1);
                $planet->setFoodMax($planet->getFoodMax() + 5000000);
            } elseif ($build == 'city') {
                $planet->setCity($planet->getCity() + 1);
                $planet->setWorkerProduction($planet->getWorkerProduction() + 5.56);
                $planet->setWorkerMax($planet->getWorkerMax() + 25000);
                $quest = $planet->getUser()->checkQuests('build_city');
                if($quest) {
                    $planet->getUser()->getRank()->setWarPoint($planet->getUser()->getRank()->getWarPoint() + $quest->getGain());
                    $planet->getUser()->removeQuest($quest);
                }
            } elseif ($build == 'metropole') {
                $planet->setMetropole($planet->getMetropole() + 1);
                $planet->setWorkerProduction($planet->getWorkerProduction() + 8.32);
                $planet->setWorkerMax($planet->getWorkerMax() + 75000);
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
        }

        $nowReport = new DateTime();
        $nowReport->setTimezone(new DateTimeZone('Europe/Paris'));
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        foreach ($fleets as $fleet) {
            if ($fleet->getUser()->getMerchant() == 1) {
                $em->remove($fleet->getDestination());
                $em->remove($fleet);
            } else {
                $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($fleet->getUser()->getUsername());
                if (!$usePlanet) {
                    $em->remove($fleet);
                } else {
                    $newHome = $fleet->getDestination()->getPlanet();

                    $userFleet = $fleet->getUser();
                    $report = new Report();
                    $report->setType('move');
                    $report->setTitle("Votre flotte " . $fleet->getName() . " est arrivée");
                    $report->setImageName("travel_report.jpg");
                    $report->setSendAt($now);
                    $report->setUser($userFleet);
                    $report->setContent("Bonjour dirigeant " . $userFleet->getUserName() . " votre flotte " . "<span><a href='/connect/gerer-flotte/" . $fleet->getId() . "/" . $usePlanet->getId() . "'>" . $fleet->getName() . "</a></span>" . " vient d'arriver en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() . "/" . $newHome->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $newHome->getSector()->getGalaxy()->getPosition() . ":" . $newHome->getSector()->getPosition() . ":" . $newHome->getPosition() . "</a></span>.");
                    $userFleet->setViewReport(false);
                    $oldPlanet = $fleet->getPlanet();
                    $fleet->setFlightTime(null);
                    $fleet->setPlanet($newHome);
                    if ($fleet->getFlightType() != '2') {
                        $em->remove($fleet->getDestination());
                    }

                    $user = $fleet->getUser();
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
                        $allyF = 'war';
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
                        ->join('f.user', 'u')
                        ->leftJoin('u.ally', 'a')
                        ->where('f.planet = :planet')
                        ->andWhere('f.fightAt is not null')
                        ->andWhere('f.flightTime is null')
                        ->setParameters(['planet' => $newHome])
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
                        $user = $fleet->getUser();
                        $newPlanet = $fleet->getPlanet();

                        if ($fleet->getUser()->getZombie() == 1) {
                            $zbRegroups = $em->getRepository('App:Fleet')
                                ->createQueryBuilder('f')
                                ->where('f.planet = :planet')
                                ->andWhere('f.flightTime is null')
                                ->andWhere('f.user = :user')
                                ->andWhere('f.id != :fleet')
                                ->setParameters(['planet' => $newHome, 'user' => $fleet->getUser(), 'fleet' => $fleet->getId()])
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
                        }
                        if ($fleet->getFlightType() == '1' && $fleet->getUser()->getZombie() == 0) {
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
                                    $newWarPointS = round((((($fleet->getScientist() * 100) + ($fleet->getWorker() * 50) + ($fleet->getSoldier() * 10) + ($fleet->getWater() / 3) + ($fleet->getNiobium() / 6)) / 1000)) * (1 + ($user->getPoliticPdg() / 10)));
                                } else {
                                    $newWarPointS = round((($fleet->getScientist() * 100) + ($fleet->getWorker() * 50) + ($fleet->getSoldier() * 10) + ($fleet->getWater() / 3) + ($fleet->getNiobium() / 6)) / 1000);
                                }
                                if ($user->getPoliticMerchant() > 0) {
                                    $gainSell = (($fleet->getWater() * 0.25) + ($fleet->getSoldier() * 80) + ($fleet->getWorker() * 5) + ($fleet->getScientist() * 300) + ($fleet->getNiobium() * 0.10)) * (1 + ($fleet->getUser()->getPoliticMerchant() / 20));
                                } else {
                                    $gainSell = ($fleet->getWater() * 0.25) + ($fleet->getSoldier() * 80) + ($fleet->getWorker() * 5) + ($fleet->getScientist() * 300) + ($fleet->getNiobium() * 0.10);
                                }
                                $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre. Votre flotte " . $fleet->getName() . " est sur le chemin du retour.");
                                $em->persist($reportSell);
                                $user->setBitcoin($user->getBitcoin() + $gainSell);
                                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
                                $fleet->setNiobium(0);
                                $fleet->setWater(0);
                                $fleet->setSoldier(0);
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
                                if ($newPlanet->getSoldier() + $fleet->getSoldier() <= $newPlanet->getSoldierMax()) {
                                    $newPlanet->setSoldier($newPlanet->getSoldier() + $fleet->getSoldier());
                                    $fleet->setSoldier(0);
                                } else {
                                    $fleet->setSoldier($fleet->getSoldier() - ($newPlanet->getSoldierMax() - $newPlanet->getSoldier()));
                                    $newPlanet->setSoldier($newPlanet->getSoldierMax());
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
                            $fuser = $fleet->getUser();
                            if ($carburant <= $fuser->getBitcoin()) {
                                if ($fleet->getMotherShip()) {
                                    $speed = $fleet->getSpeed() - ($fleet->getSpeed() * 0.10);
                                } else {
                                    $speed = $fleet->getSpeed();
                                }
                                $distance = $speed * $base * 100;
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
                                $fuser->setBitcoin($user->getBitcoin() - $carburant);
                            }
                        } elseif ($fleet->getFlightType() == '3') {
                            if ($fleet->getColonizer() && $newPlanet->getUser() == null &&
                                $newPlanet->getEmpty() == false && $newPlanet->getMerchant() == false &&
                                $newPlanet->getCdr() == false && $fleet->getUser()->getColPlanets() < 26 &&
                                $fleet->getUser()->getColPlanets() <= ($user->getTerraformation() + 1 + $user->getPoliticColonisation())) {

                                $fleet->setColonizer($fleet->getColonizer() - 1);
                                $newPlanet->setUser($fleet->getUser());
                                if ($fleet->getUser()->getZombie() == 1) {
                                    $newPlanet->setName('Base Zombie');
                                    $newPlanet->setWorker(125000);
                                    $newPlanet->setSoldier(2500);
                                    $newPlanet->setSoldierMax(2500);
                                    $newPlanet->setCaserne(1);
                                } else {
                                    $newPlanet->setName('Colonie');
                                    $newPlanet->setSoldier(50);
                                }
                                $newPlanet->setNbColo(count($fleet->getUser()->getPlanets()) + 1);
                                $quest = $fleet->getUser()->checkQuests('colonize');
                                if ($quest) {
                                    $fleet->getUser()->getRank()->setWarPoint($fleet->getUser()->getRank()->getWarPoint() + $quest->getGain());
                                    $fleet->getUser()->removeQuest($quest);
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
                                $fleet->getUser()->setViewReport(false);
                                $em->persist($reportColo);
                            }
                        } elseif ($fleet->getFlightType() == '4') {
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
                                    $aMilitary = $aMilitary * (1 + ($fleet->getUser()->getPoliticSoldierAtt() / 10));
                                }
                            } else {
                                $this->addFlash("fail", "Vous ne disposez pas de barges d'invasions.");
                                return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
                            }

                            $defender = $fleet->getPlanet();
                            $userDefender = $fleet->getPlanet()->getUser();
                            $barbed = $userDefender->getBarbedAdv();
                            $dSoldier = $defender->getSoldier() > 0 ? ($defender->getSoldier() * 6) * $barbed : 0;
                            $dTanks = $defender->getTank() > 0 ? $defender->getTank() * 900 : 0;
                            $soldierDtmp = $defender->getSoldier();
                            $tankDtmp = $defender->getTank();
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

                            if ($fleet->getPlanet()->getUser() && $fleet->getAllianceUser() && $fleet->getUser()->getSigleAllied($dSigle) == NULL && $userDefender->getZombie() == 0) {
                                if($dMilitary >= $aMilitary) {
                                    $warPointDef = round($aMilitary);
                                    if ($userDefender->getPoliticPdg() > 0) {
                                        $warPointDef = round($warPointDef * (1 + ($userDefender->getPoliticPdg() / 10)));
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
                                        $diviser = (1 + ($userDefender->getPoliticTankDef() / 10)) * 900;
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
                                    $reportDef->setContent("Le dirigeant " . $fleet->getUser()->getUserName() . " a tenté de piller votre planète " . $defender->getName() . " en " . $defender->getSector()->getgalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . ". Il a échoué grâce a vos solides défenses. Vous avez éliminé <span class='text-vert'>" . number_format($soldierAtmp) . "</span> soldats et prit le contrôle des barges de l'attaquant.<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointDef) . "</span> points de Guerre.");
                                    $reportLoot->setTitle("Rapport de pillage : Défaite (attaque)");
                                    $reportLoot->setImageName("invade_lose_report.jpg");
                                    $reportLoot->setContent("Le dirigeant " . $userDefender->getUserName() . " vous attendait de pieds fermes. Sa planète " . $defender->getName() . " en " . $defender->getSector()->getgalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . " était trop renforcée pour vous. Vous tué tout de même <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks. Tous vos soldats sont morts et vos barges sont resté sur la planète.<br>La prochaine fois, préparez votre attaque commandant.");
                                } else {
                                    $warPointAtt = round($soldierDtmp?$soldierDtmp:1 + $tankDtmp);
                                    if ($fleet->getUser()->getPoliticPdg() > 0) {
                                        $warPointAtt = round($warPointAtt * (1 + ($fleet->getUser()->getPoliticPdg() / 10)));
                                    }
                                    $diviser = (1 + ($fleet->getUser()->getPoliticSoldierAtt() / 10)) * 6;
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
                                    $fleet->getUser()->getRank()->setWarPoint($fleet->getUser()->getRank()->getWarPoint() + $warPointAtt);
                                    $reportDef->setTitle("Rapport de pillage : Défaite (défense)");
                                    $reportDef->setImageName("defend_lose_report.jpg");
                                    $reportDef->setContent("Le dirigeant " . $fleet->getUser()->getUserName() . " vient de piller (" . number_format(round($niobium)) . " niobiums" . number_format(round($water)) . " eaux" . number_format(round($uranium)) . " uraniums) votre planète " . $defender->getName() . " - " . $defender->getSector()->getgalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . ".  " . number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminé. C'est toujours ça de gagner. Vos <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) ."</span> tanks. Votre économie en a prit un coup, mais si vous étiez là pour planter des choux ça se serait ! Préparez la contre-attaque !");
                                    $reportLoot->setTitle("Rapport de pillage : Victoire (attaque)");
                                    $reportLoot->setImageName("invade_win_report.jpg");
                                    $reportLoot->setContent("Vos soldats ont finit de charger vos cargos ( <span class='text-vert'>" . number_format(round($niobium)) . " niobiums - " . number_format(round($water)) . " eaux - " . number_format(round($uranium)) . " uraniums </span>) et remontent dans les barges, le pillage de la planète " . $defender->getName() . " en " . $defender->getSector()->getgalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . " s'est bien passé. Vos pertes sont de <span class='text-rouge'>" . number_format(round($soldierAtmp)) . "</span> soldats. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks.<br>Vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre.");
                                    $quest = $fleet->getUser()->checkQuests('loot');
                                    if($quest) {
                                        $fleet->getUser()->getRank()->setWarPoint($fleet->getUser()->getRank()->getWarPoint() + $quest->getGain());
                                        $fleet->getUser()->removeQuest($quest);
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
                            $userDefender= $fleet->getPlanet()->getUser();
                            $barbed = $userDefender->getBarbedAdv();
                            $dSoldier = $defender->getSoldier() > 0 ? ($defender->getSoldier() * 6) * $barbed : 0;
                            $dTanks = $defender->getTank() > 0 ? $defender->getTank() * 900 : 0;
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
                                $dWorker = $dWorker / 10;
                                $dSoldier = $dSoldier / 5;
                                $dTanks = $dTanks / 2;
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
                                            $warPointDef = round($warPointDef * (1 + ($user->getPoliticPdg() / 10)));
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
                                            $diviser = (1 + ($userDefender->getPoliticTankDef() / 10)) * 900;
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
                                        $reportInv->setContent("Vous pensiez partir pour une promenade de santé mais la réalité vous rattrape vite... Vous avez envoyé tout vos soldats au casse-pipe.<br>Pire, vous avez attirer l'attention des zombies et fait monter la menace de 10 points ! Vous avez interêt a prendre vite " . $defender->getName() . " en (" . $defender->getSector()->getgalaxy()->getPosition() . "." . $defender->getSector()->getPosition() . "." . $defender->getPosition() . ") sinon votre Empire ne tiendra pas longtemps. Vous avez tué <span class='text-vert'>" . number_format(round($soldierDtmp + ($workerDtmp / 6) + ($tankDtmp * 900))) . "</span> zombies. Tous vos soldats sont morts et vos barges se sont égarés sur la planète.<br>N'abandonnez pas et sortez vos tripes !");
                                        $user->setZombieAtt($user->getZombieAtt() + 10);
                                    } else {
                                        $reportDef->setTitle("Rapport d'invasion : Victoire (défense)");
                                        $reportDef->setImageName("defend_win_report.jpg");
                                        $reportDef->setContent("Bien joué ! Vos travailleurs et soldats ont repoussé l'invasion du joueur " . $user->getUserName() . " sur votre planète " . $defender->getName() . " - (" . $defender->getSector()->getgalaxy()->getPosition() . "." . $defender->getSector()->getPosition() . "." . $defender->getPosition() . ") .  <span class='text-vert'>" . number_format($soldierAtmp) . "</span> soldats vous ont attaqué, tous ont été tué. Vous avez ainsi prit le contrôle des barges de l'attaquant.<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointDef) . "</span> points de Guerre.");
                                        $reportInv->setTitle("Rapport d'invasion : Défaite (attaque)");
                                        $reportInv->setImageName("invade_lose_report.jpg");
                                        $reportInv->setContent("'AH AH AH AH' le rire de " . $userDefender->getUserName() . " résonne à vos oreilles d'un curieuse façon. Votre sang bouillonne vous l'a vouliez cette planète. Qu'il rigole donc, vous reviendrez prendre " . $defender->getName() . " - (" . $defender->getSector()->getgalaxy()->getPosition() . "." . $defender->getSector()->getPosition() . "." . $defender->getPosition() . ") et ferez effacer des livres d'histoires son ridicule nom. Vous avez tout de même tué <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) . "</span> travailleurs à l'ennemi. Tous vos soldats sont morts et vos barges sont resté sur la planète.<br>Courage commandant.");
                                    }
                                } else {
                                    $warPointAtt = round(($soldierDtmp?$soldierDtmp:1 + ($workerDtmp / 10)) * 1);
                                    if ($user->getPoliticPdg() > 0) {
                                        $warPointAtt = round($warPointAtt * (1 + ($user->getPoliticPdg() / 10)));
                                    }
                                    $diviser = (1 + ($user->getPoliticSoldierAtt() / 10)) * $alea;
                                    $aMilitary = $aMilitary - $dMilitary;
                                    $fleet->setSoldier(abs($soldierAtmpTotal + round($aMilitary / $diviser)));
                                    $soldierAtmp = $fleet->getSoldier() - round($soldierAtmpTotal + $soldierAtmp);
                                    $defender->setSoldier(0);
                                    $defender->setTank(0);
                                    $defender->setWorker(2000);

                                    if($fleet->getUser()->getColPlanets() <= ($fleet->getUser()->getTerraformation() + 1 + $fleet->getUser()->getPoliticInvade()) && $userDefender->getZombie() == 0) {
                                        $fleet->getUser()->getRank()->setWarPoint($fleet->getUser()->getRank()->getWarPoint() + $warPointAtt);
                                        $defender->setUser($fleet->getUser());
                                        $em->flush();
                                        $reportDef->setTitle("Rapport d'invasion : Défaite (défense)");
                                        $reportDef->setImageName("defend_lose_report.jpg");
                                        $reportDef->setContent("Mais QUI ? QUI !!! Vous as donné un commandant si médiocre " . $defender->getUser()->getUserName() . " n'a pas eu a faire grand chose pour prendre votre planète " . $defender->getName() . " - " . $defender->getSector()->getgalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . ".  " . number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminé. C'est toujours ça de gagner. Vos <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-rouge'>" . number_format($workerDtmp) . "</span> travailleurs sont tous mort. Votre empire en a prit un coup, mais il vous reste des planètes, il est l'heure de la revanche !");
                                        $reportInv->setTitle("Rapport d'invasion : Victoire (attaque)");
                                        $reportInv->setImageName("invade_win_report.jpg");
                                        $reportInv->setContent("Vous débarquez après que la planète ait été prise et vous installez sur le trône de " . $userDefender->getUserName() . ". Qu'il est bon d'entendre ses pleures lointains... La planète " . $defender->getName() . " - " . $defender->getSector()->getgalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . " est désormais votre! Il est temps de remettre de l'ordre dans la galaxie. <span class='text-rouge'>" . number_format(round($soldierAtmp)) . "</span> de vos soldats ont péri dans l'invasion. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) . "</span> travailleurs ont péri. Cependant vous épargnez 2000 travailleurs dans votre bonté (surtout pour faire tourner la planète).<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre.");
                                    } else {
                                        $warPointAtt = $warPointAtt * 10;
                                        $fleet->getUser()->getRank()->setWarPoint($fleet->getUser()->getRank()->getWarPoint() + $warPointAtt);
                                        $hydra = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);
                                        if ($userDefender->getZombie() == 0) {
                                            $reportDef->setTitle("Rapport d'invasion : Défaite (défense)");
                                            $reportDef->setImageName("defend_lose_report.jpg");
                                            $reportDef->setContent("Mais QUI ? QUI !!! Vous as donné un commandant si médiocre " . $defender->getUser()->getUserName() . " n'a pas eu a faire grand chose pour prendre votre planète " . $defender->getName() . " - " . $defender->getSector()->getgalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . ".  " . number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminé. C'est toujours ça de gagner. Vos <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-rouge'>" . number_format($workerDtmp) . "</span> travailleurs sont tous mort. Votre empire en a prit un coup, mais il vous reste des planètes, il est l'heure de la revanche !");
                                            $reportInv->setTitle("Rapport d'invasion : Victoire (attaque)");
                                            $reportInv->setImageName("invade_win_report.jpg");
                                            $reportInv->setContent("Vous débarquez après que la planète ait été prise et vous installez sur le trône de " . $userDefender->getUserName() . ". Qu'il est bon d'entendre ses pleures lointains... La planète " . $defender->getName() . " - (" . $defender->getSector()->getgalaxy()->getPosition() . "." . $defender->getSector()->getPosition() . "." . $defender->getPosition() . ") est désormais votre! Il est temps de remettre de l'ordre dans la galaxie. <span class='text-rouge'>" . number_format(round($soldierAtmp)) . "</span> de vos soldats ont péri dans l'invasion. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) . "</span> travailleurs ont péri. Cependant vous épargnez 2000 travailleurs dans votre bonté (surtout pour faire tourner la planète).<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre.");
                                        } else {
                                            $reportInv->setTitle("Rapport contre attaque : Victoire");
                                            $reportInv->setImageName("zombie_win_report.jpg");
                                            $reportInv->setContent("Vos soldats débarquent sur la planète zombie et sorte l'artillerie lourde ! Les rues s'enlisent de mort mais l'entraînement prévaut sur la peur et vous purgez cette planète de cette peste macabre.<br> La planète " . $defender->getName() . " en (" . $defender->getSector()->getgalaxy()->getPosition() . "." . $defender->getSector()->getPosition() . "." . $defender->getPosition() . ") est désormais libre. Et votre indice d'attaque zombie est divisé par 10. Lors de l'assaut vous dénombrez <span class='text-rouge'>" . number_format(round($soldierAtmp)) . "</span> pertes parmis vos soldats. Mais vous avez exterminé <span class='text-vert'>" . number_format(round($soldierDtmp + ($workerDtmp / 6) + ($tankDtmp * 900))) . "</span> zombies ! <br>Et vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre.");
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
                                            $fleet->getUser()->setZombieAtt(round($fleet->getUser()->getZombieAtt() / 10));
                                            $defender->setName('Inhabitée');
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
                                        $userDefender->setGameOver($fleet->getUser()->getUserName());
                                        $userDefender->setGrade(null);
                                        foreach($userDefender->getFleets() as $tmpFleet) {
                                            $tmpFleet->setUser($fleet->getUser());
                                            $tmpFleet->setFleetList(null);
                                        }
                                    }
                                    $quest = $fleet->getUser()->checkQuests('invade');
                                    if($quest) {
                                        $fleet->getUser()->getRank()->setWarPoint($fleet->getUser()->getRank()->getWarPoint() + $quest->getGain());
                                        $fleet->getUser()->removeQuest($quest);
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
                        }
                    } else {
                        if ($fleet->getUser()->getZombie() == 0) {
                            $em->persist($report);
                        }
                    }
                }
            }
        }
        $em->flush();
        exit;
    }

    /**
     * @Route("/repare/", name="repare_it")
     */
    public function repareAction()
    {

        $em = $this->getDoctrine()->getManager();
        $bots = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->where('u.bot = true')
            ->getQuery()
            ->getResult();

        $date = new DateTime("first day of last month");
        foreach ($bots as $bot) {
            $bot->setCreatedAt($date->setDate(2019, rand(11, 12), rand(1, 30)));
            $bot->setDailyConnect($date->setDate(2019, rand(11, 12), rand(1, 30)));
        }
        $em->flush();
        exit;
    }
}
