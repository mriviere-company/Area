<?php

namespace App\Controller\Connected\Execute;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Destination;
use App\Entity\Report;
use App\Entity\Fleet;
use DateInterval;
use DateTime;

/**
 * Class ZombiesController
 * @package App\Controller\Connected\Execute
 */
class ZombiesController extends AbstractController
{
    /**
     * @param $zCharacters
     * @param $now
     * @param $em
     * @return Response
     * @throws Exception
     */
    public function zombiesAction($zCharacters, $now, $em): Response
    {
        foreach ($zCharacters as $zCharacter) {
            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($zCharacter);
            $zombie = $em->getRepository('App:Character')->findOneBy(['zombie' => 1]);

            $planetAtt = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->where('p.character = :character')
                ->andWhere('p.radarAt is null and p.brouilleurAt is null')
                ->setParameters(['character' => $zCharacter])
                ->orderBy('p.ground', 'ASC')
                ->getQuery()
                ->setMaxresults(1)
                ->getOneOrNullResult();

            $planetZb = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->where('p.character = :character')
                ->setParameters(['character' => $zombie])
                ->orderBy('p.ground', 'DESC')
                ->getQuery()
                ->setMaxresults(1)
                ->getOneOrNullResult();

            if (!$planetAtt) {
                echo "Attaques zombies impossible - ";

                return new Response ("KO<br/>");
            }

            if ($zCharacter->getZombieAtt() > 0) {
                $reportDef = new Report();
                $reportDef->setType('invade');
                $reportDef->setSendAt($now);
                $reportDef->setCharacter($zCharacter);
                if ($zCharacter->getZombieAtt() >= 1 && $zCharacter->getUser()->getTutorial() == 50) {
                    $zCharacter->getUser()->setTutorial(51);
                }

                $barbed = $zCharacter->getBarbedAdv();
                $dSoldier = $planetAtt->getSoldier() > 0 ? ($planetAtt->getSoldier() * 6) * $barbed : 0;
                $dTanks = $planetAtt->getTank() > 0 ? $planetAtt->getTank() * 3000 : 0;
                $dWorker = $planetAtt->getWorker();
                if ($zCharacter->getPoliticSoldierAtt() > 0) {
                    $dSoldier = $dSoldier * (1 + ($zCharacter->getPoliticSoldierAtt() / 10));
                }
                if ($zCharacter->getPoliticTankDef() > 0) {
                    $dTanks = $dTanks * (1 + ($zCharacter->getPoliticTankDef() / 10));
                }
                if ($zCharacter->getPoliticWorkerDef() > 0) {
                    $dWorker = $dWorker * (1 + ($zCharacter->getPoliticWorkerDef() / 5));
                }
                $dMilitary = $dWorker + $dSoldier + $dTanks;
                $aMilitary = (500 * (($zCharacter->getZombieAtt() / 2) + 1) * 2 * round(1 + ($zCharacter->getTerraformation()) / 5));
                $soldierAtmp = (500 * (($zCharacter->getZombieAtt() / 2) + 1));

                if ($dMilitary > $aMilitary) {
                    if ($zCharacter->getAlly()) {
                        if ($zCharacter->getAlly()->getPolitic() == 'fascism') {
                            $zCharacter->setZombieAtt($zCharacter->getZombieAtt() + 150);
                        } else {
                            $zCharacter->setZombieAtt($zCharacter->getZombieAtt() + 200);
                        }
                    } else {
                        $zCharacter->setZombieAtt($zCharacter->getZombieAtt() + 100);
                    }
                    $warPointDef = round($aMilitary / 10);
                    $zCharacter->getRank()->setWarPoint($zCharacter->getRank()->getWarPoint() + $warPointDef);
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
                            $planetAtt->setWorker($planetAtt->getWorker() + ($aMilitary / (1 + ($zCharacter->getPoliticWorkerDef() / 5))));
                            $workerDtmp = $workerDtmp - $planetAtt->getWorker();
                            $reportDef->setContent("«Au secours !» des civils crient et cours dans tous les sens sur " . $planetAtt->getName() . " en <span><a href='/connect/carte-spatiale/" . $planetAtt->getSector()->getPosition() . "/" . $planetAtt->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $planetAtt->getSector()->getGalaxy()->getPosition() . ":" . $planetAtt->getSector()->getPosition() . ":" . $planetAtt->getPosition() . "</a></span>.<br>Vous n'aviez pas prévu suffisament de soldats et tanks pour faire face a la menace et des zombies envahissent les villes. Heureusement pour vous les travailleurs se réunissent et parviennent exterminer les zombies mais ce n'est pas grâce a vous.<br>" . number_format($soldierAtmp) . " zombies sont tués. <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> de vos soldats succombent aux mâchoires de ces infamies et <span class='text-rouge'>" . number_format($tankDtmp) . "</span> tanks sont mit hors de service. <span class='text-rouge'>" . number_format($workerDtmp) . "</span> de vos travailleurs sont retrouvés morts.<br>Vous ne remportez aucun points de Guerre pour avoir sacrifié vos civils.");
                            $em->persist($reportDef);
                        } else {
                            $diviser = (1 + ($zCharacter->getPoliticTankDef() / 10)) * 3000;
                            $planetAtt->setTank(round($aMilitary / $diviser));
                            $tankDtmp = $tankDtmp - $planetAtt->getTank();
                            $reportDef->setContent("Vos tanks ont suffit a arrêter les zombies pour cette fois-ci sur la planète " . $planetAtt->getName() . " en <span><a href='/connect/carte-spatiale/" . $planetAtt->getSector()->getPosition() . "/" . $planetAtt->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $planetAtt->getSector()->getGalaxy()->getPosition() . ":" . $planetAtt->getSector()->getPosition() . ":" . $planetAtt->getPosition() . "</a></span>.br>Mais pensez a rester sur vos gardes. Votre armée extermine " . number_format($soldierAtmp) . " zombies. <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> de vos soldats succombent aux mâchoires de ces infamies et <span class='text-rouge'>" . number_format($tankDtmp) . "</span> tanks sont mit hors de service.<br>Vous remportez <span class='text-vert'>+" . number_format($warPointDef) . "</span> points de Guerre.");
                            $em->persist($reportDef);
                        }
                    } else {
                        $diviser = (1 + ($zCharacter->getPoliticSoldierAtt() / 10)) * (6 * $zCharacter->getBarbedAdv());
                        $planetAtt->setSoldier(round($aMilitary / $diviser));
                        $soldierDtmp = $soldierDtmp - $planetAtt->getSoldier();
                        $reportDef->setContent("Une attaque de zombie est déclarée sur " . $planetAtt->getName() . " en <span><a href='/connect/carte-spatiale/" . $planetAtt->getSector()->getPosition() . "/" . $planetAtt->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $planetAtt->getSector()->getGalaxy()->getPosition() . ":" . $planetAtt->getSector()->getPosition() . ":" . $planetAtt->getPosition() . "</a></span>.<br>Vous étiez préparé a cette éventualité et vos soldats exterminent " . number_format($soldierAtmp) . " zombies. <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> de vos soldats succombent aux mâchoires de ces infamies.<br>Vous remportez <span class='text-vert'>+" . number_format($warPointDef) . "</span> points de Guerre.");
                        $em->persist($reportDef);
                    }
                } else {
                    $zCharacter->setZombieAtt((round($zCharacter->getZombieAtt() / 2)));
                    $soldierDtmp = $planetAtt->getSoldier() != 0 ? $planetAtt->getSoldier() : 1;
                    $workerDtmp = $planetAtt->getWorker();
                    $tankDtmp = $planetAtt->getTank();
                    $reportDef->setTitle("Rapport invasion zombies : Défaite");
                    $reportDef->setType("zombie");
                    $reportDef->setImageName("zombie_lose_report.jpg");
                    $reportDef->setContent("Vous recevez des rapports de toutes parts vous signalant des zombies sur la planète " . $planetAtt->getName() . " en <span><a href='/connect/carte-spatiale/" . $planetAtt->getSector()->getPosition() . "/" . $planetAtt->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" . $planetAtt->getSector()->getGalaxy()->getPosition() . ":" . $planetAtt->getSector()->getPosition() . ":" . $planetAtt->getPosition() . "</a></span>.<br>Mais vous tardez a réagir et le manque de préparation lui est fatale.<br>Vous recevez ces derniers mots de votre Gouverneur local «Salopard! Vous étiez censé nous protéger, ou est l'armée !»<br> Les dernières images de la planète vous montre des zombies envahissant le moindre recoin de la planète.<br>Vos <span class='text-rouge'>" . number_format($soldierDtmp) . "</span> soldats, <span class='text-rouge'>" . number_format($tankDtmp) . "</span> tanks et <span class='text-rouge'>" . number_format($workerDtmp) . "</span> travailleurs sont tous mort. Votre empire en a pris un coup, mais il vous reste des planètes, remettez vous en question! Consolidez vos positions et allez détuire les nids de zombies!");
                    $em->persist($reportDef);
                    $planetAtt->setWorker(125000);
                    if ($planetAtt->getGround() != 25 && $planetAtt->getSky() != 5) {
                        if ($planetAtt->getSoldierMax() >= 2500) {
                            $planetAtt->setSoldier($planetAtt->getSoldierMax());
                        } else {
                            $planetAtt->setCaserne(1);
                            $planetAtt->setSoldier(500);
                            $planetAtt->setSoldierMax(500);
                        }
                        $planetAtt->setName('Base Zombie');
                        $planetAtt->setImageName('hydra_planet.png');
                        $planetAtt->setCharacter($zombie);
                    } else {
                        $planetAtt->setName('Inhabitée');
                        $planetAtt->setCharacter(null);
                    }
                    $em->flush();
                    if ($zCharacter->getAllPlanets() == 0) {
                        $zCharacter->setGameOver($zombie->getUsername());
                        $zCharacter->setGrade(null);
                        foreach ($zCharacter->getFleets() as $tmpFleet) {
                            $tmpFleet->setCharacter($zombie);
                            $tmpFleet->setFleetList(null);
                        }
                    }
                }
            } else {
                if ($zCharacter->getAlly()) {
                    if ($zCharacter->getAlly()->getPolitic() == 'fascism') {
                        $zCharacter->setZombieAtt($zCharacter->getZombieAtt() + 150);
                    } else {
                        $zCharacter->setZombieAtt($zCharacter->getZombieAtt() + 200);
                    }
                } else {
                    $zCharacter->setZombieAtt($zCharacter->getZombieAtt() + 100);
                }
            }
            $zCharacter->setViewReport(false);
            $timeAtt = new DateTime();
            $timeAtt->add(new DateInterval('PT' . round(86400 * rand(1,3)) . 'S'));
            $nextZombie = new DateTime();
            $nextZombie->add(new DateInterval('PT' . round(12 * rand(1,5)) . 'H'));
            $zCharacter->setZombieAt($nextZombie);
            $fleetZb = new Fleet();
            $fleetZb->setName('Horde');
            $fleetZb->setHunter(1 + round(($zCharacter->getAllShipsPoint() / (3 * rand(1, 5))) / 5));
            $fleetZb->setHunterWar(1 + round(($zCharacter->getAllShipsPoint() / (4 * rand(1, 5))) / 5));
            $fleetZb->setCorvet(1 + round(($zCharacter->getAllShipsPoint() / (5 * rand(1, 5))) / 5));
            $fleetZb->setCorvetLaser(1 + round(($zCharacter->getAllShipsPoint() / (6 * rand(1, 5))) / 5));
            $fleetZb->setCorvetWar(1 + round(($zCharacter->getAllShipsPoint() / (7 * rand(1, 5))) / 5));
            $fleetZb->setFregate(1 + round(($zCharacter->getAllShipsPoint() / (8 * rand(1, 5))) / 5));
            $fleetZb->setCharacter($zombie);
            $fleetZb->setPlanet($planetZb);
            $fleetZb->setSignature($fleetZb->getNbrSignatures());
            $destination = new Destination($fleetZb, $planetAtt);
            $em->persist($destination);
            $fleetZb->setFlightTime($timeAtt);
            $fleetZb->setAttack(1);
            $fleetZb->setFlightType(1);
            $em->persist($fleetZb);
        }
        echo "Flush -> " . count($zCharacters) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}