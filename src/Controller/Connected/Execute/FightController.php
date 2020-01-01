<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Exchange;
use App\Entity\Report;

class FightController extends AbstractController
{
    public function fightAction($firstFleet, $now, $em)
    {
        $winner = null;

        $fleetsWars = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->where('p.id = :id')
            ->andWhere('f.flightTime is null')
            ->setParameters(['id' => $firstFleet['id']])
            ->orderBy('f.attack', 'ASC')
            ->getQuery()
            ->getResult();

        $teamBlock = [];
        $fleetsId = [];
        foreach ($fleetsWars as $fleetsWar) {
            if ($fleetsWar->getUser()->getAlly()) {
                if (in_array($fleetsWar->getUser()->getAlly()->getSigle(), $teamBlock) == false && $fleetsWar->getUser()->getAlly()->getSigleAlliedArray($teamBlock) == NULL &&
                    in_array($fleetsWar->getId(), $fleetsId) == false) {
                    $teamBlock[] = $fleetsWar->getUser()->getAlly()->getSigle();
                    $fleetsId[] = $fleetsWar->getId();
                }
            } elseif (in_array($fleetsWar->getUser()->getUserName(), $teamBlock) == false &&
                in_array($fleetsWar->getId(), $fleetsId) == false) {
                $teamBlock[] = $fleetsWar->getUser()->getUserName();
                $fleetsId[] = $fleetsWar->getId();
            }
        }
        $tmpcount = count($teamBlock);
        if ($tmpcount < 2) {
            foreach ($fleetsWars as $fleetsWar) {
                $fleetsWar->setFightAt(null);
                $fleetsWar->setSignature($fleetsWar->getNbrSignatures());
                $em->flush();
            }
            return new Response ('true');
        }
        $team = $tmpcount;
        $isAttack = [];

        while ($team > 0) {
            $team--;
            ${'oneBlock' . $team} = new \ArrayObject();
            foreach ($fleetsWars as $fleetsWar) {
                if ($fleetsWar->getUser()->getAlly()) {
                    if ($teamBlock[$team] == $fleetsWar->getUser()->getAlly()->getSigle() || $fleetsWar->getUser()->getAlly()->getSigleAllied($teamBlock[$team])) {
                        ${'oneBlock' . $team}->append($fleetsWar);
                        $isAttack[$team] = $fleetsWar->getAttack();
                    }
                } elseif ($teamBlock[$team] == $fleetsWar->getUser()->getUserName()) {
                    ${'oneBlock' . $team}->append($fleetsWar);
                    $isAttack[$team] = $fleetsWar->getAttack();
                }
            }
        }
        $team1 = $tmpcount - 1;
        $team2 = $tmpcount - 2;

        if ($isAttack[$team1] == true || $isAttack[$team2] == true) {
            $winner = self::attackAction(${'oneBlock' . $team1}, ${'oneBlock' . $team2}, $now, $em);
        } else {
            while ($isAttack[$team2--] == true) {
                $winner = self::attackAction(${'oneBlock' . $team1}, ${'oneBlock' . $team2}, $now, $em);
            }
        }
        if ($winner != null) {
            if ($winner == ${'oneBlock' . $team1}) {
                $team2 = $team2 - 1;
                ${'oneBlock' . $team1} = $winner;
            } elseif ($winner == ${'oneBlock' . $team2}) {
                $team1 = $team1 - $team2;
                ${'oneBlock' . $team2} = $winner;
            }
        } else {
            foreach ($fleetsWars as $fleetsWar) {
                $fleetsWar->setFightAt(null);
                $fleetsWar->setSignature($fleetsWar->getNbrSignatures());
                $em->flush();
            }
            return new Response ('true');
        }

        $team = $tmpcount - 2;
        while ($team > 0) {
            $winner = self::attackAction(${'oneBlock' . $team1}, ${'oneBlock' . $team2}, $now, $em);
            $team--;
            if ($winner == ${'oneBlock' . $team1}) {
                $team2 = $team2 - 1;
                ${'oneBlock' . $team1} = $winner;
            } elseif ($winner == ${'oneBlock' . $team2}) {
                $team1 = $team1 - $team2;
                ${'oneBlock' . $team2} = $winner;
            }
        }
        echo "Combat Spatial générés.<br/>";

        $em->flush();

        return new Response ('true');
      }


    public function attackAction($blockAtt, $blockDef, $now, $em)
    {
        $armor = 0;
        $shield = 0;
        $missile = 0;
        $laser = 0;
        $plasma = 0;
        $debrisAtt = 0;
        $armorD = 0;
        $shieldD = 0;
        $missileD = 0;
        $laserD = 0;
        $plasmaD = 0;
        $debrisDef = 0;
        $politicA = null;
        $politicB = null;
        $zombie = 0;

        foreach($blockAtt as $attacker) {
            if ($attacker->getUser()->getAlly()) {
                $politicA = $attacker->getUser()->getAlly()->getPolitic();
                if ($attacker->getUser()->getPoliticArmor() > 0) {
                    $armor = $armor + ($attacker->getArmor() * (1 + ($attacker->getUser()->getPoliticArmor() / 10)));
                } else {
                    $armor = $armor + $attacker->getArmor();
                }
                if ($attacker->getUser()->getPoliticArmement() > 0) {
                    $missile = $missile + ($attacker->getMissile() * (1 + ($attacker->getUser()->getPoliticArmement() / 10)));
                    $laser = $laser + ($attacker->getLaser() * (1 + ($attacker->getUser()->getPoliticArmement() / 10)));
                } else {
                    $missile = $missile + $attacker->getMissile();
                    $laser = $laser + $attacker->getLaser();
                }
            } else {
                $armor = $armor + $attacker->getArmor();
                $missile = $missile + $attacker->getMissile();
                $laser = $laser + $attacker->getLaser();
            }
            $plasma = $plasma + $attacker->getPlasma();
            $shield = $shield + $attacker->getShield();
            $debrisAtt = $debrisAtt + $attacker->getNbrSignatures() + $attacker->getCargoFull();
            $armeSaveA = $missile + $laser + $plasma;
            if ($attacker->getUser()->getZombie() == 1) {
                $zombie = 1;
            }
        }
        foreach($blockDef as $defender) {
            if ($defender->getUser()->getAlly()) {
                $politicB = $defender->getUser()->getAlly()->getPolitic();
                if ($defender->getUser()->getPoliticArmor() > 0) {
                    $armorD = $armorD + ($defender->getArmor() * (1 + ($defender->getUser()->getPoliticArmor() / 10)));
                } else {
                    $armorD = $armorD + $defender->getArmor();
                }
                if ($defender->getUser()->getPoliticArmement() > 0) {
                    $missileD = $missileD + ($defender->getUser()->getMissile() * (1 + ($defender->getUser()->getPoliticArmement() / 10)));
                    $laserD = $laserD + ($defender->getLaser() * (1 + ($defender->getUser()->getPoliticArmement() / 10)));
                } else {
                    $missileD = $missileD + $defender->getMissile();
                    $laserD = $laserD + $defender->getLaser();
                }
            } else {
                $armorD = $armorD + $defender->getArmor();
                $missileD = $missileD + $defender->getMissile();
                $laserD = $laserD + $defender->getLaser();
            }
            $shieldD = $shieldD + $defender->getShield();
            $plasmaD = $plasmaD + $defender->getPlasma();
            $debrisDef = $debrisDef + $defender->getNbrSignatures() + $defender->getCargoFull();
            $armeSaveB = $laserD + $plasmaD + $missileD;
            if ($defender->getUser()->getZombie() == 1) {
                $zombie = 1;
            }
        }
        if ($politicA && $politicB && $politicA != $politicB) {
            if ($politicA == 'fascim' && $politicB == 'communism') {
                $missileD = $missileD * 1.2;
                $laserD = $laserD * 1.2;
                $plasmaD = $plasmaD * 1.2;
            } elseif ($politicA == 'fascim' && $politicB == 'democrat') {
                $missile = $missile * 1.2;
                $laser = $laser * 1.2;
                $plasma = $plasma * 1.2;
            } elseif ($politicA == 'democrat' && $politicB == 'communism') {
                $missile = $missile * 1.2;
                $laser = $laser * 1.2;
                $plasma = $plasma * 1.2;
            } elseif ($politicA == 'democrat' && $politicB == 'fascim') {
                $missile = $missile * 1.2;
                $laser = $laser * 1.2;
                $plasma = $plasma * 1.2;
            } elseif ($politicA == 'communism' && $politicB == 'democrat') {
                $missile = $missile * 1.2;
                $laser = $laser * 1.2;
                $plasma = $plasma * 1.2;
            } elseif ($politicA == 'communism' && $politicB == 'fascim') {
                $missile = $missile * 1.2;
                $laser = $laser * 1.2;
                $plasma = $plasma * 1.2;
            }
        }

        $armorSaveA = $armor;
        $armorSaveD = $armorD;
        if ($zombie == 1) {
            $warPointA = round($armor / 800);
            $warPointB = round($armorD / 800);
        } else {
            $warPointA = round($armor / 80);
            $warPointB = round($armorD / 80);
        }
        $attAll = $missile + $laser + $plasma;
        $defAll = $missileD + $laserD + $plasmaD;

        if($attAll <= 0 && $defAll <= 0) {
            foreach($blockDef as $cancelAtt) {
                $cancelAtt->setAttack(0);
            }
            foreach($blockAtt as $cancelAtt) {
                $cancelAtt->setAttack(0);
            }
            $em->flush();
            return($blockAtt);
        }

        if($attAll > 0 && $defAll <= 0) {
            foreach($blockDef as $removeOne) {
                $reportLoseUtilA = new Report();
                $reportLoseUtilA->setType('fight');
                $reportLoseUtilA->setSendAt($now);
                $reportLoseUtilA->setImageName("f_lose_report.jpg");
                $reportLoseUtilA->setContent("Votre flotte utilitaire " . $removeOne->getName() . " ne dispose pas des technologies nécessaires à l'identification des vaisseaux ennemis en (" . $removeOne->getPlanet()->getSector()->getGalaxy()->getPosition() . "." . $removeOne->getPlanet()->getSector()->getPosition() . "." . $removeOne->getPlanet()->getPosition() . ") .");
                $reportLoseUtilA->setTitle("Rapport de combat : Défaite");
                $reportLoseUtilA->setUser($removeOne->getUser());
                $removeOne->getUser()->setViewReport(false);
                $em->persist($reportLoseUtilA);
                $em->remove($removeOne);
            }
            foreach($blockAtt as $reportWin) {
                $reportWinUtilA = new Report();
                $reportWinUtilA->setType('fight');
                $reportWinUtilA->setSendAt($now);
                $reportWinUtilA->setImageName("f_win_report.jpg");
                $reportWinUtilA->setContent("Vous venez de détruire une flotte utilitaire en (" . $reportWin->getPlanet()->getSector()->getGalaxy()->getPosition() . "." . $reportWin->getPlanet()->getSector()->getPosition() . "." . $reportWin->getPlanet()->getPosition() . ") .");
                $reportWinUtilA->setTitle("Rapport de combat : Victoire");
                $reportWin->getUser()->setViewReport(false);
                $reportWinUtilA->setUser($reportWin->getUser());
                $em->persist($reportWinUtilA);
                $planet = $reportWin->getPlanet();
            }
            $planet->setNbCdr($planet->getNbCdr() + ($debrisDef * rand(30,40)));
            $planet->setWtCdr($planet->getWtCdr() + $debrisDef * rand(20,30));
            $em->flush();
            return($blockAtt);
        }
        if($defAll > 0 && $attAll <= 0) {
            foreach($blockAtt as $removeTwo) {
                $reportLoseUtilB = new Report();
                $reportLoseUtilB->setType('fight');
                $reportLoseUtilB->setSendAt($now);
                $reportLoseUtilB->setImageName("f_lose_report.jpg");
                $reportLoseUtilB->setContent("Votre flotte utilitaire " . $removeTwo->getName() . " ne dispose pas des technologies nécessaires à l'identification des vaisseaux ennemis (" . $removeTwo->getPlanet()->getSector()->getGalaxy()->getPosition() . "." . $removeTwo->getPlanet()->getSector()->getPosition() . "." . $removeTwo->getPlanet()->getPosition() . ") .");
                $reportLoseUtilB->setTitle("Rapport de combat : Défaite");
                $reportLoseUtilB->setUser($removeTwo->getUser());
                $removeTwo->getUser()->setViewReport(false);
                $em->persist($reportLoseUtilB);
                $em->remove($removeTwo);
            }
            foreach($blockDef as $reportWin) {
                $reportWinUtilB = new Report();
                $reportWinUtilB->setType('fight');
                $reportWinUtilB->setSendAt($now);
                $reportWinUtilB->setImageName("f_win_report.jpg");
                $reportWinUtilB->setContent("Vous venez de détruire une flotte utilitaire en (" . $reportWin->getPlanet()->getSector()->getGalaxy()->getPosition() . "." . $reportWin->getPlanet()->getSector()->getPosition() . "." . $reportWin->getPlanet()->getPosition() . ") .");
                $reportWinUtilB->setTitle("Rapport de combat : Victoire");
                $reportWinUtilB->setUser($reportWin->getUser());
                $reportWin->getUser()->setViewReport(false);
                $em->persist($reportWinUtilB);
                $planet = $reportWin->getPlanet();
            }
            $planet->setNbCdr($planet->getNbCdr() + ($debrisAtt * rand(30,40)));
            $planet->setWtCdr($planet->getWtCdr() + $debrisAtt * rand(20,30));
            $em->flush();
            return($blockDef);
        }

        $firstBlood = (($missile / 4) + ($plasma * 2) + ($laser * 1.5));
        $firstBloodD = (($missileD / 4) + ($plasmaD * 2) + ($laserD * 1.5));
        $countSAtt = 0;
        $countSDef = 0;
        if(($firstBlood > 0) && $shieldD > 0) {
            while ($shieldD > 0) {
                $countSAtt++;
                $shieldD = $shieldD - $firstBlood;
            }
            $armorD = $armorD - $firstBlood;
        } elseif ($shieldD < 0) {
            $countSAtt = 1;
            $armorD = $armorD - $firstBlood;
        }
        if(($firstBloodD > 0) && $shield > 0) {
            while($shield > 0) {
                $countSDef++;
                $shield = $shield - $firstBloodD;
            }
            $armor = $armor - $firstBloodD;
        } elseif ($shieldD < 0) {
            $countSAtt = 1;
            $armor = $armor - $firstBloodD;
        }
        $secondShot = (($missile * 2) + ($plasma / 4) + ($laser * 1.5));
        $secondShotD = (($missileD * 2) + ($plasmaD / 4) + ($laserD * 1.5));
        if($countSDef - $countSAtt > 0) {
            $armorD = $armorD - ($secondShot * ($countSDef - $countSAtt));
            $secondShotD = (($missileD * 2) + ($plasmaD / 4) + ($laserD * 1.5));
        }
        if($countSAtt - $countSDef > 0) {
            $armor = $armor - ($secondShot * ($countSAtt - $countSDef));
            $secondShot = (($missile * 2) + ($plasma / 4) + ($laser * 1.5));
        }
        $countShot = 0;
        while($armorD > 0 && $armor > 0 && $countShot < 100) {
            $countShot++;
            if ($armorD > 0) {
                $armorD = $armorD - $secondShot;
                $secondShot = $secondShot - (($armor - $secondShotD) / 11);
            }
            if($armor > 0) {
                $armor = $armor - $secondShotD;
                $secondShotD = $secondShotD - (($armorD - $secondShot) / 11);
            }
        }

        if ($armorD > $armor || $shieldD > 0) {
            if($armorD * 1.1 < $armorSaveD) {
                $armorD = $armorD * (rand(11, 13) / 10);
            }
            if($armorD < 0) {
                $armorD = $armorSaveD / 20;
            }
            foreach($blockDef as $defenderWin) {
                $reportWinA = new Report();
                $reportWinA->setType('fight');
                $reportWinA->setSendAt($now);
                $reportWinA->setContent("<table class=\"table table-striped table-bordered table-dark\"><tbody><tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 1</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSDef . " tir(s) pour percer les boucliers</th></tr>");
                $reportWinA->setTitle("Rapport de combat : Victoire");
                $reportWinA->setUser($defenderWin->getUser());
                foreach ($blockDef as $fleetA) {
                    $player = $fleetA->getFleetTags();
                    if($armorSaveD != $armorD) {
                        $percentArmor = ($fleetA->getArmor() * 100) / $armorSaveD;
                        $newArmor = round($fleetA->getArmor() - (round($percentArmor * $armorD) / 100));
                        $ships = $fleetA->getShipsReport($newArmor);
                    } else {
                        $ships = $fleetA->getShipsReportNoLost();
                    }
                    $reportWinA->setContent($reportWinA->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $ships . "</th></tr>");
                }
                $reportWinA->setContent($reportWinA->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 2</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSAtt . " tir(s) pour percer les boucliers</th></tr>");
                foreach ($blockAtt as $fleetB) {
                    $player = $fleetB->getFleetTags();
                    $lose = $fleetB->getShipsLoseReport();
                    $reportWinA->setContent($reportWinA->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $lose . "</th></tr>");
                }
                $reportWinA->setContent($reportWinA->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $countShot . " rounds de combat.</th></tr></tbody></table>");
                $reportWinA->setContent($reportWinA->getContent() . "Vous avez gagné le combat en ("  . $defenderWin->getPlanet()->getSector()->getGalaxy()->getPosition() . "." . $defenderWin->getPlanet()->getSector()->getPosition() . "." . $defenderWin->getPlanet()->getPosition() . ") , vous remportez " . number_format($warPointA * 10) . " points de Guerre");
                $reportWinA->setImageName("fight_win_report.jpg");
                $defenderWin->getUser()->setViewReport(false);
                $quest = $defenderWin->getUser()->checkQuests('destroy_fleet');
                if($quest) {
                    $defenderWin->getUser()->getRank()->setWarPoint($defenderWin->getUser()->getRank()->getWarPoint() + $quest->getGain());
                    $defenderWin->getUser()->removeQuest($quest);
                }
                $em->persist($reportWinA);
            }
            foreach($blockAtt as $attackerLose) {
                $reportLoseA = new Report();
                $reportLoseA->setType('fight');
                $reportLoseA->setSendAt($now);
                $reportLoseA->setContent("<table class=\"table table-striped table-bordered table-dark\"><tbody><tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 1</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSAtt . " tir(s) pour percer les boucliers</th></tr>");
                $reportLoseA->setTitle("Rapport de combat : Défaite");
                $reportLoseA->setImageName("fight_lose_report.jpg");
                $reportLoseA->setUser($attackerLose->getUser());
                foreach ($blockAtt as $fleetB) {
                    $player = $fleetB->getFleetTags();
                    $lose = $fleetB->getShipsLoseReport();
                    $reportLoseA->setContent($reportLoseA->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $lose . "</th></tr>");
                }
                $reportLoseA->setContent($reportLoseA->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 2</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSDef . " tir(s) pour percer les boucliers</th></tr>");
                foreach ($blockDef as $fleetA) {
                    $player = $fleetA->getFleetTags();
                    if($armorSaveD != $armorD) {
                        $percentArmor = ($fleetA->getArmor() * 100) / $armorSaveD;
                        $newArmor = $fleetA->getArmor() - (round($percentArmor * $armorD) / 100);
                        $ships = $fleetA->getShipsReport($newArmor);
                    } else {
                        $ships = $fleetA->getShipsReportNoLost();
                    }
                    $reportLoseA->setContent($reportLoseA->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $ships . "</th></tr>");
                }
                $reportLoseA->setContent($reportLoseA->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $countShot . " rounds de combat.</th></tr></tbody></table>");
                $reportLoseA->setContent($reportLoseA->getContent() . "Vous avez perdu le combat en (" . $attackerLose->getPlanet()->getSector()->getGalaxy()->getPosition() . "." . $attackerLose->getPlanet()->getSector()->getPosition() . "." . $attackerLose->getPlanet()->getPosition() . ") , vos adversaires remportent " . number_format($warPointA * 10) . " points de Guerre.");
                $attackerLose->getUser()->setViewReport(false);
                $planet = $attackerLose->getPlanet();

                $loseArm = $attackerLose->getLaser() + $attackerLose->getMissile() + $attackerLose->getPlasma();
                $percentWarPoint = ($loseArm * 100) / $armeSaveA;
                $warPointB = ($warPointB - ($armorD / 80)) / 10;
                $newWarPoint = round(($percentWarPoint * $warPointB) / 10);
                if ($attackerLose->getUser()->getPoliticPdg() > 0) {
                    $newWarPoint = round($newWarPoint * (1 + ($attackerLose->getUser()->getPoliticPdg() / 10)));
                }
                if($newWarPoint < 0) {
                    $newWarPoint = $newWarPoint * -1;
                }
                if($attackerLose->getUser()->getPeaces()) {
                    $peace = $attackerLose->getUser()->getPeaces();
                    if ($peace->getPdg() > 0) {
                        $pdgPeace = round($newWarPoint * ($peace->getPdg() / 100));
                        $newWarPoint = $newWarPoint - $pdgPeace;
                        $otherAlly = $em->getRepository('App:Ally')
                            ->createQueryBuilder('a')
                            ->where('a.sigle = :sigle')
                            ->setParameter('sigle', $peace->getAllyTag())
                            ->getQuery()
                            ->getOneOrNullResult();

                        $otherAlly->setPdg($otherAlly->getPdg() + $pdgPeace);
                        $exchangeLoseA = new Exchange();
                        $exchangeLoseA->setAlly($otherAlly);
                        $exchangeLoseA->setCreatedAt($now);
                        $exchangeLoseA->setType(1);
                        $exchangeLoseA->setAmount($pdgPeace);
                        $exchangeLoseA->setAccepted(1);
                        $exchangeLoseA->setContent("Taxe liée à la paix.");
                        $exchangeLoseA->setName($attackerLose->getUser()->getUserName());
                        $em->persist($exchangeLoseA);
                        $reportLoseA->setContent($reportLoseA->getContent() . " Votre accord de paix ayant envoyé " . number_format($pdgPeace) . " points de Guerre à l'alliance [" . $otherAlly->getSigle() . "].");
                    }
                }
                $reportLoseA->setContent($reportLoseA->getContent() . " Mais vous remportez vous même " . number_format($newWarPoint) . " points de Guerre !");
                if($attackerLose->getUser()->getRank()) {
                    $attackerLose->getUser()->getRank()->setWarPoint($attackerLose->getUser()->getRank()->getWarPoint() + $newWarPoint);
                }
                $em->persist($reportLoseA);
                $em->remove($attackerLose);
            }
            foreach($blockDef as $defenderWin) {
                $defArm = $defenderWin->getLaser() + $defenderWin->getMissile() + $defenderWin->getPlasma();
                $percentWarPoint = ($defArm * 100) / $armeSaveB;
                $newWarPoint = round(($percentWarPoint * $warPointA) / 10);
                if ($defenderWin->getUser()->getPoliticPdg() > 0) {
                    $newWarPoint = round($newWarPoint * (1 + ($defenderWin->getUser()->getPoliticPdg() / 10)));
                }
                if($defenderWin->getUser()->getPeaces()) {
                    $peace = $defenderWin->getUser()->getPeaces();
                    if ($peace->getPdg() > 0) {
                        $pdgPeace = round($newWarPoint * ($peace->getPdg() / 100));
                        $newWarPoint = $newWarPoint - $pdgPeace;
                        $otherAlly = $em->getRepository('App:Ally')
                            ->createQueryBuilder('a')
                            ->where('a.sigle = :sigle')
                            ->setParameter('sigle', $peace->getAllyTag())
                            ->getQuery()
                            ->getOneOrNullResult();
                        $otherAlly->setPdg($otherAlly->getPdg() + $pdgPeace);
                        $exchangeWinA = new Exchange();
                        $exchangeWinA->setAlly($otherAlly);
                        $exchangeWinA->setCreatedAt($now);
                        $exchangeWinA->setType(1);
                        $exchangeWinA->setAmount($pdgPeace);
                        $exchangeWinA->setAccepted(1);
                        $exchangeWinA->setContent("Taxe liée à la paix.");
                        $exchangeWinA->setName($defenderWin->getUser()->getUserName());
                        $em->persist($exchangeWinA);
                    }
                }
                $percentArmor = ($defenderWin->getArmor() * 100) / $armorSaveD;
                $newArmor = $defenderWin->getArmor() - (round($percentArmor * $armorD) / 100);
                $defenderWin->setFleetWinRatio($newArmor);
                if($defenderWin->getUser()->getRank()) {
                    $defenderWin->getUser()->getRank()->setWarPoint($defenderWin->getUser()->getRank()->getWarPoint() + $newWarPoint);
                }
                $defenderWin->setFightAt(null);
                $defenderWin->setSignature($defenderWin->getNbrSignatures());
            }
            $planet->setNbCdr($planet->getNbCdr() + ($debrisAtt * rand(30,40)));
            $planet->setWtCdr($planet->getWtCdr() + $debrisAtt * rand(20,30));
            $em->flush();
            return($blockDef);
        } else {
            if($armor * 1.1 < $armorSaveA) {
                $armor = $armor * (rand(11, 13) / 10);
            }
            if($armor < 0) {
                $armor = $armorSaveA / 20;
            }
            foreach($blockAtt as $attackerWin) {
                $reportWinB = new Report();
                $reportWinB->setType('fight');
                $reportWinB->setSendAt($now);
                $reportWinB->setContent("<table class=\"table table-striped table-bordered table-dark\"><tbody><tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 1</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSDef . " tir(s) pour percer les boucliers</th></tr>");
                $reportWinB->setTitle("Rapport de combat : Victoire");
                $reportWinB->setImageName("fight_win_report.jpg");
                $reportWinB->setUser($attackerWin->getUser());
                foreach ($blockDef as $fleetA) {
                    $player = $fleetA->getFleetTags();
                    $lose = $fleetA->getShipsLoseReport();
                    $reportWinB->setContent($reportWinB->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $lose . "</th></tr>");
                }
                $reportWinB->setContent($reportWinB->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 2</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSAtt . " tir(s) pour percer les boucliers</th></tr>");
                foreach ($blockAtt as $fleetB) {
                    $player = $fleetB->getFleetTags();
                    if($armorSaveA != $armor) {
                        $percentArmor = ($fleetB->getArmor() * 100) / $armorSaveA;
                        $newArmor = $fleetB->getArmor() - (round($percentArmor * $armor) / 100);
                        $ships = $fleetB->getShipsReport($newArmor);
                    } else {
                        $ships = $fleetB->getShipsReportNoLost();
                    }
                    $reportWinB->setContent($reportWinB->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $ships . "</th></tr>");
                }
                $reportWinB->setContent($reportWinB->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $countShot . " rounds de combat.</th></tr></tbody></table>");
                $reportWinB->setContent($reportWinB->getContent() . "Vous avez gagné le combat en ("  . $attackerWin->getPlanet()->getSector()->getGalaxy()->getPosition() . "." . $attackerWin->getPlanet()->getSector()->getPosition() . "." . $attackerWin->getPlanet()->getPosition() . ") , vous remportez " . number_format($warPointB * 10) . " points de Guerre");
                $attackerWin->getUser()->setViewReport(false);
                $em->persist($reportWinB);
                $quest = $attackerWin->getUser()->checkQuests('destroy_fleet');
                if($quest) {
                    $attackerWin->getUser()->getRank()->setWarPoint($attackerWin->getUser()->getRank()->getWarPoint() + $quest->getGain());
                    $attackerWin->getUser()->removeQuest($quest);
                }
            }
            foreach($blockDef as $defenderLose) {
                $reportLoseB = new Report();
                $reportLoseB->setType('fight');
                $reportLoseB->setSendAt($now);
                $reportLoseB->setContent("<table class=\"table table-striped table-bordered table-dark\"><tbody><tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 1</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSAtt . " tir(s) pour percer les boucliers</th></tr>");
                $reportLoseB->setTitle("Rapport de combat : Défaite");
                $reportLoseB->setImageName("fight_lose_report.jpg");
                $reportLoseB->setUser($defenderLose->getUser());
                foreach ($blockAtt as $fleetB) {
                    $player = $fleetB->getFleetTags();
                    if($armorSaveA != $armor) {
                        $percentArmor = ($fleetB->getArmor() * 100) / $armorSaveA;
                        $newArmor = $fleetB->getArmor() - (round($percentArmor * $armor) / 100);
                        $ships = $fleetB->getShipsReport($newArmor);
                    } else {
                        $ships = $fleetB->getShipsReportNoLost();
                    }
                    $reportLoseB->setContent($reportLoseB->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $ships . "</th></tr>");
                }
                $reportLoseB->setContent($reportLoseB->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 2</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSDef . " tir(s) pour percer les boucliers</th></tr>");
                foreach ($blockDef as $fleetA) {
                    $player = $fleetA->getFleetTags();
                    $lose = $fleetA->getShipsLoseReport();
                    $reportLoseB->setContent($reportLoseB->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $lose . "</th></tr>");
                }
                $reportLoseB->setContent($reportLoseB->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $countShot . " rounds de combat.</th></tr></tbody></table>");
                $reportLoseB->setContent($reportLoseB->getContent() . "Vous avez perdu le combat en (" . $defenderLose->getPlanet()->getSector()->getGalaxy()->getPosition() . "." . $defenderLose->getPlanet()->getSector()->getPosition() . "." . $defenderLose->getPlanet()->getPosition() . ") , vos adversaires remportent " . number_format($warPointB * 10) . " points de Guerre.");
                $defenderLose->getUser()->setViewReport(false);
                $planet = $defenderLose->getPlanet();
                $loseArm = $defenderLose->getLaser() + $defenderLose->getMissile() + $defenderLose->getPlasma();
                $percentWarPoint = ($loseArm * 100) / $armeSaveB;
                $warPointA = ($warPointA - ($armor / 80)) / 10;
                $newWarPoint = round(($percentWarPoint * $warPointA) / 10);
                if ($defenderLose->getUser()->getPoliticPdg() > 0) {
                    $newWarPoint = round($newWarPoint * (1 + ($defenderLose->getUser()->getPoliticPdg() / 10)));
                }
                if($newWarPoint < 0) {
                    $newWarPoint = $newWarPoint * -1;
                }
                if($defenderLose->getUser()->getPeaces()) {
                    $peace = $defenderLose->getUser()->getPeaces();
                    if ($peace->getPdg() > 0) {
                        $pdgPeace = round($newWarPoint * ($peace->getPdg() / 100));
                        $newWarPoint = $newWarPoint - $pdgPeace;
                        $otherAlly = $em->getRepository('App:Ally')
                            ->createQueryBuilder('a')
                            ->where('a.sigle = :sigle')
                            ->setParameter('sigle', $peace->getAllyTag())
                            ->getQuery()
                            ->getOneOrNullResult();
                        $otherAlly->setPdg($otherAlly->getPdg() + $pdgPeace);
                        $exchangeLoseB = new Exchange();
                        $exchangeLoseB->setAlly($otherAlly);
                        $exchangeLoseB->setCreatedAt($now);
                        $exchangeLoseB->setType(1);
                        $exchangeLoseB->setAmount($pdgPeace);
                        $exchangeLoseB->setAccepted(1);
                        $exchangeLoseB->setContent("Taxe liée à la paix.");
                        $exchangeLoseB->setName($defenderLose->getUser()->getUserName());
                        $em->persist($exchangeLoseB);
                        $reportLoseB->setContent($reportWinB->getContent() . " Votre accord de paix ayant envoyé " . number_format($pdgPeace) . " points de Guerre à l'alliance [" . $otherAlly->getSigle() . "].");
                    }
                }
                $reportLoseB->setContent($reportWinB->getContent() . " Mais vous remportez vous même " . number_format($newWarPoint) . " points de Guerre !");
                if($defenderLose->getUser()->getRank()) {
                    $defenderLose->getUser()->getRank()->setWarPoint($defenderLose->getUser()->getRank()->getWarPoint() + $newWarPoint);
                }
                $em->persist($reportLoseB);
                $em->remove($defenderLose);
            }
            foreach($blockAtt as $attackerWin) {
                $attArm = $attackerWin->getLaser() + $attackerWin->getMissile() + $attackerWin->getPlasma();
                $percentWarPoint = ($attArm * 100) / $armeSaveA;
                $newWarPoint = round(($percentWarPoint * $warPointB) / 10);
                if ($attackerWin->getUser()->getPoliticPdg() > 0) {
                    $newWarPoint = round($newWarPoint * (1 + ($attackerWin->getUser()->getPoliticPdg() / 10)));
                }
                if($attackerWin->getUser()->getPeaces()) {
                    $peace = $attackerWin->getUser()->getPeaces();
                    if ($peace->getPdg() > 0) {
                        $pdgPeace = round($newWarPoint * ($peace->getPdg() / 100));
                        $newWarPoint = $newWarPoint - $pdgPeace;
                        $otherAlly = $em->getRepository('App:Ally')
                            ->createQueryBuilder('a')
                            ->where('a.sigle = :sigle')
                            ->setParameter('sigle', $peace->getAllyTag())
                            ->getQuery()
                            ->getOneOrNullResult();
                        $otherAlly->setPdg($otherAlly->getPdg() + $pdgPeace);
                        $otherAlly->setPdg($otherAlly->getPdg() + $pdgPeace);
                        $exchangeWinB = new Exchange();
                        $exchangeWinB->setAlly($otherAlly);
                        $exchangeWinB->setCreatedAt($now);
                        $exchangeWinB->setType(1);
                        $exchangeWinB->setAmount($pdgPeace);
                        $exchangeWinB->setAccepted(1);
                        $exchangeWinB->setContent("Taxe liée à la paix.");
                        $exchangeWinB->setName($attackerWin->getUser()->getUserName());
                        $em->persist($exchangeWinB);
                    }
                }
                $percentArmor = ($attackerWin->getArmor() * 100) / $armorSaveA;
                $newArmor = $attackerWin->getArmor() - (round($percentArmor * $armor) / 100);
                $attackerWin->setFleetWinRatio($newArmor);
                if($attackerWin->getUser()->getRank()) {
                    $attackerWin->getUser()->getRank()->setWarPoint($attackerWin->getUser()->getRank()->getWarPoint() + $newWarPoint);
                }
                $attackerWin->setFightAt(null);
                $attackerWin->setSignature($attackerWin->getNbrSignatures());
            }
            $planet->setNbCdr($planet->getNbCdr() + ($debrisDef * rand(30,40)));
            $planet->setWtCdr($planet->getWtCdr() + $debrisDef * rand(20,30));
            $em->flush();
            return($blockAtt);
        }
        return NULL;
    }
}