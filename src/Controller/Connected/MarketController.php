<?php

namespace App\Controller\Connected;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\MarketType;
use App\Entity\Planet;
use App\Entity\Report;
use App\Entity\Destination;
use App\Entity\Fleet;
use DateTime;
use DateTimeZone;
use DateInterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class MarketController extends AbstractController
{
    /**
     * @Route("/marchands/{usePlanet}", name="market", requirements={"usePlanet"="\d+"})
     */
    public function marketAction(Request $request, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $quests = $em->getRepository('App:Quest')
            ->createQueryBuilder('q')
            ->select('q.name, q.gain')
            ->join('q.users', 'u')
            ->where('u.id = :user')
            ->setParameters(['user' => $user->getId()])
            ->getQuery()
            ->getResult();

        $form_market = $this->createForm(MarketType::class, null, ["user" => $user->getId()]);
        $form_market->handleRequest($request);

        if ($form_market->isSubmitted() && $form_market->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $planetBuy = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->where('p.id = :id')
                ->setParameters(['id' => $form_market->get('planet')->getData()])
                ->getQuery()
                ->getOneOrNullResult();

            if(!$planetBuy) {
                $planetBuy = $usePlanet;
            }
            $cost = (abs($form_market->get('bitcoin')->getData())) + (abs($form_market->get('soldier')->getData())) + (abs($form_market->get('worker')->getData()));
            $cost = ceil($cost);
            if(($cost > $user->getRank()->getWarPoint() ||
                ($planetBuy->getSoldier() + abs($form_market->get('soldier')->getData())) > $planetBuy->getSoldierMax()) ||
                    ($planetBuy->getWorker() + abs($form_market->get('worker')->getData())) > $planetBuy->getWorkerMax()) {
                if ($planetBuy->getSoldier() + abs($form_market->get('soldier')->getData()) > $planetBuy->getSoldierMax()) {
                    $this->addFlash("fail", "Vous dépassez la limite de soldats sur la planète.");
                } elseif ($planetBuy->getWorker() + abs($form_market->get('worker')->getData()) > $planetBuy->getWorkerMax()) {
                    $this->addFlash("fail", "Vous dépassez la limite de travailleurs sur la planète.");
                } elseif ($cost > $user->getRank()->getWarPoint()) {
                    $this->addFlash("fail", "Vous n'avez pas assez de bitcoins.");
                } else {
                    $this->addFlash("fail", "Vous n'avez pas toutes les conditions requises.");
                }
                return $this->redirectToRoute('market', ['usePlanet' => $usePlanet->getId()]);
            }

            $user->setBitcoin($user->getBitcoin() + abs($form_market->get('bitcoin')->getData()));
            $planetBuy->setSoldier($planetBuy->getSoldier() + abs($form_market->get('soldier')->getData() / 25));
            $planetBuy->setWorker($planetBuy->getWorker() + abs($form_market->get('worker')->getData() / 5));
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() - $cost);
            $quest = $user->checkQuests('pdg');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }

            $em->flush();
            return $this->redirectToRoute('market', ['usePlanet' => $usePlanet->getId()]);
        }

        if(($user->getTutorial() == 16)) {
            $user->setTutorial(17);
            $em->flush();
        }

        return $this->render('connected/market.html.twig', [
            'usePlanet' => $usePlanet,
            'form_market' => $form_market->createView(),
            'quests' => $quests
        ]);
    }

    /**
     * @Route("/planete-ajouter-marchand/{usePlanet}/{planet}", name="planet_seller_add", requirements={"usePlanet"="\d+","planet"="\d+"})
     */
    public function planetAddAction(Planet $usePlanet, Planet $planet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if($user == $planet->getUser()) {
            $planet->setAutoSeller(true);
            $em->flush();
        }

        return $this->redirectToRoute('planet', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/planete-enlever-marchand/{planet}/{usePlanet}", name="planet_seller_sub", requirements={"usePlanet"="\d+","planet"="\d+"})
     */
    public function planetSubAction(Planet $usePlanet, Planet $planet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if($user == $planet->getUser()) {
            $planet->setAutoSeller(false);
            $em->flush();
        }

        return $this->redirectToRoute('planet', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/vente-auto-marchand/{usePlanet}/", name="planets_seller", requirements={"usePlanet"="\d+"})
     */
    public function sellMerchantAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }
        $merchant = $em->getRepository('App:User')->findOneBy(['merchant' => 1]);
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        $planetsSeller = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.autoSeller = true')
            ->setParameters(['user' => $user])
            ->getQuery()
            ->getResult();

        $planetMerchant = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->andWhere('p.merchant = true')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        $gain = 0;
        foreach ($planetsSeller as $planet) {
            if ($planet->getOffensiveFleet($user) != 'ennemy') {
                if ($user->getAlly() && $user->getAlly()->getPolitic() == 'democrat') {
                    if ($user->getPoliticMerchant() > 0) {
                        $gain = $gain + round((($planet->getWater() * 0.5) + ($planet->getNiobium() * 0.25)) * (1 + ($user->getPoliticMerchant() / 20)) * 0.75);
                    } else {
                        $gain = $gain + round((($planet->getWater() * 0.5) + ($planet->getNiobium() * 0.25)) * 0.75);
                    }
                } else {
                    $gain = $gain + round((($planet->getWater() * 0.5) + ($planet->getNiobium() * 0.25)) * 0.75);
                }
                $planet->setNiobium(0);
                $planet->setWater(0);
                if ($gain > 0) {
                    $repor = new DateTime();
                    $repor->setTimezone(new DateTimeZone('Europe/Paris'));
                    $repor->add(new DateInterval('PT' . 1200 . 'S'));
                    $fleet = new Fleet();
                    $fleet->setHunter(1);
                    $fleet->setUser($merchant);
                    $fleet->setPlanet($planet);
                    $destination = new Destination();
                    $destination->setFleet($fleet);
                    $destination->setPlanet($planetMerchant);
                    $em->persist($destination);
                    $fleet->setFlightTime($repor);
                    $fleet->setAttack(0);
                    $fleet->setName('Cargos');
                    $fleet->setSignature(250);
                    $em->persist($fleet);
                }
            }
        }
        if ($gain > 0) {
            $reportSell = new Report();
            $reportSell->setType('economic');
            $reportSell->setSendAt($now);
            $reportSell->setUser($user);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.jpg");
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format($gain) . "</span> bitcoins. Vous ne gagnez pas de points de Guerre dans les ventes automatiques, pour en gagner vendez directement aux Marchands.");
            $em->persist($reportSell);
            $user->setBitcoin($user->getBitcoin() + $gain);
            $user->setViewReport(false);
            $quest = $user->checkQuests('sell');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
        }

        $em->flush();

        return $this->redirectToRoute('report_id', ['id' => 'economic', 'usePlanet' => $usePlanet->getId()]);
    }
}