<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PlanetService extends AbstractController
{
    public function userRadarAction(ManagerRegistry $doctrine, $sector, $galaxy): Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getMainCommander();
        $ally = $commander->getAlliance();

        if ($ally) {
            $planet = $doctrine->getRepository(Planet::class)
                ->createQueryBuilder('p')
                ->join('p.commander', 'c')
                ->join('c.ally', 'a')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->select('case when p.radar is not null and p.skyRadar is not null then (p.radar + p.skyRadar) when p.radar is not null then p.radar when p.skyRadar is not null then p.skyRadar else 0 end as allRadar')
                ->where('a.id = :ally')
                ->andWhere('s.position = :sPos and g.position = :gPos')
                ->setParameters(['ally' => $ally->getId(), 'sPos' => $sector, 'gPos' => $galaxy])
                ->setMaxResults(1)
                ->orderBy('allRadar', 'DESC')
                ->getQuery()
                ->getOneOrNullResult();
        } else {
            $planet = $doctrine->getRepository(Planet::class)
                ->createQueryBuilder('p')
                ->join('p.sector', 's')
                ->join('s.galaxy', 'g')
                ->select('case when p.radar is not null and p.skyRadar is not null then (p.radar + p.skyRadar) when p.radar is not null then p.radar when p.skyRadar is not null then p.skyRadar else 0 end as allRadar')
                ->where('p.commander = :commander')
                ->andWhere('s.position = :sPos and g.position = :gPos')
                ->setParameters(['commander' => $commander, 'sPos' => $sector, 'gPos' => $galaxy])
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

    public function planetsAttackedAction(ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getMainCommander();
        $ally = $commander->getAlliance();

        $eAlliance = $commander->getAllianceEnnemy();
        $warAlliance = [];
        $x = 0;
        foreach ($eAlliance as $tmp) {
            $warAlliance[$x] = $tmp->getAllianceTag();
            $x++;
        }

        $fAlliance = $commander->getAllianceFriends();
        $friendAlliance = [];
        $x = 0;
        foreach ($fAlliance as $tmp) {
            if($tmp->getAccepted() == 1) {
                $friendAlliance[$x] = $tmp->getAllianceTag();
                $x++;
            }
        }
        if(!$friendAlliance) {
            $friendAlliance = ['impossibleBRU', 'personneICI'];
        }

        if(!$ally) {
            $ally = 'AlliancewedontexistsokBYE';
        }

        $attacker = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->join('f.commander', 'c')
            ->join('f.planet', 'p')
            ->leftJoin('c.ally', 'a')
            ->select('f.id')
            ->where('p.commander = :commander')
            ->andWhere('f.attack = true OR a.tag in (:ally)')
            ->andWhere('f.commander != :commander')
            ->andWhere('f.flightAt is null')
            ->andWhere('c.ally is null OR a.tag not in (:friend)')
            ->andWhere('c.ally is null OR c.ally != :myAlliance')
            ->setParameters(['ally' => $warAlliance, 'commander' => $commander, 'friend' => $friendAlliance, 'myAlliance' => $ally])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($attacker) {
            return new Response (true);
        }

        return new Response (null);
    }

    public function planetAttackedAction(ManagerRegistry $doctrine, $planet): Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getMainCommander();
        $ally = $commander->getAlliance();

        $eAlliance = $commander->getAllianceEnnemy();
        $warAlliance = [];
        $x = 0;
        foreach ($eAlliance as $tmp) {
            $warAlliance[$x] = $tmp->getAllianceTag();
            $x++;
        }

        $fAlliance = $commander->getAllianceFriends();
        $friendAlliance = [];
        $x = 0;
        foreach ($fAlliance as $tmp) {
            if($tmp->getAccepted() == 1) {
                $friendAlliance[$x] = $tmp->getAllianceTag();
                $x++;
            }
        }
        if(!$friendAlliance) {
            $friendAlliance = ['impossibleBRU', 'personneICI'];
        }

        if(!$ally) {
            $ally = 'AlliancewedontexistsokBYE';
        }

        $attacker = $doctrine->getRepository(Fleet::class)
            ->createQueryBuilder('f')
            ->join('f.commander', 'c')
            ->leftJoin('c.ally', 'a')
            ->select('f.id')
            ->where('f.planet = :planet')
            ->andWhere('f.attack = true OR a.tag in (:ally)')
            ->andWhere('f.commander != :commander')
            ->andWhere('f.flightAt is null')
            ->andWhere('c.ally is null OR a.tag not in (:friend)')
            ->andWhere('c.ally is null OR c.ally != :myAlliance')
            ->setParameters(['planet' => $planet, 'ally' => $warAlliance, 'commander' => $commander, 'friend' => $friendAlliance, 'myAlliance' => $ally])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($attacker) {
            return new Response (true);
        }

        return new Response (null);
    }
}