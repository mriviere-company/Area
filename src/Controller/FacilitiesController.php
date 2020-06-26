<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FacilitiesController extends AbstractController
{
    public function userReportAction($user, $usePlanet)
    {
        $return = " <span><a href='/connect/profil-joueur/" . $user->getId() . "/" . $usePlanet->getId() . "'>" . $user->getUserName() . "</a></span> ";

        return new Response ($return);
    }

    public function coordinatesAction($planet, $usePlanet)
    {
        $return = " " . $planet->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $planet->getSector()->getPosition() .
            "/" . $planet->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" .
            $planet->getSector()->getGalaxy()->getPosition() . ":" . $planet->getSector()->getPosition() . ":" . $planet->getPosition() . "</a></span>) ";

        return new Response ($return);
    }

    public function fleetManageAction($fleet, $usePlanet)
    {
        $return = " <span><a href='/connect/gerer-flotte/" . $fleet->getId() ."/" . $usePlanet->getId() . "'>" . $fleet->getName() . "</a></span> ";

        return new Response ($return);
    }
}