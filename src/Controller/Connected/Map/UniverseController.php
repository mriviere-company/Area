<?php

namespace App\Controller\Connected\Map;

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
     * @param Planet|null $usePlanet
     * @return RedirectResponse|Response
     */
    public function universeAction(Planet $usePlanet = null)
    {
        $em = $this->getDoctrine()->getManager();
        $server = $usePlanet ? $usePlanet->getSector()->getGalaxy()->getServer() : $planet = $em->getRepository(
            'App:Server'
        )->createQueryBuilder('s')->getQuery()->setMaxresults(1)->getOneOrNullResult();

        $galaxys = $em->getRepository('App:Galaxy')
            ->createQueryBuilder('g')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->join('p.character', 'c')
            ->select('g.id, g.position, count(DISTINCT c.id) as characters')
            ->groupBy('g.id')
            ->orderBy('g.position', 'ASC')
            ->where('g.server = :server')
            ->setParameters(['server' => $server])
            ->getQuery()
            ->getResult();

        $doms = $em->getRepository('App:Ally')
            ->createQueryBuilder('a')
            ->join('a.characters', 'c')
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

        $totalPlanet = $em->getRepository('App:Server')
            ->createQueryBuilder('s')
            ->join('s.galaxys', 'g')
            ->join('g.sectors', 'se')
            ->join('se.planets', 'p')
            ->join('p.character', 'c')
            ->join('c.ally', 'a')
            ->select('count(p) as number')
            ->where('g.server = :server')
            ->setParameters(['server' => $server])
            ->groupBy('s.id')
            ->getQuery()
            ->getOneOrNullResult();

        if ($totalPlanet) {
            $totalPlanet = $em->getRepository('App:Server')
                ->createQueryBuilder('s')
                ->join('s.galaxys', 'g')
                ->join('g.sectors', 'se')
                ->join('se.planets', 'p')
                ->join('p.character', 'c')
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