<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserService extends AbstractController
{
    public function willSeeAction(ManagerRegistry $doctrine, $sector, $gal)
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();

        return new Response (null);
    }
}