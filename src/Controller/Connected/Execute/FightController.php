<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Exchange;
use App\Entity\Report;

/**
 * Class FightController
 * @package App\Controller\Connected\Execute
 */
class FightController extends AbstractController
{
    /**
     * @param $firstFleet
     * @param $now
     * @param $em
     * @return Response
     */
    public function fightAction($firstFleet, $now, $em)
    {
        $winner = null;

        $demoFleets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->where('p.id = :id')
            ->andWhere('f.flightTime is null')
            ->setParameters(['id' => $firstFleet['id']])
            ->orderBy('f.signature', 'DESC')
            ->getQuery()
            ->getResult();

        foreach ($demoFleets as $demoFleet) {
            $fleetRegroups = $em->getRepository('App:Fleet')
                ->createQueryBuilder('f')
                ->join('f.planet', 'p')
                ->where('p.id = :id')
                ->andWhere('f.flightTime is null')
                ->andWhere('f.character = :character')
                ->setParameters(['id' => $firstFleet['id'], 'character' => $demoFleet->getCharacter()])
                ->orderBy('f.signature', 'DESC')
                ->getQuery()
                ->getResult();

            if (count($fleetRegroups) > 1) {
                echo "Regroupement Flottes : ";
                $cronValue = $this->forward('App\Controller\Connected\Execute\FleetsController::oneFleetAction', [
                    'fleetRegroups'  => $fleetRegroups,
                    'demoFleet'  => $demoFleet,
                    'em'  => $em
                ]);
                echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
            }
        }

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
            if ($fleetsWar->getCharacter()->getAlly()) {
                if (in_array($fleetsWar->getCharacter()->getAlly()->getSigle(), $teamBlock) == false && $fleetsWar->getCharacter()->getAlly()->getSigleAlliedArray($teamBlock) == null &&
                    in_array($fleetsWar->getId(), $fleetsId) == false) {
                    $teamBlock[] = $fleetsWar->getCharacter()->getAlly()->getSigle();
                    $fleetsId[] = $fleetsWar->getId();
                }
            } elseif (in_array($fleetsWar->getCharacter()->getUsername(), $teamBlock) == false &&
                in_array($fleetsWar->getId(), $fleetsId) == false) {
                $teamBlock[] = $fleetsWar->getCharacter()->getUsername();
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
                if ($fleetsWar->getCharacter()->getAlly()) {
                    if ($teamBlock[$team] == $fleetsWar->getCharacter()->getAlly()->getSigle() || $fleetsWar->getCharacter()->getAlly()->getSigleAllied($teamBlock[$team])) {
                        ${'oneBlock' . $team}->append($fleetsWar);
                        $isAttack[$team] = $fleetsWar->getAttack();
                    }
                } elseif ($teamBlock[$team] == $fleetsWar->getCharacter()->getUsername()) {
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
        echo "Flush ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
      }


    /**
     * @param $blockAtt
     * @param $blockDef
     * @param $now
     * @param $em
     * @return |null
     */
    public function attackAction($blockAtt, $blockDef, $now, $em)
    {
        $armor = 0;
        $shield = 0;
        $missile = 0;
        $laser = 0;
        $plasma = 0;
        $debrisAttNiobium = 0;
        $debrisAttWater = 0;
        $armorD = 0;
        $shieldD = 0;
        $missileD = 0;
        $laserD = 0;
        $plasmaD = 0;
        $debrisDefNiobium = 0;
        $debrisDefWater = 0;
        $politicA = null;
        $politicB = null;
        $zombie = 0;
        $nbrBlockDef = 0;
        $nbrBlockAtt = 0;

        foreach($blockAtt as $attacker) {
            $nbrBlockAtt = $nbrBlockAtt + 1;
            if ($attacker->getCharacter()->getAlly()) {
                $politicA = $attacker->getCharacter()->getAlly()->getPolitic();
                if ($attacker->getCharacter()->getPoliticArmor() > 0) {
                    $armor = $armor + ($attacker->getArmor() * (1 + ($attacker->getCharacter()->getPoliticArmor() / 10)));
                } else {
                    $armor = $armor + $attacker->getArmor();
                }
                if ($attacker->getCharacter()->getPoliticArmement() > 0) {
                    $missile = $missile + ($attacker->getMissile() * (1 + ($attacker->getCharacter()->getPoliticArmement() / 10)));
                    $laser = $laser + ($attacker->getLaser() * (1 + ($attacker->getCharacter()->getPoliticArmement() / 10)));
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
            $debrisAttNiobium = $debrisAttNiobium + ($attacker->getNbrSignatures() * rand(15,20)) + $attacker->getCargoFull();
            $debrisAttWater = $debrisAttWater + ($attacker->getNbrSignatures() * rand(10,15)) + $attacker->getCargoFull();
            if ($attacker->getCharacter()->getId() == 1) {
                $zombie = 1;
            }
        }
        foreach($blockDef as $defender) {
            $nbrBlockDef = $nbrBlockDef + 1;
            if ($defender->getCharacter()->getAlly()) {
                $politicB = $defender->getCharacter()->getAlly()->getPolitic();
                if ($defender->getCharacter()->getPoliticArmor() > 0) {
                    $armorD = $armorD + ($defender->getArmor() * (1 + ($defender->getCharacter()->getPoliticArmor() / 10)));
                } else {
                    $armorD = $armorD + $defender->getArmor();
                }
                if ($defender->getCharacter()->getPoliticArmement() > 0) {
                    $missileD = $missileD + ($defender->getMissile() * (1 + ($defender->getCharacter()->getPoliticArmement() / 10)));
                    $laserD = $laserD + ($defender->getLaser() * (1 + ($defender->getCharacter()->getPoliticArmement() / 10)));
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
            $debrisDefNiobium = $debrisDefNiobium + ($defender->getNbrSignatures() * rand(15,20)) + $defender->getNiobium();
            $debrisDefWater = $debrisDefWater + ($defender->getNbrSignatures() * rand(10,15)) + $defender->getWater();
            if ($defender->getCharacter()->getId() == 1) {
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
        if($attAll > 0 && $defAll < 1) {
            echo "BA Util Win : ";
            $cronValue = self::utilitFightAction($blockDef, $blockAtt, $debrisDefNiobium, $debrisDefWater, $now, $em);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
            return($blockAtt);
        }
        if($defAll > 0 && $attAll < 1) {
            echo "BB Util Win : ";
            $cronValue = self::utilitFightAction($blockDef, $blockAtt, $debrisAttNiobium, $debrisAttWater, $now, $em);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
            return($blockDef);
        }

        $firstBlood = (($missile / 7) + ($plasma * 4) + ($laser * 2));
        $firstBloodD = (($missileD / 7) + ($plasmaD * 4) + ($laserD * 2));
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
        $secondShot = ($missile + ($plasma / 2) + ($laser * 2));
        $secondShotD = ($missileD + ($plasmaD / 2) + ($laserD * 2));
        if($countSDef - $countSAtt > 0) {
            $armorD = $armorD - ($secondShot * ($countSDef - $countSAtt));
            $secondShotD = ($missileD + ($plasmaD / 2) + ($laserD * 2));
        }
        if($countSAtt - $countSDef > 0) {
            $armor = $armor - ($secondShot * ($countSAtt - $countSDef));
            $secondShot = ($missile + ($plasma / 3) + ($laser * 2));
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

        if ($zombie == 1) {
            $warPointA = round((($armorSaveA - ($armor > 1 ? $armor : 0)) / 65) / $nbrBlockDef);
            $warPointB = round((($armorSaveD - ($armorD > 1 ? $armorD : 0)) / 65) / $nbrBlockAtt);
        } else {
            $warPointA = round((($armorSaveA - ($armor > 1 ? $armor : 0)) / 10) / $nbrBlockDef);
            $warPointB = round((($armorSaveD - ($armorD > 1 ? $armorD : 0)) / 10) / $nbrBlockAtt);
        }

        if ($armorD > $armor) {
            if($armorD * 1.1 < $armorSaveD) {
                $armorD = $armorD * (rand(11, 13) / 10);
            }
            if($armorD < 0) {
                $armorD = $armorSaveD / 20;
            }
            foreach($blockDef as $defenderWin) {
                $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($defenderWin->getCharacter());
                $reportWinDef = new Report();
                $reportWinDef->setType('fight');
                $reportWinDef->setSendAt($now);
                $reportWinDef->setContent("<table class=\"table table-striped table-bordered table-dark\"><tbody><tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 1</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSDef . " tir(s) pour percer les boucliers</th></tr>");
                $reportWinDef->setTitle("Rapport de combat : Victoire");
                $reportWinDef->setCharacter($defenderWin->getCharacter());
                foreach ($blockDef as $fleetA) {
                    $player = $fleetA->getFleetTags($usePlanet);
                    if($armorSaveD != $armorD) {
                        $percentArmor = ($fleetA->getArmor() * 100) / $armorSaveD;
                        $newArmor = round($fleetA->getArmor() - (round($percentArmor * $armorD) / 100));
                        $ships = $fleetA->getShipsReport($newArmor);
                    } else {
                        $ships = $fleetA->getShipsReportNoLost();
                    }
                    $reportWinDef->setContent($reportWinDef->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $ships . "</th></tr>");
                }
                $reportWinDef->setContent($reportWinDef->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 2</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSAtt . " tir(s) pour percer les boucliers</th></tr>");
                foreach ($blockAtt as $fleetB) {
                    $player = $fleetB->getFleetTags(null);
                    $lose = $fleetB->getShipsLoseReport();
                    $reportWinDef->setContent($reportWinDef->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $lose . "</th></tr>");
                }
                $reportWinDef->setContent($reportWinDef->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $countShot . " rounds de combat.</th></tr></tbody></table>");
                if ($usePlanet) {
                    $reportWinDef->setContent($reportWinDef->getContent() . "Vous avez gagné le combat en (" . "<span><a href='/connect/carte-spatiale/" . $defenderWin->getPlanet()->getSector()->getPosition() . "/" . $defenderWin->getPlanet()->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $defenderWin->getPlanet()->getSector()->getGalaxy()->getPosition() . ":" . $defenderWin->getPlanet()->getSector()->getPosition() . ":" . $defenderWin->getPlanet()->getPosition() . "</a></span>) , vous remportez <span class='text-vert'>+" . number_format($warPointA) . "</span> points de Guerre");
                }
                $reportWinDef->setImageName("fight_win_report.jpg");
                $defenderWin->getCharacter()->setViewReport(false);
                $quest = $defenderWin->getCharacter()->checkQuests('destroy_fleet');
                if($quest) {
                    $defenderWin->getCharacter()->getRank()->setWarPoint($defenderWin->getCharacter()->getRank()->getWarPoint() + $quest->getGain());
                    $defenderWin->getCharacter()->removeQuest($quest);
                }
                $em->persist($reportWinDef);
            }
            foreach($blockAtt as $attackerLose) {

                $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($attackerLose->getCharacter());
                $reportLoseA = new Report();
                $reportLoseA->setType('fight');
                $reportLoseA->setSendAt($now);
                $reportLoseA->setContent("<table class=\"table table-striped table-bordered table-dark\"><tbody><tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 1</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSAtt . " tir(s) pour percer les boucliers</th></tr>");
                $reportLoseA->setTitle("Rapport de combat : Défaite");
                $reportLoseA->setImageName("fight_lose_report.jpg");
                $reportLoseA->setCharacter($attackerLose->getCharacter());
                foreach ($blockAtt as $fleetB) {
                    $player = $fleetB->getFleetTags(null);
                    $lose = $fleetB->getShipsLoseReport();
                    $reportLoseA->setContent($reportLoseA->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $lose . "</th></tr>");
                }
                $reportLoseA->setContent($reportLoseA->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 2</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSDef . " tir(s) pour percer les boucliers</th></tr>");
                foreach ($blockDef as $fleetA) {
                    $player = $fleetA->getFleetTags($usePlanet);
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
                if ($usePlanet) {
                    $reportLoseA->setContent($reportLoseA->getContent() . "Vous avez perdu le combat en (" . "<span><a href='/connect/carte-spatiale/" . $attackerLose->getPlanet()->getSector()->getPosition() . "/" . $attackerLose->getPlanet()->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $attackerLose->getPlanet()->getSector()->getGalaxy()->getPosition() . ":" . $attackerLose->getPlanet()->getSector()->getPosition() . ":" . $attackerLose->getPlanet()->getPosition() . "</a></span>) , vos adversaires remportent " . number_format($warPointA) . " points de Guerre.");
                }
                $attackerLose->getCharacter()->setViewReport(false);
                $planet = $attackerLose->getPlanet();

                $newWarPoint = $warPointB / 10;
                if ($attackerLose->getCharacter()->getPoliticPdg() > 0) {
                    $newWarPoint = round($newWarPoint * (1 + ($attackerLose->getCharacter()->getPoliticPdg() / 10)));
                }
                if($attackerLose->getCharacter()->getPeaces()) {
                    $peace = $attackerLose->getCharacter()->getPeaces();
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
                        $exchangeLoseA = new Exchange($otherAlly, $attackerLose->getCharacter()->getCharacterName(), 1, 1, $pdgPeace, "Taxe liée à la paix.");
                        $em->persist($exchangeLoseA);
                        $reportLoseA->setContent($reportLoseA->getContent() . " Votre accord de paix ayant envoyé <span class='text-rouge'>-" . number_format($pdgPeace) . "</span> points de Guerre à l'alliance [" . $otherAlly->getSigle() . "].");
                    }
                }
                if ($newWarPoint > 0) {
                    $reportLoseA->setContent($reportLoseA->getContent() . " Mais vous remportez vous même <span class='text-vert'>+" . number_format($newWarPoint) . "</span> points de Guerre !");
                }
                if($attackerLose->getCharacter()->getRank()) {
                    $attackerLose->getCharacter()->getRank()->setWarPoint($attackerLose->getCharacter()->getRank()->getWarPoint() + $newWarPoint);
                }
                $em->persist($reportLoseA);
                $em->remove($attackerLose);
            }
            foreach($blockDef as $defenderWin) {
                $newWarPoint = $warPointA / 10;
                if ($defenderWin->getCharacter()->getPoliticPdg() > 0) {
                    $newWarPoint = round($newWarPoint * (1 + ($defenderWin->getCharacter()->getPoliticPdg() / 10)));
                }
                if($defenderWin->getCharacter()->getPeaces()) {
                    $peace = $defenderWin->getCharacter()->getPeaces();
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
                        $exchangeWinA = new Exchange($otherAlly, $defenderWin->getCharacter()->getCharacterName(), 1, 1, $pdgPeace, "Taxe liée à la paix.");
                        $em->persist($exchangeWinA);
                    }
                }
                $percentArmor = ($defenderWin->getArmor() * 100) / $armorSaveD;
                $newArmor = $defenderWin->getArmor() - (round($percentArmor * $armorD) / 100);
                $defenderWin->setFleetWinRatio(abs($newArmor));
                if($defenderWin->getCharacter()->getRank()) {
                    $defenderWin->getCharacter()->getRank()->setWarPoint($defenderWin->getCharacter()->getRank()->getWarPoint() + $newWarPoint);
                }
                $defenderWin->setFightAt(null);
                $defenderWin->setSignature($defenderWin->getNbrSignatures());
                if ($zombie == 1) {
                    $zombieDebris = 40;
                } else {
                    $zombieDebris = 1;
                }
            }
            $planet->setNbCdr(round($planet->getNbCdr() + ($debrisDefNiobium / $zombieDebris)));
            $planet->setWtCdr(round($planet->getWtCdr() + ($debrisDefWater / $zombieDebris)));
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
                $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($attackerWin->getCharacter());
                $reportWinAtt = new Report();
                $reportWinAtt->setType('fight');
                $reportWinAtt->setSendAt($now);
                $reportWinAtt->setContent("<table class=\"table table-striped table-bordered table-dark\"><tbody><tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 1</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSDef . " tir(s) pour percer les boucliers</th></tr>");
                $reportWinAtt->setTitle("Rapport de combat : Victoire");
                $reportWinAtt->setImageName("fight_win_report.jpg");
                $reportWinAtt->setCharacter($attackerWin->getCharacter());
                foreach ($blockDef as $fleetA) {
                    $player = $fleetA->getFleetTags(null);
                    $lose = $fleetA->getShipsLoseReport();
                    $reportWinAtt->setContent($reportWinAtt->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $lose . "</th></tr>");
                }
                $reportWinAtt->setContent($reportWinAtt->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 2</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSAtt . " tir(s) pour percer les boucliers</th></tr>");
                foreach ($blockAtt as $fleetB) {
                    $player = $fleetB->getFleetTags($usePlanet);
                    if($armorSaveA != $armor) {
                        $percentArmor = ($fleetB->getArmor() * 100) / $armorSaveA;
                        $newArmor = $fleetB->getArmor() - (round($percentArmor * $armor) / 100);
                        $ships = $fleetB->getShipsReport($newArmor);
                    } else {
                        $ships = $fleetB->getShipsReportNoLost();
                    }
                    $reportWinAtt->setContent($reportWinAtt->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $ships . "</th></tr>");
                }
                $reportWinAtt->setContent($reportWinAtt->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $countShot . " rounds de combat.</th></tr></tbody></table>");
                if ($usePlanet) {
                    $reportWinAtt->setContent($reportWinAtt->getContent() . "Vous avez gagné le combat en (" . "<span><a href='/connect/carte-spatiale/" . $attackerWin->getPlanet()->getSector()->getPosition() . "/" . $attackerWin->getPlanet()->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $attackerWin->getPlanet()->getSector()->getGalaxy()->getPosition() . ":" . $attackerWin->getPlanet()->getSector()->getPosition() . ":" . $attackerWin->getPlanet()->getPosition() . "</a></span>) , vous remportez <span class='text-vert'>+" . number_format($warPointB) . "</span> points de Guerre");
                }
                $attackerWin->getCharacter()->setViewReport(false);
                $em->persist($reportWinAtt);
                $quest = $attackerWin->getCharacter()->checkQuests('destroy_fleet');
                if($quest) {
                    $attackerWin->getCharacter()->getRank()->setWarPoint($attackerWin->getCharacter()->getRank()->getWarPoint() + $quest->getGain());
                    $attackerWin->getCharacter()->removeQuest($quest);
                }
            }
            foreach($blockDef as $defenderLose) {
                $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($defenderLose->getCharacter());
                $reportLoseB = new Report();
                $reportLoseB->setType('fight');
                $reportLoseB->setSendAt($now);
                $reportLoseB->setContent("<table class=\"table table-striped table-bordered table-dark\"><tbody><tr><th class=\"tab-cells-name p-1 ml-2\">Groupe de combat 1</th><th class=\"tab-cells-name p-1 ml-2\">" . $countSAtt . " tir(s) pour percer les boucliers</th></tr>");
                $reportLoseB->setTitle("Rapport de combat : Défaite");
                $reportLoseB->setImageName("fight_lose_report.jpg");
                $reportLoseB->setCharacter($defenderLose->getCharacter());
                foreach ($blockAtt as $fleetB) {
                    $player = $fleetB->getFleetTags(null);
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
                    $player = $fleetA->getFleetTags(null);
                    $lose = $fleetA->getShipsLoseReport();
                    $reportLoseB->setContent($reportLoseB->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $player . "</th><th class=\"tab-cells-name p-1 ml-2\">" . $lose . "</th></tr>");
                }
                $reportLoseB->setContent($reportLoseB->getContent() . "<tr><th class=\"tab-cells-name p-1 ml-2\">" . $countShot . " rounds de combat.</th></tr></tbody></table>");
                if ($usePlanet) {
                    $reportLoseB->setContent($reportLoseB->getContent() . "Vous avez perdu le combat en (" . "<span><a href='/connect/carte-spatiale/" . $defenderLose->getPlanet()->getSector()->getPosition() . "/" . $defenderLose->getPlanet()->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $defenderLose->getPlanet()->getSector()->getGalaxy()->getPosition() . ":" . $defenderLose->getPlanet()->getSector()->getPosition() . ":" . $defenderLose->getPlanet()->getPosition() . "</a></span>) , vos adversaires remportent " . number_format($warPointB) . " points de Guerre.");
                }
                $defenderLose->getCharacter()->setViewReport(false);
                $planet = $defenderLose->getPlanet();
                $newWarPoint = $warPointA / 10;
                if ($defenderLose->getCharacter()->getPoliticPdg() > 0) {
                    $newWarPoint = round($newWarPoint * (1 + ($defenderLose->getCharacter()->getPoliticPdg() / 10)));
                }
                if($defenderLose->getCharacter()->getPeaces()) {
                    $peace = $defenderLose->getCharacter()->getPeaces();
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
                        $exchangeLoseB = new Exchange($otherAlly, $defenderLose->getCharacter()->getCharacterName(), 1, 1, $pdgPeace, "Taxe liée à la paix.");
                        $em->persist($exchangeLoseB);
                        $reportLoseB->setContent($reportLoseB->getContent() . " Votre accord de paix ayant envoyé <span class='text-rouge'>-" . number_format($pdgPeace) . "</span> points de Guerre à l'alliance [" . $otherAlly->getSigle() . "].");
                    }
                }
                if ($newWarPoint > 0) {
                    $reportLoseB->setContent($reportLoseB->getContent() . " Mais vous remportez vous même <span class='text-vert'>+" . number_format($newWarPoint) . "</span> points de Guerre !");
                }
                if($defenderLose->getCharacter()->getRank()) {
                    $defenderLose->getCharacter()->getRank()->setWarPoint($defenderLose->getCharacter()->getRank()->getWarPoint() + $newWarPoint);
                }
                $em->persist($reportLoseB);
                $em->remove($defenderLose);
            }
            foreach($blockAtt as $attackerWin) {
                $newWarPoint = $warPointB / 10;
                if ($attackerWin->getCharacter()->getPoliticPdg() > 0) {
                    $newWarPoint = round($newWarPoint * (1 + ($attackerWin->getCharacter()->getPoliticPdg() / 10)));
                }
                if($attackerWin->getCharacter()->getPeaces()) {
                    $peace = $attackerWin->getCharacter()->getPeaces();
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
                        $exchangeWinB = new Exchange($otherAlly, $attackerWin->getCharacter()->getCharacterName(), 1, 1, $pdgPeace, "Taxe liée à la paix.");
                        $em->persist($exchangeWinB);
                    }
                }
                $percentArmor = ($attackerWin->getArmor() * 100) / $armorSaveA;
                $newArmor = $attackerWin->getArmor() - (round($percentArmor * $armor) / 100);
                $attackerWin->setFleetWinRatio(abs($newArmor));
                if($attackerWin->getCharacter()->getRank()) {
                    $attackerWin->getCharacter()->getRank()->setWarPoint($attackerWin->getCharacter()->getRank()->getWarPoint() + $newWarPoint);
                }
                $attackerWin->setFightAt(null);
                $attackerWin->setSignature($attackerWin->getNbrSignatures());
                if ($zombie == 1) {
                    $zombieDebris = 40;
                } else {
                    $zombieDebris = 1;
                }
            }
            $planet->setNbCdr(round($planet->getNbCdr() + ($debrisDefNiobium / $zombieDebris)));
            $planet->setWtCdr(round($planet->getWtCdr() + ($debrisDefWater / $zombieDebris)));
            $em->flush();
            return($blockAtt);
        }
        return null;
    }

    /**
     * @param $blockA
     * @param $blockB
     * @param $debrisNb
     * @param $debrisWt
     * @param $now
     * @param $em
     * @return Response
     */
    public function utilitFightAction($blockA, $blockB, $debrisNb, $debrisWt, $now, $em)
    {
        foreach($blockA as $removeTwo) {
            $reportLoseUtilB = new Report();
            $reportLoseUtilB->setType('fight');
            $reportLoseUtilB->setSendAt($now);
            $reportLoseUtilB->setImageName("f_lose_report.jpg");
            $reportLoseUtilB->setContent("Votre flotte utilitaire " . $removeTwo->getName() . " ne dispose pas des technologies nécessaires à l'identification des vaisseaux ennemis (" . $removeTwo->getPlanet()->getSector()->getGalaxy()->getPosition() . "." . $removeTwo->getPlanet()->getSector()->getPosition() . "." . $removeTwo->getPlanet()->getPosition() . ") .");
            $reportLoseUtilB->setTitle("Rapport de combat : Défaite");
            $reportLoseUtilB->setCharacter($removeTwo->getCharacter());
            $removeTwo->getCharacter()->setViewReport(false);
            $em->persist($reportLoseUtilB);
            $em->remove($removeTwo);
        }
        foreach($blockB as $reportWin) {
            $reportWinUtilB = new Report();
            $reportWinUtilB->setType('fight');
            $reportWinUtilB->setSendAt($now);
            $reportWinUtilB->setImageName("f_win_report.jpg");
            $reportWinUtilB->setContent("Vous venez de détruire une flotte utilitaire en (" . $reportWin->getPlanet()->getSector()->getGalaxy()->getPosition() . "." . $reportWin->getPlanet()->getSector()->getPosition() . "." . $reportWin->getPlanet()->getPosition() . ") .");
            $reportWinUtilB->setTitle("Rapport de combat : Victoire");
            $reportWinUtilB->setCharacter($reportWin->getCharacter());
            $reportWin->getCharacter()->setViewReport(false);
            $em->persist($reportWinUtilB);
            $planet = $reportWin->getPlanet();
        }
        $planet->setNbCdr($planet->getNbCdr() + $debrisNb);
        $planet->setWtCdr($planet->getWtCdr() + $debrisWt);

        echo "Flush ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}