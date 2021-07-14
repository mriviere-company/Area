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
     * @param $character
     * @param $now
     * @param $em
     * @return Response
     */
    public function userActivityAction($character, $now, $em): Response
    {
        if (!$character->getLastActivity()) {
            $character->setLastActivity($now);
            $em->flush($character);
        }
        $seconds = ($now->format('U') - ($character->getLastActivity()->format('U')));

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
        if (!$planet->getLastActivity()) {
            $planet->setLastActivity($now);
            $em->flush($planet);
        }
        $seconds = ($now->format('U') - ($planet->getLastActivity()->format('U')));

        return new Response ($seconds);
    }
}