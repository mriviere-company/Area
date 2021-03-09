<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class HomeController
 * @package App\Controller
 */
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
            /*$usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($character);
            if ($usePlanet) {
                return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
            }*/
            return $this->redirectToRoute('server_select');
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
