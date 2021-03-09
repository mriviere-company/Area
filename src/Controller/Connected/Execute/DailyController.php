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
    public function dailyLoadAction($server, $em)
    {
        $now = clone $server->getDailyReport();
        $nowDaily = clone $server->getDailyReport();
        $characters = $em->getRepository('App:Character')
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
        foreach ($characters as $character) {

            $maxQuest = count($character->getWhichQuest()) - 1;
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
            $questOne = $em->getRepository('App:Quest')->findOneByName($character->getWhichQuest()[$first]);
            $questTwo = $em->getRepository('App:Quest')->findOneByName($character->getWhichQuest()[$second]);
            $questTree = $em->getRepository('App:Quest')->findOneByName($character->getWhichQuest()[$third]);
            $report = new Report();
            $report->setType('economic');
            $report->setTitle("Rapport de l'empire");
            $report->setImageName("daily_report.jpg");
            $report->setSendAt($now);
            $report->setCharacter($character);
            $ally = $character->getAlly();
            $nbrQuests = count($character->getQuests());
            if ($nbrQuests) {
                foreach ($character->getQuests() as $quest) {
                    $character->removeQuest($quest);
                }
            }
            $character->addQuest($questOne);
            $worker = 0;
            $planetPoint= 0;
            $buildingCost = 0;
            foreach ($character->getPlanets() as $planet) {
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
                $character->addQuest($questTwo);
                if($ally->getPeaces()) {
                    foreach($ally->getPeaces() as $peace) {
                        if($peace->getType() == false && $peace->getAccepted() == 1) {
                            $otherAlly = $em->getRepository('App:Ally')
                                ->createQueryBuilder('a')
                                ->where('a.sigle = :sigle')
                                ->setParameter('sigle', $peace->getAllyTag())
                                ->getQuery()
                                ->getOneOrNullResult();

                            $lose = (($peace->getTaxe() / 100) * $gain);
                            if($lose < 0) {
                                $lose = (($peace->getTaxe() / 100) * $character->getBitcoin());
                            }
                            $gain = $gain - $lose;
                            $character->setBitcoin($character->getBitcoin() - $lose);
                            $otherAlly->setBitcoin($otherAlly->getBitcoin() + $lose);
                            $exchange = new Exchange($otherAlly, $character->getUserName(), 0, 1, $lose, "Taxe liée à la paix.");
                            $em->persist($exchange);
                            $report->setContent($report->getContent() . " La paix que vous avez signé envoi directement <span class='text-rouge'>" . number_format(round($lose)) . "</span> bitcoins à l'aliance [" . $otherAlly->getSigle() . "].<br>");
                        }
                    }
                }
                $characterBitcoin = $character->getBitcoin();
                $newGain = $gain /  (1 + $ally->getTaxe() / 100);
                $taxe = $gain - $newGain;
                $gain = $newGain;
                $character->setBitcoin($characterBitcoin - $taxe);
                $report->setContent(" Le montant envoyé dans les fonds de votre alliance s'élève à <span class='text-rouge'>" . number_format(round($taxe)) . "</span> bitcoins.<br>");
                $allyBitcoin = $ally->getBitcoin();
                $allyBitcoin = $allyBitcoin + $taxe;
                $ally->setBitcoin($allyBitcoin);
            } else {
                $questAlly = $em->getRepository('App:Quest')->findOneById(50);
                $character->addQuest($questAlly);
            }
            $character->addQuest($questTree);
            $troops = $character->getAllTroops();
            $ship = $character->getAllShipsCost();
            $cost = $character->getBitcoin();
            $report->setContent($report->getContent() . " Le travail fourni par vos travailleurs vous rapporte <span class='text-vert'>+" . number_format(round($gain)) . "</span> bitcoins.");
            $empireCost = $troops + $ship + $buildingCost;
            $cost = $cost - $empireCost + ($gain);
            $report->setContent($report->getContent() . " L'entretien de votre empire vous coûte cependant <span class='text-rouge'>" . number_format(round($empireCost)) . "</span> bitcoins.<br>");
            $point = round(round($worker / 100) + round($character->getAllShipsPoint() / 10) + round($troops / 50) + $planetPoint);
            $character->setBitcoin($cost);

            if ($character->getBitcoin() < 0) {
                foreach ($character->getFleetLists() as $list) {
                    foreach ($list->getFleets() as $fleetL) {
                        $fleetL->setFleetList(null);
                    }
                    $em->remove($list);
                }
                foreach($character->getFleets() as $fleet) {
                    if ($fleet->getDestination()) {
                        $em->remove($fleet->getDestination());
                    }
                    $em->remove($fleet);
                }
                if ($character->getMissions()) {
                    foreach($character->getMissions() as $mission) {
                        $em->remove($mission);
                    }
                foreach ($character->getPlanets() as $planet) {
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
                $character->setBitcoin(5000);
            }
            if ($gain - $empireCost > 0) {
                $color = '<span class="text-vert">+';
            } else {
                $color = '<span class="text-rouge">';
            }
            if ($nbrQuests == 0) {
                $report->setContent($report->getContent() . " Ce qui vous donne un revenu de " . $color . number_format(round($gain - $empireCost)) . "</span> bitcoins. Comme vous avez terminé toutes les quêtes vous recevez un bonus de 20.000 PDG ! Bonne journée suprême Commandant.");
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + 250);
            } else {
                $report->setContent($report->getContent() . " Ce qui vous donne un revenu de " . $color . number_format(round($gain - $empireCost)) . "</span> bitcoins.<br>Bonne journée Commandant.");
            }
            if ($point - $character->getRank()->getOldPoint() > 0) {
                $character->setExperience($character->getExperience() + ($point - $character->getRank()->getOldPoint()));
            }
            $character->getRank()->setOldPoint($character->getRank()->getPoint());
            $character->getRank()->setPoint($point);
            $character->setViewReport(false);

            $stats = new Stats($character, $character->getBitcoin(), $point, $character->getRank()->getWarPoint(),  $character->getZombieAtt());
            $em->persist($stats);

            if ($economicGO == 1) {
                $report->setContent($report->getContent() . "<br>Votre réserve de Bitcoins passe en négatif et vous n'êtes plus en mesure d'entretenir votre armada.<br>Vous perdez tous les vaisseaux que contenait votre Empire et redémarrez avec 5.000 Bitcoins.");
            }
            $em->persist($report);

            $x++;
        }

        $em->flush();

        $charactersUp = $em->getRepository('App:Character')
            ->createQueryBuilder('c')
            ->join('c.rank', 'r')
            ->where('c.id != 1')
            ->andWhere('c.bot = false')
            ->orderBy('r.point', 'DESC')
            ->getQuery()
            ->getResult();

        $x = 1;
        foreach ($charactersUp as $character) {
            $character->getRank()->setOldPosition($character->getRank()->getPosition());
            $character->getRank()->setPosition($x);
            $x++;
        }

        $allys = $em->getRepository('App:Ally')->findAll();

        foreach ($allys as $ally) {
            $ally->setRank($ally->getCharactersPoint());
        }
        $nowDaily->add(new DateInterval('P1D'));

        $server->setDailyReport($nowDaily);
        echo "Flush -> " . count($characters) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}
