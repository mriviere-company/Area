<?php

namespace App\Controller\Connected;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Report;
use App\Entity\Planet;
use App\Entity\Fleet;
use DateTime;
use DateTimeZone;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class InvadeController extends AbstractController
{
      /**
       * @Route("/hello-we-come-for-you/{invader}/{usePlanet}", name="invader_planet", requirements={"usePlanet"="\d+", "invader"="\d+"})
       */
    public function invaderAction(Planet $usePlanet, Fleet $invader)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $alea = rand(4, 8);
        if ($usePlanet->getUser() != $user || $invader->getUser() != $user) {
            return $this->redirectToRoute('home');
        }
        if ($user->getPoliticBarge() > 0) {
            $barge = $invader->getBarge() * 2500 * (1 + ($user->getPoliticBarge() / 4));
        } else {
            $barge = $invader->getBarge() * 2500;
        }
        if ($barge) {
            if($barge >= $invader->getSoldier()) {
                $aMilitary = $invader->getSoldier() * $alea;
                $soldierAtmp = $invader->getSoldier();
                $soldierAtmpTotal = 0;
            } else {
                $aMilitary = $barge * $alea;
                $soldierAtmp = $barge;
                $soldierAtmpTotal = $invader->getSoldier() - $barge;
            }
            if ($user->getPoliticSoldierAtt() > 0) {
                $aMilitary = $aMilitary * (1 + ($user->getPoliticSoldierAtt() / 10));
            }
        } else {
            $this->addFlash("fail", "Vous ne disposez pas de barges d'invasions.");
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $invader->getId(), 'usePlanet' => $usePlanet->getId()]);
        }

        $defender = $invader->getPlanet();
        $userDefender= $invader->getPlanet()->getUser();
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

        if($invader->getPlanet()->getUser() && $invader->getAllianceUser() && $invader->getFightAt() == null && $invader->getFlightTime() == null && $user->getSigleAllied($dSigle) == NULL) {
            if($dMilitary >= $aMilitary) {
                if ($userDefender->getZombie() == 0) {
                    $warPointDef = round($aMilitary);
                    if ($user->getPoliticPdg() > 0) {
                        $warPointDef = round($warPointDef * (1 + ($user->getPoliticPdg() / 10)));
                    }
                    $userDefender->getRank()->setWarPoint($userDefender->getRank()->getWarPoint() + $warPointDef);
                }
                $aMilitary = $aMilitary - $dSoldier;
                if($barge < $invader->getSoldier()) {
                    $invader->setSoldier($invader->getSoldier() - $barge);
                } else {
                    $invader->setSoldier(0);
                }
                $defender->setBarge($defender->getBarge() + $invader->getBarge());
                $invader->setBarge(0);
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
                $invader->setSoldier(abs($soldierAtmpTotal + round($aMilitary / $diviser)));
                $soldierAtmp = $invader->getSoldier() - round($soldierAtmpTotal + $soldierAtmp);
                $defender->setSoldier(0);
                $defender->setTank(0);
                $defender->setWorker(2000);

                if($invader->getUser()->getColPlanets() <= ($invader->getUser()->getTerraformation() + 1 + $user->getPoliticInvade()) && $userDefender->getZombie() == 0) {
                    $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $warPointAtt);
                    $defender->setUser($user);
                    $em->flush();
                    $reportDef->setTitle("Rapport d'invasion : Défaite (défense)");
                    $reportDef->setImageName("defend_lose_report.jpg");
                    $reportDef->setContent("Mais QUI ? QUI !!! Vous as donné un commandant si médiocre " . $defender->getUser()->getUserName() . " n'a pas eu a faire grand chose pour prendre votre planète " . $defender->getName() . " - (" . $defender->getSector()->getgalaxy()->getPosition() . "." . $defender->getSector()->getPosition() . "." . $defender->getPosition() . ") .  " . number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminé. C'est toujours ça de gagner. Vos <span class='text-rouge'>-" . number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>-" . number_format($tankDtmp) ."</span> tanks et <span class='text-rouge'>-" . number_format($workerDtmp) . "</span> travailleurs sont tous mort. Votre empire en a prit un coup, mais il vous reste des planètes, il est l'heure de la revanche !");
                    $reportInv->setTitle("Rapport d'invasion : Victoire (attaque)");
                    $reportInv->setImageName("invade_win_report.jpg");
                    $reportInv->setContent("Vous débarquez après que la planète ait été prise et vous installez sur le trône de " . $userDefender->getUserName() . ". Qu'il est bon d'entendre ses pleures lointains... La planète " . $defender->getName() . " - (" . $defender->getSector()->getgalaxy()->getPosition() . "." . $defender->getSector()->getPosition() . "." . $defender->getPosition() . ") est désormais votre! Il est temps de remettre de l'ordre dans la galaxie. <span class='text-rouge'>-" . number_format(round($soldierAtmp)) . "</span> de vos soldats ont péri dans l'invasion. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) . "</span> travailleurs ont péri. Cependant vous épargnez 2000 travailleurs dans votre bonté (surtout pour faire tourner la planète).<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre.");
                } else {
                    $warPointAtt = $warPointAtt * 10;
                    $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $warPointAtt);
                    $hydra = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);
                    if ($userDefender->getZombie() == 0) {
                        $reportDef->setTitle("Rapport d'invasion : Défaite (défense)");
                        $reportDef->setImageName("defend_lose_report.jpg");
                        $reportDef->setContent("Mais QUI ? QUI !!! Vous as donné un commandant si médiocre " . $defender->getUser()->getUserName() . " n'a pas eu a faire grand chose pour prendre votre planète " . $defender->getName() . " - " . $defender->getSector()->getgalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . ".  " . number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminé. C'est toujours ça de gagner. Vos <span class='text-rouge'>-" . number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>-" . number_format($tankDtmp) ."</span> tanks et <span class='text-rouge'>-" . number_format($workerDtmp) . "</span> travailleurs sont tous mort. Votre empire en a prit un coup, mais il vous reste des planètes, il est l'heure de la revanche !");
                        $reportInv->setTitle("Rapport d'invasion : Victoire (attaque)");
                        $reportInv->setImageName("invade_win_report.jpg");
                        $reportInv->setContent("Vous débarquez après que la planète ait été prise et vous installez sur le trône de " . $userDefender->getUserName() . ". Qu'il est bon d'entendre ses pleures lointains... La planète " . $defender->getName() . " - (" . $defender->getSector()->getgalaxy()->getPosition() . "." . $defender->getSector()->getPosition() . "." . $defender->getPosition() . ") est désormais votre! Il est temps de remettre de l'ordre dans la galaxie. <span class='text-rouge'>-" . number_format(round($soldierAtmp)) . "</span> de vos soldats ont péri dans l'invasion. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks et <span class='text-vert'>" . number_format($workerDtmp) . "</span> travailleurs ont péri. Cependant vous épargnez 2000 travailleurs dans votre bonté (surtout pour faire tourner la planète).<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre.");
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
                        $user->setZombieAtt(round($user->getZombieAtt() / 10));
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
                    $userDefender->setGameOver($user->getUserName());
                    $userDefender->setGrade(null);
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
            if($invader->getNbrShips() == 0) {
                $em->remove($invader);
            }
            $em->persist($reportInv);
            if ($userDefender->getZombie() == 0) {
                $em->persist($reportDef);
            }
            $em->flush();
        }

        return $this->redirectToRoute('report', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/merci-pour-les-ressources/{raider}/{usePlanet}", name="raid_planet", requirements={"usePlanet"="\d+", "raider"="\d+"})
     */
    public function raidAction(Planet $usePlanet, Fleet $raider)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));

        if ($usePlanet->getUser() != $user || $raider->getUser() != $user) {
            return $this->redirectToRoute('home');
        }
        if ($raider->getUser()->getPoliticBarge() > 0) {
            $barge = $raider->getBarge() * 2500 * (1 + ($raider->getUser()->getPoliticBarge() / 4));
        } else {
            $barge = $raider->getBarge() * 2500;
        }
        if ($barge) {
            if($barge >= $raider->getSoldier()) {
                $aMilitary = $raider->getSoldier() * 6;
                $soldierAtmp = $raider->getSoldier();
                $soldierAtmpTotal = 0;
            } else {
                $aMilitary = $barge * 6;
                $soldierAtmp = $barge;
                $soldierAtmpTotal = $raider->getSoldier() - $barge;
            }
            if ($raider->getUser()->getPoliticSoldierAtt() > 0) {
                $aMilitary = $aMilitary * (1 + ($user->getPoliticSoldierAtt() / 10));
            }
        } else {
            $this->addFlash("fail", "Vous ne disposez pas de barges d'invasions.");
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $raider->getId(), 'usePlanet' => $usePlanet->getId()]);
        }

        $defender = $raider->getPlanet();
        $userDefender = $raider->getPlanet()->getUser();
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
        $reportLoot->setUser($raider->getUser());
        $raider->getUser()->setViewReport(false);
        $reportDef = new Report();
        $reportDef->setType('invade');
        $reportDef->setSendAt($now);
        $reportDef->setUser($userDefender);
        $userDefender->setViewReport(false);
        $dSigle = null;
        if ($userDefender->getAlly()) {
            $dSigle = $userDefender->getAlly()->getSigle();
        }

        if ($raider->getPlanet()->getUser() && $raider->getAllianceUser() && $raider->getUser()->getSigleAllied($dSigle) == NULL && $userDefender->getZombie() == 0) {
            if($dMilitary >= $aMilitary) {
                $warPointDef = round($aMilitary);
                if ($userDefender->getPoliticPdg() > 0) {
                    $warPointDef = round($warPointDef * (1 + ($userDefender->getPoliticPdg() / 10)));
                }
                $userDefender->getRank()->setWarPoint($userDefender->getRank()->getWarPoint() + $warPointDef);
                if($barge < $raider->getSoldier()) {
                    $raider->setSoldier($raider->getSoldier() - $barge);
                } else {
                    $raider->setSoldier(0);
                }
                $defender->setBarge($defender->getBarge() + $raider->getBarge());
                $raider->setBarge(0);
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
                $reportDef->setContent("Le dirigeant " . $raider->getUser()->getUserName() . " a tenté de piller votre planète " . $defender->getName() . " en " . $defender->getSector()->getgalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . ". Il a échoué grâce a vos solides défenses. Vous avez éliminé <span class='text-vert'>" . number_format($soldierAtmp) . "</span> soldats et prit le contrôle des barges de l'attaquant.<br>Et vous remportez <span class='text-vert'>+" . number_format($warPointDef) . "</span> points de Guerre.");
                $reportLoot->setTitle("Rapport de pillage : Défaite (attaque)");
                $reportLoot->setImageName("invade_lose_report.jpg");
                $reportLoot->setContent("Le dirigeant " . $userDefender->getUserName() . " vous attendait de pieds fermes. Sa planète " . $defender->getName() . " en " . $defender->getSector()->getgalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . " était trop renforcée pour vous. Vous tué tout de même <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks. Tous vos soldats sont morts et vos barges sont resté sur la planète.<br>La prochaine fois, préparez votre attaque commandant.");
            } else {
                $warPointAtt = round($soldierDtmp?$soldierDtmp:1 + $tankDtmp);
                if ($raider->getUser()->getPoliticPdg() > 0) {
                    $warPointAtt = round($warPointAtt * (1 + ($raider->getUser()->getPoliticPdg() / 10)));
                }
                $diviser = (1 + ($user->getPoliticSoldierAtt() / 10)) * 6;
                $aMilitary = $aMilitary - $dMilitary;
                $raider->setSoldier(abs($soldierAtmpTotal + round($aMilitary / $diviser)));
                $soldierAtmp = $raider->getSoldier() - round($soldierAtmpTotal + $soldierAtmp);
                $defender->setSoldier(0);
                $defender->setTank(0);
                if($raider->getCargoPlace() > $raider->getCargoFull()) {
                    $place = $raider->getCargoPlace() - $raider->getCargoFull();
                    if ($place > $defender->getNiobium()) {
                        $raider->setNiobium($raider->getNiobium() + $defender->getNiobium());
                        $place = $place - $defender->getNiobium();
                        $niobium = $defender->getNiobium();
                        $defender->setNiobium(0);
                    } else {
                        $raider->setNiobium($raider->getNiobium() + $place);
                        $defender->setNiobium($raider->getNiobium() - $place);
                        $niobium = $place;
                        $place = 0;
                    }
                    if ($place > $defender->getWater()) {
                        $raider->setWater($raider->getWater() + $defender->getWater());
                        $place = $place - $defender->getWater();
                        $water = $defender->getWater();
                        $defender->setWater(0);
                    } else {
                        $raider->setWater($raider->getWater() + $place);
                        $defender->setWater($raider->getWater() - $place);
                        $water = $place;
                        $place = 0;
                    }
                    if ($place > $defender->getUranium()) {
                        $raider->setUranium($raider->getUranium() + $defender->getUranium());
                        $uranium = $defender->getUranium();
                        $defender->setUranium(0);
                    } else {
                        $raider->setUranium($raider->getUranium() + $place);
                        $defender->setUranium($raider->getUranium() - $place);
                        $uranium = $place;
                    }
                }
                $raider->getUser()->getRank()->setWarPoint($raider->getUser()->getRank()->getWarPoint() + $warPointAtt);
                $reportDef->setTitle("Rapport de pillage : Défaite (défense)");
                $reportDef->setImageName("defend_lose_report.jpg");
                $reportDef->setContent("Le dirigeant " . $raider->getUser()->getUserName() . " vient de piller (" . number_format(round($niobium)) . " niobiums" . number_format(round($water)) . " eaux" . number_format(round($uranium)) . " uraniums) votre planète " . $defender->getName() . " - " . $defender->getSector()->getgalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . ".  " . number_format(round($soldierAtmp)) . " soldats ennemis sont tout de même éliminé. C'est toujours ça de gagner. Vos <span class='text-rouge'>-" . number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>-" . number_format($tankDtmp) ."</span> tanks. Votre économie en a prit un coup, mais si vous étiez là pour planter des choux ça se serait ! Préparez la contre-attaque !");
                $reportLoot->setTitle("Rapport de pillage : Victoire (attaque)");
                $reportLoot->setImageName("invade_win_report.jpg");
                $reportLoot->setContent("Vos soldats ont finit de charger vos cargos ( <span class='text-vert'>" . number_format(round($niobium)) . " niobiums - " . number_format(round($water)) . " eaux - " . number_format(round($uranium)) . " uraniums </span>) et remontent dans les barges, le pillage de la planète " . $defender->getName() . " en " . $defender->getSector()->getgalaxy()->getPosition() . ":" . $defender->getSector()->getPosition() . ":" . $defender->getPosition() . " s'est bien passé. Vos pertes sont de <span class='text-rouge'>" . number_format(round($soldierAtmp)) . "</span> soldats. Mais les défenseurs ont aussi leurs pertes : <span class='text-vert'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-vert'>" . number_format($tankDtmp) ."</span> tanks.<br>Vous remportez <span class='text-vert'>+" . number_format($warPointAtt) . "</span> points de Guerre.");
                $quest = $raider->getUser()->checkQuests('loot');
                if($quest) {
                    $raider->getUser()->getRank()->setWarPoint($raider->getUser()->getRank()->getWarPoint() + $quest->getGain());
                    $raider->getUser()->removeQuest($quest);
                }
            }
            $em->persist($reportLoot);
            $em->persist($reportDef);
        } else {
            $this->addFlash("fail", "Vous ne pouvez pas piller une planète Zombie.");
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $raider->getId(), 'usePlanet' => $usePlanet->getId()]);
        }

        $em->flush();

        return $this->redirectToRoute('report', ['usePlanet' => $usePlanet->getId()]);
    }
}