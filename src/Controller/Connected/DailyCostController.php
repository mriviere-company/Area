<?php

namespace App\Controller\Connected;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class DailyCostController extends AbstractController
{
    /**
     * @Route("/aides/{usePlanet}", name="help_new", requirements={"usePlanet"="\d+"})
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function helpNewAction(Planet $usePlanet): RedirectResponse|Response
    {
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        return $this->render('connected/help_new.html.twig', [
            'usePlanet' => $usePlanet,
        ]);
    }
}