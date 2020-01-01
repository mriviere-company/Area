<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Report;
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

            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($newHome->getUser()->getUsername());
            $reportNuclearAtt = new Report();
            $reportNuclearAtt->setType('fight');
            $reportNuclearAtt->setTitle("Votre missile nucléaire a touché sa cible !");
            $reportNuclearAtt->setImageName("nuclear_attack.png");
            $reportNuclearAtt->setSendAt($now);
            $reportNuclearAtt->setUser($nukeBomb->getUser());
            $reportNuclearDef = new Report();
            $reportNuclearDef->setType('fight');
            $reportNuclearDef->setTitle("Un missile nucléaire vous a frapper !");
            $reportNuclearDef->setImageName("nuclear_attack.png");
            $reportNuclearDef->setSendAt($now);
            $reportNuclearDef->setUser($newHome->getUser());
            $em->remove($nukeBomb);
            if ($newHome->getMetropole() > 0) {
                $newHome->setMetropole($newHome->getMetropole() - 1);
                $newHome->setWorkerMax($newHome->getWorkerMax() - 75000);
                $newHome->setWorkerProduction($newHome->getWorkerProduction() - 8.32);
                if ($newHome->getWorker() > $newHome->getWorkerMax()) {
                    $newHome->setWorker($newHome->getWorkerMax());
                }
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Une métropole a été détruite. Il provenait du Dirigeant " . $nukeBomb->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Une métropole a été détruite.");
            } elseif ($newHome->getCity() > 0) {
                $newHome->setCity($newHome->getCity() - 1);
                $newHome->setWorkerMax($newHome->getWorkerMax() - 25000);
                $newHome->setWorkerProduction($newHome->getWorkerProduction() - 5.56);
                if ($newHome->getWorker() > $newHome->getWorkerMax()) {
                    $newHome->setWorker($newHome->getWorkerMax());
                }
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Une ville a été détruite. Il provenait du Dirigeant " . $nukeBomb->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Une ville a été détruite.");
            } elseif ($newHome->getBunker() > 0) {
                $newHome->setBunker($newHome->getBunker() - 1);
                $newHome->setSoldierMax($newHome->getSoldierMax() - 20000);
                if ($newHome->getSoldier() > $newHome->getSoldierMax()) {
                    $newHome->setSoldier($newHome->getSoldierMax());
                }
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Un bunker a été détruit. Il provenait du Dirigeant " . $nukeBomb->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Un bunker a été détruit.");
            } elseif ($newHome->getCaserne() > 0) {
                $newHome->setCaserne($newHome->getCaserne() - 1);
                $newHome->setSoldierMax($newHome->getSoldierMax() - 2500);
                if ($newHome->getSoldier() > $newHome->getSoldierMax()) {
                    $newHome->setSoldier($newHome->getSoldierMax());
                }
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span> Une caserne a été détruite. Il provenait du Dirigeant " . $nukeBomb->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Une caserne a été détruite.");
            } else {
                $reportNuclearDef->setContent("Un missile vient de frapper votre planète " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span> Par chance votre planète n'avait aucune infrastructures ciblées. Il provenait du Dirigeant " . $nukeBomb->getUser()->getUsername() . ".");
                $reportNuclearAtt->setContent("Votre missile vient de frapper la planète adverse " . $newHome->getName() . " en " . "<span><a href='/connect/carte-spatiale/" . $newHome->getSector()->getPosition() ."/" . $newHome->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>(" . $newHome->getSector()->getGalaxy()->getPosition() . "." . $newHome->getSector()->getPosition() . "." . $newHome->getPosition() . ")</a></span>. Aucune infrastructure n'a été détruite.");
            }
            $em->persist($reportNuclearAtt);
            $em->persist($reportNuclearDef);
        }
        echo "Bombes nucléaires lâchées.<br/>";

        $em->flush();

        return new Response ('true');
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
                        $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($fleetCdr->getUser()->getUsername());
                        $reportRec->setContent("Bonjour dirigeant " . $fleetCdr->getUser()->getUserName() . " votre flotte " . "<span><a href='/connect/gerer-flotte/" . $fleetCdr->getId() ."/" . $usePlanet->getId() . "'>" . $fleetCdr->getName() . "</a></span>" . " vient de terminer de recycler en " . "<span><a href='/connect/carte-spatiale/" . $fleetCdr->getPlanet()->getSector()->getPosition() ."/" . $fleetCdr->getPlanet()->getSector()->getGalaxy()->getPosition() ."/" . $usePlanet->getId() . "'>" . $fleetCdr->getPlanet()->getSector()->getGalaxy()->getPosition() . ":" . $fleetCdr->getPlanet()->getSector()->getPosition() . ":" . $fleetCdr->getPlanet()->getPosition() . "</a></span>.");
                        $em->persist($reportRec);
                        $fleetCdr->getUser()->setViewReport(false);
                    }
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
                if ($planetCdr->getNbCdr() == 0 && $planetCdr->getWtCdr() == 0 || $fleetCdr->getCargoPlace() == $fleetCdr->getCargoFull()) {
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
        echo "Recyclage effectué.<br/>";

        $em->flush();

        return new Response ('true');
    }
}