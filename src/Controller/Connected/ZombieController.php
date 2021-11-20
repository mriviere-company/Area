<?php

namespace App\Controller\Connected;

use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use App\Entity\Report;
use App\Entity\Mission;
use DateTime;
use DateInterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class ZombieController extends AbstractController
{
    /**
     * @Route("/zombie/{usePlanet}", name="zombie", requirements={"usePlanet"="\d+"})
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function ZombieAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if($character->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if (count($character->getMissions()) == 0) {
            $i = 1;
            while ($i != 41) {
                $mission = new Mission($character, $i);
                $em->persist($mission);
                $i++;
            }
        }
        $em->flush();

        $planet = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.character = :character')
            ->andWhere('p.radarAt is null and p.brouilleurAt is null')
            ->setParameters(['character' => $character])
            ->orderBy('p.ground', 'ASC')
            ->getQuery()
            ->setMaxresults(1)
            ->getOneOrNullResult();

        $usePlanet = $planet;

        if(($user->getTutorial() == 51)) {
            $user->setTutorial(52);
            $em->flush();
        }

        $otherPoints = $em->getRepository('App:Stats')
            ->createQueryBuilder('s')
            ->join('s.character', 'c')
            ->select('count(s) as numbers, sum(DISTINCT s.zombie) as allZombie')
            ->groupBy('s.date')
            ->andWhere('c.bot = false')
            ->getQuery()
            ->getResult();

        $missions = $em->getRepository('App:Mission')
            ->createQueryBuilder('m')
            ->where('m.character = :character')
            ->setParameters(['character' => $character])
            ->orderBy('m.type', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('connected/zombie.html.twig', [
            'usePlanet' => $usePlanet,
            'planet' => $planet,
            'otherPoints' => $otherPoints,
            'missions' => $missions
        ]);
    }

    /**
     * @Route("/finir-mission/{mission}/{usePlanet}", name="mission_finish", requirements={"usePlanet"="\d+", "mission"="\d+"})
     * @param Mission $mission
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function zombieFinishAction(Mission $mission, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();
        $nowMission = new DateTime();
        $nowMission->add(new DateInterval('PT' . $mission->getTime() . 'S'));

        if ($usePlanet->getCharacter() != $character || $mission->getMissionAt() > $now) {
            return $this->redirectToRoute('home');
        }

        $reportMission = new Report();
        $reportMission->setType('zombie');
        $reportMission->setSendAt($now);
        $reportMission->setCharacter($character);
        $planet = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.character = :character')
            ->andWhere('p.radarAt is null and p.brouilleurAt is null')
            ->setParameters(['character' => $character])
            ->orderBy('p.ground', 'ASC')
            ->getQuery()
            ->setMaxresults(1)
            ->getOneOrNullResult();

        if ($character->getZombieAtt() > 0) {
            if (rand(0, 100) >= 5) {
                $character->setZombieAtt($character->getZombieAtt() - $mission->getGain());
                $mission->setMissionAt($nowMission);
                $reportMission->setTitle("Mission d'élimination zombies");
                $reportMission->setImageName("zombie_win_report.webp");
                $reportMission->setContent("L'escouade militaire envoyée en mission est de retour, son capitaine vous fait son rapport :<br> <span class='text-vert'>-" . number_format($mission->getGain()) . "</span> menace zombie sur la planète.");
            } else {
                $reportMission->setTitle("Échec mission");
                $reportMission->setImageName("zombie_lose_report.webp");
                $loseSoldiers = ($planet->getSoldier() / rand(8,10));
                $planet->setSoldier($planet->getSoldier() - $loseSoldiers);
                $character->setZombieAtt($character->getZombieAtt() + $mission->getGain());
                $reportMission->setContent("Des hommes de l'escouade militaire envoyée reviennent progressivement par petit groupe.<br>Ils ne s'attendaient pas à une telle résistance... <span class='text-rouge'>" . number_format($loseSoldiers) . "</span> soldats sont morts durant la mission et votre niveau de menace a augmenté de " . $mission->getGain() . " !");
                $mission->setMissionAt($nowMission);
            }
        } else {
            if (rand(0, 100) >= 5) {
                $zombieThreat = rand(1, 10);
                $character->setZombieAtt($character->getZombieAtt() + $zombieThreat);
                $reportMission->setTitle("Mission de récupération d'uranium");
                $reportMission->setImageName("uranium_win_report.webp");
                $reportMission->setContent("L'escouade militaire envoyée en mission est de retour, son capitaine vous fait son rapport :<br> <span class='text-vert'>+" . number_format($mission->getGain()) . "</span> uranium ont été récupérés en zone zombie.<br>Vous étiez en mission sur le territoire zombie et avez fait augmenter la menace de <span class='text-rouge'>+" . $zombieThreat ."</span>.");
                $planet->setUranium($planet->getUranium() + $mission->getGain());
                $mission->setMissionAt($nowMission);
            } else {
                $character->setZombieAtt($character->getZombieAtt() + $mission->getGain());
                $reportMission->setTitle("Échec mission");
                $reportMission->setImageName("zombie_lose_report.webp");
                $reportMission->setContent("Des hommes de l'escouade militaire envoyée reviennent progressivement par petit groupe.<br>Ils ne s'attendaient pas à une telle résistance...<br>Vous étiez en mission sur le territoire zombie et avez fait augmenter la menace de <span class='text-rouge'>+". $mission->getGain() ."</span>.");
                $mission->setMissionAt($nowMission);
            }
        }

        if ($character->getZombieAtt() <= -150) {
            $character->setZombieAtt(-150);
        }

        if ($user->getTutorial() == 52) {
            $user->setTutorial(53);
        }

        $em->persist($reportMission);
        $em->flush();

        return $this->redirectToRoute('zombie', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/finir-missions/{usePlanet}", name="mission_finish_all", requirements={"usePlanet"="\d+"})
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function zombieFinishAllAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $planet = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.character = :character')
            ->andWhere('p.radarAt is null and p.brouilleurAt is null')
            ->setParameters(['character' => $character])
            ->orderBy('p.ground', 'ASC')
            ->getQuery()
            ->setMaxresults(1)
            ->getOneOrNullResult();

        $reportMission = new Report();
        $reportMission->setType('zombie');
        $reportMission->setSendAt($now);
        $reportMission->setCharacter($character);
        $reportMission->setTitle("Mission d'élimination zombies");
        $reportMission->setImageName("zombie_win_report.webp");
        $zombieAtt = $character->getZombieAtt();
        $zombieUranium = 0;
        $loseSoldiers = 0;

        $missions = $em->getRepository('App:Mission')
            ->createQueryBuilder('m')
            ->where('m.character = :character')
            ->andWhere('m.missionAt < :now')
            ->andWhere('m.type <= :level')
            ->setParameters(['character' => $character, 'now' => $now, 'level' => $character->getLevel(),])
            ->orderBy('m.type', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($missions as $mission) {

            $nowMission = new DateTime();
            $nowMission->add(new DateInterval('PT' . $mission->getTime() . 'S'));

            if ($zombieAtt >= 0) {
                if (rand(0, 100) >= 5) {
                    $zombieAtt = $zombieAtt - $mission->getGain();
                    $mission->setMissionAt($nowMission);
                } else {
                    $loseSoldiers = $loseSoldiers + ($planet->getSoldier() / rand(8,10));
                    $zombieAtt = $zombieAtt + $mission->getGain();
                    $mission->setMissionAt($nowMission);
                }
            } else {
                if (rand(0, 100) >= 5) {
                    $zombieAtt = $zombieAtt + rand(1, 10);
                    $zombieUranium = $zombieUranium + $mission->getGain();
                    $mission->setMissionAt($nowMission);
                } else {
                    $zombieAtt = $zombieAtt + $mission->getGain();
                    $mission->setMissionAt($nowMission);
                }
            }
        }
        $planet->setSoldier($planet->getSoldier() - $loseSoldiers);
        $planet->setUranium($planet->getUranium() + $zombieUranium);
        $diffZombieAtt = $zombieAtt - $character->getZombieAtt();
        $character->setZombieAtt($zombieAtt);

        if ($character->getZombieAtt() <= -150) {
            $character->setZombieAtt(-150);
        }

        if ($user->getTutorial() == 52) {
            $user->setTutorial(53);
        }

        $reportMission->setContent("Voici le rapport de vos missions zombies :<br><span class='text-vert'>+" . number_format($zombieUranium) . "</span> gains d'uraniums lors de vos recherches !<br><span class='text-vert'>" . number_format($diffZombieAtt) . "</span> menaces zombies. <br><span class='text-rouge'>-" . number_format($loseSoldiers) . "</span> soldats perdus.");

        $em->persist($reportMission);
        $em->flush();

        return $this->redirectToRoute('zombie', ['usePlanet' => $usePlanet->getId()]);
    }
}