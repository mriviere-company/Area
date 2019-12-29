<?php

namespace App\Controller\Connected;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\MissionType;
use App\Form\Front\MissionUraType;
use App\Entity\Planet;
use App\Entity\Report;
use App\Entity\Mission;
use DateTime;
use Dateinterval;
use DateTimeZone;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class ZombieController extends AbstractController
{
    /**
     * @Route("/zombie/{usePlanet}", name="zombie", requirements={"usePlanet"="\d+"})
     */
    public function ZombieAction(Request $request, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $nowMission = new DateTime();
        $nowMission->setTimezone(new DateTimeZone('Europe/Paris'));

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planet = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.radarAt is null and p.brouilleurAt is null')
            ->setParameters(['user' => $user])
            ->orderBy('p.ground', 'ASC')
            ->getQuery()
            ->setMaxresults(1)
            ->getOneOrNullResult();

        $planetBis = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->leftJoin('p.missions', 'm')
            ->where('p.user = :user')
            ->andWhere('p.radarAt is null and p.brouilleurAt is null and m.soldier is not null')
            ->setParameters(['user' => $user])
            ->orderBy('p.ground', 'ASC')
            ->getQuery()
            ->setMaxresults(1)
            ->getOneOrNullResult();

        if ($planetBis) {
            $planet = $planetBis;
        }
        $usePlanet = $planet;

        $form_missionUranium = $this->createForm(MissionUraType::class);
        $form_missionUranium->handleRequest($request);

        $form_missionZombie = $this->createForm(MissionType::class);
        $form_missionZombie->handleRequest($request);

        if ($form_missionZombie->isSubmitted() && $form_missionZombie->isValid()) {
            $soldier = abs($form_missionZombie->get('soldier')->getData());
            $tank = abs($form_missionZombie->get('tank')->getData());
            $time = abs($form_missionZombie->get('time')->getData());
            $zombie = abs($user->getZombieAtt()) * 75;
            $zombieTotal = $zombie;
            $zombie = $zombie - $soldier - $tank;
            $alea = rand(1, 100) == 100 ? 2 : 1;
            if ($zombie <= 1) {
                $zombie = 1;
            } else {
                $zombie = 1 + ((100 * $zombie) / $zombieTotal) / 100;
            }
            if ($time == 1) {
                $percent = ROUND(90 / $zombie);
                $gain = 2;
            } elseif ($time == 3) {
                $percent = ROUND(70 / $zombie);
                $gain = 5;
            } elseif ($time == 6) {
                $percent = ROUND(50 / $zombie);
                $gain = 8;
            } elseif ($time == 10) {
                $percent = ROUND(30 / $zombie);
                $gain = 15;
            }
            if ($soldier > $planet->getSoldier() || $tank > $planet->getTank() || !$gain || ($soldier == 0 && $tank == 0) || count($planet->getMissions()) >= 3) {
                if (count($planet->getMissions()) >= 3) {
                    $this->addFlash("fail", "Maximum 3 missions.");
                } elseif ($soldier == 0 && $tank == 0) {
                    $this->addFlash("fail", "Vous n'avez pas suffisament de soldats/tanks.");
                } else {
                    $this->addFlash("fail", "Vous n'avez pas toutes les conditions requises.");
                }
                return $this->redirectToRoute('zombie', ['usePlanet' => $usePlanet->getId()]);
            } else {
                $nowMission->add(new DateInterval('PT' . $time . 'H'));
                $mission = new Mission();
                $mission->setMissionAt($nowMission);
                $mission->setType(0);
                $mission->setPlanet($planet);
                $mission->setSoldier($soldier);
                $mission->setTank($tank);
                $mission->setPercent($percent);
                $mission->setGain($gain * $alea);
                $planet->setSoldier($planet->getSoldier() - $soldier);
                $planet->setTank($planet->getTank() - $tank);
                $em->persist($mission);
                if ($user->getTutorial() == 52) {
                    $user->setTutorial(53);
                }
                $em->flush();
            }
            return $this->redirectToRoute('zombie', ['usePlanet' => $usePlanet->getId()]);
        }

        if ($form_missionUranium->isSubmitted() && $form_missionUranium->isValid()) {
            $soldier = abs($form_missionUranium->get('soldier')->getData());
            $tank = abs($form_missionUranium->get('tank')->getData());
            $time = abs($form_missionUranium->get('time')->getData());
            $zombie = $user->getZombieAtt() * 75;
            if ($zombie <= 0) {
                $zombie = 1;
            }
            $zombieTotal = $zombie;
            $zombie = $zombie - $soldier - $tank;
            $alea = rand(1, 100) == 100 ? 2 : 1;
            if ($zombie <= 1) {
                $zombie = 1;
            } else {
                $zombie = 1 + ((100 * $zombie) / $zombieTotal) / 100;
            }
            if ($time == 1) {
                $percent = ROUND(90 / $zombie);
                $gain = 2;
            } elseif ($time == 3) {
                $percent = ROUND(70 / $zombie);
                $gain = 5;
            } elseif ($time == 6) {
                $percent = ROUND(50 / $zombie);
                $gain = 8;
            } elseif ($time == 10) {
                $percent = ROUND(30 / $zombie);
                $gain = 15;
            }
            if ($soldier > $planet->getSoldier() || $tank > $planet->getTank() || !$gain || ($soldier == 0 && $tank == 0) || count($planet->getMissions()) >= 3) {
                $this->addFlash("fail", "Maximum 3 missions.");
                return $this->redirectToRoute('zombie', ['usePlanet' => $usePlanet->getId()]);
            } else {
                $nowMission->add(new DateInterval('PT' . $time . 'H'));
                $mission = new Mission();
                $mission->setMissionAt($nowMission);
                $mission->setType(1);
                $mission->setPlanet($planet);
                $mission->setSoldier($soldier);
                $mission->setTank($tank);
                $mission->setPercent($percent);
                $mission->setGain($gain * $alea);
                $planet->setSoldier($planet->getSoldier() - $soldier);
                $planet->setTank($planet->getTank() - $tank);
                $em->persist($mission);

                if ($user->getTutorial() == 52) {
                    $user->setTutorial(53);
                }
                $em->flush();
            }
            return $this->redirectToRoute('zombie', ['usePlanet' => $usePlanet->getId()]);
        }

        if(($user->getTutorial() == 51)) {
            $user->setTutorial(52);
            $em->flush();
        }

        $otherPoints = $em->getRepository('App:Stats')
            ->createQueryBuilder('s')
            ->join('s.user', 'u')
            ->select('count(s) as numbers, sum(DISTINCT s.zombie) as allZombie')
            ->groupBy('s.date')
            ->where('s.user != :user')
            ->andWhere('u.bot = false')
            ->setParameters(['user' => $user])
            ->getQuery()
            ->getResult();

        return $this->render('connected/zombie.html.twig', [
            'usePlanet' => $usePlanet,
            'planet' => $planet,
            'otherPoints' => $otherPoints,
            'form_missionZombie' => $form_missionZombie->createView(),
            'form_missionUranium' => $form_missionUranium->createView()
        ]);
    }

    /**
     * @Route("/finir-mission/{mission}/{usePlanet}", name="mission_finish", requirements={"usePlanet"="\d+", "mission"="\d+"})
     */
    public function zombieFinishAction(Planet $usePlanet, Mission $mission)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));

        if ($usePlanet->getUser() != $user || $mission->getMissionAt() > $now) {
            return $this->redirectToRoute('home');
        }
        $reportMission = new Report();
        $reportMission->setType('zombie');
        $reportMission->setSendAt($now);
        $reportMission->setUser($user);
        $planet = $mission->getPlanet();
        if ($mission->getType() == 0) {
            if (rand(1, 100) <= $mission->getPercent()) {
                $lose = 1 + ((100 - $mission->getPercent()) / rand(4,7)) / 100;
                if ($planet->getSoldier() + round($mission->getSoldier() / $lose) > $planet->getSoldierMax() || $planet->getTank() + round($mission->getTank() / $lose) > 500) {
                    $this->addFlash("fail", "Vous n'avez pas assez de place pour vos soldats ou tanks.");
                    return $this->redirectToRoute('zombie', ['usePlanet' => $usePlanet->getId()]);
                }
                $soldier = $mission->getSoldier() - round($mission->getSoldier() / $lose);
                $tank = $mission->getTank() - round($mission->getTank() / $lose);
                $planet->setSoldier($planet->getSoldier() + round($mission->getSoldier() / $lose));
                $planet->setTank($planet->getTank() + round($mission->getTank() / $lose));
                $user->setZombieAtt($user->getZombieAtt() - $mission->getGain());
                $em->remove($mission);
                $reportMission->setTitle("Mission d'élimination zombies");
                $reportMission->setImageName("zombie_win_report.jpg");
                $reportMission->setContent("L'escouade militaire envoyé en mission est de retour, son capitaine vous fait son rapport :<br> <span class='text-vert'>-" . number_format($mission->getGain()) . "</span> menace zombie sur la planète.<br><span class='text-rouge'>" . number_format($soldier) . "</span> soldats meurent dans la mission ainsi que <span class='text-rouge'>" . number_format($tank) . "</span> tanks.");
            } else {
                $reportMission->setTitle("Échec mission");
                $reportMission->setImageName("zombie_lose_report.jpg");
                if ($planet->getSoldier() + round($mission->getSoldier() / 2) > $planet->getSoldierMax() || $planet->getTank() + round($mission->getTank() / 2) > 500) {
                    $this->addFlash("fail", "Vous n'avez pas assez de place pour vos soldats ou tanks.");
                    return $this->redirectToRoute('zombie', ['usePlanet' => $usePlanet->getId()]);
                }
                $planet->setSoldier($planet->getSoldier() + round($mission->getSoldier() / 2));
                $planet->setTank($planet->getTank() + round($mission->getTank() / 2));
                $reportMission->setContent("Des hommes de l'escouade militaire envoyée reviennent progressivement par petit groupe.<br>Ils ne s'attendaient pas a une telle résistance... <span class='text-rouge'>" . number_format($mission->getSoldier() / 2) . "</span> soldats sont morts durant la mission ainsi que <span class='text-rouge'>" . number_format($mission->getTank() / 2) . "</span> tanks.");
                $em->remove($mission);
            }
        } else {
            if (rand(1, 100) <= $mission->getPercent()) {
                $lose = 1 + ((100 - $mission->getPercent()) / rand(3,6)) / 100;
                if ($planet->getSoldier() + round($mission->getSoldier() / $lose) > $planet->getSoldierMax() || $planet->getTank() + round($mission->getTank() / $lose) > 500) {
                    $this->addFlash("fail", "Vous n'avez pas assez de place pour vos soldats ou tanks.");
                    return $this->redirectToRoute('zombie', ['usePlanet' => $usePlanet->getId()]);
                }
                $soldier = $mission->getSoldier() - round($mission->getSoldier() / $lose);
                $tank = $mission->getTank() - round($mission->getTank() / $lose);
                $planet->setSoldier($planet->getSoldier() + round($mission->getSoldier() / $lose));
                $planet->setTank($planet->getTank() + round($mission->getTank() / $lose));
                $planet->setUranium($planet->getUranium() + $mission->getGain());
                $user->setZombieAtt($user->getZombieAtt() + 1);
                $reportMission->setTitle("Mission de récupération d'uranium");
                $reportMission->setImageName("uranium_win_report.jpg");
                $reportMission->setContent("L'escouade militaire envoyé en mission est de retour, son capitaine vous fait son rapport :<br> <span class='text-vert'>+" . number_format($mission->getGain()) . "</span> uranium ont été récupérés en zone zombie.<br><span class='text-rouge'>" . number_format($soldier) . "</span> soldats meurent dans la mission ainsi que <span class='text-rouge'>" . number_format($tank) . "</span> tanks.<br>Vous étiez en mission sur le territoire zombie et avez fait augmenter la menace de <span class='text-rouge'>+1</span>.");
                $em->remove($mission);
            } else {
                $lose = round($mission->getPercent() / 10);
                $user->setZombieAtt($user->getZombieAtt() + $lose);
                $reportMission->setTitle("Échec mission");
                $reportMission->setImageName("zombie_lose_report.jpg");
                if ($planet->getSoldier() + round($mission->getSoldier() / 2) > $planet->getSoldierMax() || $planet->getTank() + round($mission->getTank() / 2) > 500) {
                    $this->addFlash("fail", "Vous n'avez pas assez de place pour vos soldats ou tanks.");
                    return $this->redirectToRoute('zombie', ['usePlanet' => $usePlanet->getId()]);
                }
                $planet->setSoldier($planet->getSoldier() + round($mission->getSoldier() / 2));
                $planet->setTank($planet->getTank() + round($mission->getTank() / 2));
                $reportMission->setContent("Des hommes de l'escouade militaire envoyée reviennent progressivement par petit groupe.<br>Ils ne s'attendaient pas a une telle résistance... <span class='text-rouge'>" . number_format($mission->getSoldier() / 2) . "</span> soldats sont morts durant la mission ainsi que <span class='text-rouge'>" . number_format($mission->getTank() / 2) . "</span> tanks.<br>Vous étiez en mission sur le territoire zombie et avez fait augmenter la menace de <span class='text-rouge'>+". $lose ."</span>.");
                $em->remove($mission);
            }
        }
        if ($user->getZombieAtt() <= -20) {
            $user->setZombieAtt(-20);
        }
        if ($user->getTutorial() == 53) {
            $user->setTutorial(54);
        }
        $em->persist($reportMission);
        $em->flush();

        return $this->redirectToRoute('zombie', ['usePlanet' => $usePlanet->getId()]);
    }
}