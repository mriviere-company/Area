<?php

namespace App\Controller\CronController;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Fleet;
use DateTimeZone;
use DateInterval;
use DateTime;

class CronTaskController extends AbstractController
{
    /**
     * @Route("/construction/", name="cron_task")
     * @Route("/construction/{opened}/", name="cron_task_user", requirements={"opened"="\d+"})
     * @Route("/connect/construction/{opened}/", name="connect_cron_task_user", requirements={"opened"="\d+"})
     * @Route("/connect/carte-spatiale/construction/{opened}/", name="map_cron_task_user", requirements={"opened"="\d+"})
     */
    public function cronTaskAction($opened = NULL)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $server = $em->getRepository('App:Server')->find(['id' => 1]);

        $userGOs = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->where('u.gameOver is not null')
            ->andWhere('u.rank is not null')
            ->getQuery()
            ->getResult();

        if ($userGOs) {
            echo "Game Over : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\GameOverController::gameOverCronAction', [
                'userGOs'  => $userGOs,
                'now'  => $now,
                'em' => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $asteroides = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.cdr = true')
            ->andWhere('p.recycleAt < :now OR p.recycleAt IS NULL')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if($asteroides) {
            echo "Astéroides : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\AsteroideController::AsteroideAction', [
                'asteroides'  => $asteroides,
                'em' => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $dailyReport = $em->getRepository('App:Server')
            ->createQueryBuilder('s')
            ->where('s.dailyReport < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getOneOrNullResult();

        if ($dailyReport) {
            echo "Rapport quotidien : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\DailyController::dailyLoadAction', [
                'now'  => $now,
                'em' => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $dests = $em->getRepository('App:Destination')
            ->createQueryBuilder('d')
            ->where('d.fleet is null')
            ->getQuery()
            ->getResult();

        if($dests) {
            echo "Suppression destinations : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\FleetsController::destinationDeleteAction', [
                'dests'  => $dests,
                'em' => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
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
                echo "Combat spatial : ";
                $cronValue = $this->forward('App\Controller\Connected\Execute\FightController::fightAction', [
                    'firstFleet'  => $firstFleet,
                    'now' => $now,
                    'em'  => $em
                ]);
                echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
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
            echo "Construction : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\PlanetsController::buildingsAction', [
                'planets'  => $planets,
                'em' => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $planetSoldiers = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.soldierAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($planetSoldiers) {
            echo "Soldats : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\PlanetsController::soldiersAction', [
                'planetSoldiers'  => $planetSoldiers,
                'em'  => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $planetTanks = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.tankAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($planetTanks) {
            echo "Tanks : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\PlanetsController::tanksAction', [
                'planetTanks'  => $planetTanks,
                'em'  => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $planetNuclears = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.nuclearAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($planetNuclears) {
            echo "Fabrication bombes nucléaires : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\PlanetsController::nuclearsAction', [
                'planetNuclears'  => $planetNuclears,
                'em'  => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $planetScientists = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.scientistAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($planetScientists) {
            echo "Scientifiques : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\PlanetsController::scientistsAction', [
                'planetScientists'  => $planetScientists,
                'em'  => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $prods = $em->getRepository('App:Product')
            ->createQueryBuilder('p')
            ->where('p.planet is null')
            ->getQuery()
            ->getResult();

        if($prods) {
            echo "Suppression Products : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\PlanetsController::productionDeleteAction', [
                'prods'  => $prods,
                'em' => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $products = $em->getRepository('App:Product')
            ->createQueryBuilder('p')
            ->where('p.productAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($products) {
            echo "Production flottes : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\PlanetsController::productsAction', [
                'products'  => $products,
                'em' => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $radars = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.radarAt < :now or p.brouilleurAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($radars) {
            echo "Radar/Brouilleur : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\PlanetsController::radarsAction', [
                'radars'  => $radars,
                'now' => $now,
                'em'  => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $fleets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->where('f.flightTime < :now')
            ->andWhere('f.flightType != :six or f.flightType is null')
            ->setParameters(['now' => $now, 'six' => 6])
            ->getQuery()
            ->getResult();

        if ($fleets) {
            echo "Flottes : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\MoveFleetController::centralizeFleetAction', [
                'fleets'  => $fleets,
                'server' => $server,
                'now'  => $now,
                'em'  => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $nukeBombs = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->where('f.flightTime < :now')
            ->andWhere('f.flightType = :six')
            ->setParameters(['now' => $now, 'six' => 6])
            ->getQuery()
            ->getResult();

        if ($nukeBombs) {
            echo "Impacts bombes nucléaires : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\FleetsController::nukeBombAction', [
                'nukeBombs'  => $nukeBombs,
                'now'  => $now,
                'em'  => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $fleetCdrs = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.planet', 'p')
            ->where('f.recycleAt < :now')
            ->andWhere('f.recycleur > 0')
            ->andWhere('f.flightTime is null')
            ->andWhere('p.nbCdr > 0 or p.wtCdr > 0')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($fleetCdrs) {
            echo "Recyclage : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\FleetsController::recycleAction', [
                'fleetCdrs'  => $fleetCdrs,
                'now'  => $now,
                'em'  => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $pacts = $em->getRepository('App:Allied')
            ->createQueryBuilder('al')
            ->where('al.dismissAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($pacts) {
            echo "Pactes : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\AlliancesController::pactsAction', [
                'pacts'  => $pacts,
                'em'  => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $peaces = $em->getRepository('App:Peace')
            ->createQueryBuilder('p')
            ->where('p.signedAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($peaces) {
            echo "Paix : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\AlliancesController::peacesAction', [
                'peaces'  => $peaces,
                'em'  => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $reports = $em->getRepository('App:Report')
            ->createQueryBuilder('r')
            ->join('r.user', 'u')
            ->andWhere('u.bot = true')
            ->getQuery()
            ->getResult();

        if($reports) {
            echo "Report : ";
            foreach ($reports as $report) {
                $report->setImageName(null);
                $em->remove($report);
            }
            $em->flush();
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        $zUsers = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.planets', 'p')
            ->where('u.zombieAt < :now')
            ->andWhere('u.rank is not null')
            ->andWhere('u.bot = false')
            ->andWhere('u.zombie = false')
            ->andWhere('p.id is not null')
            ->groupBy('u.id')
            ->having('count(p.id) >= 3')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if($zUsers) {
            echo "Zombies : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\ZombiesController::zombiesAction', [
                'zUsers'  => $zUsers,
                'now' => $now,
                'em' => $em
            ]);
            echo $cronValue->getContent()?$cronValue->getContent():"<span style='color:#FF0000'>KO<span><br/>";
        }

        if ($now > $server->getEmbargo()) {
            $embargos = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.user', 'u')
                ->join('p.fleets', 'f')
                ->join('f.user', 'uf')
                ->where('f.attack = true')
                ->andWhere('u.zombie = false')
                ->andWhere('uf.zombie = false')
                ->andWhere('u.bot = 0')
                ->andWhere('f.user != p.user')
                ->andWhere('f.signature > 125000')
                ->getQuery()
                ->getResult();

            if ($embargos) {
                echo "Embargos : ";
                $cronValue = $this->forward('App\Controller\Connected\Execute\PlanetsController::embargoPlanetAction', [
                    'embargos' => $embargos,
                    'server' => $server,
                    'now' => $now,
                    'em' => $em
                ]);
                echo $cronValue->getContent() ? $cronValue->getContent() : "<span style='color:#FF0000'>KO<span><br/>";
            }
        }

        if ($opened) {
            echo "<script>window.close();</script>";
        } else {
            echo "Cron terminé.";
            exit;
        }
        return new Response ("true");
    }

    /**
     * @Route("/make-miner/", name="make_miner")
     */
    public function makeMinerAction()
    {
        $em = $this->getDoctrine()->getManager();

        $planets = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->andWhere('p.miner > 0 or p.extractor > 0')
            ->getQuery()
            ->getResult();

        foreach ($planets as $planet) {
            if ($planet->getMiner()) {
                $planet->setNbProduction($planet->getMiner() * 22);
            } else {
                $planet->setNbProduction(7);
            }
            if ($planet->getExtractor()) {
                $planet->setWtProduction($planet->getExtractor() * 15);
            } else {
                $planet->setWtProduction(6);
            }
        }

        echo "Planète stats -> " . count($planets);
        $em->flush();

        exit;
    }

    /**
     * @Route("/delete-reports/", name="delete_reportss")
     */
    public function deleteReportAction()
    {
        $em = $this->getDoctrine()->getManager();

        $stats = $em->getRepository('App:Report')
            ->createQueryBuilder('r')
            ->join('r.user', 'u')
            ->andWhere('u.bot = true')
            ->getQuery()
            ->getResult();

        foreach ($stats as $stat) {
            $stat->setImageName(null);
            $em->remove($stat);
        }

        echo "Stats nettoyé.";
        $em->flush();

        exit;
    }

    /**
     * @Route("/new-bot/", name="new_bot")
     */
    public function newBotAction()
    {
        $em = $this->getDoctrine()->getManager();
        $fiveWeeks = new DateTime();
        $fiveWeeks->setTimezone(new DateTimeZone('Europe/Paris'));
        $fiveWeeks->sub(new DateInterval('PT' . 3888000 . 'S'));

        $newBots = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->where('u.bot = false')
            ->andWhere('u.lastActivity < :five')
            ->setParameters(['five' => $fiveWeeks])
            ->getQuery()
            ->getResult();

        foreach ($newBots as $newBot) {
            $newBot->setBot(1);
        }
        echo count($newBots) . " Nouveau bots finis.";
        $em->flush();
        exit;
    }

    /**
     * @Route("/regroupement/", name="horde_regroup")
     */
    public function HordeYesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $hydra = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);
        $count = 0;

        $planets = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->join('p.fleets', 'f')
            ->where('f.user = :user')
            ->andWhere('f.flightTime is null')
            ->setParameters(['user' => $hydra])
            ->getQuery()
            ->getResult();

        foreach ($planets as $planet) {
            if (count($planet->getFleets()) > 1) {
                $one = new Fleet();
                $one->setUser($hydra);
                $one->setPlanet($planet);
                $one->setName('Horde V');
                $one->setAttack(1);
                foreach ($planet->getFleets() as $fleet) {
                    if ($fleet->getUser() == $hydra && !$fleet->getDestination()) {
                        $one->setBarge($one->getBarge() + $fleet->getBarge());
                        $one->setHunter($one->getHunter() + $fleet->getHunter());
                        $one->setHunterWar($one->getHunterWar() + $fleet->getHunterWar());
                        $one->setCorvet($one->getCorvet() + $fleet->getCorvet());
                        $one->setCorvetLaser($one->getCorvetLaser() + $fleet->getCorvetLaser());
                        $one->setCorvetWar($one->getCorvetWar() + $fleet->getCorvetWar());
                        $one->setFregate($one->getFregate() + $fleet->getFregate());
                        $one->setFregatePlasma($one->getFregatePlasma() + $fleet->getFregatePlasma());
                        $one->setDestroyer($one->getDestroyer() + $fleet->getDestroyer());
                        $fleet->setUser(null);
                        $em->remove($fleet);
                        $count = $count + 1;
                    }
                }
                $one->setSignature($one->getNbrSignatures());
                $em->persist($one);
            }
        }
        echo $count . " Horde regroupés finis.";
        $em->flush();
        exit;
    }

    /**
     * @Route("/test/", name="test")
     */
    public function testAction()
    {
        $em = $this->getDoctrine()->getManager();

        $plas = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.metropole > 0 or p.city > 0')
            ->getQuery()
            ->getResult();

        foreach ($plas as $pla) {
            $pla->setWorkerMax(250000 + ($pla->getCity() * 125000) + ($pla->getMetropole() * 400000));
        }
        $em->flush();
        echo "<span style='color:#FF0000'>KO<span><br/>";
        exit;
    }
}
