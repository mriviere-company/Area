<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FleetsController extends AbstractController
{
    public function nameAction()
    {
        $em = $this->getDoctrine()->getManager();

        return new Response ('true');
    }
}