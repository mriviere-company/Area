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
        $character = $user->getMainCharacter();
        $now = new DateTime();

            $mission = $em->getRepository('App:Mission')
                ->createQueryBuilder('m')
                ->select('m.id')
                ->where('m.character = :character')
                ->andWhere('m.missionAt < :now')
                ->andWhere('m.type <= :level')
                ->setParameters(['character' => $character, 'now' => $now, 'level' => $character->getLevel()])
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

        if ($mission) {
            return new Response (true);
        }

        return new Response (false);
    }
}