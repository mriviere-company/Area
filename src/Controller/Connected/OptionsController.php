<?php

namespace App\Controller\Connected;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\CharacterOptionType;
use App\Form\Front\UserOptionType;
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

    /**
     * @Route("/mes-options", name="parameters")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function optionsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();


        $form_parameters = $this->createForm(UserOptionType::class, null, ["username" => $user->getUsername(), "newletter" => $user->getNewletter() ? true : false, "connectLast" => $user->getConnectLast() ? true : false]);
        $form_parameters->handleRequest($request);

        if ($form_parameters->isSubmitted() && $form_parameters->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if(password_verify($form_parameters->get('oldPassword')->getData(), $user->getPassword())) {
                if(count($form_parameters->get('password')->getData()) == 1 && $form_parameters->get('password')->getData() == $form_parameters->get('confirmPassword')->getData()) {
                    $user->setPassword(password_hash($form_parameters->get('password')->getData(), PASSWORD_BCRYPT));
                }
            }
            $user->setUsername($form_parameters->get('username')->getData());
            $user->setConnectLast($form_parameters->get('connect_last')->getData());
            $user->setNewletter($form_parameters->get('newletter')->getData());

            $em->flush();
        }

        return $this->render('anonymous/parameters.html.twig', [
            'form_parameters' => $form_parameters->createView()
        ]);
    }
}