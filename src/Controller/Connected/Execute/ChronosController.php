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
    public function userActivityAction($character, $now, $em)
    {
        if ($character->getLastActivity()) {
            $seconds = ($now->format('U') - ($character->getLastActivity()->format('U')));
        } else {
            $character->setLastActivity($now);
            $em->flush($character);
            $seconds = ($now->format('U') - ($character->getLastActivity()->format('U')));
        }
        return new Response ($seconds);
    }

    /**
     * @param $planet
     * @param $now
     * @param $em
     * @return Response
     */
    public function planetActivityAction($planet, $now, $em)
    {
        if ($planet->getLastActivity()) {
            $seconds = ($now->format('U') - ($planet->getLastActivity()->format('U')));
        } else {
            $planet->setLastActivity($now);
            $em->flush($planet);
            $seconds = ($now->format('U') - ($planet->getLastActivity()->format('U')));
        }
        return new Response ($seconds);
    }
}