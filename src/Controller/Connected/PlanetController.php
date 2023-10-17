<?php

namespace App\Controller\Connected;

use App\Entity\Fleet;
use App\Entity\Report;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\PlanetRenameType;
use App\Entity\Planet;
use DateTime;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class PlanetController extends AbstractController
{
    /**
     * @Route("/planete/{usePlanet}", name="planet", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     */
    public function planetAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if($commander->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $otherPoints = $doctrine->getRepository(Stats::class)
            ->createQueryBuilder('s')
            ->join('s.commander', 'c')
            ->select('count(s) as numbers, sum(DISTINCT s.bitcoin) as allBitcoin')
            ->groupBy('s.date')
            ->andWhere('c.bot = false')
            ->getQuery()
            ->getResult();

        $planetsSeller = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->where('p.commander = :commander')
            ->andWhere('p.autoSeller = true')
            ->setParameters(['commander' => $commander])
            ->getQuery()
            ->getResult();

        $planetsNoSell = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->where('p.commander = :commander')
            ->andWhere('p.autoSeller = false')
            ->setParameters(['commander' => $commander])
            ->getQuery()
            ->getResult();

        $form_manageRenamePlanet = $this->createForm(PlanetRenameType::class);
        $form_manageRenamePlanet->handleRequest($request);

        if ($form_manageRenamePlanet->isSubmitted() && $form_manageRenamePlanet->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $renamePlanet = $doctrine->getRepository(Planet::class)
                ->createQueryBuilder('p')
                ->where('p.id = :id')
                ->andWhere('p.commander = :commander')
                ->setParameters(['id' => $form_manageRenamePlanet->get('id')->getData(), 'commander' => $commander])
                ->getQuery()
                ->getOneOrNullResult();

            $renamePlanet->setName($form_manageRenamePlanet->get('name')->getData());
            if(($user->getTutorial() == 3)) {
                $user->setTutorial(4);
            }

            $em->flush();
            return $this->redirectToRoute('planet', ['usePlanet' => $usePlanet->getId()]);
        }
        if(($user->getTutorial() == 2)) {
            $user->setTutorial(3);
            $em->flush();
        }

        return $this->render('connected/planet.html.twig', [
            'usePlanet' => $usePlanet,
            'formObject' => $form_manageRenamePlanet,
            'planetsSeller' => $planetsSeller,
            'planetsNoSell' => $planetsNoSell,
            'otherPoints' => $otherPoints
        ]);
    }

    /**
     * @Route("/colonisation-planete/{fleet}/", name="colonizer_planet", requirements={"fleet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Fleet $fleet
     * @return RedirectResponse
     */
    public function colonizeAction(ManagerRegistry $doctrine, Fleet $fleet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getMainCommander();
        $colonize = $doctrine->getRepository(Fleet::class)->find(['id' => $fleet]);
        $newPlanet = $colonize->getPlanet();

        if($colonize->getColonizer() && $newPlanet->getCommander() == null &&
            !$newPlanet->getEmpty() && !$newPlanet->getTrader() &&
            !$newPlanet->getCdr() && $colonize->getCommander()->getColPlanets() < 26 &&
            $colonize->getCommander()->getColPlanets() <= ($commander->getTerraformation() + 1 + $commander->getPoliticColonisation())) {

            $colonize->setColonizer($colonize->getColonizer() - 1);
            $newPlanet->setCommander($colonize->getCommander());
            $newPlanet->setName('Colonie');
            $newPlanet->setSoldier(20);
            $newPlanet->setScientist(0);
            $newPlanet->setNbColo(count($fleet->getCommander()->getPlanets()) + 1);
            if($colonize->getNbrShip() == 0) {
                $em->remove($colonize);
            }
            $reportColo = new Report();
            $reportColo->setSendAt($now);
            $reportColo->setCommander($commander);
            $reportColo->setTitle("Colonisation de planète");
            $reportColo->setImageName("colonize_report.webp");
            $reportColo->setContent("Vous venez de coloniser une planète inhabitée en : (" .  $newPlanet->getSector()->getgalaxy()->getPosition() . "." . $newPlanet->getSector()->getPosition() . "." . $newPlanet->getPosition() . ") . Cette planète fait désormais partie de votre Empire, pensez à la renommer sur la page Planètes.");
            $commander->setNewReport(false);
            $em->persist($reportColo);
            $quest = $commander->checkQuests('colonize');
            if($quest) {
                $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + $quest->getGain());
                $commander->removeQuest($quest);
            }
            $em->flush();
        }

        return $this->redirectToRoute('building', ['usePlanet' => $newPlanet->getId()]);
    }

    /**
     * @Route("/planete-abandon/{abandonPlanet}/{usePlanet}", name="planet_abandon", requirements={"usePlanet"="\d+","abandonPlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @param Planet $abandonPlanet
     * @return RedirectResponse
     */
    public function planetAbandonAction(ManagerRegistry $doctrine, Planet $usePlanet, Planet $abandonPlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        
        if ($usePlanet->getCommander() != $commander || $abandonPlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $fleetComing = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->join('dp.sector', 's')
            ->join('s.galaxy', 'g')
            ->where('f.flightAt is not null')
            ->andWhere('dp.position = :planete')
            ->andWhere('s.position = :sector')
            ->andWhere('g.position = :galaxy')
            ->andWhere('f.commander != :commander')
            ->andWhere('f.attack = true')
            ->setParameters(['planete' => $abandonPlanet->getPosition(), 'sector' => $abandonPlanet->getSector()->getPosition(), 'galaxy' => $abandonPlanet->getSector()->getGalaxy()->getPosition(), 'commander' => $commander])
            ->getQuery()
            ->getResult();

        if($abandonPlanet->getFleetsAbandon($commander) == 1 || $fleetComing) {
            return $this->redirectToRoute('planet', ['usePlanet' => $usePlanet->getId()]);
        }

        if($abandonPlanet->getSky() == 5 && $abandonPlanet->getGround() == 25) {
            if($abandonPlanet->getWorker() < 10000) {
                $abandonPlanet->setWorker(10000);
            }
            $abandonPlanet->setCommander(null);
            $abandonPlanet->setName('Abandonnée');
        } else {
            $hydra = $doctrine->getRepository(Commander::class)->findOneBy(['zombie' => 1]);

            $abandonPlanet->setCommander($hydra);
            $abandonPlanet->setWorker(125000);
            if ($abandonPlanet->getSoldierMax() >= 500) {
                $abandonPlanet->setSoldier($abandonPlanet->getSoldierMax());
            } else {
                $abandonPlanet->setCaserne(1);
                $abandonPlanet->setSoldier(500);
                $abandonPlanet->setSoldierMax(500);
            }
            $abandonPlanet->setName('Base Zombie');
            $abandonPlanet->setImageName('hydra_planet.webp');
        }

        $em->flush();

        if($commander->getColPlanets() == 0) {
            $hydra = $doctrine->getRepository(Commander::class)->findOneBy(['zombie' => 1]);
            foreach ($commander->getFleets() as $fleet) {
                if($fleet->getFleetList()) {
                    $fleet->getFleetList()->removeFleet($fleet);
                    $fleet->setFleetList(null);
                }
                $fleet->setCommander($hydra);
                $fleet->setName('Incursion H');
                $fleet->setAttack(true);
            }

            $em->flush();

            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet === $abandonPlanet) {
            $usePlanet = $doctrine->getRepository(Planet::class)->findByFirstPlanet($commander);
        }

        return $this->redirectToRoute('planet', ['usePlanet' => $usePlanet->getId()]);
    }
}