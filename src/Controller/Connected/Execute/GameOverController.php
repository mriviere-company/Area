<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class GameOverController extends AbstractController
{
    public function gameOverCronAction($userGOs, $now, $em)
    {
        foreach ($userGOs as $userGO) {
            foreach ($userGO->getFleetLists() as $list) {
                foreach ($list->getFleets() as $fleetL) {
                    $fleetL->setFleetList(null);
                }
                $em->remove($list);
            }
            $ship = $userGO->getShip();
            $userGO->setShip(null);
            if ($ship) {
                $em->remove($ship);
            }
            $userGO->setBitcoin(25000);
            $userGO->setSearch(null);
            $em->remove($userGO->getRank(null));
            $userGO->setRank(null);
            $userGO->setJoinAllyAt(null);
            $userGO->setAllyBan(null);
            $userGO->setScientistProduction(1);
            $userGO->setSearchAt(null);
            $userGO->setDemography(0);
            $userGO->setUtility(0);
            $userGO->setArmement(0);
            $userGO->setIndustry(0);
            $userGO->setTerraformation(round($userGO->getTerraformation(0) / 2));
            $userGO->setPlasma(0);
            $userGO->setLaser(0);
            $userGO->setMissile(0);
            $userGO->setRecycleur(0);
            $userGO->setCargo(0);
            $userGO->setBarge(0);
            $userGO->setHyperespace(0);
            $userGO->setDiscipline(0);
            $userGO->setHeavyShip(0);
            $userGO->setAeroponicFarm(0);
            $userGO->setLightShip(0);
            $userGO->setOnde(0);
            $userGO->setHyperespace(0);
            $userGO->setDiscipline(0);
            $userGO->setBarbed(0);
            $userGO->setTank(0);
            $userGO->setExpansion(0);
            $userGO->setPoliticArmement(0);
            $userGO->setPoliticCostScientist(0);
            $userGO->setPoliticArmor(0);
            $userGO->setPoliticBarge(0);
            $userGO->setPoliticCargo(0);
            $userGO->setPoliticColonisation(0);
            $userGO->setPoliticCostSoldier(0);
            $userGO->setPoliticCostTank(0);
            $userGO->setPoliticInvade(0);
            $userGO->setPoliticMerchant(0);
            $userGO->setPoliticPdg(0);
            $userGO->setPoliticProd(0);
            $userGO->setPoliticRecycleur(0);
            $userGO->setPoliticSearch(0);
            $userGO->setPoliticSoldierAtt(0);
            $userGO->setPoliticSoldierSale(0);
            $userGO->setPoliticTankDef(0);
            $userGO->setPoliticWorker(0);
            $userGO->setPoliticWorkerDef(0);
            $userGO->setZombieAtt(1);
            if ($userGO->getAlly()) {
                $ally = $userGO->getAlly();
                if (count($ally->getUsers()) == 1 || ($ally->getPolitic() == 'fascism' && $userGO->getGrade()->getPlacement() == 1)) {
                    foreach ($ally->getUsers() as $userGO) {
                        $userGO->setAlly(null);
                        $userGO->setGrade(null);
                        $userGO->setAllyBan($now);
                    }
                    foreach ($ally->getFleets() as $fleet) {
                        $fleet->setAlly(null);
                    }
                    foreach ($ally->getGrades() as $grade) {
                        $em->remove($grade);
                    }
                    foreach ($ally->getSalons() as $salon) {
                        foreach ($salon->getContents() as $content) {
                            $em->remove($content);
                        }
                        foreach ($salon->getViews() as $view) {
                            $em->remove($view);
                        }
                        $em->remove($salon);
                    }
                    foreach ($ally->getExchanges() as $exchange) {
                        $em->remove($exchange);
                    }

                    foreach ($ally->getPnas() as $pna) {
                        $em->remove($pna);
                    }

                    foreach ($ally->getWars() as $war) {
                        $em->remove($war);
                    }

                    foreach ($ally->getAllieds() as $allied) {
                        $em->remove($allied);
                    }

                    foreach ($ally->getProposals() as $proposal) {
                        $em->remove($proposal);
                    }
                    $em->flush();

                    $pnas = $em->getRepository('App:Pna')
                        ->createQueryBuilder('p')
                        ->where('p.allyTag = :allytag')
                        ->setParameters(['allytag' => $ally->getSigle()])
                        ->getQuery()
                        ->getResult();

                    $pacts = $em->getRepository('App:Allied')
                        ->createQueryBuilder('a')
                        ->where('a.allyTag = :allytag')
                        ->setParameters(['allytag' => $ally->getSigle()])
                        ->getQuery()
                        ->getResult();

                    $wars = $em->getRepository('App:War')
                        ->createQueryBuilder('w')
                        ->where('w.allyTag = :allytag')
                        ->setParameters(['allytag' => $ally->getSigle()])
                        ->getQuery()
                        ->getResult();

                    foreach ($pnas as $pna) {
                        $em->remove($pna);
                    }

                    foreach ($pacts as $pact) {
                        $em->remove($pact);
                    }

                    foreach ($wars as $war) {
                        $em->remove($war);
                    }

                    $ally->setImageName(null);
                    $em->remove($ally);
                }
            }
            $userGO->setAlly(null);
            $userGO->setGrade(null);

            foreach ($userGO->getSalons() as $salon) {
                $salon->removeUser($userGO);
            }

            $salon = $em->getRepository('App:Salon')
                ->createQueryBuilder('s')
                ->where('s.name = :name')
                ->setParameters(['name' => 'Public'])
                ->getQuery()
                ->getOneOrNullResult();

            $salon->removeUser($userGO);
            $userGO->setSalons(null);
        }
        echo "Flush -> " . count($userGOs) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}