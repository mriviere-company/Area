<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PlanetService extends AbstractController
{
    public function userRadarAction($sector, $gal)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $ally = $user->getAlly();

        if ($ally) {
            $planet = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.user', 'u')
                ->join('u.ally', 'a')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->select('case when p.radar is not null and p.skyRadar is not null then (p.radar + p.skyRadar) when p.radar is not null then p.radar when p.skyRadar is not null then p.skyRadar else 0 end as allRadar')
                ->where('a.id = :ally')
                ->andWhere('s.position = :sPos and g.position = :gPos')
                ->setParameters(['ally' => $ally->getId(), 'sPos' => $sector, 'gPos' => $gal])
                ->setMaxResults(1)
                ->orderBy('allRadar', 'DESC')
                ->getQuery()
                ->getOneOrNullResult();
        } else {
            $planet = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->select('case when p.radar is not null and p.skyRadar is not null then (p.radar + p.skyRadar) when p.radar is not null then p.radar when p.skyRadar is not null then p.skyRadar else 0 end as allRadar')
                ->where('p.user = :user')
                ->andWhere('s.position = :sPos and g.position = :gPos')
                ->setParameters(['user' => $user, 'sPos' => $sector, 'gPos' => $gal])
                ->setMaxResults(1)
                ->orderBy('allRadar', 'DESC')
                ->getQuery()
                ->getOneOrNullResult();
        }

        if ($planet['allRadar']) {
            return new Response ($planet['allRadar']);
        }

        return new Response (null);
    }

    public function planetsAttackedAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $ally = $user->getAlly();

        $eAlly = $user->getAllyEnnemy();
        $warAlly = [];
        $x = 0;
        foreach ($eAlly as $tmp) {
            $warAlly[$x] = $tmp->getAllyTag();
            $x++;
        }

        $fAlly = $user->getAllyFriends();
        $friendAlly = [];
        $x = 0;
        foreach ($fAlly as $tmp) {
            if($tmp->getAccepted() == 1) {
                $friendAlly[$x] = $tmp->getAllyTag();
                $x++;
            }
        }
        if(!$friendAlly) {
            $friendAlly = ['impossibleBRU', 'personneICI'];
        }

        if(!$ally) {
            $ally = 'AllywedontexistsokBYE';
        }

        $attacker = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.user', 'u')
            ->join('f.planet', 'p')
            ->leftJoin('u.ally', 'a')
            ->select('f.id')
            ->where('p.user = :user')
            ->andWhere('f.attack = true OR a.sigle in (:ally)')
            ->andWhere('f.user != :user')
            ->andWhere('f.flightTime is null')
            ->andWhere('u.ally is null OR a.sigle not in (:friend)')
            ->andWhere('u.ally is null OR u.ally != :myAlly')
            ->setParameters(['ally' => $warAlly, 'user' => $user, 'friend' => $friendAlly, 'myAlly' => $ally])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($attacker) {
            return new Response (true);
        }

        return new Response (false);
    }

    public function planetAttackedAction($planet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $ally = $user->getAlly();

        $eAlly = $user->getAllyEnnemy();
        $warAlly = [];
        $x = 0;
        foreach ($eAlly as $tmp) {
            $warAlly[$x] = $tmp->getAllyTag();
            $x++;
        }

        $fAlly = $user->getAllyFriends();
        $friendAlly = [];
        $x = 0;
        foreach ($fAlly as $tmp) {
            if($tmp->getAccepted() == 1) {
                $friendAlly[$x] = $tmp->getAllyTag();
                $x++;
            }
        }
        if(!$friendAlly) {
            $friendAlly = ['impossibleBRU', 'personneICI'];
        }

        if(!$ally) {
            $ally = 'AllywedontexistsokBYE';
        }

        $attacker = $em->getRepository('App:Fleet')
            ->createQueryBuilder('f')
            ->join('f.user', 'u')
            ->leftJoin('u.ally', 'a')
            ->select('f.id')
            ->where('f.planet = :planet')
            ->andWhere('f.attack = true OR a.sigle in (:ally)')
            ->andWhere('f.user != :user')
            ->andWhere('f.flightTime is null')
            ->andWhere('u.ally is null OR a.sigle not in (:friend)')
            ->andWhere('u.ally is null OR u.ally != :myAlly')
            ->setParameters(['planet' => $planet, 'ally' => $warAlly, 'user' => $user, 'friend' => $friendAlly, 'myAlly' => $ally])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($attacker) {
            return new Response (true);
        }

        return new Response (false);
    }
}