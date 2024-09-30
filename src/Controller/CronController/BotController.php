<?php

namespace App\Controller\CronController;

use App\Entity\Construction;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Ship;
use App\Entity\Rank;
use App\Entity\Destination;
use App\Entity\Fleet;
use App\Entity\S_Content;
use DateTime;
use Dateinterval;

class BotController extends AbstractController
{
    /**
     * @Route("/creation-bot/", name="create_the_bot")
     */
    public function createBotAction()
    {
        /*$em = $doctrine->getManager();
        $now = new DateTime();
        $creation = $now;
        $nickeNames = $doctrine->getRepository(NickName::class)->findAll();
        $salon = $doctrine->getRepository(Salon::class)
        ->createQueryBuilder('s')
        ->where('s.name = :name')
        ->setParameters(['name' => 'Public'])
        ->getQuery()
        ->getOneOrNullResult();

        foreach ($nickeNames as $nickName) {
            $nick = ucfirst ($nickName->getPseudo());
            $user = new User();
            $creation->add(new DateInterval('PT' . rand(1, 1000) . 'H'));
            $user->setUsername($nick);
            $user->setEmail($nick . '@fake.com');
            $user->setCreatedAt($creation);
            $user->setPassword(password_hash($nick . 'bot', PASSWORD_BCRYPT));
            $user->setTutorial(60);
            $user->setBot(true);
            $user->setActivityAt($now);
            $user->setActivityAt($now);
            $user->setNewletter(false);
            // image de profil
            $em->persist($commander);
            $em->flush();

            $planet = $doctrine->getRepository(Planet::class)
                ->createQueryBuilder('p')
                ->join('p.sector', 's')
                ->where('p.user is null')
                ->andWhere('p.ground = 25')
                ->andWhere('p.sky = 5')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if($planet) {
                $planet->setCommander($commander);
                $planet->setName('Terra Nova');
                $planet->setSonde(10);
                $planet->setRadar(2);
                $planet->setGroundPlace(10);
                $planet->setSkyPlace(1);
                $planet->setMiner(3);
                $planet->setNbProduction(66);
                $planet->setWtProduction(45);
                $planet->setExtractor(3);
                $planet->setSpaceShip(1);
                $planet->setHunter(500);
                $planet->setNiobium(150000);
                $planet->setWater(100000);
                $planet->setFregate(20);
                $planet->setWorker(100000);
                $planet->setSoldier(200);
                $planet->setColonizer(1);
                $user->addPlanet($planet);
                foreach ($planet->getFleets() as $fleet) {
                    if ($fleet->getCommander()->getZombie() == 1) {
                        $em->remove($fleet);
                    } else {
                        $fleet->setPlanet($fleet->getCommander()->getFirstPlanetFleet());
                    }
                }
            }
            $ship = new Ship();
            $user->setShip($ship);
            $ship->setCommander($commander);
            $em->persist($ship);
            $rank = new Rank($user);
            $em->persist($rank);
            $user->setRank($rank);
            $salon->addUser($commander);
        }
        $em->flush();
        exit;*/
    }

    /**
     * @Route("/manage-bot/", name="manage_the_bot")
     */
    #[NoReturn] public function manageBotAction(ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $move = new DateTime();
        $messageTime = new DateTime();
        $messageTime->sub(new DateInterval('PT' . rand(1, 400) . 'S'));
        $messageSent = 1;

        if (true) {
            $user = $doctrine->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.bot = true')
                ->andWhere('u.rank is null')
                ->andWhere('u.zombie = false')
                ->andWhere('u.trader = false')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            $commander = null;
            if ($commander) {

                $planet = $doctrine->getRepository(Planet::class)
                    ->createQueryBuilder('p')
                    ->join('p.sector', 's')
                    ->join('s.galaxy', 'g')
                    ->where('p.user is null')
                    ->andWhere('p.ground = 25')
                    ->andWhere('p.sky = 5')
                    ->andWhere('p.empty = false and p.trader = false and p.cdr = false and g.position = :gal and s.position = :sector')
                    ->setParameters(['galaxy' => rand(1, 25), 'sector' => rand(1, 100)])
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();

                if (!$planet) {
                    $planet = $doctrine->getRepository(Planet::class)
                        ->createQueryBuilder('p')
                        ->join('p.sector', 's')
                        ->join('s.galaxy', 'g')
                        ->where('p.user is null')
                        ->andWhere('p.ground = 25')
                        ->andWhere('p.sky = 5')
                        ->andWhere('p.empty = false and p.trader = false and p.cdr = false and g.position = :gal and s.position = :sector')
                        ->setParameters(['galaxy' => rand(1, 25), 'sector' => rand(1, 100)])
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();
                }

                if ($planet) {
                    $planet->setCommander($commander);
                    $planet->setName('Terra Nova');
                    $planet->setSonde(10);
                    $planet->setRadar(2);
                    $planet->setGroundPlace(10);
                    $planet->setSkyPlace(1);
                    $planet->setMiner(3);
                    $planet->setNbProduction(66);
                    $planet->setWtProduction(45);
                    $planet->setExtractor(3);
                    $planet->setSpaceShip(1);
                    $planet->setHunter(500);
                    $planet->setNiobium(150000);
                    $planet->setWater(100000);
                    $planet->setFregate(20);
                    $planet->setWorker(100000);
                    $planet->setSoldier(200);
                    $planet->setColonizer(1);
                    $user->addPlanet($planet);
                    foreach ($planet->getFleets() as $fleet) {
                        if ($fleet->getCommander()->getZombie() == 1) {
                            $em->remove($fleet);
                        } else {
                            $fleet->setPlanet($fleet->getCommander()->getFirstPlanetFleet());
                        }
                    }
                    $user->setTutorial(60);
                    $commander->setActivityAt($now);
                    //$commander->setActivityAt($now);

                    $ship = new Ship();
                    $commander->setShip($ship);
                    $ship->setCommander($commander);
                    $em->persist($ship);

                    $rank = new Rank($commander);
                    $em->persist($rank);
                }
            }
            echo "Ajout nouveau bot finis.<br>";
            $em->flush();
        }

        $bots = $doctrine->getRepository(User::class)
            ->createQueryBuilder('u')
            ->join('u.planets', 'p')
            ->join('u.rank', 'r')
            ->where('u.bot = true and u.trader = false and u.zombie = false')
            ->getQuery()
            ->getResult();

        $trader = $doctrine->getRepository(User::class)->findOneBy(['trader' => 1]);
        $planetTrader = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->andWhere('p.trader = true')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        $salon = $doctrine->getRepository(Salon::class)
            ->createQueryBuilder('s')
            ->where('s.name = :name')
            ->setParameters(['name' => 'Public'])
            ->getQuery()
            ->getOneOrNullResult();

        foreach ($bots as $bot) {
            if (rand(1, 5) == 2) {
                $cPlanet = $doctrine->getRepository(Planet::class)
                    ->createQueryBuilder('p')
                    ->where('p.user = :user')
                    ->andWhere('p.groundPlace < p.ground or p.island < 5')
                    ->andWhere('p.construct is null')
                    ->setParameters(['user' => $bot])
                    ->getQuery()
                    ->setMaxResults(1)
                    ->getOneOrNullResult();

                if (!$bot->getAlliance()) {
                    $offer = $doctrine->getRepository(Offer::class)
                        ->createQueryBuilder('p')
                        ->where('p.user = :user')
                        ->setParameters(['user' => $bot])
                        ->getQuery()
                        ->setMaxResults(1)
                        ->getOneOrNullResult();

                    if ($offer) {
                        $ally = $offer->getAlliance();
                        $ally->addUser($bot);
                        $bot->setAlliance($ally);
                        $bot->setJoinAllianceAt($now);
                        $bot->setGrade($ally->getNewMember());
                        $em->remove($offer);
                    }
                }

                $move->add(new DateInterval('PT' . rand(1, 60) . 'S'));
                if (rand(1, 500) == 501) {
                    $fPlanet = $doctrine->getRepository(Planet::class)
                        ->createQueryBuilder('p')
                        ->where('p.user = :user')
                        ->setParameters(['user' => $bot])
                        ->getQuery()
                        ->setMaxResults(1)
                        ->getOneOrNullResult();

                    $planet = $doctrine->getRepository(Planet::class)
                        ->createQueryBuilder('p')
                        ->join('p.commander', 'c')
                        ->join('p.sector', 's')
                        ->join('s.galaxy', 'g')
                        ->where('u.bot = false')
                        ->andWhere('p.user != :user')
                        ->andWhere('g.id = :galaxie')
                        ->setParameters(['user' => $bot, 'galaxie' => $fPlanet->getSector()->getGalaxy()->getId()])
                        ->getQuery()
                        ->setMaxResults(1)
                        ->getOneOrNullResult();

                    if ($planet) {
                        $sFleet = $fPlanet->getSector()->getPosition();
                        $sector = $planet->getSector()->getPosition();
                        $planete = $planet->getPosition();
                        $galaxy = $planet->getSector()->getGalaxy()->getPosition();
                        if ($fPlanet->getSector()->getGalaxy()->getPosition() != $galaxy) {
                            $base = 18;
                        } else {
                            $pFleet = $fPlanet->getPosition();
                            if ($sFleet == $sector) {
                                $x1 = ($pFleet - 1) % 5;
                                $x2 = ($planete - 1) % 5;
                                $y1 = ($pFleet - 1) / 5;
                                $y2 = ($planete - 1) / 5;
                            } else {
                                $x1 = (($sFleet - 1) % 10) * 3;
                                $x2 = (($sector - 1) % 10) * 3;
                                $y1 = (($sFleet - 1) / 10) * 3;
                                $y2 = (($sector - 1) / 10) * 3;
                            }
                            $base = sqrt(pow(($x2 - $x1), 2) + pow(($y2 - $y1), 2));
                        }
                        $sonde = new Fleet();
                        $sonde->setSonde(1);
                        $sonde->setCommander($bot);
                        $sonde->setPlanet($fPlanet);
                        $sonde->setName('Auto Sonde');
                        $sonde->setSignature($sonde->getNbSignature());
                        $speed = $sonde->getSpeed();
                        $server = $doctrine->getRepository(Server::class)->find(['id' => 1]);
                        $distance = $speed * $base * 1000 * $server->getSpeed();
                        $move->add(new DateInterval('PT' . round($distance) . 'S'));
                        $moreNow = new DateTime();
                        $moreNow->add(new DateInterval('PT' . 120 . 'S'));
                        $sonde->setFlightTime($move);
                        $destination = new Destination($sonde, $planet);
                        $em->persist($destination);
                        $sonde->setFlightAt(1);
                        $sonde->setCancelFlight($moreNow);
                        $em->persist($sonde);
                    }
                    //$bot->setActivityAt($now);
                }

                if (rand(1, 1500) == 1501) {
                    $planetsSeller = $doctrine->getRepository(Planet::class)
                        ->createQueryBuilder('p')
                        ->where('p.user = :user')
                        ->setParameters(['user' => $bot])
                        ->getQuery()
                        ->getResult();

                    foreach ($planetsSeller as $planet) {
                        if ($this->forward('App\Service\PlanetService::planetAttackedAction', ['planet'  => $planet->getId()])) {
                            $sellTime = new DateTime();
                            $sellTime->add(new DateInterval('PT' . 1200 . 'S'));
                            $seller = new Fleet();
                            $seller->setHunter(1);
                            $seller->setCommander($trader);
                            $seller->setPlanet($planet);
                            $destination = new Destination($seller, $planetTrader);
                            $em->persist($destination);
                            $seller->setFlightTime($sellTime);
                            $seller->setAttack(0);
                            $seller->setName('Cargos');
                            $seller->setSignature(250);
                            $em->persist($seller);
                            $bot->setBitcoin($bot->getBitcoin() + 50000);
                        }
                    }
                    // créer une flotte et l'envoyer recyclage
                    //$bot->setActivityAt($now);
                }
                if (rand(1, 8000) == 8001 && $messageSent == 1) {
                    $messageSent = 0;
                    $allMessages = ['Salut', 'Plop', 'bonjour', 'bonjour', 'ca va', 'Salut', 'Salut', 'Salut', 'Salut', 'Salut', 'Salut', 'bonjour', 'bonjour', 'bonjour', 'bonjour', 'bonjour', 'bonjour', 'bonjour', 'Slt tlm', 'ca va ?', 'wesh', 'bj', 'bonjour', 'hellooo', 'hello', 'hello', 'hello', 'hello', 'hello'];
                    $body = $allMessages[mt_rand(0, count($allMessages) - 1)];
                    $message = new S_Content($bot, nl2br($body), $salon);
                    $em->persist($message);
                    $userViews = $doctrine->getRepository(User::class)
                        ->createQueryBuilder('u')
                        ->where('u.bot = false')
                        ->getQuery()
                        ->getResult();
                    foreach ($userViews as $userView) {
                        $userView->setSalonAt(null);
                    }
                    // Alliance création/rejoindre/inviter
                    // créer une flotte et l'envoyer coloniser/envahir
                    //$bot->setActivityAt($now);
                }
                if ($cPlanet) {
                    $cPlanet->setSoldier($cPlanet->getSoldierMax());
                    $cPlanet->setHunter($cPlanet->getHunter() + rand(20, 75));
                    $cPlanet->setCorvet($cPlanet->getCorvet() + rand(10, 50));
                    $cPlanet->setFregate($cPlanet->getFregate() + rand(5, 25));
                    $construct = new Response ('false');
                    if ($cPlanet->getSpaceShip() < 1) {
                        $construct = $this->forward('App\Controller\CronController\BotController::buildBuildingBotAction', [
                            'usePlanet' => $cPlanet,
                            'building' => 'spaceShip',
                            'user' => $bot
                        ]);
                    }
                    if ($cPlanet->getIsland() < 5 && $construct && $construct->getContent() === 'false') {
                        $construct = $this->forward('App\Controller\CronController\BotController::buildBuildingBotAction', [
                            'usePlanet' => $cPlanet,
                            'building' => 'island',
                            'user' => $bot
                        ]);
                    }
                    if ($cPlanet->getLightUsine() < 1 && $construct && $construct->getContent() === 'false') {
                        $construct = $this->forward('App\Controller\CronController\BotController::buildBuildingBotAction', [
                            'usePlanet' => $cPlanet,
                            'building' => 'lightUsine',
                            'user' => $bot
                        ]);
                    }
                    if ($cPlanet->getHeavyUsine() < 1 && $construct && $construct->getContent() === 'false') {
                        $construct = $this->forward('App\Controller\CronController\BotController::buildBuildingBotAction', [
                            'usePlanet' => $cPlanet,
                            'building' => 'heavyUsine',
                            'user' => $bot
                        ]);
                    }
                    if ($cPlanet->getBunker() < 1 && $construct && $construct->getContent() === 'false') {
                        $construct = $this->forward('App\Controller\CronController\BotController::buildBuildingBotAction', [
                            'usePlanet' => $cPlanet,
                            'building' => 'bunker',
                            'user' => $bot
                        ]);
                    }
                    if ($construct && $construct->getContent() === 'false' && rand(1, 2) == 1) {
                        $construct = $this->forward('App\Controller\CronController\BotController::buildBuildingBotAction', [
                            'usePlanet' => $cPlanet,
                            'building' => 'skyRadar',
                            'user' => $bot
                        ]);
                    } elseif ($construct && $construct->getContent() === 'false') {
                        $construct = $this->forward('App\Controller\CronController\BotController::buildBuildingBotAction', [
                            'usePlanet' => $cPlanet,
                            'building' => 'skyJammer',
                            'user' => $bot
                        ]);
                    }
                    if ($construct && $construct->getContent() === 'false' && rand(1, 2) == 1) {
                        $this->forward('App\Controller\CronController\BotController::buildBuildingBotAction', [
                            'usePlanet' => $cPlanet,
                            'building' => 'miner',
                            'user' => $bot
                        ]);
                        $this->forward('App\Controller\CronController\BotController::buildBuildingBotAction', [
                            'usePlanet' => $cPlanet,
                            'building' => 'miner',
                            'user' => $bot
                        ]);
                        $this->forward('App\Controller\CronController\BotController::buildBuildingBotAction', [
                            'usePlanet' => $cPlanet,
                            'building' => 'miner',
                            'user' => $bot
                        ]);
                    } elseif ($construct && $construct->getContent() === 'false') {
                        $this->forward('App\Controller\CronController\BotController::buildBuildingBotAction', [
                            'usePlanet' => $cPlanet,
                            'building' => 'extractor',
                            'user' => $bot
                        ]);
                        $this->forward('App\Controller\CronController\BotController::buildBuildingBotAction', [
                            'usePlanet' => $cPlanet,
                            'building' => 'extractor',
                            'user' => $bot
                        ]);
                        $this->forward('App\Controller\CronController\BotController::buildBuildingBotAction', [
                            'usePlanet' => $cPlanet,
                            'building' => 'extractor',
                            'user' => $bot
                        ]);
                    }
                } elseif (rand(1, 2) == 1 && $bot->getTerraformation() < 25) {
                    $bot->setTerraformation(count($bot->getPlanets()) + 1);

                    if ($bot->getFirstPlanetFleet()) {
                        $newPlanet = $doctrine->getRepository(Planet::class)
                            ->createQueryBuilder('p')
                            ->join('p.sector', 's')
                            ->join('s.galaxy', 'g')
                            ->where('p.user is null')
                            ->andWhere('p.empty = false and p.trader = false and p.cdr = false and g.id = :gal and s.position = :sector')
                            ->setParameters(['galaxy' => $bot->getFirstPlanetFleet()->getSector()->getGalaxy(), 'sector' => rand(1, 100)])
                            ->getQuery()
                            ->setMaxResults(1)
                            ->getOneOrNullResult();

                        if ($newPlanet) {
                            $newPlanet->setCommander($bot);
                            $newPlanet->setName('Colonie');
                            $newPlanet->setSoldier(200);
                            $newPlanet->setScientist(0);
                            $newPlanet->setNbColo(count($bot->getPlanets()) + 1);
                        } else {
                            $newPlanet = $doctrine->getRepository(Planet::class)
                                ->createQueryBuilder('p')
                                ->join('p.sector', 's')
                                ->join('s.galaxy', 'g')
                                ->where('p.user is null')
                                ->andWhere('p.empty = false and p.trader = false and p.cdr = false and g.position = :gal and s.position = :sector')
                                ->setParameters(['galaxy' => rand(10, 25), 'sector' => rand(1, 100)])
                                ->getQuery()
                                ->setMaxResults(1)
                                ->getOneOrNullResult();

                            if ($newPlanet) {
                                $newPlanet->setCommander($bot);
                                $newPlanet->setName('Colonie');
                                $newPlanet->setSoldier(200);
                                $newPlanet->setScientist(0);
                                $newPlanet->setNbColo(count($bot->getPlanets()) + 1);
                            }
                        }
                    }
                }
                if (rand(1, 80) == 81) {
                    // envahir et lancer recherches
                    //$bot->setActivityAt($now);
                }
            }
        }
        echo "Bâtiment bot finis.";
        $em->flush();
        exit;
    }

    public function buildBuildingBotAction(ManagerRegistry $doctrine, $usePlanet, $building, $user): Response
    {
        $em = $doctrine->getManager();
        $now = new DateTime();

        $level = $user->getWhichBuilding($building, $usePlanet) + 1;
        $time = $user->getBuildingTime($building);
        $newGround = $usePlanet->getGroundPlace() + $user->getBuildingGroundPlace($building);
        $newSky = $usePlanet->getSkyPlace() + $user->getBuildingSkyPlace($building);

        if(($newGround > $usePlanet->getGround()) ||
            ($newSky > $usePlanet->getSky())) {
            return new Response ('false');
        }
        if ($usePlanet->getConstructAt() > $now) {
            $level = $level + $usePlanet->getConstructionsLike($building);
            $construction = new Construction($usePlanet, $building, $level * $time);
            $usePlanet->setGroundPlace($newGround);
            $usePlanet->setSkyPlace($newSky);
            $em->persist($construction);
        } else {
            $now->add(new DateInterval('PT' . round($level * $time) . 'S'));
            $usePlanet->setGroundPlace($newGround);
            $usePlanet->setSkyPlace($newSky);
            $usePlanet->setConstruct($building);
            $usePlanet->setConstructAt($now);
        }
        $em->flush();

        return new Response ('true');
    }
}
