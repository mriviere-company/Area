<?php

namespace App\Controller\Connected;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use DateTime;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class AllPlanetsController extends AbstractController
{
    public function allPlanetsAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $planetSoldiers = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->where('p.soldierAt < :now')
            ->andWhere('p.commander = :commander')
            ->setParameters(['now' => $now, 'commander' => $commander])
            ->getQuery()
            ->getResult();

        if ($planetSoldiers) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::soldiersAction', [
                'planetSoldiers'  => $planetSoldiers,
                'em'  => $em
            ]);
        }

        $planetTanks = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->where('p.tankAt < :now')
            ->andWhere('p.commander = :commander')
            ->setParameters(['now' => $now, 'commander' => $commander])
            ->getQuery()
            ->getResult();

        if ($planetTanks) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::tanksAction', [
                'planetTanks'  => $planetTanks,
                'em'  => $em
            ]);
        }

        $planetScientists = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->where('p.scientistAt < :now')
            ->andWhere('p.commander = :commander')
            ->setParameters(['now' => $now, 'commander' => $commander])
            ->getQuery()
            ->getResult();

        if ($planetScientists) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::scientistsAction', [
                'planetScientists'  => $planetScientists,
                'em'  => $em
            ]);
        }

        $products = $doctrine->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->join('p.planet', 'pp')
            ->where('p.productAt < :now')
            ->andWhere('pp.commander = :commander')
            ->setParameters(['now' => $now, 'commander' => $commander])
            ->getQuery()
            ->getResult();

        if ($products) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::productsAction', [
                'products'  => $products,
                'em' => $em
            ]);
        }

        $seconds = $this->forward('App\Controller\Connected\Execute\ChronosController::userActivityAction', [
            'commander' => $commander,
            'now'  => $now,
            'em' => $em]);

        if ($seconds->getContent() >= 60) {
            $this->forward('App\Controller\Connected\Execute\PlanetsGenController::planetsGenAction', [
                'commander' => $commander,
                'seconds' => $seconds->getContent(),
                'now'  => $now,
                'em' => $em]);
        }

        if ($commander->getOrderPlanet() == 'alpha') {
            $crit = 'p.name';
        } elseif ($commander->getOrderPlanet() == 'colo') {
            $crit = 'p.nbColo';
        } else {
            $crit = 'p.id';
        }
        $order = 'ASC';

        $allPlanets = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->select('p.id, p.position, p.name, p.caserne, p.wtCdr, p.nbCdr, p.signature, p.bunker, p.centerSearch, p. lightUsine, p.uranium, p.empty, s.position as sector, g.position as galaxy, s.id as idSector, g.id as idGalaxy, p.construct, p.ground, p.groundPlace, p.sky, p.skyPlace, p.imageName, p.radarAt, p.brouilleurAt, p.moon')
            ->where('p.commander = :commander')
            ->setParameters(['commander' => $commander])
            ->orderBy($crit, $order)
            ->getQuery()
            ->getResult();

        $em->clear();
        return $this->render('menu/_right_planet.html.twig', ['allPlanets' => $allPlanets, 'usePlanet' => $usePlanet, 'commander' => $commander]);
    }
}