<?php

namespace App\Controller\Connected;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function rankAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $server = $usePlanet->getSector()->getGalaxy()->getServer();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $allAlliances = $doctrine->getRepository(Alliance::class)
            ->createQueryBuilder('a')
            ->join('a.commanders', 'c')
            ->join('c.planets', 'p')
            ->join('c.rank', 'r')
            ->select('a.id, a.tag, a.imageName, a.name, count(DISTINCT c.id) as commanders, count(DISTINCT p) as planets, sum(DISTINCT r.point) as point, sum(DISTINCT r.oldPoint) as oldPoint, a.maxMembers, a.createdAt, a.politic')
            ->groupBy('a.id')
            ->where('a.rank is not null')
            ->andWhere('c.server =:server')
            ->setParameters(['server' => $server])
            ->orderBy('point', 'DESC')
            ->getQuery()
            ->getResult();

        if ($commander->getAlliance()) {
            $allyPoints = $doctrine->getRepository(Stats::class)
                ->createQueryBuilder('s')
                ->join('s.commander', 'c')
                ->select('count(s) as numbers, sum(DISTINCT s.points) as ally, s.date')
                ->groupBy('s.date')
                ->where('c.ally = :ally')
                ->andWhere('c.server =:server')
                ->setParameters(['ally' => $commander->getAlliance(), 'server' => $server])
                ->getQuery()
                ->getResult();

            $otherPoints = $doctrine->getRepository(Stats::class)
                ->createQueryBuilder('s')
                ->join('s.commander', 'c')
                ->select('count(s) as numbers, sum(DISTINCT s.points) as allAlliance')
                ->groupBy('s.date')
                ->where('c.ally != :ally')
                ->andWhere('c.bot = false')
                ->andWhere('c.server =:server')
                ->setParameters(['ally' => $commander->getAlliance(), 'server' => $server])
                ->getQuery()
                ->getResult();
        } else {
            $allyPoints = null;
            $otherPoints = null;
        }

        return $this->render('connected/ally/rank.html.twig', [
            'usePlanet' => $usePlanet,
            'allAlliances' => $allAlliances,
            'allyPoints' => $allyPoints,
            'otherPoints' => $otherPoints
        ]);
    }

    /**
     * @Route("/classement-joueurs/{usePlanet}", name="rank_user", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function rankUserAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $server = $usePlanet->getSector()->getGalaxy()->getServer();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $allCommanders = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->join('c.planets', 'p')
            ->select('a.id as alliance, a.tag as tag, count(DISTINCT p) as planets, c.id, c.imageName, c.username, r.point as point, r.oldPoint as oldPoint, r.position as position, r.oldPosition as oldPosition, r.warPoint as warPoint, c.createdAt, a.politic as politic, c.bot')
            ->leftJoin('c.rank', 'r')
            ->leftJoin('c.ally', 'a')
            ->groupBy('c.id')
            ->where('c.rank is not null')
            ->andWhere('c.id != 1')
            ->andWhere('c.bot = false')
            ->andWhere('r.point > 200')
            ->andWhere('c.server =:server')
            ->setParameters(['server' => $server])
            ->orderBy('point', 'DESC')
            ->getQuery()
            ->setMaxResults(100)
            ->getResult();

        $nbrPlayers = $doctrine->getRepository(Rank::class)
            ->createQueryBuilder('r')
            ->join('r.commander', 'c')
            ->select('count(r.id) as nbrPlayer')
            ->where('c.bot = false')
            ->andWhere('c.server =:server')
            ->setParameters(['server' => $server])
            ->getQuery()
            ->getSingleScalarResult();

        $otherPoints = $doctrine->getRepository(Stats::class)
            ->createQueryBuilder('s')
            ->join('s.commander', 'c')
            ->select('count(s) as numbers, sum(DISTINCT s.pdg) as allPdg, sum(DISTINCT s.points) as allPoint')
            ->andWhere('c.server =:server')
            ->setParameters(['server' => $server])
            ->groupBy('s.date')
            ->getQuery()
            ->getResult();

        return $this->render('connected/rank.html.twig', [
            'usePlanet' => $usePlanet,
            'allCommanders' => $allCommanders,
            'nbrPlayers' => $nbrPlayers - 100,
            'otherPoints' => $otherPoints
        ]);
    }
}