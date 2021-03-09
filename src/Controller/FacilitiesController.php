<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FacilitiesController
 * @package App\Controller
 */
class FacilitiesController extends AbstractController
{
    /**
     * @param $user
     * @param $usePlanet
     * @return Response
     */
    public function userReportAction($user, $usePlanet)
    {
        $return = " <span><a href='/connect/profil-joueur/" . $user->getId() . "/" . $usePlanet->getId() . "'>" . $user->getUserName() . "</a></span> ";

        return new Response ($return);
    }

    /**
     * @param $planet
     * @param $usePlanet
     * @return Response
     */
    public function coordinatesAction($planet, $usePlanet)
    {
        $return = " " . $planet->getName() . " en (" . "<span><a href='/connect/carte-spatiale/" . $planet->getSector()->getPosition() .
            "/" . $planet->getSector()->getGalaxy()->getPosition() . "/" . $usePlanet->getId() . "'>" .
            $planet->getSector()->getGalaxy()->getPosition() . ":" . $planet->getSector()->getPosition() . ":" . $planet->getPosition() . "</a></span>) ";

        return new Response ($return);
    }

    /**
     * @param $fleet
     * @param $usePlanet
     * @return Response
     */
    public function fleetManageAction($fleet, $usePlanet)
    {
        $return = " <span><a href='/connect/gerer-flotte/" . $fleet->getId() ."/" . $usePlanet->getId() . "'>" . $fleet->getName() . "</a></span> ";

        return new Response ($return);
    }
}