<?php

namespace App\Controller\Security;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/administration", name="administration")
     * @Route("/administration/", name="administration_slash")
     */
    public function AdministrationAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($user->getRoles()[0] == 'ROLE_MODO' || $user->getRoles()[0] == 'ROLE_ADMIN') {

            $servers = $em->getRepository('App:Server')
                ->createQueryBuilder('s')
                ->select('s.id, s.open, s.pvp')
                ->groupBy('s.id')
                ->orderBy('s.id', 'ASC')
                ->getQuery()
                ->getResult();

            $galaxys = $em->getRepository('App:Galaxy')
                ->createQueryBuilder('g')
                ->join('g.server', 'ss')
                ->join('g.sectors', 's')
                ->join('s.planets', 'p')
                ->leftJoin('p.user', 'u')
                ->select('g.id, g.position, count(DISTINCT u.id) as users, ss.id as server')
                ->groupBy('g.id')
                ->orderBy('g.position', 'ASC')
                ->getQuery()
                ->getResult();

            return $this->render('admin/administration.html.twig', [
                'servers' => $servers,
                'galaxys' => $galaxys
            ]);
        }
        return $this->redirectToRoute('logout');
    }
}