<?php

namespace App\Controller\Connected;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
use DateInterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class MarketController extends AbstractController
{
    /**
     * @Route("/marchands/{usePlanet}", name="market", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     */
    public function marketAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $quests = $em->getRepository('App:Quest')
            ->createQueryBuilder('q')
            ->select('q.name, q.gain')
            ->join('q.characters', 'c')
            ->where('c.id = :character')
            ->setParameters(['character' => $character->getId()])
            ->getQuery()
            ->getResult();

        $form_market = $this->createForm(MarketType::class, null, ["character" => $character->getId()]);
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
            if(($cost > $character->getRank()->getWarPoint() ||
                ($planetBuy->getSoldier() + abs($form_market->get('soldier')->getData() * 20)) > $planetBuy->getSoldierMax()) ||
                    ($planetBuy->getWorker() + abs($form_market->get('worker')->getData() * 100)) > $planetBuy->getWorkerMax()) {
                if ($planetBuy->getSoldier() + abs($form_market->get('soldier')->getData() * 20) > $planetBuy->getSoldierMax()) {
                    $this->addFlash("fail", "Vous dépassez la limite de soldats sur la planète.");
                } elseif ($planetBuy->getWorker() + abs($form_market->get('worker')->getData() * 100) > $planetBuy->getWorkerMax()) {
                    $this->addFlash("fail", "Vous dépassez la limite de travailleurs sur la planète.");
                } elseif ($cost > $character->getRank()->getWarPoint()) {
                    $this->addFlash("fail", "Vous n'avez pas assez de points de Guerre.");
                } else {
                    $this->addFlash("fail", "Vous n'avez pas toutes les conditions requises.");
                }
                return $this->redirectToRoute('market', ['usePlanet' => $usePlanet->getId()]);
            }

            $character->setBitcoin($character->getBitcoin() + abs($form_market->get('bitcoin')->getData() * 10));
            $planetBuy->setSoldier($planetBuy->getSoldier() + abs($form_market->get('soldier')->getData() * 20));
            $planetBuy->setWorker($planetBuy->getWorker() + abs($form_market->get('worker')->getData() * 100));
            $character->getRank()->setWarPoint($character->getRank()->getWarPoint() - $cost);
            $quest = $character->checkQuests('pdg');
            if($quest) {
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                $character->removeQuest($quest);
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
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @param Planet $planet
     * @return RedirectResponse
     */
    public function planetAddAction(ManagerRegistry $doctrine, Planet $usePlanet, Planet $planet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if($character->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if($character == $planet->getCharacter()) {
            $planet->setAutoSeller(true);
            $em->flush();
        }

        return $this->redirectToRoute('planet', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/planete-enlever-marchand/{planet}/{usePlanet}", name="planet_seller_sub", requirements={"usePlanet"="\d+","planet"="\d+"})
     */
    public function planetSubAction(ManagerRegistry $doctrine, Planet $usePlanet, Planet $planet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if($character->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        if($character == $planet->getCharacter()) {
            $planet->setAutoSeller(false);
            $em->flush();
        }

        return $this->redirectToRoute('planet', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/vente-auto-marchand/{usePlanet}/", name="planets_seller", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws NonUniqueResultException
     */
    public function sellMerchantAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }
        $merchant = $em->getRepository('App:Character')->findOneBy(['merchant' => 1]);
        $now = new DateTime();

        if($character->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }

        $planetsSeller = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.character = :character')
            ->andWhere('p.autoSeller = true')
            ->setParameters(['character' => $character])
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
            if ($this->forward('App\Service\PlanetService::planetAttackedAction', ['planet'  => $planet->getId()])) {
                if ($character->getAlly() && $character->getAlly()->getPolitic() == 'democrat') {
                    if ($character->getPoliticMerchant() > 0) {
                        $gain = $gain + round((($planet->getWater() * 0.5) + ($planet->getNiobium() * 0.25)) * (1 + ($character->getPoliticMerchant() / 20)) * 0.75);
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
                    $repor->add(new DateInterval('PT' . 1200 . 'S'));
                    $fleet = new Fleet();
                    $fleet->setHunter(1);
                    $fleet->setCharacter($merchant);
                    $fleet->setPlanet($planet);
                    $destination = new Destination($fleet, $planetMerchant);
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
            $reportSell->setCharacter($character);
            $reportSell->setTitle("Vente aux marchands");
            $reportSell->setImageName("sell_report.webp");
            $reportSell->setContent("Votre vente aux marchands vous a rapporté <span class='text-vert'>+" . number_format($gain) . "</span> bitcoins. Vous ne gagnez pas de points de Guerre dans les ventes automatiques, pour en gagner vendez directement aux Marchands.");
            $em->persist($reportSell);
            $character->setBitcoin($character->getBitcoin() + $gain);
            $character->setViewReport(false);
            $quest = $character->checkQuests('sell');
            if($quest) {
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                $character->removeQuest($quest);
            }
        }

        $em->flush();

        return $this->redirectToRoute('report_id', ['id' => 'economic', 'usePlanet' => $usePlanet->getId()]);
    }
}