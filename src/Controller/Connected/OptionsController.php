<?php

namespace App\Controller\Connected;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\CharacterOptionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class OptionsController extends AbstractController
{
    /**
     * @Route("/preferences/{usePlanet}", name="prefers", requirements={"usePlanet"="\d+"})
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function prefersAction(Request $request, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $form_prefers = $this->createForm(CharacterOptionType::class, null, ["username" => $character->getUsername()]);
        $form_prefers->handleRequest($request);

        if ($form_prefers->isSubmitted() && $form_prefers->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if($form_prefers->get('planetOrder')->getData()) {
                $character->setOrderPlanet($form_prefers->get('planetOrder')->getData());
            }
            $character->setUsername($form_prefers->get('username')->getData());

            $em->flush();
        }

        return $this->render('connected/options.html.twig', [
            'form_prefers' => $form_prefers->createView(),
            'usePlanet' => $usePlanet,
        ]);
    }
}