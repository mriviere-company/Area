<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Report;
use App\Entity\Fleet;
use DateTimeZone;
use DateInterval;
use DateTime;

class FleetsController extends AbstractController
{
    public function nukeBombAction($nukeBombs, $now, $em)
    {
        foreach ($nukeBombs as $nukeBomb) {

            $newHome = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->where('p.position = :planete')
                ->andWhere('s.position = :sector')
                ->andWhere('g.position = :galaxy')
                ->setParameters(['planete' => $nukeBomb->getDestination()->getPlanet()->getPosition(), 'sector' => $nukeBomb->getDestination()->getPlanet()->getSector()->getPosition(), 'galaxy' => $nukeBomb->getDestination()->getPlanet()->getSector()->getGalaxy()->getPosition()])
                ->getQuery()
                ->getOneOrNullResult();

            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($newHome->getUser());
            $reportNuclearAtt = new Report();
            $reportNuclearAtt->setType('fight');
            $reportNuclearAtt->setTitle("Votre missile nucléaire a touché sa cible !");
            $reportNuclearAtt->setImageName("nuclear_attack.png");
            $reportNuclearAtt->setSendAt($now);
            $reportNuclearAtt->setUser($nukeBomb->getUser());
            $reportNuclearDef = new Report();
            $reportNuclearDef->setType('fight');
            $reportNuclearDef->setTitle("Un missile nucléaire vous a frappé !");
            $reportNuclearDef->setImageName("nuclear_attack.png");
            $reportNuclearDef->setSendAt($now);
            $reportNuclearDef->setUser($newHome->getUser());
            $dest = $nukeBomb->getDestination();
            $nukeBomb->setDestination(null);
            $em->remove($dest);
            $em->remove($nukeBomb);
            if ($newHome->getMetropole() > 0) {
                $newHome->setMetropole($newHome->getMetropole() - 1);
                $newHome->setWorkerMax($newHome->getWorkerMax() - 400000);
                $newHome->setWorkerProduction($newHome->getWorkerProduction() - 8.32);
                if ($newHome->getWorker() > $newHome->getWorkerMax()) {
                    $newHome->setWorker($newHome->getWorkerMax());
                }
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Une métropole a été détruite, ses terrains et ses espaces sont désormais radioactifs. Il provenait du Dirigeant " . $nukeBomb->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $usePlanet->getPosition() . ")</a></span>. Une métropole a été détruite.");
            } elseif ($newHome->getCity() > 0) {
                $newHome->setCity($newHome->getCity() - 1);
                $newHome->setWorkerMax($newHome->getWorkerMax() - 125000);
                $newHome->setWorkerProduction($newHome->getWorkerProduction() - 5.56);
                if ($newHome->getWorker() > $newHome->getWorkerMax()) {
                    $newHome->setWorker($newHome->getWorkerMax());
                }
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Une ville a été détruite, ses terrains sont désormais radioactifs. Il provenait du Dirigeant " . $nukeBomb->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $usePlanet->getPosition() . ")</a></span>. Une ville a été détruite.");
            } elseif ($newHome->getBunker() > 0) {
                $newHome->setBunker($newHome->getBunker() - 1);
                $newHome->setSoldierMax($newHome->getSoldierMax() - 20000);
                if ($newHome->getSoldier() > $newHome->getSoldierMax()) {
                    $newHome->setSoldier($newHome->getSoldierMax());
                }
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Un bunker a été détruit, ses terrains sont désormais radioactifs. Il provenait du Dirigeant " . $nukeBomb->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $usePlanet->getPosition() . ")</a></span>. Un bunker a été détruit.");
            } elseif ($newHome->getCaserne() > 0) {
                $newHome->setCaserne($newHome->getCaserne() - 1);
                $newHome->setSoldierMax($newHome->getSoldierMax() - 2500);
                if ($newHome->getSoldier() > $newHome->getSoldierMax()) {
                    $newHome->setSoldier($newHome->getSoldierMax());
                }
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span> Une caserne a été détruite, ses terrains sont désormais radioactifs. Il provenait du Dirigeant " . $nukeBomb->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $usePlanet->getPosition() . ")</a></span>. Une caserne a été détruite.");
            } else {
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span> Par chance votre planète n'avait aucune infrastructures ciblées. Il provenait du Dirigeant " . $nukeBomb->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $usePlanet->getPosition() . ")</a></span>. Aucune infrastructure n'a été détruite.");
            }
            $em->persist($reportNuclearAtt);
            $em->persist($reportNuclearDef);
        }
        echo "Flush -> " . count($nukeBombs) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    public function recycleAction($fleetCdrs, $now , $em)
    {
        $tmpNoCdr = new DateTime();
        $tmpNoCdr->setTimezone(new DateTimeZone('Europe/Paris'));
        $tmpNoCdr->add(new DateInterval('PT' . 300 . 'S'));
        foreach ($fleetCdrs as $fleetCdr) {
            if ($fleetCdr->getUser()->getPoliticRecycleur() > 0) {
                $recycle = $fleetCdr->getRecycleur() * (1000 + ($fleetCdr->getUser()->getPoliticRecycleur() * 400));
            } else {
                $recycle = $fleetCdr->getRecycleur() * 1000;
            }
            $planetCdr = $fleetCdr->getPlanet();
            if ($fleetCdr->getCargoPlace() > ($fleetCdr->getCargoFull() + ($recycle * 2))) {
                if($planetCdr->getNbCdr() == 0 || $planetCdr->getWtCdr() == 0) {
                    $recycle = $recycle * 2;
                }
                if ($planetCdr->getNbCdr() > $recycle) {
                    $fleetCdr->setNiobium($fleetCdr->getNiobium() + $recycle);
                    $planetCdr->setNbCdr($planetCdr->getNbCdr() - $recycle);
                    if ($planetCdr->getWtCdr() < $recycle) {
                        if ($planetCdr->getNbCdr() > $recycle) {
                            $fleetCdr->setNiobium($fleetCdr->getNiobium() + ($recycle - $planetCdr->getWtCdr()));
                            $planetCdr->setNbCdr($planetCdr->getNbCdr() - ($recycle - $planetCdr->getWtCdr()));
                        } else {
                            $fleetCdr->setNiobium($fleetCdr->getNiobium() + ($recycle - $planetCdr->getWtCdr()));
                            $planetCdr->setNbCdr(0);
                        }
                    }
                } else {
                    $fleetCdr->setNiobium($fleetCdr->getNiobium() + $planetCdr->getNbCdr());
                    $planetCdr->setNbCdr(0);
                }
                if ($planetCdr->getWtCdr() > $recycle) {
                    $fleetCdr->setWater($fleetCdr->getWater() + $recycle);
                    $planetCdr->setWtCdr($planetCdr->getWtCdr() - $recycle);
                } else {
                    $fleetCdr->setWater($fleetCdr->getWater() + $planetCdr->getWtCdr());
                    $planetCdr->setWtCdr(0);
                }
                if($planetCdr->getNbCdr() == 0 && $planetCdr->getWtCdr() == 0) {
                    $fleetCdr->setRecycleAt(null);
                    if ($fleetCdr->getCargoPlace() > ($fleetCdr->getCargoFull() + ($recycle * 2))) {
                        $reportRec = new Report();
                        $reportRec->setType('move');
                        $reportRec->setTitle("Votre flotte " . $fleetCdr->getName() . " a arrêté de recycler!");
                        $reportRec->setImageName("recycle_report.jpg");
                        $reportRec->setSendAt($now);
                        $reportRec->setUser($fleetCdr->getUser());
                        $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($fleetCdr->getUser());
                        $reportRec->setContent("Bonjour dirigeant " . $fleetCdr->getUser()->getUserName() . " votre flotte " . "<span><a href='/connect/gerer-flotte/" . $fleetCdr->getId() ."/" . $usePlanet->getId() . "'>" . $fleetCdr->getName() . "</a></span>" . " vient de terminer de recycler en " . "<span><a href='/connect/carte-spatiale/" . $fleetCdr->getPlanet()->getSector()->getPosition() ."/" . $fleetCdr->getPlanet()->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>" . $fleetCdr->getPlanet()->getSector()->getGalaxy()->getPosition() . ":" . $fleetCdr->getPlanet()->getSector()->getPosition() . ":" . $fleetCdr->getPlanet()->getPosition() . "</a></span>.");
                        $em->persist($reportRec);
                        $fleetCdr->getUser()->setViewReport(false);
                    }
                } elseif ($fleetCdr->getCargoPlace() == $fleetCdr->getCargoFull()) {
                    $reportRec = new Report();
                    $reportRec->setType('move');
                    $reportRec->setTitle("Votre flotte " . $fleetCdr->getName() . " a arrêté de recycler!");
                    $reportRec->setImageName("recycle_report.jpg");
                    $reportRec->setSendAt($now);
                    $reportRec->setUser($fleetCdr->getUser());
                    $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($fleetCdr->getUser());
                    $reportRec->setContent("Bonjour dirigeant " . $fleetCdr->getUser()->getUserName() . " votre flotte " . "<span><a href='/connect/gerer-flotte/" . $fleetCdr->getId() ."/" . $usePlanet->getId() . "'>" . $fleetCdr->getName() . "</a></span>" . " vient d'arrêter de recycler en " . "<span><a href='/connect/carte-spatiale/" . $fleetCdr->getPlanet()->getSector()->getPosition() ."/" . $fleetCdr->getPlanet()->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>" . $fleetCdr->getPlanet()->getSector()->getGalaxy()->getPosition() . ":" . $fleetCdr->getPlanet()->getSector()->getPosition() . ":" . $fleetCdr->getPlanet()->getPosition() . "</a></span> car ses soutes sont pleines.");
                    $em->persist($reportRec);
                    $fleetCdr->getUser()->setViewReport(false);
                    $fleetCdr->setRecycleAt(null);
                } else {
                    $fleetCdr->setRecycleAt($tmpNoCdr);
                }
            } else {
                if($planetCdr->getNbCdr() == 0 || $planetCdr->getWtCdr() == 0) {
                    $recycle = ($fleetCdr->getCargoPlace() >= ($fleetCdr->getCargoFull() + ($recycle * 2))) ? $recycle * 2 : (($fleetCdr->getCargoPlace() - $fleetCdr->getCargoFull()));
                } else {
                    $recycle = ($fleetCdr->getCargoPlace() >= ($fleetCdr->getCargoFull() + ($recycle * 2))) ? $recycle : (($fleetCdr->getCargoPlace() - $fleetCdr->getCargoFull()) / 2);
                }
                if ($planetCdr->getNbCdr() > $recycle) {
                    $fleetCdr->setNiobium($fleetCdr->getNiobium() + $recycle);
                    $planetCdr->setNbCdr($planetCdr->getNbCdr() - $recycle);
                } else {
                    $fleetCdr->setNiobium($fleetCdr->getNiobium() + $planetCdr->getNbCdr());
                    $planetCdr->setNbCdr(0);
                }
                if ($planetCdr->getWtCdr() > $recycle) {
                    $fleetCdr->setWater($fleetCdr->getWater() + $recycle);
                    $planetCdr->setWtCdr($planetCdr->getWtCdr() - $recycle);
                } else {
                    $fleetCdr->setWater($fleetCdr->getWater() + $planetCdr->getWtCdr());
                    $planetCdr->setWtCdr(0);
                }
                if ($planetCdr->getNbCdr() == 0 && $planetCdr->getWtCdr() == 0) {
                    $reportRec = new Report();
                    $reportRec->setType('move');
                    $reportRec->setTitle("Votre flotte " . $fleetCdr->getName() . " a arrêté de recycler!");
                    $reportRec->setImageName("recycle_report.jpg");
                    $reportRec->setSendAt($now);
                    $reportRec->setUser($fleetCdr->getUser());
                    $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($fleetCdr->getUser());
                    $reportRec->setContent("Bonjour dirigeant " . $fleetCdr->getUser()->getUserName() . " votre flotte " . "<span><a href='/connect/gerer-flotte/" . $fleetCdr->getId() ."/" . $usePlanet->getId() . "'>" . $fleetCdr->getName() . "</a></span>" . " vient de terminer de recycler en " . "<span><a href='/connect/carte-spatiale/" . $fleetCdr->getPlanet()->getSector()->getPosition() ."/" . $fleetCdr->getPlanet()->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>" . $fleetCdr->getPlanet()->getSector()->getGalaxy()->getPosition() . ":" . $fleetCdr->getPlanet()->getSector()->getPosition() . ":" . $fleetCdr->getPlanet()->getPosition() . "</a></span>.");
                    $em->persist($reportRec);
                    $fleetCdr->getUser()->setViewReport(false);
                    $fleetCdr->setRecycleAt(null);
                } elseif ($fleetCdr->getCargoPlace() == $fleetCdr->getCargoFull()) {
                    $reportRec = new Report();
                    $reportRec->setType('move');
                    $reportRec->setTitle("Votre flotte " . $fleetCdr->getName() . " a arrêté de recycler!");
                    $reportRec->setImageName("recycle_report.jpg");
                    $reportRec->setSendAt($now);
                    $reportRec->setUser($fleetCdr->getUser());
                    $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($fleetCdr->getUser());
                    $reportRec->setContent("Bonjour dirigeant " . $fleetCdr->getUser()->getUserName() . " votre flotte " . "<span><a href='/connect/gerer-flotte/" . $fleetCdr->getId() ."/" . $usePlanet->getId() . "'>" . $fleetCdr->getName() . "</a></span>" . " vient d'arrêter de recycler en " . "<span><a href='/connect/carte-spatiale/" . $fleetCdr->getPlanet()->getSector()->getPosition() ."/" . $fleetCdr->getPlanet()->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>" . $fleetCdr->getPlanet()->getSector()->getGalaxy()->getPosition() . ":" . $fleetCdr->getPlanet()->getSector()->getPosition() . ":" . $fleetCdr->getPlanet()->getPosition() . "</a></span> car ses soutes sont pleines.");
                    $em->persist($reportRec);
                    $fleetCdr->getUser()->setViewReport(false);
                    $fleetCdr->setRecycleAt(null);
                } else {
                    $fleetCdr->setRecycleAt($tmpNoCdr);
                }
            }
            $quest = $fleetCdr->getUser()->checkQuests('recycle');
            if($quest) {
                $fleetCdr->getUser()->getRank()->setWarPoint($fleetCdr->getUser()->getRank()->getWarPoint() + $quest->getGain());
                $fleetCdr->getUser()->removeQuest($quest);
            }
        }
        echo "Flush -> " . count($fleetCdrs) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    public function oneFleetAction($fleetRegroups, $demoFleet)
    {
        $em = $this->getDoctrine()->getManager();

        $one = new Fleet();
        $one->setUser($demoFleet->getUser());
        $one->setPlanet($demoFleet->getPlanet());
        $one->setName($demoFleet->getName());
        $one->setAttack($demoFleet->getAttack());
        $one->setFightAt($demoFleet->getFightAt());
        $one->setAlly($demoFleet->getAlly());
        $one->setFleetList($demoFleet->getFleetList());
        foreach ($fleetRegroups as $fleetRegroup) {
            $one->setSonde($one->getSonde() + $fleetRegroup->getSonde());
            $one->setCargoI($one->getCargoI() + $fleetRegroup->getCargoI());
            $one->setCargoV($one->getCargoV() + $fleetRegroup->getCargoV());
            $one->setCargoX($one->getCargoX() + $fleetRegroup->getCargoX());
            $one->setColonizer($one->getColonizer() + $fleetRegroup->getColonizer());
            $one->setRecycleur($one->getRecycleur() + $fleetRegroup->getRecycleur());
            $one->setBarge($one->getBarge() + $fleetRegroup->getBarge());
            $one->setMoonMaker($one->getMoonMaker() + $fleetRegroup->getMoonMaker());
            $one->setRadarShip($one->getRadarShip() + $fleetRegroup->getRadarShip());
            $one->setBrouilleurShip($one->getBrouilleurShip() + $fleetRegroup->getBrouilleurShip());
            $one->setMotherShip($one->getMotherShip() + $fleetRegroup->getMotherShip());
            $one->setHunter($one->getHunter() + $fleetRegroup->getHunter());
            $one->setHunterHeavy($one->getHunterHeavy() + $fleetRegroup->getHunterHeavy());
            $one->setHunterWar($one->getHunterWar() + $fleetRegroup->getHunterWar());
            $one->setCorvet($one->getCorvet() + $fleetRegroup->getCorvet());
            $one->setCorvetLaser($one->getCorvetLaser() + $fleetRegroup->getCorvetLaser());
            $one->setCorvetWar($one->getCorvetWar() + $fleetRegroup->getCorvetWar());
            $one->setFregate($one->getFregate() + $fleetRegroup->getFregate());
            $one->setFregatePlasma($one->getFregatePlasma() + $fleetRegroup->getFregatePlasma());
            $one->setCroiser($one->getCroiser() + $fleetRegroup->getCroiser());
            $one->setIronClad($one->getIronClad() + $fleetRegroup->getIronClad());
            $one->setDestroyer($one->getDestroyer() + $fleetRegroup->getDestroyer());
            $one->setSoldier($one->getSoldier() + $fleetRegroup->getSoldier());
            $one->setTank($one->getTank() + $fleetRegroup->getTank());
            $one->setWorker($one->getWorker() + $fleetRegroup->getWorker());
            $one->setScientist($one->getScientist() + $fleetRegroup->getScientist());
            $one->setNiobium($one->getNiobium() + $fleetRegroup->getNiobium());
            $one->setWater($one->getWater() + $fleetRegroup->getWater());
            $one->setFood($one->getFood() + $fleetRegroup->getFood());
            $one->setUranium($one->getUranium() + $fleetRegroup->getUranium());
            $one->setNuclearBomb($one->getNuclearBomb() + $fleetRegroup->getNuclearBomb());
            $fleetRegroup->setUser(null);
            $em->remove($fleetRegroup);
        }
        $one->setSignature($one->getNbrSignatures());
        $em->persist($one);
        echo "Flush -> " . count($fleetRegroups) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    public function destinationDeleteAction($dests, $em)
    {
        foreach ($dests as $dest) {
            $em->remove($dest);
        }
        echo "Flush -> " . count($dests) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}