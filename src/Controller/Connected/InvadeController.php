<?php

namespace App\Controller\Connected;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use App\Entity\Fleet;
use DateTime;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class InvadeController extends AbstractController
{
    /**
     * @Route("/hello-we-come-for-you/{fleet}/{usePlanet}", name="invader_planet", requirements={"fleet"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Fleet $fleet
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function invaderAction(ManagerRegistry $doctrine, Fleet $fleet, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();

        if ($usePlanet->getCharacter() != $character || $fleet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $redirect = $this->forward('App\Controller\Connected\Execute\WarPlanetController::invaderAction', [
            'usePlanet' => $usePlanet,
            'fleet' => $fleet,
            'character' => $character,
            'now'  => $now,
            'em' => $em]);

        if ($redirect->getContent() == 'nobarge') {
            $this->addFlash("fail", "Vous ne disposez pas de barges d'invasions.");
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
        } elseif ($redirect->getContent() == 'ally') {
            $this->addFlash("fail", "Vous ne pouvez pas envahir une planète alliée.");
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
        } elseif ($redirect->getContent() == 'noplayer') {
            $this->addFlash("fail", "Vous ne pouvez pas envahir une planète sans joueur.");
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
        } else
            return $this->redirectToRoute('report', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/merci-pour-les-ressources/{fleet}/{usePlanet}", name="raid_planet", requirements={"fleet"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Fleet $fleet
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function raidAction(ManagerRegistry $doctrine, Fleet $fleet, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();

        if ($usePlanet->getCharacter() != $character || $fleet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $redirect = $this->forward('App\Controller\Connected\Execute\WarPlanetController::raidAction', [
            'usePlanet' => $usePlanet,
            'fleet' => $fleet,
            'character' => $character,
            'now'  => $now,
            'em' => $em]);

        if ($redirect->getContent() == 'nobarge') {
            $this->addFlash("fail", "Vous ne disposez pas de barges d'invasions.");
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
        } elseif ($redirect->getContent() == 'zombie') {
            $this->addFlash("fail", "Vous ne pouvez pas piller une planète Zombie.");
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
        } elseif ($redirect->getContent() == 'ally') {
            $this->addFlash("fail", "Vous ne pouvez pas piller une planète alliée.");
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
        } elseif ($redirect->getContent() == 'noplayer') {
            $this->addFlash("fail", "Vous ne pouvez pas piller une planète sans joueur.");
            return $this->redirectToRoute('manage_fleet', ['fleetGive' => $fleet->getId(), 'usePlanet' => $usePlanet->getId()]);
        } else
            return $this->redirectToRoute('report', ['usePlanet' => $usePlanet->getId()]);
    }
}