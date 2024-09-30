<?php

namespace App\Controller\CronController;

use App\Entity\User;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Fleet;
use DateInterval;
use DateTime;

class CronTaskController extends AbstractController
{
    /**
     * @Route("/construction/", name="cron_task")
     * @Route("/construction/{opened}/", name="cron_task_commander", requirements={"opened"="\d+"})
     * @Route("/connect/construction/{opened}/", name="connect_cron_task_commander", requirements={"opened"="\d+"})
     * @Route("/connect/carte-spatiale/construction/{opened}/", name="map_cron_task_commander", requirements={"opened"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param null $opened
     * @return Response
     * @throws NonUniqueResultException
     */
    public function cronTaskAction(ManagerRegistry $doctrine, $opened = null): Response
    {
        $em = $doctrine->getManager();
        $now = new DateTime();

        $commanderGOs = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->where('c.gameOver is not null')
            ->andWhere('c.rank is not null')
            ->getQuery()
            ->getResult();

        if ($commanderGOs) {
            echo "Game Over : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\GameOverController::gameOverCronAction', [
                'commanderGOs'  => $commanderGOs,
                'now'  => $now,
                'em' => $em
            ]);
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $asteroides = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->where('p.cdr = true')
            ->andWhere('p.recycleAt < :now OR p.recycleAt IS null')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if($asteroides) {
            echo "Astéroides : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\AsteroideController::AsteroideAction', [
                'asteroides'  => $asteroides,
                'em' => $em
            ]);
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $dailyReports = $doctrine->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->where('s.dailyReport < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($dailyReports) {
            foreach ($dailyReports as $dailyReport) {
                echo "Rapport quotidien : ";
                $cronValue = $this->forward(
                    'App\Controller\Connected\Execute\DailyController::dailyLoadAction',
                    [
                        'server' => $dailyReport,
                        'em' => $em
                    ]
                );
                echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
            }
        }

        $dests = $doctrine->getRepository(Destination::class)
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
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        while (1) {
            $firstFleet = $doctrine->getRepository(Fleet::class)
                ->createQueryBuilder('f')
                ->join('f.planet', 'p')
                ->select('p.id')
                ->where('f.fightAt < :now')
                ->andWhere('f.flightAt is null')
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
                echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
            } else {
                break;
            }
        }

        $planets = $doctrine->getRepository(Planet::class) // Actualiser DONE
            ->createQueryBuilder('p')
            ->where('p.constructAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($planets) {
            echo "Construction : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\PlanetsController::buildingsAction', [
                'planets'  => $planets,
                'now' => $now,
                'em' => $em
            ]);
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $planetSoldiers = $doctrine->getRepository(Planet::class) // CHANGE -> Actualiser dans entraînement, zombie, invasion, pillage, planètes, daily.
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
            echo $cronValue->getContent() ?? "<span style='color:#FF0000'>KO<span><br/>";
        }

        $planetTanks = $doctrine->getRepository(Planet::class) // CHANGE -> Actualiser dans entraînement, zombie, invasion, pillage, planètes, daily.
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
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $planetNuclears = $doctrine->getRepository(Planet::class) // CHANGE -> Actualiser dans overview, entraînement, chantier spatial.
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
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $planetScientists = $doctrine->getRepository(Planet::class) // Actualiser DONE
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
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $prods = $doctrine->getRepository(Product::class) // Actualiser DONE
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
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $products = $doctrine->getRepository(Product::class) // Actualiser DONE
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
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $radars = $doctrine->getRepository(Planet::class) // CHANGE -> Actualiser dans overview, carte spatiale.
            ->createQueryBuilder('p')
            ->where('p.radarAt < :now or p.brouilleurAt < :now')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if ($radars) {
            echo "Radar/Jammer : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\PlanetsController::radarsAction', [
                'radars'  => $radars,
                'now' => $now,
                'em'  => $em
            ]);
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $fleets = $doctrine->getRepository(Fleet::class) // Actualiser DONE
            ->createQueryBuilder('f')
            ->where('f.flightAt < :now')
            ->andWhere('f.flightType != :six or f.flightType is null')
            ->setParameters(['now' => $now, 'six' => 6])
            ->getQuery()
            ->getResult();

        if ($fleets) {
            echo "Flottes : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\MoveFleetController::centralizeFleetAction', [
                'fleets'  => $fleets,
                'now'  => $now,
                'em'  => $em
            ]);
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $nukeBombs = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->where('f.flightAt < :now')
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
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $fleetCdrs = $doctrine->getRepository(Fleet::class) // CHANGE ->  Flottes, carte spatiale, gérer flotte, combat.
            ->createQueryBuilder('f')                           // Pouvoir cumuler plusieurs recyclage en une seule fois.
            ->join('f.planet', 'p')
            ->where('f.recycleAt < :now')
            ->andWhere('f.recycleur > 0')
            ->andWhere('f.flightAt is null')
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
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $pacts = $doctrine->getRepository(Allied::class)
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
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $peaces = $doctrine->getRepository(Peace::class)
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
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $reports = $doctrine->getRepository(Report::class)
            ->createQueryBuilder('r')
            ->join('r.commander', 'c')
            ->andWhere('c.bot = true')
            ->getQuery()
            ->getResult();

        if($reports) {
            echo "Report : ";
            foreach ($reports as $report) {
                $report->setImageName(null);
                $em->remove($report);
            }
            $em->flush();
        }

        $zCommanders = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->join('c.planets', 'p')
            ->where('c.zombieAt < :now')
            ->andWhere('c.rank is not null')
            ->andWhere('c.bot = false')
            ->andWhere('c.zombie = false')
            ->andWhere('p.id is not null')
            ->groupBy('c.id')
            ->having('count(p.id) >= 3')
            ->setParameters(['now' => $now])
            ->getQuery()
            ->getResult();

        if($zCommanders) {
            echo "Zombies : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\ZombiesController::zombiesAction', [
                'zCommanders'  => $zCommanders,
                'now' => $now,
                'em' => $em
            ]);
            echo $cronValue->getContent()?:"<span style='color:#FF0000'>KO<span><br/>";
        }

        $embargos = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->join('p.commander', 'c')
            ->join('p.fleets', 'f')
            ->join('f.commander', 'cf')
            ->where('f.attack = true')
            ->andWhere('c.zombie = false')
            ->andWhere('cf.zombie = false')
            ->andWhere('c.bot = 0')
            ->andWhere('f.commander != p.commander')
            ->andWhere('f.signature > 125000')
            ->getQuery()
            ->getResult();

        if ($embargos) {
            echo "Embargos : ";
            $cronValue = $this->forward('App\Controller\Connected\Execute\PlanetsController::embargoPlanetAction', [
                'embargos' => $embargos,
                'now' => $now,
                'em' => $em
            ]);
            echo $cronValue->getContent() ?: "<span style='color:#FF0000'>KO<span><br/>";
        }

        if ($opened) {
            echo "<script>window.close();</script>";
        } else {
            echo "Cron succeed.";
            exit;
        }
        return new Response ("true");
    }

    /**
     * @Route("/make-miner/", name="make_miner")
     */
    public function makeMinerAction(ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();

        $planets = $doctrine->getRepository(Planet::class)
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
    public function deleteReportAction(ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();

        $stats = $doctrine->getRepository(Report::class)
            ->createQueryBuilder('r')
            ->join('r.commander', 'c')
            ->andWhere('c.bot = true')
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
    public function newBotAction(ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $fiveWeeks = new DateTime();
        $fiveWeeks->sub(new DateInterval('PT' . 3888000 . 'S'));

        $newBots = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->where('c.bot = false')
            ->andWhere('c.activityAt < :five')
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
    public function HordeYesAction(ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $hydra = $doctrine->getRepository(Commander::class)->findOneBy(['zombie' => 1]);
        $count = 0;

        $planets = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->join('p.fleets', 'f')
            ->where('f.commander = :commander')
            ->andWhere('f.flightAt is null')
            ->setParameters(['commander' => $hydra])
            ->getQuery()
            ->getResult();

        foreach ($planets as $planet) {
            if (count($planet->getFleets()) > 1) {
                $one = new Fleet();
                $one->setCommander($hydra);
                $one->setPlanet($planet);
                $one->setName('Horde V');
                $one->setAttack(1);
                foreach ($planet->getFleets() as $fleet) {
                    if ($fleet->getCommander() == $hydra && !$fleet->getDestination()) {
                        $one->setBarge($one->getBarge() + $fleet->getBarge());
                        $one->setHunter($one->getHunter() + $fleet->getHunter());
                        $one->setHunterWar($one->getHunterWar() + $fleet->getHunterWar());
                        $one->setCorvet($one->getCorvet() + $fleet->getCorvet());
                        $one->setCorvetLaser($one->getCorvetLaser() + $fleet->getCorvetLaser());
                        $one->setCorvetWar($one->getCorvetWar() + $fleet->getCorvetWar());
                        $one->setFregate($one->getFregate() + $fleet->getFregate());
                        $one->setFregatePlasma($one->getFregatePlasma() + $fleet->getFregatePlasma());
                        $one->setDestroyer($one->getDestroyer() + $fleet->getDestroyer());
                        $fleet->setCommander(null);
                        $em->remove($fleet);
                        $count = $count + 1;
                    }
                }
                $one->setSignature($one->getNbSignature());
                $em->persist($one);
            }
        }
        echo $count . " Horde regroupés finis.";
        $em->flush();
        exit;
    }

    /**
     * @Route("/mission-new/", name="mission_new")
     */
    public function missionNewAction(ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();

        $missions = $doctrine->getRepository(Mission::class)->findAll();

        foreach ($missions as $mission) {
            $em->remove($mission);
        }

        $em->flush();
        echo "<span style='color:#FF0000'>KO<span><br/>";
        exit;
    }

    /**
     * @Route("/test/", name="test")
     */
    public function testAction(ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $importedUsers = $doctrine->getRepository(ImportUser::class)
            ->createQueryBuilder('iu')
            ->getQuery()
            ->getResult();

        foreach ($importedUsers as $importedUser) {
            $user = new User( $importedUser->getUsername(), $importedUser->getEmail(), $importedUser->getPassword(), $importedUser->getIpAddress(), $importedUser->isConfirmed());
            $em->persist($user);
        }
        $em->flush();
        echo "<span style='color:#FF0000'>KO<span><br/>";
        exit;
    }

    /**
     * @Route("/soldierNew/", name="soldier_new")
     */
    public function soldierNewAction(ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();

        $grade = $doctrine->getRepository(Rank::class)
            ->createQueryBuilder('g')
            ->where('g.id = :id')
            ->setParameter('id', 34)
            ->getQuery()
            ->getOneOrNullResult();

        $em->remove($grade);
        $em->flush();
        echo "<span style='color:#FF0000'>KO<span><br/>";
        exit;
    }

    /**
     * @Route("/stockNew/", name="stock_new")
     */
    public function stockAction(ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();

        $plas = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->getQuery()
            ->getResult();

        foreach ($plas as $pla) {
            $pla->setNiobiumMax(75000 + ($pla->getNiobiumStock() > 0 ? $pla->getNiobiumStock() * 50000 : 0));
            $pla->setWaterMax(60000 + ($pla->getWaterStock() > 0 ? $pla->getWaterStock() * 50000 : 0));
            if ($pla->getWater() > $pla->getWaterMax()) {
                $pla->setWater($pla->getWaterMax());
            }
            if ($pla->getNiobium() > $pla->getNiobiumMax()) {
                $pla->setNiobium($pla->getNiobiumMax());
            }
        }
        $em->flush();
        echo "<span style='color:#FF0000'>KO<span><br/>";
        exit;
    }
}
