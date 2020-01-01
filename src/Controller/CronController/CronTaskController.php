<?php

namespace App\Controller\CronController;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use DateTimeZone;
use DateInterval;
use DateTime;

class CronTaskController extends AbstractController
{
    /**
     * @Route("/construction/", name="cron_task")
     * @Route("/construction/{opened}/", name="cron_task_user", requirements={"opened"="\d+", "id"="\d+"})
     */
    public function cronTaskAction($opened = NULL)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));

        $userGOs = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->where('u.gameOver is not null')
            ->andWhere('u.rank is not null')
            ->getQuery()
            ->getResult();

        if ($userGOs) {
            $this->forward('App\Controller\Connected\Execute\GameOverController::gameOverAction', [
                'userGOs'  => $userGOs,
                'em' => $em
            ]);
        }

        $asteroides = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.cdr = true')
            ->andWhere('p.recycleAt < :now OR p.recycleAt IS NULL')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if($asteroides) {
            $this->forward('App\Controller\Connected\Execute\AsteroideController::AsteroideAction', [
                'asteroides'  => $asteroides,
                'em' => $em
            ]);
        }

        $dailyReport = $em->getRepository('App:Server')
            ->createQueryBuilder('s')
            ->where('s.dailyReport < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getOneOrNullResult();

        if ($dailyReport) {
            $this->forward('App\Controller\Connected\Execute\DailyController::dailyLoadAction', [
                'now'  => $now,
                'em' => $em
            ]);
        }

        while (1) {
            $firstFleet = $em->getRepository('App:Fleet')
                ->createQueryBuilder('f')
                ->join('f.planet', 'p')
                ->select('p.id')
                ->where('f.fightAt < :now')
                ->andWhere('f.flightTime is null')
                ->setParameters(['now' => $now])
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();

            if ($firstFleet) {
                $this->forward('App\Controller\Connected\Execute\FightController::fightAction', [
                    'firstFleet'  => $firstFleet,
                    'now' => $now,
                    'em'  => $em
                ]);
            } else {
                break;
            }
        }

        $planets = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.constructAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($planets) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::buildingsAction', [
                'planets'  => $planets,
                'em' => $em
            ]);
        }

        $planetSoldiers = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.soldierAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($planetSoldiers) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::soldiersAction', [
                'planetSoldiers'  => $planetSoldiers,
                'em'  => $em
            ]);
        }

        $planetTanks = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.tankAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($planetTanks) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::tanksAction', [
                'planetTanks'  => $planetTanks,
                'em'  => $em
            ]);
        }

        $planetNuclears = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.nuclearAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($planetNuclears) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::nuclearsAction', [
                'planetNuclear'  => $planetNuclears,
                'em'  => $em
            ]);
        }

        $planetScientists = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.scientistAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($planetScientists) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::scientistsAction', [
                'planetScientists'  => $planetScientists,
                'em'  => $em
            ]);
        }

        $products = $em->getRepository('App:Product')
            ->createQueryBuilder('p')
            ->where('p.productAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($products) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::productsAction', [
                'products'  => $products,
                'em' => $em
            ]);
        }

        $radars = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.radarAt < :now or p.brouilleurAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($radars) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::radarsAction', [
                'radars'  => $radars,
                'now' => $now,
                'em'  => $em
            ]);
        }

        $fleets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->where('f.flightTime < :now')
            ->andWhere('f.flightType != :six or f.flightType is null')
            ->setParameters(['now' => $now, 'six' => 6])
            ->getQuery()
            ->getResult();

        if ($fleets) {
            $this->forward('App\Controller\Connected\Execute\MoveFleetController::centralizeFleetAction', [
                'fleets'  => $fleets,
                'now'  => $now,
                'em'  => $em
            ]);
        }

        $nukeBombs = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->where('f.flightTime < :now')
            ->andWhere('f.flightType = :six')
            ->setParameters(['now' => $now, 'six' => 6])
            ->getQuery()
            ->getResult();

        if ($nukeBombs) {
            $this->forward('App\Controller\Connected\Execute\FleetsController::nukeBombAction', [
                'nukeBombs'  => $nukeBombs,
                'now'  => $now,
                'em'  => $em
            ]);
        }

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

        if ($fleetCdrs) {
            $this->forward('App\Controller\Connected\Execute\FleetsController::recycleAction', [
                'fleetCdrs'  => $fleetCdrs,
                'now'  => $now,
                'em'  => $em
            ]);
        }

        $pacts = $em->getRepository('App:Allied')
            ->createQueryBuilder('al')
            ->where('al.dismissAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($pacts) {
            $this->forward('App\Controller\Connected\Execute\AlliancesController::pactsAction', [
                'pacts'  => $pacts,
                'em'  => $em
            ]);
        }

        $peaces = $em->getRepository('App:Peace')
            ->createQueryBuilder('p')
            ->where('p.signedAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($peaces) {
            $this->forward('App\Controller\Connected\Execute\AlliancesController::peacesAction', [
                'peaces'  => $peaces,
                'em'  => $em
            ]);
        }

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

        if($zUsers) {
            $this->forward('App\Controller\Connected\Execute\ZombiesController::zombiesAction', [
                '$zUsers'  => $zUsers,
                'now' => $now,
                'em' => $em
            ]);
        }

        if ($opened) {
            echo "<script>window.close();</script>";
        } else {
            echo "Cron terminé.";
            exit;
        }
    }

    /**
     * @Route("/repare/", name="repare_it")
     */
    public function repareAction()
    {
        $em = $this->getDoctrine()->getManager();

        $bots = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.rank', 'r')
            ->where('u.bot = true')
            ->getQuery()
            ->getResult();

        foreach ($bots as $bot) {
            $bot->setTerraformation($bot->getTerraformation() + 1);
            if ($bot->getFirstPlanetFleet()) {
                $newPlanet = $em->getRepository('App:Planet')
                    ->createQueryBuilder('p')
                    ->join('p.sector', 's')
                    ->join('s.galaxy', 'g')
                    ->where('p.user is null')
                    ->andWhere('p.empty = false and p.merchant = false and p.cdr = false and g.id = :gal and s.position = :sector')
                    ->setParameters(['gal' => $bot->getFirstPlanetFleet()->getSector()->getGalaxy(), 'sector' => rand(1, 100)])
                    ->getQuery()
                    ->setMaxResults(1)
                    ->getOneOrNullResult();

                if ($newPlanet) {
                    $newPlanet->setUser($bot);
                    $newPlanet->setName('Colonie');
                    $newPlanet->setSoldier(50);
                    $newPlanet->setScientist(0);
                    $newPlanet->setNbColo(count($bot->getPlanets()) + 1);
                } else {
                    $newPlanet = $em->getRepository('App:Planet')
                        ->createQueryBuilder('p')
                        ->join('p.sector', 's')
                        ->join('s.galaxy', 'g')
                        ->where('p.user is null')
                        ->andWhere('p.empty = false and p.merchant = false and p.cdr = false and g.position = :gal and s.position = :sector')
                        ->setParameters(['gal' => rand(4, 10), 'sector' => rand(1, 100)])
                        ->getQuery()
                        ->setMaxResults(1)
                        ->getOneOrNullResult();

                    if ($newPlanet) {
                        $newPlanet->setUser($bot);
                        $newPlanet->setName('Colonie');
                        $newPlanet->setSoldier(50);
                        $newPlanet->setScientist(0);
                        $newPlanet->setNbColo(count($bot->getPlanets()) + 1);
                    }
                }
            }
        }
        $em->flush();
        exit;
    }

    /**
     * @Route("/new-bot/", name="new_bot")
     */
    public function newBotAction()
    {
        $em = $this->getDoctrine()->getManager();
        $threeWeeks = new DateTime();
        $threeWeeks->setTimezone(new DateTimeZone('Europe/Paris'));
        $threeWeeks->sub(new DateInterval('PT' . 1814400 . 'S'));

        $newBots = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.rank', 'r')
            ->where('u.bot = false')
            ->andWhere('u.lastActivity < :three')
            ->setParameters(['three' => $threeWeeks])
            ->getQuery()
            ->getResult();

        foreach ($newBots as $newBot) {
            $newBot->setBot(1);
        }
        $em->flush();
        exit;
    }
}
