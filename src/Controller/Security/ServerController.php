<?php

namespace App\Controller\Security;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use App\Entity\Sector;
use App\Entity\Galaxy;
use App\Entity\Salon;
use App\Entity\User;
use App\Entity\Rank;
use App\Entity\Fleet;
use DateTime;

/**
 * @Route("/serveur")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class ServerController extends AbstractController
{
    /**
     * @Route("/creation-galaxie/{serverId}", name="create_galaxy", requirements={"serverId"="\d+"})
     */
    public function createServerAction($serverId)
    {
        $em = $this->getDoctrine()->getManager();
        $image = [
            'planet1.png', 'planet2.png', 'planet3.png', 'planet4.png', 'planet5.png', 'planet6.png',
            'planet7.png', 'planet8.png', 'planet9.png', 'planet10.png', 'planet11.png', 'planet12.png',
            'planet13.png', 'planet14.png', 'planet15.png', 'planet16.png', 'planet17.png', 'planet18.png',
            'planet19.png', 'planet20.png', 'planet21.png', 'planet22.png', 'planet23.png', 'planet24.png',
            'planet25.png', 'planet26.png', 'planet27.png', 'planet28.png', 'planet29.png', 'planet30.png',
            'planet31.png', 'planet32.png', 'planet33.png'
        ];
        $server = $em->getRepository('App:Server')->find(['id' => $serverId]);

        $nbrGalaxy = $em->getRepository('App:Galaxy')->findBy(['server' => $server]);
        $nbrSector = 1;
        $nbrPlanets = 0;
        $galaxy = new Galaxy();
        $galaxy->setPosition(count($nbrGalaxy) + 1);
        $galaxy->setServer($server);
        $em->persist($galaxy);
        /*$salon = new Salon();
        $salon->setName('Public');
        $em->persist($salon);
        $em->flush();*/
        $iaPlayer = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);

        /*if ($iaPlayer == null) {
            $iaPlayer = new User();
            $now = new DateTime();
            $iaPlayer->setUsername('Zombies');
            $iaPlayer->setEmail('support@areauniverse.eu');
            $iaPlayer->setCreatedAt($now);
            $iaPlayer->setPassword(password_hash('ViolGratuit2019', PASSWORD_BCRYPT));
            $iaPlayer->setBitcoin(100);
            $iaPlayer->setImageName('hydre.png');
            $rank = new Rank();
            $em->persist($rank);
            $iaPlayer->setRank($rank);
            $em->persist($iaPlayer);

            $iaSalon = $em->getRepository('App:Salon')->find(['id' => 1]);
            $iaSalon->addUser($iaPlayer);
            $em->flush();
        }*/

        while ($nbrSector <= 100) {
            $nbrPlanet = 1;
            $sector = new Sector();
            $sector->setGalaxy($galaxy);
            $sector->setPosition($nbrSector);
            $em->persist($sector);
            while ($nbrPlanet <= 25) {
                if (($nbrSector == 23 || $nbrSector == 28 || $nbrSector == 73 || $nbrSector == 78) && $nbrPlanet == 13) {
                    $planet = new Planet();
                    $planet->setMerchant(true);
                    $planet->setGround(4000);
                    $planet->setSky(800);
                    $planet->setImageName('merchant.png');
                    $planet->setName('Marchands');
                    $planet->setSector($sector);
                    $planet->setPosition($nbrPlanet);
                    if ($server->getPvp(1)) {
                        $fleet = new Fleet();
                        $fleet->setHunterWar(200);
                        $fleet->setCorvetWar(25);
                        $fleet->setFregatePlasma(2);
                        $fleet->setDestroyer(1);
                        $fleet->setUser($iaPlayer);
                        $fleet->setPlanet($planet);
                        $fleet->setAttack(1);
                        $fleet->setName('Horde');
                        $fleet->setSignature($fleet->getNbrSignatures());
                        $em->persist($fleet);
                    }
                } else {
                    if (rand(1, 20) < 6) {
                        $planet = new Planet();
                        $planet->setEmpty(true);
                        $planet->setName('Vide');
                        $planet->setSector($sector);
                        $planet->setPosition($nbrPlanet);
                    } elseif (rand(0, 101) < 2) {
                        $planet = new Planet();
                        $planet->setCdr(true);
                        $planet->setImageName('cdr.png');
                        $planet->setName('Astéroïdes');
                        $planet->setSector($sector);
                        $planet->setPosition($nbrPlanet);
                        if ($server->getPvp(1)) {
                            $fleet = new Fleet();
                            $fleet->setHunterWar(rand(50, 3000));
                            $fleet->setCorvetWar(rand(50, 200));
                            $fleet->setFregatePlasma(rand(20, 100));
                            $fleet->setDestroyer(rand(1, 50));
                            $fleet->setUser($iaPlayer);
                            $fleet->setPlanet($planet);
                            $fleet->setAttack(1);
                            $fleet->setName('Horde');
                            $fleet->setSignature($fleet->getNbrSignatures());
                            $em->persist($fleet);
                        }
                    } else {
                        $nbrPlanets++;
                        $planet = new Planet();

                        $planet->setImageName($image[rand(0, 32)]);
                        $planet->setSector($sector);
                        $planet->setPosition($nbrPlanet);
                        if (($nbrSector >= 1 && $nbrSector <= 9) || ($nbrSector >= 92 && $nbrSector <= 99) || ($nbrSector % 10 == 0 || $nbrSector % 10 == 1)) {
                            if ($nbrPlanet == 4 || $nbrPlanet == 6 || $nbrPlanet == 15 || $nbrPlanet == 17 || $nbrPlanet == 25) {
                                $planet->setGround(25);
                                $planet->setSky(5);
                            } else {
                                $planet->setGround(rand(80, 95));
                                $planet->setSky(rand(18, 21));
                            }
                        } elseif ($nbrSector == 45 || $nbrSector == 46 || $nbrSector == 55 || $nbrSector == 56) {
                            $planet->setGround(rand(140, 180));
                            $planet->setSky(rand(30, 50));
                        } else {
                            $planet->setGround(rand(100, 115));
                            $planet->setSky(rand(25, 29));
                        }
                    }
                }
                $em->persist($planet);
                $nbrPlanet++;
            }
            $nbrSector++;
        }
        $em->flush();

        if ($server->getPvp(1)) {
            $iaPlanet = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->where('p.user is null')
                ->andWhere('p.ground > 70')
                ->andWhere('p.sky > 15')
                ->andWhere('p.ground < 86')
                ->andWhere('p.sky < 21')
                ->andWhere('g.position = :galaxy')
                ->setParameters(['galaxy' => count($nbrGalaxy) + 1])
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            $iaPlanet->setUser($iaPlayer);
            $iaPlanet->setWorker(498500000);
            $iaPlanet->setWorkerMax(498500000);
            $iaPlanet->setSoldier(100010000);
            $iaPlanet->setSoldierMax(100010000);
            $iaPlanet->setTank(50000);
            $iaPlanet->setBunker(50000);
            $iaPlanet->setMetropole(744);
            $iaPlanet->setGroundPlace(5744);
            $iaPlanet->setGround(6000);
            $iaPlanet->setSkyPlace(744);
            $iaPlanet->setSky(1000);
            $iaPlanet->setImageName('hydra_planet.png');
            $iaPlanet->setName('Fort Hydra');
            $iaPlayer->addPlanet($iaPlanet);

            $putFleets = $em->getRepository('App:Planet')
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
                $fleet->setHunterWar(rand(80000, 250000));
                $fleet->setCorvetWar(rand(40000, 80000));
                $fleet->setFregatePlasma(rand(20000, 40000));
                $fleet->setDestroyer(rand(10000, 20000));
                $fleet->setUser($iaPlayer);
                $fleet->setPlanet($putFleet);
                $fleet->setAttack(1);
                $fleet->setName('Horde');
                $fleet->setSignature($fleet->getNbrSignatures());
                $em->persist($fleet);
            }
            $em->flush();
        }

        return $this->redirectToRoute('administration');
    }

    /**
     * @Route("/creation-univers-petit/{serverId}", name="create_little", requirements={"serverId"="\d+"})
     */
    public function createServerLittleAction($serverId)
    {
        $em = $this->getDoctrine()->getManager();
        $image = [
            'planet1.png', 'planet2.png', 'planet3.png', 'planet4.png', 'planet5.png', 'planet6.png',
            'planet7.png', 'planet8.png', 'planet9.png', 'planet10.png', 'planet11.png', 'planet12.png',
            'planet13.png', 'planet14.png', 'planet15.png', 'planet16.png', 'planet17.png', 'planet18.png',
            'planet19.png', 'planet20.png', 'planet21.png', 'planet22.png', 'planet23.png', 'planet24.png',
            'planet25.png', 'planet26.png', 'planet27.png', 'planet28.png', 'planet29.png', 'planet30.png',
            'planet31.png', 'planet32.png', 'planet33.png'
        ];
        $x = 1;
        while($x < 6) {
            $server = $em->getRepository('App:Server')->find(['id' => $serverId]);
            $nbrSector = 1;
            $nbrPlanets = 0;
            $galaxy = new Galaxy();
            $galaxy->setPosition($x);
            $galaxy->setServer($server);
            $em->persist($galaxy);

            while ($nbrSector <= 16) {
                $nbrPlanet = 1;
                $sector = new Sector();
                $sector->setGalaxy($galaxy);
                $sector->setPosition($nbrSector);
                $em->persist($sector);
                while ($nbrPlanet <= 25) {
                    if (($nbrSector == 7 || $nbrSector == 10) && $nbrPlanet == 13) {
                        $planet = new Planet();
                        $planet->setMerchant(true);
                        $planet->setGround(400);
                        $planet->setSky(80);
                        $planet->setImageName('merchant.png');
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
                            $planet->setImageName('cdr.png');
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

        return $this->redirectToRoute('administration');
    }

    /**
     * @Route("/destruction-univers/{serverId}", name="destroy_server", requirements={"serverId"="\d+"})
     */
    public function destroyServerAction($serverId)
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('App:User')->findAll();

        $salon = $em->getRepository('App:Salon')
            ->createQueryBuilder('s')
            ->where('s.name = :name')
            ->setParameters(['name' => 'Public'])
            ->getQuery()
            ->getOneOrNullResult();

        foreach ($users as $user) {
            $ship = $user->getShip();
            if ($ship && $user->getId() != 1) {
                $user->setShip(null);
                $em->remove($ship);
            }
            $user->setBitcoin(500);
            $user->setAlly(null);
            $user->setSearch(null);
            if ($user->getRank()) {
                $em->remove($user->getRank());
            }
            if ($user->getId() != 1) {
                $user->setBot(0);
            }
            $user->setRank(null);
            $user->setGrade(null);
            $user->setJoinAllyAt(null);
            $user->setAllyBan(null);
            $user->setScientistProduction(1);
            $user->setSearchAt(null);
            $user->setPlasma(0);
            $user->setLaser(0);
            $user->setMissile(0);
            $user->setArmement(0);
            $user->setRecycleur(0);
            $user->setCargo(0);
            $user->setTerraformation(0);
            $user->setDemography(0);
            $user->setUtility(0);
            $user->setBarge(0);
            $user->setHyperespace(0);
            $user->setDiscipline(0);
            $user->setHeavyShip(0);
            $user->setLightShip(0);
            $user->setIndustry(0);
            $user->setOnde(0);
            $user->setHyperespace(0);
            $user->setDiscipline(0);
            $user->setBarbed(0);
            $user->setTank(0);
            $user->setExpansion(0);
            $user->setPoliticArmement(0);
            $user->setPoliticCostScientist(0);
            $user->setPoliticArmor(0);
            $user->setPoliticBarge(0);
            $user->setPoliticCargo(0);
            $user->setPoliticColonisation(0);
            $user->setPoliticCostSoldier(0);
            $user->setPoliticCostTank(0);
            $user->setPoliticInvade(0);
            $user->setPoliticMerchant(0);
            $user->setPoliticPdg(0);
            $user->setPoliticProd(0);
            $user->setPoliticRecycleur(0);
            $user->setPoliticSearch(0);
            $user->setPoliticSoldierAtt(0);
            $user->setPoliticSoldierSale(0);
            $user->setPoliticTankDef(0);
            $user->setPoliticWorker(0);
            $user->setPoliticWorkerDef(0);
            foreach ($user->getProposals() as $proposal) {
                $user->removeProposal($proposal);
            }
            foreach ($user->getFleets() as $fleet) {
                if ($fleet->getDestination()) {
                    $em->remove($fleet->getDestination());
                }
                $user->removeFleet($fleet);
            }
            foreach ($user->getPlanets() as $planet) {
                $user->removePlanet($planet);
            }
            $salon->removeUser($user);
        }

        $sContents = $em->getRepository('App:S_Content')->findAll();

        foreach ($sContents as $sContent) {
            $em->remove($sContent);
        }

        $salons = $em->getRepository('App:Salon')
            ->createQueryBuilder('s')
            ->leftJoin('s.allys', 'a')
            ->where('s.name != :name')
            ->andWhere('a.id is null')
            ->setParameters(['name' => 'Public'])
            ->getQuery()
            ->getResult();

        foreach ($salons as $salon) {
            foreach ($salon->getViews() as $view) {
                $em->remove($view);
            }
            $em->remove($salon);
        }

        $reports = $em->getRepository('App:Report')->findAll();

        foreach ($reports as $report) {
            $report->setImageName(null);
            $em->remove($report);
        }

        $messages = $em->getRepository('App:Message')->findAll();

        foreach ($messages as $message) {
            $em->remove($message);
        }

        $products = $em->getRepository('App:Product')->findAll();

        foreach ($products as $product) {
            $product->setPlanet(null);
            $em->remove($product);
        }

        $destinations = $em->getRepository('App:Destination')->findAll();

        foreach ($destinations as $destination) {
            $em->remove($destination);
        }

        $fleets = $em->getRepository('App:Fleet')->findAll();

        foreach ($fleets as $fleet) {
            $em->remove($fleet);
        }

        $wars = $em->getRepository('App:War')->findAll();

        foreach ($wars as $war) {
            $em->remove($war);
        }

        $peaces = $em->getRepository('App:Peace')->findAll();

        foreach ($peaces as $peace) {
            $em->remove($peace);
        }

        $allys = $em->getRepository('App:Ally')->findAll();

        foreach ($allys as $ally) {
            $ally->setBitcoin(0);
            $ally->setPdg(0);
        }

        $constructions = $em->getRepository('App:Construction')->findAll();

        foreach ($constructions as $construction) {
            $em->remove($construction);
        }

        $missions = $em->getRepository('App:Mission')->findAll();

        foreach ($missions as $mission) {
            $em->remove($mission);
        }

        $planets = $em->getRepository('App:Planet')->findAll();

        foreach ($planets as $planet) {
            $planet->setImageName(null);
            $em->remove($planet);
        }

        $sectors = $em->getRepository('App:Sector')->findAll();

        foreach ($sectors as $sector) {
            $em->remove($sector);
        }

        $galaxys = $em->getRepository('App:Galaxy')->findAll();

        foreach ($galaxys as $galaxy) {
            $em->remove($galaxy);
        }

        $stats = $em->getRepository('App:Stats')->findAll();

        foreach ($stats as $stat) {
            $em->remove($stat);
        }

        $tracks = $em->getRepository('App:Track')->findAll();

        foreach ($tracks as $track) {
            $em->remove($track);
        }

        $allieds = $em->getRepository('App:Allied')->findAll();

        foreach ($allieds as $allied) {
            $em->remove($allied);
        }

        $wars = $em->getRepository('App:War')->findAll();

        foreach ($wars as $war) {
            $em->remove($war);
        }

        $pnas = $em->getRepository('App:Pna')->findAll();

        foreach ($pnas as $pna) {
            $em->remove($pna);
        }

        $proposals = $em->getRepository('App:Proposal')->findAll();

        foreach ($proposals as $proposal) {
            $em->remove($proposal);
        }

        $peaces = $em->getRepository('App:Peace')->findAll();

        foreach ($peaces as $peace) {
            $em->remove($peace);
        }

        $exchanges = $em->getRepository('App:Exchange')->findAll();

        foreach ($exchanges as $exchange) {
            $em->remove($exchange);
        }

        $grades = $em->getRepository('App:Grade')->findAll();

        foreach ($grades as $grade) {
            $em->remove($grade);
        }

        $allys = $em->getRepository('App:Ally')->findAll();

        foreach ($allys as $ally) {
            $em->remove($ally);
        }

        $views = $em->getRepository('App:View')->findAll();

        foreach ($views as $view) {
            $em->remove($view);
        }

        //$server = $em->getRepository('App:Server')->find(['id' => $serverId]);
        //$em->remove($server);

        $usserss = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->where('u.email LIKE :fake')
            ->setParameters(['fake' => '%fake.com%'])
            ->getQuery()
            ->getResult();
        foreach ($usserss as $ussers) {
            foreach ($ussers->getFleetLists() as $fleetList) {
                $em->remove($fleetList);
            }
            $em->remove($ussers);
        }

        $em->flush();

        return $this->redirectToRoute('administration');
    }

    /**
     * @Route("/destruction-galaxy/{galaxyId}", name="destroy_galaxy", requirements={"galaxyId"="\d+"})
     */
    public function destroyGalaxyAction($galaxyId)
    {
        $em = $this->getDoctrine()->getManager();
        $galaxy = $em->getRepository('App:Galaxy')->find(['id' => $galaxyId]);

        foreach ($galaxy->getSectors() as $sector) {
            foreach ($sector->getPlanets() as $planet) {
                if ($planet->getProduct()) {
                    $em->remove($planet->getProduct());
                }
                foreach ($planet->getFleets() as $fleet) {
                    $em->remove($fleet);
                }
                foreach ($planet->getConstructions() as $construction) {
                    $em->remove($construction);
                }
                $planet->setImageName(null);
                $em->remove($planet);
            }
            $em->remove($sector);
        }

        $destinations = $em->getRepository('App:Destination')
            ->createQueryBuilder('d')
            ->join('d.planet', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->where('g.id = :galaxyId')
            ->setParameters(['galaxyId' => $galaxyId])
            ->getQuery()
            ->getResult();

        foreach ($destinations as $destination) {
            $em->remove($destination);
        }

        $em->flush();

        $em->remove($galaxy);
        $em->flush();

        return $this->redirectToRoute('administration');
    }

    /**
     * @Route("/destruction-secteur", name="destroy_sectors")
     */
    public function destroySectorsAction()
    {
        $em = $this->getDoctrine()->getManager();

        $sectors = $em->getRepository('App:Sector')
            ->createQueryBuilder('s')
            ->where('s.destroy = true')
            ->getQuery()
            ->getResult();

        foreach ($sectors as $sector) {
            foreach ($sector->getPlanets() as $planet) {
                foreach ($planet->getFleets() as $fleet) {
                    $fleet->setPlanet(null);
                }
                $em->remove($planet);
            }
        }

        $fleets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->where('f.planet is null')
            ->andWhere('f.flightTime is null')
            ->getQuery()
            ->getResult();

        foreach ($fleets as $fleet) {
            $em->remove($fleet);
        }

        $users = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.planets', 'p')
            ->where('p.id is null')
            ->getQuery()
            ->getResult();

        foreach ($users as $user) {
            $user->setGameOver(1);
        }
        $em->flush();

        return $this->redirectToRoute('administration');
    }

    /**
     * @Route("/activer-connexion/{serverId}", name="active_server", requirements={"serverId"="\d+"})
     */
    public function activeServerAction($serverId)
    {
        $em = $this->getDoctrine()->getManager();
        $server = $em->getRepository('App:Server')->find(['id' => $serverId]);

        $server->setOpen(1);

        $em->flush();

        return $this->redirectToRoute('administration');
    }

    /**
     * @Route("/desactiver-connexion/{serverId}", name="deactive_server", requirements={"serverId"="\d+"})
     */
    public function deactivateServerAction($serverId)
    {
        $em = $this->getDoctrine()->getManager();
        $server = $em->getRepository('App:Server')->find(['id' => $serverId]);

        $server->setOpen(0);

        $em->flush();

        return $this->redirectToRoute('administration');
    }

    /**
     * @Route("/activer-pve/{serverId}", name="pve_server", requirements={"serverId"="\d+"})
     */
    public function PveAction($serverId)
    {
        $em = $this->getDoctrine()->getManager();
        $server = $em->getRepository('App:Server')->find(['id' => $serverId]);

        $server->setPvp(0);

        $em->flush();

        return $this->redirectToRoute('administration');
    }

    /**
     * @Route("/activer-pvp/{serverId}", name="pvp_server", requirements={"serverId"="\d+"})
     */
    public function PvpAction($serverId)
    {
        $em = $this->getDoctrine()->getManager();
        $server = $em->getRepository('App:Server')->find(['id' => $serverId]);

        $server->setPvp(1);

        $em->flush();

        return $this->redirectToRoute('administration');
    }
}