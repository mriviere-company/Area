<?php

namespace App\Controller\Connected\Map;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\NavigateType;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class GalaxyController extends AbstractController
{
    /**
     * @Route("/galaxie/{id}/{usePlanet}", name="galaxy", requirements={"id"="\d+", "usePlanet"="\d+"})
     */
    public function galaxyAction(Request $request, $id, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $form_navigate = $this->createForm(NavigateType::class, null, ["galaxy" => $id, "sector" => 0]);
        $form_navigate->handleRequest($request);

        if ($form_navigate->isSubmitted() && $form_navigate->isValid()) {
            if ($form_navigate->get('sector')->getData() && $form_navigate->get('galaxy')->getData()) {
                return $this->redirectToRoute('map', ['sector' => $form_navigate->get('sector')->getData(), 'gal' => $form_navigate->get('galaxy')->getData(), 'usePlanet' => $usePlanet->getId()]);
            }
            return $this->redirectToRoute('galaxy', ['id' => $form_navigate->get('galaxy')->getData(), 'usePlanet' => $usePlanet->getId()]);
        }

        $planets = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->select('p.merchant, p.cdr, p.empty, s.position as sector, g.position as galaxy, u.username as username, a.sigle as alliance, s.destroy as destroy, u.zombie as zombie')
            ->leftJoin('p.user', 'u')
            ->leftJoin('u.ally', 'a')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->where('g.position = :id')
            ->setParameter('id', $id)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();

        $doms = $em->getRepository('App:Ally')
            ->createQueryBuilder('a')
            ->join('a.users', 'u')
            ->join('u.planets', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->select('a.id, a.sigle as alliance, count(p) as number')
            ->groupBy('a.id')
            ->where('g.position = :id')
            ->setParameter('id', $id)
            ->orderBy('count(p.id)', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        $zombies = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.planets', 'p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->select('u.id, count(p) as number')
            ->groupBy('u.id')
            ->where('g.position = :id')
            ->andWhere('u.id = :one')
            ->setParameters(['one' => 1, 'id' => $id])
            ->orderBy('count(p.id)', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();

        $totalPlanet = $em->getRepository('App:Galaxy')
            ->createQueryBuilder('g')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->join('p.user', 'u')
            ->select('count(p) as number')
            ->groupBy('g.id')
            ->where('g.position = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('connected/map/galaxy.html.twig', [
            'form_navigate' => $form_navigate->createView(),
            'planets' => $planets,
            'usePlanet' => $usePlanet,
            'doms' => $doms,
            'zombies' => $zombies,
            'totalPlanet' => $totalPlanet
        ]);
    }
}