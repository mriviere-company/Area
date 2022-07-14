<?php

namespace App\Controller\Connected\Map;

use App\Entity\Galaxy;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/galaxie/{galaxy}/{usePlanet}", name="galaxy", requirements={"id"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Galaxy $galaxy
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function galaxyAction(ManagerRegistry $doctrine, Request $request, Galaxy $galaxy, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $server = $usePlanet->getSector()->getGalaxy()->getServer();
        $character = $user->getCharacter($server);
        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $form_navigate = $this->createForm(
            NavigateType::class,
            null,
            ["galaxy" => $galaxy->getPosition(), "sector" => 0]
        );
        $form_navigate->handleRequest($request);

        if ($form_navigate->isSubmitted() && $form_navigate->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if ($form_navigate->get('sector')->getData() && $form_navigate->get('galaxy')->getData()) {
                return $this->redirectToRoute(
                    'map',
                    [
                        'sector' => $form_navigate->get('sector')->getData(),
                        'galaxy' => $form_navigate->get('galaxy')->getData(),
                        'usePlanet' => $usePlanet->getId()
                    ]
                );
            }

            return $this->redirectToRoute(
                'galaxy',
                [
                    'id' => $form_navigate->get('galaxy')->getData(),
                    'usePlanet' => $usePlanet->getId()
                ]
            );
        }

        $planets = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->select(
                'p.merchant, p.cdr, p.empty, s.position as sector, s.id as sectorId, g.id as galaxy, c.username as username, a.sigle as alliance, s.destroy as destroy, c.zombie as zombie'
            )
            ->leftJoin('p.character', 'c')
            ->leftJoin('c.ally', 'a')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->where('g.position = :id')
            ->andWhere('g.server = :server')
            ->setParameters(['server' => $server, 'id' => $galaxy->getPosition()])
            ->orderBy('p.id', 'ASC')
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
            ->where('g.id = :galaxy')
            ->setParameters(['galaxy' => $galaxy->getId()])
            ->orderBy('count(p.id)', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();


        $totalPlanet = $em->getRepository('App:Galaxy')
            ->createQueryBuilder('g')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->join('p.character', 'c')
            ->join('c.ally', 'a')
            ->select('count(p) as number')
            ->groupBy('g.id')
            ->where('g.id = :galaxy')
            ->setParameters(['galaxy' => $galaxy->getId()])
            ->getQuery()
            ->getOneOrNullResult();

        if ($totalPlanet) {
            $totalPlanet = $em->getRepository('App:Galaxy')
                ->createQueryBuilder('g')
                ->join('g.sectors', 's')
                ->join('s.planets', 'p')
                ->join('p.character', 'c')
                ->join('c.ally', 'a')
                ->select('count(p) as number')
                ->groupBy('g.id')
                ->where('g.id = :galaxy')
                ->setParameters(['galaxy' => $galaxy->getId()])
                ->getQuery()
                ->getSingleScalarResult();
        } else {
            $totalPlanet = 1;
        }

        return $this->render(
            'connected/map/galaxy.html.twig',
            [
                'form_navigate' => $form_navigate->createView(),
                'planets' => $planets,
                'usePlanet' => $usePlanet,
                'doms' => $doms,
                'totalPlanet' => $totalPlanet
            ]
        );
    }
}