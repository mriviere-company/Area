<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FleetService extends AbstractController
{
    public function willSeeAction(ManagerRegistry $doctrine, $sector, $gal): Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();

        return new Response (null);
    }
}