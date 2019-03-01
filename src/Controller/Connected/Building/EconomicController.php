<?php

namespace App\Controller\Connected\Building;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use DateTime;
use Dateinterval;
use DateTimeZone;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class EconomicController extends AbstractController
{
    /**
     * @Route("/contruire-laboratoire/{idp}", name="building_add_search", requirements={"idp"="\d+"})
     */
    public function buildingAddSearchAction($idp)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        $usePlanet = $em->getRepository('App:Planet')->findByCurrentPlanet($idp, $user);

        $level = $usePlanet->getCenterSearch() + 1;
        $usePlanetNb = $usePlanet->getNiobium();
        $usePlanetWt = $usePlanet->getWater();
        $newGround = $usePlanet->getGroundPlace() + 5;

        if(($usePlanetNb < ($level * 2850) || $usePlanetWt < ($level * 3580)) ||
            ($usePlanet->getConstructAt() > $now || $newGround > $usePlanet->getGround())) {
            return $this->redirectToRoute('building', ['idp' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . ($level * 900) . 'S'));
        $usePlanet->setNiobium($usePlanetNb - ($level * 2850));
        $usePlanet->setWater($usePlanetWt - ($level * 3580));
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('centerSearch');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['idp' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-laboratoire/{idp}", name="building_remove_search", requirements={"idp"="\d+"})
     */
    public function buildingRemoveSearchAction($idp)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();

        $usePlanet = $em->getRepository('App:Planet')->findByCurrentPlanet($idp, $user);

        $level = $usePlanet->getCenterSearch();
        $newGround = $usePlanet->getGroundPlace() - 5;

        if(($level == 0 || $usePlanet->getConstructAt() > $now) ||
            ($usePlanet->getScientist() > $usePlanet->getScientistMax() - 500)) {
            return $this->redirectToRoute('building', ['idp' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 600 . 'S'));
        $usePlanet->setCenterSearch($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setScientistMax($usePlanet->getScientistMax() - 500);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['idp' => $usePlanet->getId()]);
    }

    /**
     * @Route("/contruire-ville/{idp}", name="building_add_city", requirements={"idp"="\d+"})
     */
    public function buildingAddCityAction($idp)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        $usePlanet = $em->getRepository('App:Planet')->findByCurrentPlanet($idp, $user);

        $level = $usePlanet->getCity() + 1;
        $usePlanetNb = $usePlanet->getNiobium();
        $usePlanetWt = $usePlanet->getWater();
        $newGround = $usePlanet->getGroundPlace() + 6;

        if(($usePlanetNb < ($level * 15000) || $usePlanetWt < ($level * 11000)) ||
            ($usePlanet->getConstructAt() > $now || $newGround > $usePlanet->getGround()) ||
            $user->getDemography() == 0) {
            return $this->redirectToRoute('building', ['idp' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . ($level * 720) . 'S'));
        $usePlanet->setNiobium($usePlanetNb - ($level * 15000));
        $usePlanet->setWater($usePlanetWt - ($level * 11000));
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('city');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['idp' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-ville/{idp}", name="building_remove_city", requirements={"idp"="\d+"})
     */
    public function buildingRemoveCityAction($idp)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();

        $usePlanet = $em->getRepository('App:Planet')->findByCurrentPlanet($idp, $user);

        $level = $usePlanet->getCity();
        $newGround = $usePlanet->getGroundPlace() - 6;

        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['idp' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 600 . 'S'));
        $usePlanet->setCity($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setWorkerMax($usePlanet->getWorkerMax() - 25000);
        $usePlanet->setWorkerProduction($usePlanet->getWorkerProduction() - 2000);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['idp' => $usePlanet->getId()]);
    }
    
    /**
     * @Route("/contruire-metropole/{idp}", name="building_add_metropole", requirements={"idp"="\d+"})
     */
    public function buildingAddMetropoleAction($idp)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        $usePlanet = $em->getRepository('App:Planet')->findByCurrentPlanet($idp, $user);

        $level = $usePlanet->getMetropole() + 1;
        $usePlanetNb = $usePlanet->getNiobium();
        $usePlanetWt = $usePlanet->getWater();
        $newGround = $usePlanet->getGroundPlace() + 6;
        $newSky = $usePlanet->getSkyPlace() + 6;

        if(($usePlanetNb < ($level * 75000) || $usePlanetWt < ($level * 55000)) ||
            ($usePlanet->getConstructAt() > $now || $newGround > $usePlanet->getGround()) ||
            ($user->getDemography() < 5 || $newSky > $usePlanet->getSky())) {
            return $this->redirectToRoute('building', ['idp' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . ($level * 2800) . 'S'));
        $usePlanet->setNiobium($usePlanetNb - ($level * 75000));
        $usePlanet->setWater($usePlanetWt - ($level * 55000));
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setSkyPlace($newSky);
        $usePlanet->setConstruct('metropole');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['idp' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-metropole/{idp}", name="building_remove_metropole", requirements={"idp"="\d+"})
     */
    public function buildingRemoveMetropoleAction($idp)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();

        $usePlanet = $em->getRepository('App:Planet')->findByCurrentPlanet($idp, $user);

        $level = $usePlanet->getMetropole();
        $newGround = $usePlanet->getGroundPlace() - 6;
        $newSky = $usePlanet->getSkyPlace() - 6;

        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['idp' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 600 . 'S'));
        $usePlanet->setMetropole($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setWorkerMax($usePlanet->getWorkerMax() - 75000);
        $usePlanet->setWorkerProduction($usePlanet->getWorkerProduction() - 5000);
        $usePlanet->setSkyPlace($newSky);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['idp' => $usePlanet->getId()]);
    }
}