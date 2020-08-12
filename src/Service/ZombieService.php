<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

class ZombieService extends AbstractController
{
    public function zombieIndicatorAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();

            $mission = $em->getRepository('App:Mission')
                ->createQueryBuilder('m')
                ->select('m.id')
                ->where('m.user = :user')
                ->andWhere('m.missionAt < :now')
                ->andWhere('m.type <= :level')
                ->setParameters(['user' => $user, 'now' => $now, 'level' => $user->getLevel()])
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

        if ($mission) {
            return new Response (true);
        }

        return new Response (false);
    }
}