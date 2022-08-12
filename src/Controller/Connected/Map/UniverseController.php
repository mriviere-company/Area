<?php

namespace App\Controller\Connected\Map;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;

class UniverseController extends AbstractController
{
    /**
     * @Route("/univers/", name="universe_unconnected")
     * @Route("/univers/{usePlanet}", name="universe", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet|null $usePlanet
     * @return RedirectResponse|Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function universeAction(ManagerRegistry $doctrine, Planet $usePlanet = null): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $server = $usePlanet ? $usePlanet->getSector()->getGalaxy()->getServer() : $planet = $doctrine->getRepository(
            Server::class
        )->createQueryBuilder('s')->getQuery()->setMaxresults(1)->getOneOrNullResult();

        $galaxys = $doctrine->getRepository(Galaxy::class)
            ->createQueryBuilder('g')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->join('p.commander', 'c')
            ->select('g.id, g.position, count(DISTINCT c.id) as commanders')
            ->groupBy('g.id')
            ->orderBy('g.position', 'ASC')
            ->where('g.server = :server')
            ->setParameters(['server' => $server])
            ->getQuery()
            ->getResult();

        $doms = $doctrine->getRepository(Ally::class)
            ->createQueryBuilder('a')
            ->join('a.commanders', 'c')
            ->join('c.planets', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->select('a.id, a.sigle as alliance, count(p) as number')
            ->groupBy('a.id')
            ->orderBy('count(p.id)', 'DESC')
            ->where('g.server = :server')
            ->setParameters(['server' => $server])
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        $totalPlanet = $doctrine->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->join('s.galaxys', 'g')
            ->join('g.sectors', 'se')
            ->join('se.planets', 'p')
            ->join('p.commander', 'c')
            ->join('c.ally', 'a')
            ->select('count(p) as number')
            ->where('g.server = :server')
            ->setParameters(['server' => $server])
            ->groupBy('s.id')
            ->getQuery()
            ->getOneOrNullResult();

        if ($totalPlanet) {
            $totalPlanet = $doctrine->getRepository(Server::class)
                ->createQueryBuilder('s')
                ->join('s.galaxys', 'g')
                ->join('g.sectors', 'se')
                ->join('se.planets', 'p')
                ->join('p.commander', 'c')
                ->join('c.ally', 'a')
                ->select('count(p) as number')
                ->where('g.server = :server')
                ->setParameters(['server' => $server])
                ->groupBy('s.id')
                ->getQuery()
                ->getSingleScalarResult();
        } else {
            $totalPlanet = 1;
        }


        if (!$usePlanet) {
            return $this->render(
                'anonymous/universe.html.twig',
                [
                    'galaxys' => $galaxys,
                    'doms' => $doms,
                    'totalPlanet' => $totalPlanet
                ]
            );
        }

        return $this->render(
            'connected/map/universe.html.twig',
            [
                'galaxys' => $galaxys,
                'usePlanet' => $usePlanet,
                'doms' => $doms,
                'totalPlanet' => $totalPlanet
            ]
        );
    }
}