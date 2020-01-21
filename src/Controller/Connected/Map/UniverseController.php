<?php

namespace App\Controller\Connected\Map;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;

class UniverseController extends AbstractController
{
    /**
     * @Route("/univers/", name="universe_unconnected")
     * @Route("/univers/{usePlanet}", name="universe", requirements={"usePlanet"="\d+"})
     */
    public function universeAction(Planet $usePlanet = NULL)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet && $usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $galaxys = $em->getRepository('App:Galaxy')
            ->createQueryBuilder('g')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->join('p.user', 'u')
            ->select('g.position, count(DISTINCT u.id) as users')
            ->groupBy('g.id')
            ->orderBy('g.position', 'ASC')
            ->getQuery()
            ->getResult();

        $doms = $em->getRepository('App:Ally')
            ->createQueryBuilder('a')
            ->join('a.users', 'u')
            ->join('u.planets', 'p')
            ->select('a.id, a.sigle as alliance, count(p) as number')
            ->groupBy('a.id')
            ->orderBy('count(p.id)', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        $zombies = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.planets', 'p')
            ->select('u.id, count(p) as number')
            ->groupBy('u.id')
            ->orderBy('count(p.id)', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        $totalPlanet = $em->getRepository('App:Server')
            ->createQueryBuilder('s')
            ->join('s.galaxys', 'g')
            ->join('g.sectors', 'se')
            ->join('se.planets', 'p')
            ->join('p.user', 'u')
            ->select('count(p) as number')
            ->groupBy('s.id')
            ->getQuery()
            ->getSingleScalarResult();


        if (!$usePlanet) {
            return $this->render('anonymous/universe.html.twig', [
                'galaxys' => $galaxys,
                'doms' => $doms,
                'zombies' => $zombies,
                'totalPlanet' => $totalPlanet
            ]);
        }

        return $this->render('connected/map/universe.html.twig', [
            'galaxys' => $galaxys,
            'usePlanet' => $usePlanet,
            'doms' => $doms,
            'zombies' => $zombies,
            'totalPlanet' => $totalPlanet
        ]);
    }
}