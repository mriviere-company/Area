<?php

namespace App\Controller\Connected;

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
    public function allPlanetsAction(Planet $usePlanet, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $planetSoldiers = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.soldierAt < :now')
            ->andWhere('p.user = :user')
            ->setParameters(['now' => $now, 'user' => $user])
            ->getQuery()
            ->getResult();

        if ($planetSoldiers) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::soldiersAction', [
                'planetSoldiers'  => $planetSoldiers,
                'em'  => $em
            ]);
        }

        $planetTanks = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.tankAt < :now')
            ->andWhere('p.user = :user')
            ->setParameters(['now' => $now, 'user' => $user])
            ->getQuery()
            ->getResult();

        if ($planetTanks) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::tanksAction', [
                'planetTanks'  => $planetTanks,
                'em'  => $em
            ]);
        }

        $planetScientists = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->where('p.scientistAt < :now')
            ->andWhere('p.user = :user')
            ->setParameters(['now' => $now, 'user' => $user])
            ->getQuery()
            ->getResult();

        if ($planetScientists) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::scientistsAction', [
                'planetScientists'  => $planetScientists,
                'em'  => $em
            ]);
        }

        $products = $em->getRepository('App:Product')
            ->createQueryBuilder('p')
            ->join('p.planet', 'pp')
            ->where('p.productAt < :now')
            ->andWhere('pp.user = :user')
            ->setParameters(['now' => $now, 'user' => $user])
            ->getQuery()
            ->getResult();

        if ($products) {
            $this->forward('App\Controller\Connected\Execute\PlanetsController::productsAction', [
                'products'  => $products,
                'em' => $em
            ]);
        }

        $seconds = $this->forward('App\Controller\Connected\Execute\ChronosController::userActivityAction', [
            'now'  => $now,
            'em' => $em]);

        if ($seconds->getContent() >= 60) {
            $this->forward('App\Controller\Connected\Execute\PlanetsGenController::planetsGenAction', [
                'seconds' => $seconds->getContent(),
                'now'  => $now,
                'em' => $em]);
        }

        if ($user->getOrderPlanet() == 'alpha') {
            $crit = 'p.name';
        } elseif ($user->getOrderPlanet() == 'colo') {
            $crit = 'p.nbColo';
        } else {
            $crit = 'p.id';
        }
        $order = 'ASC';

        $allPlanets = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->select('p.id, p.position, p.name, p.caserne, p.wtCdr, p.nbCdr, p.signature, p.bunker, p.centerSearch, p. lightUsine, p.uranium, p.empty, s.position as sector, g.position as galaxy, s.id as idSector, g.id as idGalaxy, p.construct, p.ground, p.groundPlace, p.sky, p.skyPlace, p.imageName, p.radarAt, p.brouilleurAt, p.moon')
            ->where('p.user = :user')
            ->setParameters(['user' => $user])
            ->orderBy($crit, $order)
            ->getQuery()
            ->getResult();

        return $this->render('menu/_right_planet.html.twig', ['allPlanets' => $allPlanets, 'usePlanet' => $usePlanet]);
    }
}