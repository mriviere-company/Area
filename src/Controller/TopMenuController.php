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
        $em = $this->getDoctrine()->getManager();

        if($this->getUser()) {
            $usePlanet = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.character', 'c')
                ->where('c.username = :user')
                ->setParameters(['user' => $this->getUser()->getUsername()])
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();
        } else {
            $usePlanet = null;
        }

        return $this->render('anonymous/rules.html.twig', [
            'usePlanet' => $usePlanet,
        ]);
    }

    /**
     * @Route("/medias/{_locale}", name="medias", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/medias", name="medias_noSlash")
     */
    public function mediasAction(): Response
    {
        $em = $this->getDoctrine()->getManager();

        if($this->getUser()) {
            $usePlanet = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.character', 'c')
                ->where('c.username = :user')
                ->setParameters(['user' => $this->getUser()->getUsername()])
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();
        } else {
            $usePlanet = null;
        }

        return $this->render('anonymous/medias.html.twig', [
            'usePlanet' => $usePlanet,
        ]);
    }

    /**
     * @Route("/but-du-jeu/{_locale}", name="point_game", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/but-du-jeu", name="point_game_noSlash")
     */
    public function pointGameAction(): Response
    {
        $em = $this->getDoctrine()->getManager();

        if($this->getUser()) {
            $usePlanet = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.character', 'c')
                ->where('c.username = :user')
                ->setParameters(['user' => $this->getUser()->getUsername()])
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();
        } else {
            $usePlanet = null;
        }

        return $this->render('anonymous/point_game.html.twig', [
            'usePlanet' => $usePlanet,
        ]);
    }

    /**
     * @Route("/whitepaper/{_locale}", name="whitepaper", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/whitepaper", name="whitepaper_noSlash")
     */
    public function whitepaperAction(): Response
    {
        $em = $this->getDoctrine()->getManager();

        if($this->getUser()) {
            $usePlanet = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.character', 'c')
                ->where('c.username = :user')
                ->setParameters(['user' => $this->getUser()->getUsername()])
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();
        } else {
            $usePlanet = null;
        }

        return $this->render('anonymous/white_paper.html.twig', [
            'usePlanet' => $usePlanet,
        ]);
    }
}
