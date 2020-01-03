<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Report;
use App\Entity\Stats;
use DateTimeZone;
use Dateinterval;
use DateTime;

class DailyController extends AbstractController
{
    public function dailyLoadAction($now, $em)
    {
        $servers = $em->getRepository('App:Server')->findAll();

        $users = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.rank', 'r')
            ->where('u.id != :one')
            ->setParameters(['one' => 1])
            ->orderBy('r.point', 'DESC')
            ->getQuery()
            ->getResult();

        $x = 1;
        foreach ($users as $user) {

            $stats = new Stats();
            $stats->setDate($now);
            $stats->setZombie($user->getZombieAtt());
            $stats->setUser($user);

            $maxQuest = count($user->getWhichQuest()) - 1;
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
            $questOne = $em->getRepository('App:Quest')->findOneByName($user->getWhichQuest()[$first]);
            $questTwo = $em->getRepository('App:Quest')->findOneByName($user->getWhichQuest()[$second]);
            $questTree = $em->getRepository('App:Quest')->findOneByName($user->getWhichQuest()[$third]);
            $report = new Report();
            $report->setType('economic');
            $report->setTitle("Rapport de l'empire");
            $report->setImageName("daily_report.jpg");
            $report->setSendAt($now);
            $report->setUser($user);
            $ally = $user->getAlly();
            $nbrQuests = count($user->getQuests());
            foreach ($user->getQuests() as $quest) {
                $user->removeQuest($quest);
            }
            $user->addQuest($questOne);
            $worker = 0;
            $planetPoint= 0;
            $buildingCost = 0;
            foreach ($user->getPlanets() as $planet) {
                $worker = $worker + $planet->getWorker();
                $planetPoint = $planetPoint + $planet->getBuildingPoint();
                $buildingCost = $buildingCost + $planet->getBuildingCost();
            }
            $gain = round($worker / 3);
            $lose = null;
            if($ally) {
                $user->addQuest($questTwo);
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
                                $lose = (($peace->getTaxe() / 100) * $user->getBitcoin());
                            }
                            $gain = $gain - $lose;
                            $user->setBitcoin($user->getBitcoin() - $lose);
                            $otherAlly->setBitcoin($otherAlly->getBitcoin() + $lose);
                            $exchange = new Exchange();
                            $exchange->setAlly($otherAlly);
                            $exchange->setCreatedAt($now);
                            $exchange->setType(0);
                            $exchange->setAccepted(1);
                            $exchange->setContent("Taxe liée à la paix.");
                            $exchange->setAmount($lose);
                            $exchange->setName($user->getUserName());
                            $em->persist($exchange);
                            $report->setContent($report->getContent() . " La paix que vous avez signé envoi directement <span class='text-rouge'>" . number_format(round($lose)) . "</span> bitcoins à l'aliance [" . $otherAlly->getSigle() . "].<br>");
                        }
                    }
                }
                $userBitcoin = $user->getBitcoin();
                $taxe = (($ally->getTaxe() / 100) * $gain);
                $gain = $gain - $taxe;
                $user->setBitcoin($userBitcoin - $taxe);
                $report->setContent(" Le montant envoyé dans les fonds de votre alliance s'élève à <span class='text-rouge'>" . number_format(round($taxe)) . "</span> bitcoins.<br>");
                $allyBitcoin = $ally->getBitcoin();
                $allyBitcoin = $allyBitcoin + $taxe;
                $ally->setBitcoin($allyBitcoin);
            } else {
                $questAlly = $em->getRepository('App:Quest')->findOneById(50);
                $user->addQuest($questAlly);
            }
            $user->addQuest($questTree);
            $troops = $user->getAllTroops();
            $ship = $user->getAllShipsCost();
            $cost = $user->getBitcoin();
            $report->setContent($report->getContent() . " Le travaille fournit par vos travailleurs vous rapporte <span class='text-vert'>+" . number_format(round($gain)) . "</span> bitcoins.");
            $empireCost = $troops + $ship + $buildingCost;
            $cost = $cost - $empireCost + ($gain);
            $report->setContent($report->getContent() . " L'entretien de votre empire vous coûte cependant <span class='text-rouge'>" . number_format(round($empireCost)) . "</span> bitcoins.<br>");
            $point = round(round($worker / 100) + round($user->getAllShipsPoint() / 75) + round($troops / 75) + $planetPoint);
            $user->setBitcoin($cost);
            if ($user->getBitcoin() < 0) {
                foreach ($user->getFleetLists() as $list) {
                    foreach ($list->getFleets() as $fleetL) {
                        $fleetL->setFleetList(null);
                    }
                    $em->remove($list);
                }
                foreach($user->getFleets() as $fleet) {
                    if ($fleet->getDestination()) {
                        $em->remove($fleet->getDestination());
                    }
                    $em->remove($fleet);
                }
                foreach ($user->getPlanets() as $planet) {
                    $planet->setSignature(0);
                    $planet->setSoldier(0);
                    $planet->setScientist(0);
                }
                $economicGO = 1;
                $user->setBitcoin(5000);
            }
            if ($gain - $empireCost > 0) {
                $color = '<span class="text-vert">+';
            } else {
                $color = '<span class="text-rouge">';
            }
            if ($nbrQuests == 0) {
                $report->setContent($report->getContent() . " Ce qui vous donne un revenu de " . $color . number_format(round($gain - $empireCost)) . "</span> bitcoins. Comme vous avez terminé toutes les quêtes vous recevez un bonus de 20.000 PDG ! Bonne journée suprême Commandant.");
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + 20000);
            } else {
                $report->setContent($report->getContent() . " Ce qui vous donne un revenu de " . $color . number_format(round($gain - $empireCost)) . "</span> bitcoins.<br>Bonne journée Commandant.");
            }
            if ($point - $user->getRank()->getOldPoint() > 0) {
                $user->setExperience($user->getExperience() + ($point - $user->getRank()->getOldPoint()));
            }
            $user->getRank()->setOldPoint($user->getRank()->getPoint());
            $user->getRank()->setPoint($point);
            $user->setViewReport(false);

            $stats->setPdg($user->getRank()->getWarPoint());
            $stats->setPoints($point);
            $stats->setBitcoin($user->getBitcoin());
            $em->persist($stats);

            if ($economicGO == 1) {
                $report->setContent($report->getContent() . "<br>Votre réserve de Bitcoins passe en négatif et vous n'êtes plus en mesure d'entretenir votre armada.<br>Vous perdez tout les vaisseaux que contenait votre Empire et redémarrez avec 5.000 Bitcoins.");
            }
            $em->persist($report);
            $x++;
        }

        $em->flush();

        $users = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.rank', 'r')
            ->where('u.id != :one')
            ->setParameters(['one' => 1])
            ->orderBy('r.point', 'DESC')
            ->getQuery()
            ->getResult();

        $x = 1;
        foreach ($users as $user) {
            $user->getRank()->setOldPosition($user->getRank()->getPosition());
            $user->getRank()->setPosition($x);
            $x++;
        }

        $allys = $em->getRepository('App:Ally')->findAll();

        foreach ($allys as $ally) {
            $ally->setRank($ally->getUsersPoint());
        }

        $nowDaily = new DateTime();
        $nowDaily->setTimezone(new DateTimeZone('Europe/Paris'));
        $nowDaily->add(new DateInterval('PT' . round(86400) . 'S'));
        foreach ($servers as $server) {
            $server->setDailyReport($nowDaily);
        }
        echo "Flush ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}
