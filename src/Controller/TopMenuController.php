<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class TopMenuController
 * @package App\Controller
 */
class TopMenuController extends AbstractController
{
    /**
     * @Route("/reglement/{_locale}", name="rules", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/reglement", name="rules_noSlash")
     */
    public function rulesAction(): Response
    {
        return $this->render('anonymous/rules.html.twig');
    }

    /**
     * @Route("/medias/{_locale}", name="medias", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/medias", name="medias_noSlash")
     */
    public function mediasAction(): Response
    {
        return $this->render('anonymous/medias.html.twig');
    }

    /**
     * @Route("/but-du-jeu/{_locale}", name="point_game", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/but-du-jeu", name="point_game_noSlash")
     */
    public function pointGameAction(): Response
    {
        return $this->render('anonymous/point_game.html.twig');
    }

    /**
     * @Route("/crypto-currencie/{_locale}", name="crypto", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/crypto-currencie", name="crypto_noSlash")
     */
    public function cryptoAction(): Response
    {
        return $this->render('anonymous/crypto.html.twig');
    }
}
