<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ChronosController extends AbstractController
{
    public function userActivityAction($now, $em)
    {
        $user = $this->getUser();
        if ($user->getLastActivity()) {
            $seconds = ($now->format('U') - ($user->getLastActivity()->format('U')));
        } else {
            $user->setLastActivity($now);
            $em->flush($user);
            $seconds = ($now->format('U') - ($user->getLastActivity()->format('U')));
        }
        return new Response ($seconds);
    }

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