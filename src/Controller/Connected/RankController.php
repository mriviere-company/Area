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
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $allAllys = $em->getRepository('App:Ally')
            ->createQueryBuilder('a')
            ->join('a.characters', 'c')
            ->join('c.planets', 'p')
            ->join('c.rank', 'r')
            ->select('a.id, a.sigle, a.imageName, a.name, count(DISTINCT c.id) as characters, count(DISTINCT p) as planets, sum(DISTINCT r.point) as point, sum(DISTINCT r.oldPoint) as oldPoint, a.maxMembers, a.createdAt, a.politic')
            ->groupBy('a.id')
            ->where('a.rank is not null')
            ->andWhere('c.server =:server')
            ->setParameters(['server' => $server])
            ->orderBy('point', 'DESC')
            ->getQuery()
            ->getResult();

        if ($character->getAlly()) {
            $allyPoints = $em->getRepository('App:Stats')
                ->createQueryBuilder('s')
                ->join('s.character', 'c')
                ->select('count(s) as numbers, sum(DISTINCT s.points) as ally, s.date')
                ->groupBy('s.date')
                ->where('c.ally = :ally')
                ->andWhere('c.server =:server')
                ->setParameters(['ally' => $character->getAlly(), 'server' => $server])
                ->getQuery()
                ->getResult();

            $otherPoints = $em->getRepository('App:Stats')
                ->createQueryBuilder('s')
                ->join('s.character', 'c')
                ->select('count(s) as numbers, sum(DISTINCT s.points) as allAlly')
                ->groupBy('s.date')
                ->where('c.ally != :ally')
                ->andWhere('c.bot = false')
                ->andWhere('c.server =:server')
                ->setParameters(['ally' => $character->getAlly(), 'server' => $server])
                ->getQuery()
                ->getResult();
        } else {
            $allyPoints = null;
            $otherPoints = null;
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
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $allCharacters = $em->getRepository('App:Character')
            ->createQueryBuilder('c')
            ->join('c.planets', 'p')
            ->select('a.id as alliance, a.sigle as sigle, count(DISTINCT p) as planets, c.id, c.imageName, c.username, r.point as point, r.oldPoint as oldPoint, r.position as position, r.oldPosition as oldPosition, r.warPoint as warPoint, c.createdAt, a.politic as politic, c.bot')
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

        $nbrPlayers = $em->getRepository('App:Rank')
            ->createQueryBuilder('r')
            ->join('r.character', 'c')
            ->select('count(r.id) as nbrPlayer')
            ->where('c.bot = false')
            ->andWhere('c.server =:server')
            ->setParameters(['server' => $server])
            ->getQuery()
            ->getSingleScalarResult();

        $otherPoints = $em->getRepository('App:Stats')
            ->createQueryBuilder('s')
            ->join('s.character', 'c')
            ->select('count(s) as numbers, sum(DISTINCT s.pdg) as allPdg, sum(DISTINCT s.points) as allPoint')
            ->andWhere('c.server =:server')
            ->setParameters(['server' => $server])
            ->groupBy('s.date')
            ->getQuery()
            ->getResult();

        return $this->render('connected/rank.html.twig', [
            'usePlanet' => $usePlanet,
            'allCharacters' => $allCharacters,
            'nbrPlayers' => $nbrPlayers - 100,
            'otherPoints' => $otherPoints
        ]);
    }
}