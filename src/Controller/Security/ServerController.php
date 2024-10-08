<?php

namespace App\Controller\Security;

use App\Entity\Event;
use App\Entity\Ship;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Planet;
use App\Entity\Sector;
use App\Entity\Galaxy;
use App\Entity\Server;
use App\Entity\Salon;
use App\Entity\Commander;
use App\Entity\Rank;
use App\Entity\Fleet;

/**
 * @Route("/serveur")
 */
class ServerController extends AbstractController
{
    /**
     * @Route("/creation-serveur/{name}", name="create_server", requirements={"name"="\w+"})
     * @param ManagerRegistry $doctrine
     * @param string $name
     * @return RedirectResponse
     * @throws Exception
     */
    public function createServerAction(ManagerRegistry $doctrine, string $name): RedirectResponse
    {
        $em = $doctrine->getManager();

        $server = new Server($name, 1, 0.500, 19, 30, 23, 00, 1);
        $em->persist($server);
        $em->flush();

        $event = new Event('ZombieLvlack', $server, 6, 21, 00, 6, 22, 00);
        $em->persist($event);
        $em->flush();

        $event = new Event('TotalWar', $server, 10, 8, 30, 10, 23, 00);
        $em->persist($event);
        $em->flush();

        $event = new Event('ProductArmy', $server, 2, 01, 00, 3, 00, 01);
        $em->persist($event);
        $em->flush();

        $event = new Event('InvadeAliens', $server, 7, 12, 00, 7, 14, 00);
        $em->persist($event);
        $em->flush();

        $event = new Event('Recycling', $server, 3, 01, 00, 4, 00, 01);
        $em->persist($event);
        $em->flush();

        $event = new Event('Raider', $server, 4, 12, 00, 4, 20, 00);
        $em->persist($event);
        $em->flush();

        $event = new Event('ArenaAlliance', $server, 12, 20, 00, 12, 22, 00);
        $em->persist($event);
        $em->flush();

        $event = new Event('DestroyFleet', $server, 8, 18, 00, 8, 22, 00);
        $em->persist($event);
        $em->flush();

        $event = new Event('ServerVsServer', $server, 30, 19, 00, 60, 23, 00);
        $em->persist($event);
        $em->flush();

        $salon = new Salon('Public', $server);
        $em->persist($salon);
        $em->flush();

        $iaZombieUser = $doctrine->getRepository(User::class)->findOneBy(['id' => 1]);

        $iaZombie = new Commander($iaZombieUser, 'Zombie', $server);
        $iaZombie->setBitcoin(100);
        $iaZombie->setZombie(1);
        $iaZombie->setBot(1);
        $iaZombie->setImageName('hydre.webp');
        $rank = new Rank($iaZombie);
        $em->persist($rank);
        $iaZombie->setRank($rank);
        $em->persist($iaZombie);
        $ship = new Ship();
        $iaZombie->setShip($ship);
        $ship->setCommander($iaZombie);
        $em->persist($ship);
        $em->flush();

        $iaAlien = new Commander($iaZombieUser, 'Aliens', $server);
        $iaAlien->setBitcoin(100);
        $iaAlien->setAlien(1);
        $iaAlien->setBot(1);
        $iaAlien->setImageName('hydre.webp');
        $rank = new Rank($iaAlien);
        $em->persist($rank);
        $iaAlien->setRank($rank);
        $em->persist($iaAlien);
        $ship = new Ship();
        $iaAlien->setShip($ship);
        $ship->setCommander($iaAlien);
        $em->persist($ship);
        $em->flush();

        return $this->redirectToRoute('server_select');
    }

    /**
     * @Route("/creation-galaxie/{server}", name="create_galaxy", requirements={"server"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Server $server
     * @return RedirectResponse
     * @throws NonUniqueResultException
     */
    public function createGalaxyAction(ManagerRegistry $doctrine, Server $server): RedirectResponse
    {
        $em = $doctrine->getManager();
        $image = [
            'planet1.webp', 'planet2.webp', 'planet3.webp', 'planet4.webp', 'planet5.webp', 'planet6.webp',
            'planet7.webp', 'planet8.webp', 'planet9.webp', 'planet10.webp', 'planet11.webp', 'planet12.webp',
            'planet13.webp', 'planet14.webp', 'planet15.webp', 'planet16.webp', 'planet17.webp', 'planet18.webp',
            'planet19.webp', 'planet20.webp', 'planet21.webp', 'planet22.webp', 'planet23.webp', 'planet24.webp',
            'planet25.webp', 'planet26.webp', 'planet27.webp', 'planet28.webp', 'planet29.webp', 'planet30.webp',
            'planet31.webp', 'planet32.webp', 'planet33.webp'
        ];
        $imageSun = ['sun1.webp', 'sun2.webp', 'sun3.webp', 'sun4.webp', 'sun5.webp', 'sun6.webp'];
        $planetPve = ["1", "10", "91", "100", "12", "19", "82", "89", "23", "28", "73", "78", "34", "37", "64", "67", "45", "46", "55", "56"];
        $planetPveOne = ["1", "10", "91", "100"];
        $planetPveTwo = ["12", "19", "82", "89"];
        $planetPveThree = ["23", "28", "73", "78"];
        $planetPveFour = ["34", "37", "64", "67"];
        //$planetPveFive = ["45", "46", "55", "56"];

        $sectorPositions = [
            '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52', '53',
            '54', '55', '56', '57', '58', '59', '60', '5', '6', '15', '16', '25', '26', '35',
            '36', '45', '46', '55', '56', '65', '66', '75', '76', '85', '86', '95', '96'
        ];

        $iaLevelOn = ["41", "51", "50", "60", "5", "6", "95", "96"];
        $iaLevelTwo = ["42", "52", "49", "59", "15", "16", "85", "86"];
        $iaLevelThree = ["43", "53", "48", "58", "25", "26", "75", "76"];
        $iaLevelFour = ["44", "54", "47", "57", "35", "36", "65", "66"];
        //$iaLevelFive = ["45", "46", "55", "56"];

        $iaPlayer = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->where('c.alien = true')
            ->andWhere('c.server =:server')
            ->setParameters(['server' => $server])
            ->getQuery()
            ->getOneOrNullResult();

        $nbrGalaxy = $doctrine->getRepository(Galaxy::class)->findBy(['server' => $server]);
        $nbrSector = 1;
        $nbrPlanets = 0;
        $galaxy = new Galaxy($server, count($nbrGalaxy) + 1, );
        $em->persist($galaxy);

        while ($nbrSector <= 100) {
            $nbrPlanet = 1;
            $alreadyBot1 = 0;
            $alreadyBot2 = 0;
            $sector = new Sector($galaxy, $nbrSector);
            $em->persist($sector);
            while ($nbrPlanet <= 25) {
                if ($nbrPlanet == 13) {
                    $planet = new Planet(null, 'Soleil', 0, 0, $nbrPlanet, $sector, $imageSun[rand(0, 5)],  0, null,true, false);
                } else {
                    if ((in_array($nbrSector, $planetPve)) && ((!$alreadyBot1 && rand(0, 8) == 1) || !$alreadyBot1 && $nbrPlanet == 25)) {
                        $alreadyBot1 = true;
                        if (in_array($nbrSector, $planetPveOne)) {
                            $planet = new Planet(null, 'Fort Marchand I', 500, 150, $nbrPlanet, $sector, 'trader.webp',  1, null, false, false);
                            $fleet = new Fleet();
                            $fleet->setHunterWar(200);
                            $fleet->setCorvetWar(25);
                            $fleet->setFregatePlasma(2);
                            $fleet->setDestroyer(1);
                        } elseif (in_array($nbrSector, $planetPveTwo)) {
                            $planet = new Planet(null, 'Fort Marchand II', 1000, 300, $nbrPlanet, $sector, 'trader.webp',  2, null, false, false);
                            $fleet = new Fleet();
                            $fleet->setHunterWar(2000);
                            $fleet->setCorvetWar(250);
                            $fleet->setFregatePlasma(20);
                            $fleet->setDestroyer(10);
                        } elseif (in_array($nbrSector, $planetPveThree)) {
                            $planet = new Planet(null, 'Fort Marchand III', 1500, 450, $nbrPlanet, $sector, 'trader.webp',  3, null, false, false);
                            $fleet = new Fleet();
                            $fleet->setHunterWar(20000);
                            $fleet->setCorvetWar(2500);
                            $fleet->setFregatePlasma(200);
                            $fleet->setDestroyer(100);
                        } elseif (in_array($nbrSector, $planetPveFour)) {
                            $planet = new Planet(null, 'Fort Marchand IV', 3000, 600, $nbrPlanet, $sector, 'trader.webp',  4, null, false, false);
                            $fleet = new Fleet();
                            $fleet->setHunterWar(200000);
                            $fleet->setCorvetWar(25000);
                            $fleet->setFregatePlasma(2000);
                            $fleet->setDestroyer(1000);
                        } else {
                            $planet = new Planet(null, 'Fort Marchand V', 4000, 800, $nbrPlanet, $sector, 'trader.webp',  5, null, false, false);
                            $fleet = new Fleet();
                            $fleet->setHunterWar(2000000);
                            $fleet->setCorvetWar(250000);
                            $fleet->setFregatePlasma(20000);
                            $fleet->setDestroyer(10000);
                        }
                        $fleet->setCommander($iaPlayer);
                        $fleet->setPlanet($planet);
                        $fleet->setAttack(1);
                        $fleet->setName('Horde');
                        $fleet->setSignature($fleet->getNbSignature());
                        $em->persist($fleet);
                    } elseif ((in_array($nbrSector, $sectorPositions)) && ((!$alreadyBot2 && rand(0, 8) == 1) || !$alreadyBot2 && $nbrPlanet == 24)) {
                        $alreadyBot2 = true;
                        $fleetBot = new Fleet();
                        if (in_array($nbrSector, $iaLevelOn)) {
                            $planet = new Planet($iaPlayer, 'Fort Hydra I', 0, 0, $nbrPlanet, $sector, 'bot_one.webp',  0, null, false, false);
                            $fleetBot->setHunterWar(rand(5, 200));
                        } elseif (in_array($nbrSector, $iaLevelTwo)) {
                            $planet = new Planet($iaPlayer, 'Fort Hydra II', 0, 0, $nbrPlanet, $sector, 'bot_two.webp',  0, null, false, false);
                            $fleetBot->setHunterWar(rand(5000, 20000));
                            $fleetBot->setCorvetWar(rand(500, 1500));
                        } elseif (in_array($nbrSector, $iaLevelThree)) {
                            $planet = new Planet($iaPlayer, 'Fort Hydra III', 0, 0, $nbrPlanet, $sector, 'bot_three.webp',  0, null, false, false);
                            $fleetBot->setHunterWar(rand(80000, 250000));
                            $fleetBot->setCorvetWar(rand(40000, 80000));
                            $fleetBot->setFregatePlasma(rand(20000, 40000));
                        } elseif (in_array($nbrSector, $iaLevelFour)) {
                            $planet = new Planet($iaPlayer, 'Fort Hydra IV', 0, 0, $nbrPlanet, $sector, 'bot_four.webp',  0, null, false, false);
                            $fleetBot->setHunterWar(rand(650000, 1250000));
                            $fleetBot->setCorvetWar(rand(180000, 380000));
                            $fleetBot->setFregatePlasma(rand(60000, 80000));
                            $fleetBot->setDestroyer(rand(10000, 20000));
                        } else {
                            $planet = new Planet($iaPlayer, 'Fort Hydra V', 0, 0, $nbrPlanet, $sector, 'bot_five.webp',  0, null, false, false);
                            $fleetBot->setHunterWar(rand(1250000, 3250000));
                            $fleetBot->setCorvetWar(rand(380000, 780000));
                            $fleetBot->setFregatePlasma(rand(80000, 120000));
                            $fleetBot->setDestroyer(rand(50000, 90000));
                        }
                        $iaPlayer->addPlanet($planet);
                        $fleetBot->setCommander($iaPlayer);
                        $fleetBot->setPlanet($planet);
                        $fleetBot->setAttack(1);
                        $fleetBot->setName('Horde');
                        $fleetBot->setSignature($fleetBot->getNbSignature());
                        $em->persist($fleetBot);
                    } else {
                        if (rand(1, 19) < 6) {
                            $planet = new Planet(null, 'Vide', 0, 0, $nbrPlanet, $sector, null,  0, null,false, true);
                        } elseif (rand(0, 65) < 2) {
                            $planet = new Planet(null, 'Astéroïdes', 0, 0, $nbrPlanet, $sector, 'cdr_niobium.webp',  0, 'niobium', false, false);
                            $fleet = new Fleet();
                            $fleet->setHunterWar(rand(50, 3000));
                            $fleet->setCorvetWar(rand(50, 200));
                            $fleet->setFregatePlasma(rand(20, 100));
                            $fleet->setDestroyer(rand(1, 50));
                            $fleet->setCommander($iaPlayer);
                            $fleet->setPlanet($planet);
                            $fleet->setAttack(1);
                            $fleet->setName('Horde');
                            $fleet->setSignature($fleet->getNbSignature());
                            $em->persist($fleet);
                        } elseif (rand(0, 70) < 2) {
                            $planet = new Planet(null, 'Astéroïdes', 0, 0, $nbrPlanet, $sector, 'cdr_water.webp',  0, 'water', false, false);
                            $fleet = new Fleet();
                            $fleet->setHunterWar(rand(50, 3000));
                            $fleet->setCorvetWar(rand(50, 200));
                            $fleet->setFregatePlasma(rand(20, 100));
                            $fleet->setDestroyer(rand(1, 50));
                            $fleet->setCommander($iaPlayer);
                            $fleet->setPlanet($planet);
                            $fleet->setAttack(1);
                            $fleet->setName('Horde');
                            $fleet->setSignature($fleet->getNbSignature());
                            $em->persist($fleet);
                        } elseif (rand(0, 90) < 2) {
                            $planet = new Planet(null, 'Astéroïdes', 0, 0, $nbrPlanet, $sector, 'cdr_uranium.webp',  0, 'uranium', false, false);
                            $fleet = new Fleet();
                            $fleet->setHunterWar(rand(50, 3000));
                            $fleet->setCorvetWar(rand(50, 200));
                            $fleet->setFregatePlasma(rand(20, 100));
                            $fleet->setDestroyer(rand(1, 50));
                            $fleet->setCommander($iaPlayer);
                            $fleet->setPlanet($planet);
                            $fleet->setAttack(1);
                            $fleet->setName('Horde');
                            $fleet->setSignature($fleet->getNbSignature());
                            $em->persist($fleet);
                        } else {
                            $nbrPlanets++;
                            if (($nbrSector >= 1 && $nbrSector <= 9) || ($nbrSector >= 92 && $nbrSector <= 99) || ($nbrSector % 10 == 0 || $nbrSector % 10 == 1)) {
                                if ($nbrPlanet == 4 || $nbrPlanet == 6 || $nbrPlanet == 15 || $nbrPlanet == 17 || $nbrPlanet == 25) {
                                    $planet = new Planet(null, 'Inhabitée', 25, 5, $nbrPlanet, $sector, $image[rand(0, 32)],  0, null, false, false);
                                } else {
                                    $planet = new Planet(null, 'Inhabitée', rand(80, 95), rand(18, 21), $nbrPlanet, $sector, $image[rand(0, 32)],  0, null, false, false);
                                }
                            } elseif ($nbrSector == 45 || $nbrSector == 46 || $nbrSector == 55 || $nbrSector == 56) {
                                $planet = new Planet(null, 'Inhabitée', rand(140, 180), rand(30, 50), $nbrPlanet, $sector, $image[rand(0, 32)],  0, null, false, false);
                            } else {
                                $planet = new Planet(null, 'Inhabitée', rand(100, 115), rand(25, 29), $nbrPlanet, $sector, $image[rand(0, 32)],  0, null, false, false);
                            }
                        }
                    }
                }
                $em->persist($planet);
                $nbrPlanet++;
            }
            $nbrSector++;
        }
        $em->flush();

        $putFleets = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->andWhere('s.position in (:pos)')
            ->andWhere('g.position = :galaxy')
            ->setParameters(['pos' => [45, 46, 55, 56], 'galaxy' => count($nbrGalaxy) + 1])
            ->getQuery()
            ->getResult();

        foreach ($putFleets as $putFleet) {
            $fleet = new Fleet();
            $fleet->setHunterWar(rand(1250000, 3250000));
            $fleet->setCorvetWar(rand(380000, 780000));
            $fleet->setFregatePlasma(rand(80000, 120000));
            $fleet->setDestroyer(rand(50000, 90000));
            $fleet->setCommander($iaPlayer);
            $fleet->setPlanet($putFleet);
            $fleet->setAttack(1);
            $fleet->setName('Horde');
            $fleet->setSignature($fleet->getNbSignature());
            $em->persist($fleet);
        }
        $em->flush();

        return $this->redirectToRoute('server_select');
    }

    /**
     * @Route("/creation-univers-petit/{server}", name="create_little", requirements={"server"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Server $server
     * @return RedirectResponse
     */
    public function createServerLittleAction(ManagerRegistry $doctrine, Server $server): RedirectResponse
    {
        $em = $doctrine->getManager();
        $image = [
            'planet1.webp', 'planet2.webp', 'planet3.webp', 'planet4.webp', 'planet5.webp', 'planet6.webp',
            'planet7.webp', 'planet8.webp', 'planet9.webp', 'planet10.webp', 'planet11.webp', 'planet12.webp',
            'planet13.webp', 'planet14.webp', 'planet15.webp', 'planet16.webp', 'planet17.webp', 'planet18.webp',
            'planet19.webp', 'planet20.webp', 'planet21.webp', 'planet22.webp', 'planet23.webp', 'planet24.webp',
            'planet25.webp', 'planet26.webp', 'planet27.webp', 'planet28.webp', 'planet29.webp', 'planet30.webp',
            'planet31.webp', 'planet32.webp', 'planet33.webp'
        ];
        $x = 1;
        while($x < 6) {
            $nbrSector = 1;
            $nbrPlanets = 0;
            $galaxy = new Galaxy($server, $x);
            $em->persist($galaxy);

            while ($nbrSector <= 16) {
                $nbrPlanet = 1;
                $sector = new Sector($galaxy, $nbrSector);
                $em->persist($sector);
                while ($nbrPlanet <= 25) {
                    if (($nbrSector == 7 || $nbrSector == 10) && $nbrPlanet == 13) {
                        $planet = new Planet();
                        $planet->setTrader(true);
                        $planet->setGround(400);
                        $planet->setSky(80);
                        $planet->setImageName('trader.webp');
                        $planet->setName('Marchands');
                        $planet->setSector($sector);
                        $planet->setPosition($nbrPlanet);
                    } else {
                        if (rand(1, 20) < 2) {
                            $planet = new Planet();
                            $planet->setEmpty(true);
                            $planet->setName('Vide');
                            $planet->setSector($sector);
                            $planet->setPosition($nbrPlanet);
                        } elseif (rand(0, 101) < 1) {
                            $planet = new Planet();
                            $planet->setCdr(true);
                            $planet->setImageName('cdr.webp');
                            $planet->setName('Astéroïdes');
                            $planet->setSector($sector);
                            $planet->setPosition($nbrPlanet);
                        } else {
                            $nbrPlanets++;
                            $planet = new Planet();

                            $planet->setImageName($image[rand(0, 32)]);
                            $planet->setSector($sector);
                            $planet->setPosition($nbrPlanet);
                            if (($nbrSector >= 1 && $nbrSector <= 5) || ($nbrSector >= 12 && $nbrSector <= 16) || ($nbrSector == 8 || $nbrSector == 9)) {
                                if ($nbrPlanet == 4 || $nbrPlanet == 6 || $nbrPlanet == 15 || $nbrPlanet == 17 || $nbrPlanet == 25) {
                                    $planet->setGround(25);
                                    $planet->setSky(5);
                                } else {
                                    $planet->setGround(rand(30, 40));
                                    $planet->setSky(rand(5, 8));
                                }
                            } elseif ($nbrSector == 6 || $nbrSector == 7 || $nbrSector == 10 || $nbrSector == 11) {
                                $planet->setGround(rand(65, 85));
                                $planet->setSky(rand(10, 20));
                            } else {
                                $planet->setGround(rand(48, 60));
                                $planet->setSky(rand(8, 11));
                            }
                        }
                    }
                    $em->persist($planet);
                    $nbrPlanet++;
                }
                $nbrSector++;
            }
            $em->flush();
            $x = $x + 1;
        }

        return $this->redirectToRoute('server_select');
    }

    /**
     * @Route("/destruction-univers/{server}", name="destroy_server", requirements={"server"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Server $server
     * @return RedirectResponse
     */
    public function destroyServerAction(ManagerRegistry $doctrine, Server $server): RedirectResponse
    {
        $em = $doctrine->getManager();

        $commanders = $doctrine->getRepository(Commander::class)->findBy(['server' => $server]);

        foreach ($commanders as $commander) {
            $ship = $commander->getShip();
            if ($ship && $commander->getId() != 1) {
                $commander->setShip(null);
                $em->remove($ship);
            }
            if ($commander->getAlliance()) {
                $ally = $commander->getAlliance();
                $commander->setAlliance(null);
                foreach ($ally->getAllieds() as $allied) {
                    $em->remove($allied);
                }

                foreach ($ally->getWars() as $war) {
                    $em->remove($war);
                }

                foreach ($ally->getPeaces() as $peace) {
                    $em->remove($peace);
                }

                foreach ($ally->getExchanges() as $exchange) {
                    $em->remove($exchange);
                }

                foreach ($ally->getPnas() as $pna) {
                    $em->remove($pna);
                }

                foreach ($ally->getGrades() as $grade) {
                    $em->remove($grade);
                }

                $ally->setImageName(null);
                $em->remove($ally);
            }

            if ($commander->getRank()) {
                $em->remove($commander->getRank());
            }

            foreach ($commander->getOffers() as $offer) {
                $commander->removeOffer($offer);
            }

            foreach ($commander->getFleetLists() as $list) {
                foreach ($list->getFleets() as $fleetL) {
                    $fleetL->setFleetList(null);
                }
                $em->remove($list);
            }

            foreach ($commander->getFleets() as $fleet) {
                $destination = $fleet->getDestination();
                if ($destination) {
                    $em->remove($destination);
                }
                $fleet->setCommander(null);
                $fleet->setPlanet(null);
                $commander->removeFleet($fleet);
            }

            foreach ($commander->getPlanets() as $planet) {
                $product = $planet->getProduct();
                if ($product) {
                    $product->setPlanet(null);
                    $em->remove($product);
                }
                $contructions = $planet->getConstructions();
                if ($contructions) {
                    foreach ($contructions as $contruction) {
                        $em->remove($contruction);
                    }
                }
                $planet->setCommander(null);
            }

            foreach ($commander->getStats() as $stats) {
                $commander->removeStats($stats);
                $em->remove($stats);
            }

            foreach ($commander->getViews() as $view) {
                $em->remove($view);
            }

            foreach ($commander->getReports() as $report) {
                $report->setCommander(null);
                $report->setImageName(null);
                $em->remove($report);
            }

            foreach ($commander->getMessages() as $message) {
                $em->remove($message);
            }

            foreach ($commander->getSContents() as $sContent) {
                $em->remove($sContent);
            }

            $commander->setImageName(null);
            $em->remove($commander);
        }
        $em->flush();

        $salons = $doctrine->getRepository(Salon::class)->findAll();

        foreach ($salons as $salon) {
            foreach ($salon->getViews() as $view) {
                $em->remove($view);
            }
            $em->remove($salon);
        }

        $planets = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->andWhere('g.server = :server')
            ->setParameters(['server' => $server])
            ->getQuery()
            ->getResult();

        foreach ($planets as $planet) {
            $planet->setImageName(null);
            $em->remove($planet);
        }

        $sectors = $doctrine->getRepository(Sector::class)
            ->createQueryBuilder('s')
            ->join('s.galaxy', 'g')
            ->andWhere('g.server = :server')
            ->setParameters(['server' => $server])
            ->getQuery()
            ->getResult();

        foreach ($sectors as $sector) {
            $em->remove($sector);
        }

        $galaxies = $doctrine->getRepository(Galaxy::class)->findBy(['server' => $server]);

        foreach ($galaxies as $galaxy) {
            $em->remove($galaxy);
        }

        $em->flush();

        foreach ($server->getEvents() as $event) {
            $em->remove($event);
        }

        $em->remove($server);

        $em->flush();

        return $this->redirectToRoute('server_select');
    }

    /**
     * @Route("/destruction-galaxy/{galaxy}", name="destroy_galaxy", requirements={"galaxy"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Galaxy $galaxy
     * @return RedirectResponse
     */
    public function destroyGalaxyAction(ManagerRegistry $doctrine, Galaxy $galaxy): RedirectResponse
    {
        $em = $doctrine->getManager();

        foreach ($galaxy->getSectors() as $sector) {
            foreach ($sector->getPlanets() as $planet) {
                $planet->setImageName(null);
                if ($planet->getProduct()) {
                    $em->remove($planet->getProduct());
                }
                foreach ($planet->getFleets() as $fleet) {
                    $destination = $fleet->getDestination();
                    if ($destination) {
                        $em->remove($destination);
                    }
                    $em->remove($fleet);
                }
                foreach ($planet->getConstructions() as $construction) {
                    $em->remove($construction);
                }
                $em->remove($planet);
            }
            $em->remove($sector);
        }

        $destinations = $doctrine->getRepository(Destination::class)
            ->createQueryBuilder('d')
            ->join('d.planet', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->where('g.id = :galaxyId')
            ->setParameters(['galaxyId' => $galaxy->getId()])
            ->getQuery()
            ->getResult();

        foreach ($destinations as $destination) {
            $em->remove($destination);
        }

        $em->flush();

        $em->remove($galaxy);
        $em->flush();

        return $this->redirectToRoute('server_select');
    }

    /**
     * @Route("/destruction-secteur", name="destroy_sectors")
     */
    public function destroySectorsAction(ManagerRegistry $doctrine): RedirectResponse
    {
        $em = $doctrine->getManager();

        $sectors = $doctrine->getRepository(Sector::class)
            ->createQueryBuilder('s')
            ->where('s.destroy = true')
            ->getQuery()
            ->getResult();

        foreach ($sectors as $sector) {
            foreach ($sector->getPlanets() as $planet) {
                foreach ($planet->getFleets() as $fleet) {
                    $fleet->setPlanet(null);
                }
                $planet->setImageName(null);
                $em->remove($planet);
            }
        }

        $fleets = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->where('f.planet is null')
            ->andWhere('f.flightAt is null')
            ->getQuery()
            ->getResult();

        foreach ($fleets as $fleet) {
            $em->remove($fleet);
        }

        $commanders = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->join('c.planets', 'p')
            ->where('p.id is null')
            ->getQuery()
            ->getResult();

        foreach ($commanders as $commander) {
            $commander->setGameOver(1);
        }
        $em->flush();

        return $this->redirectToRoute('server_select');
    }

    /**
     * @Route("/activer-connexion/{server}", name="active_server", requirements={"server"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Server $server
     * @return RedirectResponse
     */
    public function activeServerAction(ManagerRegistry $doctrine, Server $server): RedirectResponse
    {
        $em = $doctrine->getManager();

        $server->setOpen(1);

        $em->flush();

        return $this->redirectToRoute('server_select');
    }

    /**
     * @Route("/desactiver-connexion/{server}", name="deactive_server", requirements={"server"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Server $server
     * @return RedirectResponse
     */
    public function deactivateServerAction(ManagerRegistry $doctrine, Server $server): RedirectResponse
    {
        $em = $doctrine->getManager();

        $server->setOpen(0);

        $em->flush();

        return $this->redirectToRoute('server_select');
    }

    /**
     * @Route("/activer-pve/{server}", name="pve_server", requirements={"server"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Server $server
     * @return RedirectResponse
     */
    public function PveAction(ManagerRegistry $doctrine, Server $server): RedirectResponse
    {
        $em = $doctrine->getManager();

        $server->setPvp(0);

        $em->flush();

        return $this->redirectToRoute('server_select');
    }

    /**
     * @Route("/activer-pvp/{server}", name="pvp_server", requirements={"server"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Server $server
     * @return RedirectResponse
     */
    public function PvpAction(ManagerRegistry $doctrine, Server $server): RedirectResponse
    {
        $em = $doctrine->getManager();

        $server->setPvp(1);

        $em->flush();

        return $this->redirectToRoute('server_select');
    }
}