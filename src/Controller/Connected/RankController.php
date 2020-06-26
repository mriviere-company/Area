<?php

namespace App\Controller\Connected;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class RankController extends AbstractController
{
    /**
     * @Route("/classement-alliance/{usePlanet}", name="rank_ally", requirements={"usePlanet"="\d+"})
     */
    public function rankAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $allAllys = $em->getRepository('App:Ally')
            ->createQueryBuilder('a')
            ->join('a.users', 'u')
            ->join('u.planets', 'p')
            ->join('u.rank', 'r')
            ->select('a.id, a.sigle, a.imageName, a.name, count(DISTINCT u.id) as users, count(DISTINCT p) as planets, sum(DISTINCT r.point) as point, sum(DISTINCT r.oldPoint) as oldPoint, a.maxMembers, a.createdAt, a.politic')
            ->groupBy('a.id')
            ->where('a.rank is not null')
            ->orderBy('point', 'DESC')
            ->getQuery()
            ->getResult();

        if ($user->getAlly()) {
            $allyPoints = $em->getRepository('App:Stats')
                ->createQueryBuilder('s')
                ->join('s.user', 'u')
                ->select('count(s) as numbers, sum(DISTINCT s.points) as ally, s.date')
                ->groupBy('s.date')
                ->where('u.ally = :ally')
                ->setParameters(['ally' => $user->getAlly()])
                ->getQuery()
                ->getResult();

            $otherPoints = $em->getRepository('App:Stats')
                ->createQueryBuilder('s')
                ->join('s.user', 'u')
                ->select('count(s) as numbers, sum(DISTINCT s.points) as allAlly')
                ->groupBy('s.date')
                ->where('u.ally != :ally')
                ->andWhere('u.bot = false')
                ->setParameters(['ally' => $user->getAlly()])
                ->getQuery()
                ->getResult();
        } else {
            $allyPoints = NULL;
            $otherPoints = NULL;
        }

        return $this->render('connected/ally/rank.html.twig', [
            'usePlanet' => $usePlanet,
            'allAllys' => $allAllys,
            'allyPoints' => $allyPoints,
            'otherPoints' => $otherPoints
        ]);
    }

    /**
     * @Route("/classement-joueurs/{usePlanet}", name="rank_user", requirements={"usePlanet"="\d+"})
     */
    public function rankUserAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $users = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.planets', 'p')
            ->select('a.id as alliance, a.sigle as sigle, count(DISTINCT p) as planets, u.id, u.imageName, u.username, r.point as point, r.oldPoint as oldPoint, r.position as position, r.oldPosition as oldPosition, r.warPoint as warPoint, u.createdAt, a.politic as politic, u.bot')
            ->leftJoin('u.rank', 'r')
            ->leftJoin('u.ally', 'a')
            ->groupBy('u.id')
            ->where('u.rank is not null')
            ->andWhere('u.id != 1')
            ->andWhere('u.bot = false')
            ->andWhere('r.point > 200')
            ->orderBy('point', 'DESC')
            ->getQuery()
            ->setMaxResults(100)
            ->getResult();

        $nbrPlayers = $em->getRepository('App:Rank')
            ->createQueryBuilder('r')
            ->join('r.user', 'u')
            ->select('count(r.id) as nbrPlayer')
            ->where('u.bot = false')
            ->getQuery()
            ->getSingleScalarResult();

        $otherPoints = $em->getRepository('App:Stats')
            ->createQueryBuilder('s')
            ->select('count(s) as numbers, sum(DISTINCT s.pdg) as allPdg, sum(DISTINCT s.points) as allPoint')
            ->groupBy('s.date')
            ->getQuery()
            ->getResult();

        return $this->render('connected/rank.html.twig', [
            'usePlanet' => $usePlanet,
            'users' => $users,
            'nbrPlayers' => $nbrPlayers - 100,
            'otherPoints' => $otherPoints
        ]);
    }

//    /**
//     * @Route("/classement-joueurs/{usePlanet}", name="rank_user", requirements={"usePlanet"="\d+"})
//     */
//    public function rankUserAction(Planet $usePlanet)
//    {
//        $em = $this->getDoctrine()->getManager();
//        $user = $this->getUser();
//        if ($usePlanet->getUser() != $user) {
//          return $this->redirectToRoute('home');
//        }
//
//        $users = $em->getRepository('App:User')
//            ->createQueryBuilder('u')
//            ->leftJoin('u.rank', 'r')
//            ->where('u.rank is not null')
//            ->andWhere('u.id != 1')
//            ->orderBy('r.point', 'DESC')
//            ->getQuery()
//            ->getResult();
//
//        return $this->render('connected/rank.html.twig', [
//            'usePlanet' => $usePlanet,
//            'users' => $users,
//        ]);
//    }
}