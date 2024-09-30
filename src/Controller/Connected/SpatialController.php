<?php

namespace App\Controller\Connected;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\SpatialShipType;
use App\Form\Front\SpatialFleetType;
use App\Entity\Fleet;
use App\Entity\Product;
use App\Entity\Report;
use App\Entity\Planet;
use Dateinterval;
use DateTime;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class SpatialController extends AbstractController
{
    /**
     * @Route("/chantier-spatial/{usePlanet}", name="spatial", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function spatialAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();

        if($commander->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }
        if ($usePlanet->getProduct() && $usePlanet->getProduct()->getProductAt() < $now) {
            $this->forward('App\Controller\Connected\Execute\PlanetController::productOneAction', [
                'product'  => $usePlanet->getProduct(),
                'em' => $em
            ]);
        }

        $seconds = $this->forward('App\Controller\Connected\Execute\ChronosController::planetActivityAction', [
            'planet' => $usePlanet,
            'now'  => $now,
            'em' => $em]);

        if ($seconds->getContent() >= 60) {
            $this->forward('App\Controller\Connected\Execute\PlanetsGenController::planetGenAction', [
                'planet' => $usePlanet,
                'seconds' => $seconds->getContent(),
                'now'  => $now,
                'em' => $em]);
        }

        $form_spatialShip = $this->createForm(SpatialShipType::class);
        $form_spatialShip->handleRequest($request);

        if ($form_spatialShip->isSubmitted() && $form_spatialShip->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if($usePlanet->getSpaceShip() == 0) {
                return $this->redirectToRoute('spatial', ['usePlanet' => $usePlanet->getId()]);
            }

            if(($user->getTutorial() == 21)) {
                $user->setTutorial(22);
                $now->add(new DateInterval('PT' . 10 . 'S'));
                $product = new Product();
                $product->setPlanet($usePlanet);
                $product->setHunterWar(10);
                $product->setCorvetWar(5);
                $product->setDestroyer(1);
                $product->setProductAt($now);
                $em->persist($product);
                $iaPlayer = $doctrine->getRepository(Commander::class)->findOneBy(['zombie' => 1]);
                $fleet = new Fleet();
                $fleet->setHunter(10);
                $fleet->setCorvet(5);
                $fleet->setBarge(1);
                $fleet->setCommander($iaPlayer);
                $fleet->setPlanet($usePlanet);
                $fleet->setAttack(1);
                $fleet->setName('Horde');
                $fleet->setSignature($fleet->getNbSignature());
                $em->persist($fleet);
                $reportDef = new Report();
                $reportDef->setType('invade');
                $reportDef->setSendAt($now);
                $reportDef->setCommander($commander);
                $reportDef->setTitle("Rapport d'invasion : Victoire (défense)");
                $reportDef->setImageName("defend_win_report.webp");
                $reportDef->setContent("Bien joué ! Vos travailleurs et soldats ont repoussés l'invasion des " . $iaPlayer->getUsername() . " sur votre planète " . $usePlanet->getName() . " - (" . $usePlanet->getSector()->getgalaxy()->getPosition() . "." . $usePlanet->getSector()->getPosition() . "." . $usePlanet->getPosition() . ") . <span class='text-rouge'>100</span> zombies vous ont attaqués, tous ont été tués. Vous remportez <span class='text-vert'>+100</span> points de Guerre et <span class='text-vert'>+10.000 bitcoins</span>. Recrutez de nouveaux soldats !");
                $usePlanet->setSoldier($usePlanet->getSoldier() - 2);
                $commander->setBitcoin($commander->getBitcoin() + 10000);
                $em->persist($reportDef);
                $commander->setNewReport(false);
                $commander->getRank()->setWarPoint(100);
                $em->flush();

                return $this->redirectToRoute('spatial', ['usePlanet' => $usePlanet->getId()]);
            }

            $cargoI = abs($form_spatialShip->get('cargoI')->getData());
            $cargoV = abs($form_spatialShip->get('cargoV')->getData());
            $cargoX = abs($form_spatialShip->get('cargoX')->getData());
            $colonizer = abs($form_spatialShip->get('colonizer')->getData());
            $recycleur = abs($form_spatialShip->get('recycleur')->getData());
            $barge = abs($form_spatialShip->get('barge')->getData());
            $moonMaker = abs($form_spatialShip->get('moonMaker')->getData());
            $radarShip = abs($form_spatialShip->get('radarShip')->getData());
            $brouilleurShip = abs($form_spatialShip->get('brouilleurShip')->getData());
            $motherShip = abs($form_spatialShip->get('motherShip')->getData());
            $sonde = abs($form_spatialShip->get('sonde')->getData());
            $hunter = abs($form_spatialShip->get('hunter')->getData());
            $fregate = abs($form_spatialShip->get('fregate')->getData());
            $hunterHeavy = abs($form_spatialShip->get('hunterHeavy')->getData());
            $hunterWar = abs($form_spatialShip->get('hunterWar')->getData());
            $corvet = abs($form_spatialShip->get('corvet')->getData());
            $corvetLaser = abs($form_spatialShip->get('corvetLaser')->getData());
            $corvetWar = abs($form_spatialShip->get('corvetWar')->getData());
            $fregatePlasma = abs($form_spatialShip->get('fregatePlasma')->getData());
            $croiser = abs($form_spatialShip->get('croiser')->getData());
            $ironClad = abs($form_spatialShip->get('ironClad')->getData());
            $destroyer = abs($form_spatialShip->get('destroyer')->getData());
            $all_ship = (object) [['destroyer', $destroyer], ['ironclad', $ironClad], ['croiser', $croiser], ['fregateplasma', $fregatePlasma], ['corvetwar', $corvetWar], ['corvetlaser', $corvetLaser], ['corvet', $corvet], ['hunterwar', $hunterWar], ['hunterheavy', $hunterHeavy], ['fregate', $fregate], ['sonde', $sonde], ['brouilleurship', $brouilleurShip], ['radarship', $radarShip], ['moonmaker', $moonMaker], ['barge', $barge], ['mothership', $motherShip], ['recycleur', $recycleur], ['colonizer', $colonizer], ['cargox', $cargoX], ['cargov', $cargoV], ['cargoi', $cargoI], ['hunter', $hunter]];
            $niobiumLess = 0;
            $waterLess = 0;
            $workerLess = 0;
            $soldierLess = 0;
            $warPoint = 0;
            $bitcoinLess = 0;
            $time = 0;
            foreach ($all_ship as $one_ship) {
                $niobiumLess = $niobiumLess + $commander->getNbShip($one_ship[0], $one_ship[1]);
                $waterLess = $waterLess + $commander->getWtShip($one_ship[0], $one_ship[1]);
                $workerLess = $workerLess + $commander->getWkShip($one_ship[0], $one_ship[1]);
                $soldierLess = $soldierLess + $commander->getSdShip($one_ship[0], $one_ship[1]);
                $warPoint = $warPoint + $commander->getPdgShip($one_ship[0], $one_ship[1]);
                $bitcoinLess = $bitcoinLess + $commander->getBtShip($one_ship[0], $one_ship[1]);
                $time = $time + $commander->getTime($one_ship[0], $one_ship[1], 0);
            }
            $time = round($time / $usePlanet->getShipProduction());
            $now->add(new DateInterval('PT' . round($time) . 'S'));

            if (($usePlanet->getNiobium() < $niobiumLess || $usePlanet->getWater() < $waterLess) ||
                ($usePlanet->getWorker() < $workerLess) || ($cargoI && $commander->getCargo() < 1) ||
                ($usePlanet->getSoldier() < $soldierLess) ||
                ($cargoV && $commander->getCargo() < 3) || ($cargoX && $commander->getCargo() < 5) ||
                ($colonizer && $commander->getTerraformation() == 0) || ($recycleur && $commander->getRecycleur() == 0) ||
                ($barge && $commander->getBarge() == 0) || ($hunter && ($commander->getIndustry() == 0 || $commander->getMissile() == 0)) ||
                ($fregate && ($commander->getLaser() < 1 || $commander->getMissile() != 3 || $usePlanet->getHeavyUsine() == 0)) ||
                ($hunterHeavy && ($commander->getMissile() < 2 || $usePlanet->getLightUsine() == 0)) ||
                ($corvet && ($commander->getMissile() != 3 || $usePlanet->getLightUsine() == 0 || $commander->getLightShip() < 2)) ||
                ($corvetLaser && ($commander->getMissile() != 3 || $usePlanet->getLightUsine() == 0 || $commander->getLightShip() != 3 || $commander->getLaser() < 1)) ||
                ($fregatePlasma && ($commander->getMissile() != 3 || $usePlanet->getHeavyUsine() == 0 || $commander->getPlasma() < 1 || $commander->getLaser() < 1)) ||
                ($croiser && ($commander->getMissile() != 3 || $usePlanet->getHeavyUsine() == 0 || $commander->getPlasma() < 2 || $commander->getLaser() < 2)) ||
                ($ironClad && ($commander->getMissile() != 3 || $usePlanet->getHeavyUsine() == 0 || $commander->getHeavyShip() < 2 || $commander->getPlasma() != 3 || $commander->getLaser() != 3)) ||
                ($destroyer && ($commander->getMissile() != 3 || $usePlanet->getHeavyUsine() == 0 || $commander->getHeavyShip() != 3 || $commander->getPlasma() != 3 || $commander->getLaser() != 3)) ||
                ($moonMaker && ($commander->getTerraformation() < 15 || $usePlanet->getHeavyUsine() == 0)) ||
                ($radarShip && ($commander->getOnde() < 3 || $usePlanet->getLightUsine() == 0)) ||
                ($brouilleurShip && ($commander->getOnde() < 5 || $usePlanet->getLightUsine() == 0)) ||
                ($motherShip && ($commander->getUtility() != 3 || $usePlanet->getHeavyUsine() == 0)) ||
                ($warPoint > $commander->getRank()->getWarPoint() || $bitcoinLess > $commander->getBitcoin()) ||
                ($commander->getColonizer() && $colonizer) || ($motherShip && $commander->getMotherShip()))
            {
                return $this->redirectToRoute('spatial', ['usePlanet' => $usePlanet->getId()]);
            }
            if($usePlanet->getProduct()) {
                $reNow = new DateTime();
                $product = $usePlanet->getProduct();
                $product->setCargoI($product->getCargoI() + $cargoI);
                $product->setCargoV($product->getCargoV() + $cargoV);
                $product->setCargoX($product->getCargoX() + $cargoX);
                $product->setColonizer($product->getColonizer() + $colonizer);
                $product->setRecycleur($product->getRecycleur() + $recycleur);
                $product->setBarge($product->getBarge() + $barge);
                $product->setMoonMaker($product->getMoonMaker() + $moonMaker);
                $product->setRadarShip($product->getRadarShip() + $radarShip);
                $product->setJammerShip($product->getJammerShip() + $brouilleurShip);
                $product->setMotherShip($product->getMotherShip() + $motherShip);
                $product->setSonde($product->getSonde() + $sonde);
                $product->setHunter($product->getHunter() + $hunter);
                $product->setFregate($product->getFregate() + $fregate);
                $product->setHunterHeavy($product->getHunterHeavy() + $hunterHeavy);
                $product->setHunterWar($product->getHunterWar() + $hunterWar);
                $product->setCorvet($product->getCorvet() + $corvet);
                $product->setCorvetLaser($product->getCorvetLaser() + $corvetLaser);
                $product->setCorvetWar($product->getCorvetWar() + $corvetWar);
                $product->setFregatePlasma($product->getFregatePlasma() + $fregatePlasma);
                $product->setCroiser($product->getCroiser() + $croiser);
                $product->setIronClad($product->getIronClad() + $ironClad);
                $product->setDestroyer($product->getDestroyer() + $destroyer);
                $product->setSignature($product->getNbSignature());
                $oldNow = $product->getProductAt();
                $tmpDate = $oldNow->diff($reNow);
                $reNow->add(new DateInterval('PT' . round($time) . 'S'));
                $reNow->sub($tmpDate);
                $product->setProductAt($reNow);
            } else {
                $product = new Product();
                $product->setPlanet($usePlanet);
                $product->setCargoI($cargoI);
                $product->setCargoV($cargoV);
                $product->setCargoX($cargoX);
                $product->setColonizer($colonizer);
                $product->setRecycleur($recycleur);
                $product->setBarge($barge);
                $product->setMoonMaker($moonMaker);
                $product->setRadarShip($radarShip);
                $product->setJammerShip($brouilleurShip);
                $product->setMotherShip($motherShip);
                $product->setSonde($sonde);
                $product->setHunter($hunter);
                $product->setFregate($fregate);
                $product->setHunterHeavy($hunterHeavy);
                $product->setHunterWar($hunterWar);
                $product->setCorvet($corvet);
                $product->setCorvetLaser($corvetLaser);
                $product->setCorvetWar($corvetWar);
                $product->setFregatePlasma($fregatePlasma);
                $product->setCroiser($croiser);
                $product->setIronClad($ironClad);
                $product->setDestroyer($destroyer);
                $product->setDestroyer($destroyer);
                $product->setSignature($product->getNbSignature());
                $product->setProductAt($now);
            }

            $usePlanet->setNiobium($usePlanet->getNiobium() - $niobiumLess);
            $usePlanet->setWater($usePlanet->getWater() - $waterLess);
            $usePlanet->setWorker($usePlanet->getWorker() - $workerLess);
            $usePlanet->setSoldier($usePlanet->getSoldier() - $soldierLess);
            $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() - $warPoint);
            $commander->setBitcoin($commander->getBitcoin() - $bitcoinLess);
            $em->persist($product);
            $quest = $commander->checkQuests('ship');
            if($quest) {
                $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + $quest->getGain());
                $commander->removeQuest($quest);
            }

            $em->flush();
            return $this->redirectToRoute('spatial', ['usePlanet' => $usePlanet->getId()]);
        }

        if(($user->getTutorial() == 20)) {
            $user->setTutorial(21);
            $em->flush();
        }

        return $this->render('connected/spatial.html.twig', [
            'usePlanet' => $usePlanet,
            'form_spatialShip' => $form_spatialShip->createView()
        ]);
    }

    /**
     * @Route("/creer-flotte/{usePlanet}", name="create_fleet", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createFleetAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }


        if(count($commander->getFleets()) >= 100) {
            $this->addFlash("fail", "Vous avez atteint la limite (100) de flottes autorisées par l'Instance.");
            return $this->redirectToRoute('spatial', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_createFleet = $this->createForm(SpatialFleetType::class);
        $form_createFleet->handleRequest($request);

        if ($form_createFleet->isSubmitted() && $form_createFleet->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $cargoI = $usePlanet->getCargoI() - abs($form_createFleet->get('cargoI')->getData());
            $cargoV = $usePlanet->getCargoV() - abs($form_createFleet->get('cargoV')->getData());
            $cargoX = $usePlanet->getCargoX() - abs($form_createFleet->get('cargoX')->getData());
            $colonizer = $usePlanet->getColonizer() - abs($form_createFleet->get('colonizer')->getData());
            $recycleur = $usePlanet->getRecycleur() - abs($form_createFleet->get('recycleur')->getData());
            $barge = $usePlanet->getBarge() - abs($form_createFleet->get('barge')->getData());
            $moonMaker = $usePlanet->getMoonMaker() - abs($form_createFleet->get('moonMaker')->getData());
            $radarShip = $usePlanet->getRadarShip() - abs($form_createFleet->get('radarShip')->getData());
            $brouilleurShip = $usePlanet->getJammerShip() - abs($form_createFleet->get('brouilleurShip')->getData());
            $motherShip = $usePlanet->getMotherShip() - abs($form_createFleet->get('motherShip')->getData());
            $sonde = $usePlanet->getSonde() - abs($form_createFleet->get('sonde')->getData());
            $hunter = $usePlanet->getHunter() - abs($form_createFleet->get('hunter')->getData());
            $fregate = $usePlanet->getFregate() - abs($form_createFleet->get('fregate')->getData());
            $hunterHeavy = $usePlanet->getHunterHeavy() - abs($form_createFleet->get('hunterHeavy')->getData());
            $hunterWar = $usePlanet->getHunterWar() - abs($form_createFleet->get('hunterWar')->getData());
            $corvet = $usePlanet->getCorvet() - abs($form_createFleet->get('corvet')->getData());
            $corvetLaser = $usePlanet->getCorvetLaser() - abs($form_createFleet->get('corvetLaser')->getData());
            $corvetWar = $usePlanet->getCorvetWar() - abs($form_createFleet->get('corvetWar')->getData());
            $fregatePlasma = $usePlanet->getFregatePlasma() - abs($form_createFleet->get('fregatePlasma')->getData());
            $croiser = $usePlanet->getCroiser() - abs($form_createFleet->get('croiser')->getData());
            $ironClad = $usePlanet->getIronClad() - abs($form_createFleet->get('ironClad')->getData());
            $destroyer = $usePlanet->getDestroyer() - abs($form_createFleet->get('destroyer')->getData());
            $total = $form_createFleet->get('motherShip')->getData() + $form_createFleet->get('brouilleurShip')->getData() + $form_createFleet->get('radarShip')->getData() + $form_createFleet->get('moonMaker')->getData() + $form_createFleet->get('corvetWar')->getData() + $form_createFleet->get('hunterWar')->getData() + $form_createFleet->get('cargoI')->getData() + $form_createFleet->get('cargoV')->getData() + $form_createFleet->get('cargoX')->getData() + $form_createFleet->get('hunterHeavy')->getData() + $form_createFleet->get('corvet')->getData() + $form_createFleet->get('corvetLaser')->getData() + $form_createFleet->get('fregatePlasma')->getData() + $form_createFleet->get('croiser')->getData() + $form_createFleet->get('ironClad')->getData() + $form_createFleet->get('destroyer')->getData() + $form_createFleet->get('colonizer')->getData() + $form_createFleet->get('fregate')->getData() + $form_createFleet->get('hunter')->getData() + $form_createFleet->get('sonde')->getData() + $form_createFleet->get('barge')->getData() + $form_createFleet->get('recycleur')->getData();

            if (($colonizer < 0 || $recycleur < 0) || ($barge < 0 || $sonde < 0) || ($hunter < 0 || $fregate < 0) ||
                ($total == 0 || $cargoI < 0) || ($cargoV < 0 || $cargoX < 0) || ($hunterHeavy < 0 || $corvet < 0) ||
                ($corvetLaser < 0 || $fregatePlasma < 0) || ($croiser < 0 || $ironClad < 0) || ($destroyer < 0 || $hunterWar < 0) ||
                ($corvetWar < 0 || $moonMaker < 0 ) || ($radarShip < 0  || $brouilleurShip < 0 ) || ($motherShip < 0 )) {
                return $this->redirectToRoute('spatial', ['usePlanet' => $usePlanet->getId()]);
            }

            $fleet = new Fleet();
            $fleet->setCargoI($form_createFleet->get('cargoI')->getData());
            $fleet->setCargoV($form_createFleet->get('cargoV')->getData());
            $fleet->setCargoX($form_createFleet->get('cargoX')->getData());
            $fleet->setColonizer($form_createFleet->get('colonizer')->getData());
            $fleet->setRecycleur($form_createFleet->get('recycleur')->getData());
            $fleet->setBarge($form_createFleet->get('barge')->getData());
            $fleet->setMoonMaker($form_createFleet->get('moonMaker')->getData());
            $fleet->setRadarShip($form_createFleet->get('radarShip')->getData());
            $fleet->setJammerShip($form_createFleet->get('brouilleurShip')->getData());
            $fleet->setMotherShip($form_createFleet->get('motherShip')->getData());
            $fleet->setSonde($form_createFleet->get('sonde')->getData());
            $fleet->setHunter($form_createFleet->get('hunter')->getData());
            $fleet->setFregate($form_createFleet->get('fregate')->getData());
            $fleet->setHunterHeavy($form_createFleet->get('hunterHeavy')->getData());
            $fleet->setHunterWar($form_createFleet->get('hunterWar')->getData());
            $fleet->setCorvet($form_createFleet->get('corvet')->getData());
            $fleet->setCorvetLaser($form_createFleet->get('corvetLaser')->getData());
            $fleet->setCorvetWar($form_createFleet->get('corvetWar')->getData());
            $fleet->setFregatePlasma($form_createFleet->get('fregatePlasma')->getData());
            $fleet->setCroiser($form_createFleet->get('croiser')->getData());
            $fleet->setIronClad($form_createFleet->get('ironClad')->getData());
            $fleet->setDestroyer($form_createFleet->get('destroyer')->getData());

            $eAlliance = $commander->getAllianceEnnemy();
            $warAlliance = [];
            $x = 0;
            foreach ($eAlliance as $tmp) {
                $warAlliance[$x] = $tmp->getAllianceTag();
                $x++;
            }

            $fAlliance = $commander->getAllianceFriends();
            $friendAlliance = [];
            $x = 0;
            foreach ($fAlliance as $tmp) {
                if($tmp->getAccepted() == 1) {
                    $friendAlliance[$x] = $tmp->getAllianceTag();
                    $x++;
                }
            }
            if(!$friendAlliance) {
                $friendAlliance = ['impossible', 'personne'];
            }

            if($commander->getAlliance()) {
                $allyF = $commander->getAlliance();
            } else {
                $allyF = 'wedontexistsok';
            }

            $fleets = $doctrine->getRepository(Fleet::class)
                ->createQueryBuilder('f')
                ->join('f.commander', 'c')
                ->leftJoin('c.ally', 'a')
                ->where('f.planet = :planet')
                ->andWhere('f.attack = true OR a.tag in (:ally)')
                ->andWhere('f.commander != :commander')
                ->andWhere('f.flightAt is null')
                ->andWhere('c.ally is null OR a.tag not in (:friend)')
                ->andWhere('c.ally is null OR c.ally != :myAlliance')
                ->setParameters(['planet' => $usePlanet, 'ally' => $warAlliance, 'commander' => $commander, 'friend' => $friendAlliance, 'myAlliance' => $allyF])
                ->getQuery()
                ->getResult();

            $fleetFight = $doctrine->getRepository(Fleet::class)
                ->createQueryBuilder('f')
                ->where('f.planet = :planet')
                ->andWhere('f.commander != :commander')
                ->andWhere('f.fightAt is not null')
                ->andWhere('f.flightAt is null')
                ->setParameters(['planet' => $usePlanet, 'commander' => $commander])
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();

            if($fleetFight) {
                $fleet->setFightAt($fleetFight->getFightAt());
            } elseif ($fleets) {
                foreach ($fleets as $setWar) {
                    if($setWar->getCommander()->getAlliance()) {
                        $fleetArm = $fleet->getMissile() + $fleet->getLaser() + $fleet->getPlasma();
                        if($fleetArm > 0) {
                            $fleet->setAttack(1);
                        }
                        foreach ($eAlliance as $tmp) {
                            if ($setWar->getCommander()->getAlliance()->getTag() == $tmp->getAllianceTag()) {
                                $fleetArm = $setWar->getMissile() + $setWar->getLaser() + $setWar->getPlasma();
                                if($fleetArm > 0) {
                                    $setWar->setAttack(1);
                                }
                            }
                        }
                    }
                }
                $allFleets = $doctrine->getRepository(Fleet::class)
                    ->createQueryBuilder('f')
                    ->where('f.planet = :planet')
                    ->andWhere('f.flightAt is null')
                    ->setParameters(['planet' => $usePlanet])
                    ->getQuery()
                    ->getResult();

                $now = new DateTime();
                $now->add(new DateInterval('PT' . 300 . 'S'));

                foreach ($allFleets as $updateF) {
                    $updateF->setFightAt($now);
                }
                $fleet->setFightAt($now);
            } else {
                $now = new DateTime();
                $now->add(new DateInterval('PT' . 300 . 'S'));
            }

            if (($usePlanet->getNbCdr() > 0 || $usePlanet->getWtCdr() > 0) && $fleet->getRecycleur() > 0) {
                $fleet->setRecycleAt($now);
            }
            $fleet->setCommander($commander);
            $fleet->setPlanet($usePlanet);
            if ($form_createFleet->get('name')->getData()) {
                $fleet->setName($form_createFleet->get('name')->getData());
            }
            if(($user->getTutorial() == 22)) {
                $user->setTutorial(23);
                $fleet->setHunter($usePlanet->getHunter());
                $fleet->setFregate($usePlanet->getFregate());
                $fleet->setCorvetWar($usePlanet->getCorvetWar());
                $fleet->setHunterWar($usePlanet->getHunterWar());
                $fleet->setDestroyer($usePlanet->getDestroyer());
                $usePlanet->setHunter(0);
                $usePlanet->setFregate(0);
                $usePlanet->setCorvetWar(0);
                $usePlanet->setHunterWar(0);
                $usePlanet->setDestroyer(0);
            }
            $fleet->setSignature($fleet->getNbSignature());
            $em->persist($fleet);
            $usePlanet->setCargoI($cargoI);
            $usePlanet->setCargoV($cargoV);
            $usePlanet->setCargoX($cargoX);
            $usePlanet->setColonizer($colonizer);
            $usePlanet->setRecycleur($recycleur);
            $usePlanet->setBarge($barge);
            $usePlanet->setMoonMaker($moonMaker);
            $usePlanet->setRadarShip($radarShip);
            $usePlanet->setJammerShip($brouilleurShip);
            $usePlanet->setMotherShip($motherShip);
            $usePlanet->setSonde($sonde);
            $usePlanet->setHunter($hunter);
            $usePlanet->setFregate($fregate);
            $usePlanet->setHunterHeavy($hunterHeavy);
            $usePlanet->setHunterWar($hunterWar);
            $usePlanet->setCorvet($corvet);
            $usePlanet->setCorvetLaser($corvetLaser);
            $usePlanet->setCorvetWar($corvetWar);
            $usePlanet->setFregatePlasma($fregatePlasma);
            $usePlanet->setCroiser($croiser);
            $usePlanet->setIronClad($ironClad);
            $usePlanet->setDestroyer($destroyer);
            $usePlanet->addFleet($fleet);

            if(($user->getTutorial() == 10)) {
                $fleet->setColonizer(1);
                if ($usePlanet->getColonizer() == 1) {
                    $usePlanet->setColonizer($usePlanet->getColonizer() - 1);
                }
                $user->setTutorial(11);
            }
            $usePlanet->setSignature($usePlanet->getNbSignature());
            $em->flush();


            return $this->redirectToRoute('fleet', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->render('connected/fleet/create.html.twig', [
            'usePlanet' => $usePlanet,
            'form_createFleet' => $form_createFleet->createView(),
        ]);
    }
}