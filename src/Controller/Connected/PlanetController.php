<?php

namespace App\Controller\Connected;

use App\Entity\Fleet;
use App\Entity\Report;
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
     */
    public function planetAction(Request $request, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $otherPoints = $em->getRepository('App:Stats')
            ->createQueryBuilder('s')
            ->join('s.user', 'u')
            ->select('count(s) as numbers, sum(DISTINCT s.bitcoin) as allBitcoin')
            ->groupBy('s.date')
            ->andWhere('u.bot = false')
            ->getQuery()
            ->getResult();

        $planetsSeller = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.autoSeller = true')
            ->setParameters(['user' => $user])
            ->getQuery()
            ->getResult();

        $planetsNoSell = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.autoSeller = false')
            ->setParameters(['user' => $user])
            ->getQuery()
            ->getResult();

        $form_manageRenamePlanet = $this->createForm(PlanetRenameType::class);
        $form_manageRenamePlanet->handleRequest($request);

        if ($form_manageRenamePlanet->isSubmitted() && $form_manageRenamePlanet->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $renamePlanet = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->where('p.id = :id')
                ->andWhere('p.user = :user')
                ->setParameters(['id' => $form_manageRenamePlanet->get('id')->getData(), 'user' => $user])
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
     */
    public function colonizeAction(Fleet $fleet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $colonize = $em->getRepository('App:Fleet')->find(['id' => $fleet]);
        $newPlanet = $colonize->getPlanet();

        if($colonize->getColonizer() && $newPlanet->getUser() == null &&
            $newPlanet->getEmpty() == false && $newPlanet->getMerchant() == false &&
            $newPlanet->getCdr() == false && $colonize->getUser()->getColPlanets() < 26 &&
            $colonize->getUser()->getColPlanets() <= ($user->getTerraformation() + 1 + $user->getPoliticColonisation())) {

            $colonize->setColonizer($colonize->getColonizer() - 1);
            $newPlanet->setUser($colonize->getUser());
            $newPlanet->setName('Colonie');
            $newPlanet->setSoldier(50);
            $newPlanet->setScientist(0);
            $newPlanet->setNbColo(count($fleet->getUser()->getPlanets()) + 1);
            if($colonize->getNbrShips() == 0) {
                $em->remove($colonize);
            }
            $reportColo = new Report();
            $reportColo->setSendAt($now);
            $reportColo->setUser($user);
            $reportColo->setTitle("Colonisation de planète");
            $reportColo->setImageName("colonize_report.jpg");
            $reportColo->setContent("Vous venez de coloniser une planète inhabitée en : (" .  $newPlanet->getSector()->getgalaxy()->getPosition() . "." . $newPlanet->getSector()->getPosition() . "." . $newPlanet->getPosition() . ") . Cette planète fait désormais partie de votre Empire, pensez à la renommer sur la page Planètes.");
            $user->setViewReport(false);
            $em->persist($reportColo);
            $quest = $user->checkQuests('colonize');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
            $em->flush();
        }

        return $this->redirectToRoute('building', ['usePlanet' => $newPlanet->getId()]);
    }

    /**
     * @Route("/planete-abandon/{abandonPlanet}/{usePlanet}", name="planet_abandon", requirements={"usePlanet"="\d+","abandonPlanet"="\d+"})
     */
    public function planetAbandonAction(Planet $usePlanet, Planet $abandonPlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user || $abandonPlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $fleetComing = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.destination', 'd')
            ->join('d.planet', 'dp')
            ->join('dp.sector', 's')
            ->join('s.galaxy', 'g')
            ->where('f.flightTime is not null')
            ->andWhere('dp.position = :planete')
            ->andWhere('s.position = :sector')
            ->andWhere('g.position = :galaxy')
            ->andWhere('f.user != :user')
            ->andWhere('f.attack = true')
            ->setParameters(['planete' => $abandonPlanet->getPosition(), 'sector' => $abandonPlanet->getSector()->getPosition(), 'galaxy' => $abandonPlanet->getSector()->getGalaxy()->getPosition(), 'user' => $user])
            ->getQuery()
            ->getResult();

        if($abandonPlanet->getFleetsAbandon($user) == 1 || $fleetComing) {
            return $this->redirectToRoute('planet', ['usePlanet' => $usePlanet->getId()]);
        }

        if($abandonPlanet->getSky() == 5 && $abandonPlanet->getGround() == 25) {
            if($abandonPlanet->getWorker() < 10000) {
                $abandonPlanet->setWorker(10000);
            }
            $abandonPlanet->setUser(null);
            $abandonPlanet->setName('Abandonnée');
        } else {
            $hydra = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);

            $abandonPlanet->setUser($hydra);
            $abandonPlanet->setWorker(125000);
            if ($abandonPlanet->getSoldierMax() >= 2500) {
                $abandonPlanet->setSoldier($abandonPlanet->getSoldierMax());
            } else {
                $abandonPlanet->setCaserne(1);
                $abandonPlanet->setSoldier(2500);
                $abandonPlanet->setSoldierMax(2500);
            }
            $abandonPlanet->setName('Base Zombie');
            $abandonPlanet->setImageName('hydra_planet.png');
        }

        $em->flush();

        if($user->getColPlanets() == 0) {
            $hydra = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);
            foreach ($user->getFleets() as $fleet) {
                if($fleet->getFleetList()) {
                    $fleet->getFleetList()->removeFleet($fleet);
                    $fleet->setFleetList(null);
                }
                $fleet->setUser($hydra);
                $fleet->setName('Incursion H');
                $fleet->setAttack(true);
            }

            $em->flush();

            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet == $abandonPlanet) {
            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($user);
        }

        return $this->redirectToRoute('planet', ['usePlanet' => $usePlanet->getId()]);
    }
}