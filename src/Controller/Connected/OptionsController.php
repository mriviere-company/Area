<?php

namespace App\Controller\Connected;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\CommanderOptionType;
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
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function prefersAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $form_prefers = $this->createForm(CommanderOptionType::class, null, ["username" => $commander->getUsername()]);
        $form_prefers->handleRequest($request);

        if ($form_prefers->isSubmitted() && $form_prefers->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if($form_prefers->get('planetOrder')->getData()) {
                $commander->setOrderPlanet($form_prefers->get('planetOrder')->getData());
            }
            $commander->setUsername($form_prefers->get('username')->getData());

            $em->flush();
            return $this->redirectToRoute('prefers', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->render('connected/options.html.twig', [
            'form_prefers' => $form_prefers->createView(),
            'usePlanet' => $usePlanet,
        ]);
    }
}