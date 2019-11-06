<?php

namespace App\Controller\Connected;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use DateTime;
use DateTimeZone;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class CommanderController extends AbstractController
{
    /**
     * @Route("/commandant/{usePlanet}", name="commander", requirements={"usePlanet"="\d+"})
     */
    public function commanderAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }
        $commander = 0;
        $user->setCommander($commander);
        $em->flush();

        return $this->render('connected/commander.html.twig', [
            'usePlanet' => $usePlanet,
        ]);
    }
}