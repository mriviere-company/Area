<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

class ZombieService extends AbstractController
{
    public function zombieIndicatorAction(ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getMainCommander();
        $now = new DateTime();

            $mission = $doctrine->getRepository(Mission::class)
                ->createQueryBuilder('m')
                ->select('m.id')
                ->where('m.commander = :commander')
                ->andWhere('m.missionAt < :now')
                ->andWhere('m.type <= :level')
                ->setParameters(['commander' => $commander, 'now' => $now, 'level' => $commander->getLevel()])
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

        if ($mission) {
            return new Response (true);
        }

        return new Response (false);
    }
}