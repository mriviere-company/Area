<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/{_locale}", name="home", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $servers = $em->getRepository('App:Server')->findBy(['open' => 1]);
        $user = $this->getUser();

        if ($user) {
            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($user);
            if ($usePlanet) {
                return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
            }
        }

        $server = 0;
        if ($servers) {
            $server = 1;
        }

        return $this->render('index.html.twig', [
            'server' => $server
        ]);
    }
}
