<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ChronosController
 * @package App\Controller\Connected\Execute
 */
class ChronosController extends AbstractController
{
    /**
     * @param $commander
     * @param $now
     * @param $em
     * @return Response
     */
    public function userActivityAction($commander, $now, $em): Response
    {
        if (!$commander->getActivityAt()) {
            $commander->setActivityAt($now);
            $em->flush($commander);
        }
        $seconds = ($now->format('U') - ($commander->getActivityAt()->format('U')));

        return new Response ($seconds);
    }

    /**
     * @param $planet
     * @param $now
     * @param $em
     * @return Response
     */
    public function planetActivityAction($planet, $now, $em): Response
    {
        if (!$planet->getActivityAt()) {
            $planet->setActivityAt($now);
            $em->flush($planet);
        }
        $seconds = ($now->format('U') - ($planet->getActivityAt()->format('U')));

        return new Response ($seconds);
    }
}