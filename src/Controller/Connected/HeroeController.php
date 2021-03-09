<?php

namespace App\Controller\Connected;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use DateTime;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class HeroeController extends AbstractController
{
    /**
     * @Route("/commandants/{usePlanet}", name="heroe", requirements={"usePlanet"="\d+"})
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function heroeAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        return $this->render('connected/heroe.html.twig', [
            'usePlanet' => $usePlanet,
        ]);
    }
}