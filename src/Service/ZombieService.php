<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use DateTime;
use DateTimeZone;

class ZombieService extends AbstractController
{
    public function zombieIndicatorAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));

            $mission = $em->getRepository('App:Mission')
                ->createQueryBuilder('m')
                ->join('m.planet', 'p')
                ->select('m.id')
                ->where('p.user = :user')
                ->andWhere('m.missionAt < :now')
                ->setParameters(['user' => $user, 'now' => $now])
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

        if ($mission) {
            return new Response (true);
        }

        return new Response (false);
    }
}