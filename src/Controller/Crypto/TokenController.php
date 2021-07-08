<?php

namespace App\Controller\Crypto;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class TopMenuController
 * @package App\Controller
 */
class TokenController extends AbstractController
{
    /**
     * @Route("/whitepaper/{_locale}", name="whitepaper", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/whitepaper", name="whitepaper_noSlash")
     */
    public function whitepaperAction(): Response
    {
        return $this->render('crypto/white_paper.html.twig');
    }

    /**
     * @Route("/buy/{_locale}", name="buy", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/buy", name="buy_noSlash")
     */
    public function buyAction(): Response
    {
        return $this->render('crypto/buy.html.twig');
    }

    /**
     * @Route("/sell/{_locale}", name="sell", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/sell", name="sell_noSlash")
     */
    public function sellAction(): Response
    {
        return $this->render('crypto/sell.html.twig');
    }

    /**
     * @Route("/pools/{_locale}", name="pools", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/pools", name="pools_noSlash")
     */
    public function poolsAction(): Response
    {
        return $this->render('crypto/pools.html.twig');
    }
}
