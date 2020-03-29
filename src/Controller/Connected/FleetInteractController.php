<?php

namespace App\Controller\Connected;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\SpatialEditFleetType;
use App\Form\Front\FleetRenameType;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\Front\FleetRessourcesType;
use App\Form\Front\FleetSendType;
use App\Form\Front\FleetAttackType;
use App\Form\Front\FleetListType;
use App\Form\Front\FleetSplitType;
use App\Form\Front\FleetEditShipType;
use App\Entity\Fleet;
use App\Entity\Report;
use App\Entity\Planet;
use App\Entity\Destination;
use App\Entity\Fleet_List;
use Datetime;
use DatetimeZone;
use DateInterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class FleetInteractController  extends AbstractController
{
    /**
     * @Route("/regroupement-flottes/{usePlanet}", name="fleets_regroup", requirements={"usePlanet"="\d+"})
     */
    public function fleetsRegroupAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $allFleets = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->where('f.user = :user')
            ->andWhere('f.flightTime is null')
            ->andWhere('f.fightAt is null')
            ->setParameters(['user' => $user])
            ->orderBy('f.flightTime')
            ->getQuery()
            ->getResult();

        return $this->redirectToRoute('fleet', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/regroupement-vaisseaux/{usePlanet}", name="ships_regroup", requirements={"usePlanet"="\d+"})
     */
    public function shipsRegroupAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $allPlanets = $em->getRepository('App:Planet')
            ->createQueryBuilder('f')
            ->where('f.user = :user')
            ->andWhere('f.flightTime is not null')
            ->setParameters(['user' => $user])
            ->orderBy('f.flightTime')
            ->getQuery()
            ->getResult();

        return $this->redirectToRoute('fleet', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/regroupement-total/{usePlanet}", name="total_regroup", requirements={"usePlanet"="\d+"})
     */
    public function totalRegroupAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $allPlanets = $em->getRepository('App:Planet')
            ->createQueryBuilder('f')
            ->where('f.user = :user')
            ->andWhere('f.flightTime is not null')
            ->setParameters(['user' => $user])
            ->orderBy('f.flightTime')
            ->getQuery()
            ->getResult();

        $allFleets = $em->getRepository('App:Planet')
            ->createQueryBuilder('f')
            ->where('f.user = :user')
            ->andWhere('f.flightTime is not null')
            ->setParameters(['user' => $user])
            ->orderBy('f.flightTime')
            ->getQuery()
            ->getResult();

        return $this->redirectToRoute('fleet', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-niobium/{fleetGive}/{usePlanet}", name="discharge_fleet_niobium", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function dischargeNiobiumFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        
        if($planetTake->getMerchant() == true) {
            if ($user->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getNiobium() / 6) / 5000) * (1 + ($user->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getNiobium() / 6) / 5000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setUser($user);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($user->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getNiobium() * 0.10) * (1 + ($user->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getNiobium() * 0.10);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setNiobium($planetTake->getNiobium() + $fleetGive->getNiobium());
            $user->setBitcoin($user->getBitcoin() + $gainSell);
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setNiobium(0);
            $quest = $user->checkQuests('sell');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }
        if(($planetTake->getNiobium() + $fleetGive->getNiobium()) <= $planetTake->getNiobiumMax()) {
            $planetTake->setNiobium($planetTake->getNiobium() + $fleetGive->getNiobium());
            $fleetGive->setNiobium(0);
        } else {
            $planetTake->setNiobium($planetTake->getNiobiumMax());
            $fleetGive->setNiobium(($planetTake->getNiobium() + $fleetGive->getNiobium()) - $planetTake->getNiobiumMax());
        }
        $user->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-water/{fleetGive}/{usePlanet}", name="discharge_fleet_water", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function dischargeWaterFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            if ($user->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getWater() / 3) / 5000) * (1 + ($user->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getWater() / 3) / 5000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setUser($user);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($user->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getWater() * 0.25) * (1 + ($user->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getWater() * 0.25);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setWater($planetTake->getWater() + $fleetGive->getWater());
            $user->setBitcoin($user->getBitcoin() + $gainSell);
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setWater(0);
            $quest = $user->checkQuests('sell');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }
        if(($planetTake->getWater() + $fleetGive->getWater()) <= $planetTake->getWaterMax()) {
            $planetTake->setWater($planetTake->getWater() + $fleetGive->getWater());
            $fleetGive->setWater(0);
        } else {
            $planetTake->setWater($planetTake->getWaterMax());
            $fleetGive->setWater(($planetTake->getWater() + $fleetGive->getWater()) - $planetTake->getWaterMax());
        }
        $user->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-soldat/{fleetGive}/{usePlanet}", name="discharge_fleet_soldier", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function dischargeSoldierFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            if ($user->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getSoldier() * 10) / 5000) * (1 + ($user->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getSoldier() * 10) / 5000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setUser($user);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($user->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getSoldier() * 80) * (1 + ($user->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getSoldier() * 80);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setSoldier($planetTake->getSoldier() + $fleetGive->getSoldier());
            $user->setBitcoin($user->getBitcoin() + $gainSell);
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setSoldier(0);
            $quest = $user->checkQuests('sell');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }
        if(($planetTake->getSoldier() + $fleetGive->getSoldier()) <= $planetTake->getSoldierMax()) {
            $planetTake->setSoldier($planetTake->getSoldier() + $fleetGive->getSoldier());
            $fleetGive->setSoldier(0);
        } else {
            $planetTake->setSoldier($planetTake->getSoldierMax());
            $fleetGive->setSoldier(($planetTake->getSoldier() + $fleetGive->getSoldier()) - $planetTake->getSoldierMax());
        }
        $user->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-travailleurs/{fleetGive}/{usePlanet}", name="discharge_fleet_worker", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function dischargeWorkerFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            if ($user->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getWorker() * 50) / 5000) * (1 + ($user->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getWorker() * 50) / 5000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setUser($user);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($user->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getWorker() * 5) * (1 + ($user->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getWorker() * 5);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setWorker($planetTake->getWorker() + $fleetGive->getWorker());
            $user->setBitcoin($user->getBitcoin() + $gainSell);
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setWorker(0);
            $quest = $user->checkQuests('sell');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }
        if(($planetTake->getWorker() + $fleetGive->getWorker()) <= $planetTake->getWorkerMax()) {
            $planetTake->setWorker($planetTake->getWorker() + $fleetGive->getWorker());
            $fleetGive->setWorker(0);
        } else {
            $planetTake->setWorker($planetTake->getWorkerMax());
            $fleetGive->setWorker(($planetTake->getWorker() + $fleetGive->getWorker()) - $planetTake->getWorkerMax());
        }
        $user->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-scientifique/{fleetGive}/{usePlanet}", name="discharge_fleet_scientist", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function dischargeScientistFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            if ($user->getPoliticPdg() > 0) {
                $newWarPointS = round((($fleetGive->getScientist() * 100) / 5000) * (1 + ($user->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round(($fleetGive->getScientist() * 100) / 5000);
            }
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setUser($user);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($user->getPoliticMerchant() > 0) {
                $gainSell = ($fleetGive->getScientist() * 300) * (1 + ($user->getPoliticMerchant() / 20));
            } else {
                $gainSell = ($fleetGive->getScientist() * 300);
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format(round($gainSell)) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setScientist($planetTake->getScientist() + $fleetGive->getScientist());
            $user->setBitcoin($user->getBitcoin() + $gainSell);
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setScientist(0);
            $quest = $user->checkQuests('sell');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }
        if(($planetTake->getScientist() + $fleetGive->getScientist()) <= $planetTake->getScientistMax()) {
            $planetTake->setScientist($planetTake->getScientist() + $fleetGive->getScientist());
            $fleetGive->setScientist(0);
        } else {
            $planetTake->setScientist($planetTake->getScientistMax());
            $fleetGive->setScientist(($planetTake->getScientist() + $fleetGive->getScientist()) - $planetTake->getScientistMax());
        }
        $user->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/decharger-tout/{fleetGive}/{usePlanet}", name="discharge_fleet_all", requirements={"usePlanet"="\d+", "fleetGive"="\d+"})
     */
    public function dischargeAllFleetAction(Planet $usePlanet, Fleet $fleetGive)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user || $fleetGive->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetTake = $fleetGive->getPlanet();
        if($fleetGive && $usePlanet) {
        } else {
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
        }
        if($planetTake->getMerchant() == true) {
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setUser($user);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            if ($user->getPoliticPdg() > 0) {
                $newWarPointS = round((((($fleetGive->getScientist() * 100) + ($fleetGive->getWorker() * 50) + ($fleetGive->getSoldier() * 10) + ($fleetGive->getWater() / 3) + ($fleetGive->getNiobium() / 6) + ($fleetGive->getTank() * 5) + ($fleetGive->getUranium() * 10)) / 5000)) * (1 + ($user->getPoliticPdg() / 10)));
            } else {
                $newWarPointS = round((($fleetGive->getScientist() * 100) + ($fleetGive->getWorker() * 50) + ($fleetGive->getSoldier() * 10) + ($fleetGive->getWater() / 3) + ($fleetGive->getNiobium() / 6) + ($fleetGive->getTank() * 5) + ($fleetGive->getUranium() * 10)) / 5000);
            }
            if ($user->getPoliticMerchant() > 0) {
                $gainSell = round((($fleetGive->getWater() * 0.25) + ($fleetGive->getSoldier() * 80) + ($fleetGive->getWorker() * 5) + ($fleetGive->getScientist() * 300) + ($fleetGive->getNiobium() * 0.10) + ($fleetGive->getTank() * 2500) + ($fleetGive->getUranium() * 5000)) * (1 + ($user->getPoliticMerchant() / 20)));
            } else {
                $gainSell = round(($fleetGive->getWater() * 0.25) + ($fleetGive->getSoldier() * 80) + ($fleetGive->getWorker() * 5) + ($fleetGive->getScientist() * 300) + ($fleetGive->getNiobium() * 0.10) + ($fleetGive->getTank() * 2500) + ($fleetGive->getUranium() * 5000));
            }
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format($gainSell) . "</span> bitcoins. Et <span class='text-vert'>+" . number_format($newWarPointS) . "</span> points de Guerre.");
            $em->persist($reportSell);
            $planetTake->setScientist($planetTake->getScientist() + $fleetGive->getScientist());
            $planetTake->setWorker($planetTake->getWorker() + $fleetGive->getWorker());
            $planetTake->setSoldier($planetTake->getSoldier() + $fleetGive->getSoldier());
            $planetTake->setWater($planetTake->getWater() + $fleetGive->getWater());
            $planetTake->setNiobium($planetTake->getNiobium() + $fleetGive->getNiobium());
            $user->setBitcoin($user->getBitcoin() + $gainSell);
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $newWarPointS);
            $fleetGive->setScientist(0);
            $fleetGive->setNiobium(0);
            $fleetGive->setUranium(0);
            $fleetGive->setSoldier(0);
            $fleetGive->setTank(0);
            $fleetGive->setWorker(0);
            $fleetGive->setWater(0);
            $quest = $user->checkQuests('sell');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }
        if ($planetTake->getNiobium() + $fleetGive->getNiobium() <= $planetTake->getNiobiumMax()) {
            $planetTake->setNiobium($planetTake->getNiobium() + $fleetGive->getNiobium());
            $fleetGive->setNiobium(0);
        } else {
            $fleetGive->setNiobium($fleetGive->getNiobium() - ($planetTake->getNiobiumMax() - $planetTake->getNiobium()));
            $planetTake->setNiobium($planetTake->getNiobiumMax());
        }
        if ($planetTake->getWater() + $fleetGive->getWater() <= $planetTake->getWaterMax()) {
            $planetTake->setWater($planetTake->getWater() + $fleetGive->getWater());
            $fleetGive->setWater(0);
        } else {
            $fleetGive->setWater($fleetGive->getWater() - ($planetTake->getWaterMax() - $planetTake->getWater()));
            $planetTake->setWater($planetTake->getWaterMax());
        }
        $planetTake->setUranium($fleetGive->getUranium());
        $fleetGive->setUranium(0);
        if ($planetTake->getSoldier() + $fleetGive->getSoldier() <= $planetTake->getSoldierMax()) {
            $planetTake->setSoldier($planetTake->getSoldier() + $fleetGive->getSoldier());
            $fleetGive->setSoldier(0);
        } else {
            $fleetGive->setSoldier($fleetGive->getSoldier() - ($planetTake->getSoldierMax() - $planetTake->getSoldier()));
            $planetTake->setSoldier($planetTake->getSoldierMax());
        }
        if ($planetTake->getTank() + $fleetGive->getTank() <= 500) {
            $planetTake->setTank($planetTake->getTank() + $fleetGive->getTank());
            $fleetGive->setTank(0);
        } else {
            $fleetGive->setTank($fleetGive->getTank() - (500 - $planetTake->getTank()));
            $planetTake->setTank(500);
        }
        if ($planetTake->getWorker() + $fleetGive->getWorker() <= $planetTake->getWorkerMax()) {
            $planetTake->setWorker($planetTake->getWorker() + $fleetGive->getWorker());
            $fleetGive->setWorker(0);
        } else {
            $fleetGive->setWorker($fleetGive->getWorker() - ($planetTake->getWorkerMax() - $planetTake->getWorker()));
            $planetTake->setWorker($planetTake->getWorkerMax());
        }
        if ($planetTake->getScientist() + $fleetGive->getScientist() <= $planetTake->getScientistMax()) {
            $planetTake->setScientist($planetTake->getScientist() + $fleetGive->getScientist());
            $fleetGive->setScientist(0);
        } else {
            $fleetGive->setScientist($fleetGive->getScientist() - ($planetTake->getScientistMax() - $planetTake->getScientist()));
            $planetTake->setScientist($planetTake->getScientistMax());
        }
        $user->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleetGive->getId(), 'usePlanet' => $usePlanet->getId()]);
    }
}