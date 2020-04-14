<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserService extends AbstractController
{
    public function willSeeAction($sector, $gal)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $ally = $user->getAlly();

        return new Response (null);
    }
}