<?php

namespace App\Controller\CronController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Entity\Ships;
use App\Entity\Rank;
use App\Entity\Destination;
use App\Entity\Fleet;
use App\Entity\S_Content;
use DateTime;
use Dateinterval;
use DateTimeZone;

class BotController extends AbstractController
{
    /**
     * @Route("/creation-bot/", name="create_the_bot")
     */
    public function createBotAction()
    {
        /*$em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $creation = $now;
        $nickeNames = $em->getRepository('App:NickName')->findAll();
        $salon = $em->getRepository('App:Salon')
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
            $user->setDailyConnect($now);
            $user->setLastActivity($now);
            $user->setNewletter(false);
            // image de profil
            $em->persist($user);
            $em->flush();

            $planet = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.sector', 's')
                ->where('p.user is null')
                ->andWhere('p.ground = :ground')
                ->andWhere('p.sky = :sky')
                ->setParameters(['ground' => 25, 'sky' => 5])
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if($planet) {
                $planet->setUser($user);
                $planet->setName('Terra Nova');
                $planet->setSonde(10);
                $planet->setRadar(2);
                $planet->setGroundPlace(10);
                $planet->setSkyPlace(1);
                $planet->setMiner(3);
                $planet->setNbProduction(12.6);
                $planet->setWtProduction(11.54);
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
                    if ($fleet->getUser()->getZombie() == 1) {
                        $em->remove($fleet);
                    } else {
                        $fleet->setPlanet($fleet->getUser()->getFirstPlanetFleet());
                    }
                }
            }
            $ships = new Ships();
            $user->setShip($ships);
            $em->persist($ships);
            $rank = new Rank();
            $em->persist($rank);
            $user->setRank($rank);
            $salon->addUser($user);
        }
        $em->flush();
        exit;*/
    }

    /**
     * @Route("/manage-bot/", name="manage_the_bot")
     */
    public function manageBotAction()
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $move = new DateTime();
        $move->setTimezone(new DateTimeZone('Europe/Paris'));
        $creation = new DateTime();
        $creation->setTimezone(new DateTimeZone('Europe/Paris'));
        $messageSent = 1;
        $bots = $em->getRepository('App:User')
        ->createQueryBuilder('u')
        ->where('u.bot = true')
        ->getQuery()
        ->getResult();
        $merchant = $em->getRepository('App:User')->findOneBy(['merchant' => 1]);
        $planetMerchant = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->andWhere('p.merchant = true')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        $salon = $em->getRepository('App:Salon')
            ->createQueryBuilder('s')
            ->where('s.name = :name')
            ->setParameters(['name' => 'Public'])
            ->getQuery()
            ->getOneOrNullResult();

        foreach ($bots as $bot) {
            $move->add(new DateInterval('PT' . rand(1, 60) . 'S'));
            $creation->add(new DateInterval('PT' . rand(1, 10) . 'M'));
            if (rand(1, 48) == 1) {
                $fPlanet = $em->getRepository('App:Planet')
                    ->createQueryBuilder('p')
                    ->where('p.user = :user')
                    ->setParameters(['user' => $bot])
                    ->getQuery()
                    ->setMaxResults(1)
                    ->getOneOrNullResult();

                $planet = $em->getRepository('App:Planet')
                    ->createQueryBuilder('p')
                    ->join('p.user', 'u')
                    ->join('p.sector', 's')
                    ->join('s.galaxy', 'g')
                    ->where('u.bot = false')
                    ->andWhere('p.user != :user')
                    ->andWhere('g.id = :galaxie')
                    ->setParameters(['user' => $bot, 'galaxie' => $fPlanet->getSector()->getGalaxy()->getId()])
                    ->getQuery()
                    ->setMaxResults(1)
                    ->getOneOrNullResult();

                if ($fPlanet && $planet) {
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
                    $sonde->setUser($bot);
                    $sonde->setPlanet($fPlanet);
                    $sonde->setName('Auto Sonde');
                    $sonde->setSignature($sonde->getNbrSignatures());
                    $speed = $sonde->getSpeed();
                    $distance = $speed * $base * 100;
                    $move->add(new DateInterval('PT' . round($distance) . 'S'));
                    $moreNow = new DateTime();
                    $moreNow->setTimezone(new DateTimeZone('Europe/Paris'));
                    $moreNow->add(new DateInterval('PT' . 120 . 'S'));
                    $sonde->setFlightTime($move);
                    $destination = new Destination();
                    $destination->setFleet($sonde);
                    $destination->setPlanet($planet);
                    $em->persist($destination);
                    $sonde->setFlightType(1);
                    $sonde->setCancelFlight($moreNow);
                    $em->persist($sonde);
                }
                $bot->setLastActivity($now);
            }

            if (rand(1, 25) == 1) {
                $planetsSeller = $em->getRepository('App:Planet')
                    ->createQueryBuilder('p')
                    ->where('p.user = :user')
                    ->setParameters(['user' => $bot])
                    ->getQuery()
                    ->getResult();

                foreach ($planetsSeller as $planet) {
                    if ($planet->getOffensiveFleet($bot) != 'ennemy') {
                        $sellTime = new DateTime();
                        $sellTime->setTimezone(new DateTimeZone('Europe/Paris'));
                        $sellTime->add(new DateInterval('PT' . 1200 . 'S'));
                        $seller = new Fleet();
                        $seller->setHunter(1);
                        $seller->setUser($merchant);
                        $seller->setPlanet($planet);
                        $destination = new Destination();
                        $destination->setFleet($seller);
                        $destination->setPlanet($planetMerchant);
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
                $bot->setLastActivity($now);
            }
            if (rand(1, 150) == 1 && $messageSent == 1) {
                $message = new S_Content();
                $messageSent = 0;
                $message->setSalon($salon);
                $allMessages = ['Salut', 'Plop', 'Slt tlm', 'ca va ?', 'wesh', 'bj', 'bonjour', 'hellooo', 'hello', 'comment on fait pour coloniser ?', 'c\'est quoi les zombies ?', 'je me fais attaquer !!!!'];
                $body = $allMessages[mt_rand(0, count($allMessages) - 1)];
                $message->setMessage(nl2br($body));
                $message->setSendAt($now);
                $message->setUser($bot);
                $em->persist($message);
                $userViews = $em->getRepository('App:User')
                    ->createQueryBuilder('u')
                    ->where('u.bot = false')
                    ->getQuery()
                    ->getResult();
                foreach($userViews as $userView) {
                    $userView->setSalonAt(null);
                }
                // Alliance création/rejoindre/inviter
                // créer une flotte et l'envoyer coloniser/envahir
                $bot->setLastActivity($now);
            }
        }
        $em->flush();
        exit;
    }
}
