<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
    public function index(ManagerRegistry $doctrine): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $servers = $doctrine->getRepository(Server::class)->findBy(['open' => 1]);
        $user = $this->getUser();

        if ($user) {
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
