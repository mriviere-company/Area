<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Report;
use App\Entity\Stats;
use App\Entity\Exchange;
use Dateinterval;


/**
 * Class DailyController
 * @package App\Controller\Connected\Execute
 */
class DailyController extends AbstractController
{
    /**
     * @param $server
     * @param $em
     * @return Response
     */
    public function dailyLoadAction($server, $em): Response
    {
        $now = clone $server->getDailyReport();
        $nowDaily = clone $server->getDailyReport();
        $commanders = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->join('c.planets', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->join('c.rank', 'r')
            ->where('c.id != 1')
            ->andWhere('c.bot = false')
            ->andWhere('g.server = :server')
            ->setParameters(['server' => $server])
            ->orderBy('r.point', 'DESC')
            ->getQuery()
            ->getResult();

        $x = 1;
        foreach ($commanders as $commander) {

            $maxQuest = count($commander->getWhichQuest()) - 1;
            $first = rand(0, $maxQuest);
            $second = $first;
            $third = $second;
            $economicGO = 0;
            while ($second == $first) {
                $second = rand(0, $maxQuest);
            }
            while ($third == $first or $third == $second) {
                $third = rand(0, $maxQuest);
            }
            $questOne = $doctrine->getRepository(Quest::class)->findOneByName($commander->getWhichQuest()[$first]);
            $questTwo = $doctrine->getRepository(Quest::class)->findOneByName($commander->getWhichQuest()[$second]);
            $questTree = $doctrine->getRepository(Quest::class)->findOneByName($commander->getWhichQuest()[$third]);
            $report = new Report();
            $report->setType('economic');
            $report->setTitle("Rapport de l'empire");
            $report->setImageName("daily_report.webp");
            $report->setSendAt($now);
            $report->setCommander($commander);
            $ally = $commander->getAlly();
            $nbrQuests = count($commander->getQuests());
            if ($nbrQuests) {
                foreach ($commander->getQuests() as $quest) {
                    $commander->removeQuest($quest);
                }
            }
            $commander->addQuest($questOne);
            $worker = 0;
            $planetPoint= 0;
            $buildingCost = 0;
            foreach ($commander->getPlanets() as $planet) {
                if ($planet->getConstructAt() && $planet->getConstructAt() < $now) {
                    $this->forward('App\Controller\Connected\Execute\PlanetController::buildingOneAction', [
                        'planet'  => $planet,
                        'now' => $now,
                        'em' => $em
                    ]);
                }
                $worker = $worker + $planet->getWorker();
                $planetPoint = $planetPoint + $planet->getBuildingPoint();
                $buildingCost = $buildingCost + $planet->getBuildingCost();
            }
            $gain = $worker / 10;
            $lose = null;
            if($ally) {
                $commander->addQuest($questTwo);
                if($ally->getPeaces()) {
                    foreach($ally->getPeaces() as $peace) {
                        if(!$peace->getType() && $peace->getAccepted() == 1) {
                            $otherAlly = $doctrine->getRepository(Ally::class)
                                ->createQueryBuilder('a')
                                ->where('a.sigle = :sigle')
                                ->setParameter('sigle', $peace->getAllyTag())
                                ->getQuery()
                                ->getOneOrNullResult();

                            $lose = (($peace->getTaxe() / 100) * $gain);
                            if($lose < 0) {
                                $lose = (($peace->getTaxe() / 100) * $commander->getBitcoin());
                            }
                            $gain = $gain - $lose;
                            $commander->setBitcoin($commander->getBitcoin() - $lose);
                            $otherAlly->setBitcoin($otherAlly->getBitcoin() + $lose);
                            $exchange = new Exchange($otherAlly, $commander->getUsername(), 0, 1, $lose, "Taxe liée à la paix.");
                            $em->persist($exchange);
                            $report->setContent($report->getContent() . " La paix que vous avez signé envoi directement <span class='text-rouge'>" . number_format(round($lose)) . "</span> bitcoins à l'aliance [" . $otherAlly->getSigle() . "].<br>");
                        }
                    }
                }
                $commanderBitcoin = $commander->getBitcoin();
                $newGain = $gain /  (1 + $ally->getTaxe() / 100);
                $taxe = $gain - $newGain;
                $gain = $newGain;
                $commander->setBitcoin($commanderBitcoin - $taxe);
                $report->setContent(" Le montant envoyé dans les fonds de votre alliance s'élève à <span class='text-rouge'>" . number_format(round($taxe)) . "</span> bitcoins.<br>");
                $allyBitcoin = $ally->getBitcoin();
                $allyBitcoin = $allyBitcoin + $taxe;
                $ally->setBitcoin($allyBitcoin);
            } else {
                $questAlly = $doctrine->getRepository(Quest::class)->findOneById(50);
                $commander->addQuest($questAlly);
            }
            $commander->addQuest($questTree);
            $troops = $commander->getAllTroops();
            $ship = $commander->getAllShipsCost();
            $cost = $commander->getBitcoin();
            $report->setContent($report->getContent() . " Le travail fourni par vos travailleurs vous rapporte <span class='text-vert'>+" . number_format(round($gain)) . "</span> bitcoins.");
            $empireCost = $troops + $ship + $buildingCost;
            $cost = $cost - $empireCost + ($gain);
            $report->setContent($report->getContent() . " L'entretien de votre empire vous coûte cependant <span class='text-rouge'>" . number_format(round($empireCost)) . "</span> bitcoins.<br>");
            $point = round(round($worker / 100) + round($commander->getAllShipsPoint() / 10) + round($troops / 50) + $planetPoint);
            $commander->setBitcoin($cost);

            if ($commander->getBitcoin() < 0) {
                foreach ($commander->getFleetLists() as $list) {
                    foreach ($list->getFleets() as $fleetL) {
                        $fleetL->setFleetList(null);
                    }
                    $em->remove($list);
                }
                foreach($commander->getFleets() as $fleet) {
                    if ($fleet->getDestination()) {
                        $em->remove($fleet->getDestination());
                    }
                    $em->remove($fleet);
                }
                if ($commander->getMissions()) {
                    foreach($commander->getMissions() as $mission) {
                        $em->remove($mission);
                    }
                foreach ($commander->getPlanets() as $planet) {
                    $product = $planet->getProduct();
                    $planet->setSonde(0);
                    $planet->setCargoI(0);
                    $planet->setCargoV(0);
                    $planet->setCargoX(0);
                    $planet->setColonizer(0);
                    $planet->setRecycleur(0);
                    $planet->setBarge(0);
                    $planet->setMoonMaker(0);
                    $planet->setRadarShip(0);
                    $planet->setBrouilleurShip(0);
                    $planet->setMotherShip(0);
                    $planet->setHunter(0);
                    $planet->setHunterHeavy(0);
                    $planet->setHunterWar(0);
                    $planet->setCorvet(0);
                    $planet->setCorvetLaser(0);
                    $planet->setCorvetWar(0);
                    $planet->setFregate(0);
                    $planet->setFregatePlasma(0);
                    $planet->setCroiser(0);
                    $planet->setIronClad(0);
                    $planet->setDestroyer(0);
                    $planet->setNuclearBomb(0);
                    $planet->setSignature(0);
                    $planet->setSoldier(0);
                    $planet->setTank(0);
                    $planet->setScientist(0);
                    $planet->getSoldierAtNbr(0);
                    $planet->getTankAtNbr(0);
                    if($product) {
                        $product->setPlanet(null);
                        $em->remove($product);
                    }
                    }
                }
                $economicGO = 1;
                $commander->setBitcoin(5000);
            }
            if ($gain - $empireCost > 0) {
                $color = '<span class="text-vert">+';
            } else {
                $color = '<span class="text-rouge">';
            }
            if ($nbrQuests == 0) {
                $report->setContent($report->getContent() . " Ce qui vous donne un revenu de " . $color . number_format(round($gain - $empireCost)) . "</span> bitcoins. Comme vous avez terminé toutes les quêtes vous recevez un bonus de 20.000 PDG ! Bonne journée suprême Commandant.");
                $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + 250);
            } else {
                $report->setContent($report->getContent() . " Ce qui vous donne un revenu de " . $color . number_format(round($gain - $empireCost)) . "</span> bitcoins.<br>Bonne journée Commandant.");
            }
            if ($point - $commander->getRank()->getOldPoint() > 0) {
                $commander->setExperience($commander->getExperience() + ($point - $commander->getRank()->getOldPoint()));
            }
            $commander->getRank()->setOldPoint($commander->getRank()->getPoint());
            $commander->getRank()->setPoint($point);
            $commander->setViewReport(false);

            $stats = new Stats($commander, $commander->getBitcoin(), $point, $commander->getRank()->getWarPoint(),  $commander->getZombieAtt());
            $em->persist($stats);

            if ($economicGO == 1) {
                $report->setContent($report->getContent() . "<br>Votre réserve de Bitcoins passe en négatif et vous n'êtes plus en mesure d'entretenir votre armada.<br>Vous perdez tous les vaisseaux que contenait votre Empire et redémarrez avec 5.000 Bitcoins.");
            }
            $em->persist($report);

            $x++;
        }

        $em->flush();

        $commandersUp = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->join('c.rank', 'r')
            ->where('c.id != 1')
            ->andWhere('c.bot = false')
            ->orderBy('r.point', 'DESC')
            ->getQuery()
            ->getResult();

        $x = 1;
        foreach ($commandersUp as $commander) {
            $commander->getRank()->setOldPosition($commander->getRank()->getPosition());
            $commander->getRank()->setPosition($x);
            $x++;
        }

        $allys = $doctrine->getRepository(Ally::class)->findAll();

        foreach ($allys as $ally) {
            $ally->setRank($ally->getCommandersPoint());
        }
        $nowDaily->add(new DateInterval('P1D'));

        $server->setDailyReport($nowDaily);
        echo "Flush -> " . count($commanders) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}
