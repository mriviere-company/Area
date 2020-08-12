<?php

namespace App\Controller\Connected\Research;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use DateTime;
use Dateinterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class MilitaryController extends AbstractController
{
    /**
     * @Route("/rechercher-industrie/{usePlanet}", name="research_industry", requirements={"usePlanet"="\d+"})
     */
    public function researchIndustryAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getIndustry() + 1;
        $userBt = $user->getBitcoin();

        if(($userBt < ($level * 1500)) ||
            ($level == 6 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 150 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('industry');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 1500));
        if(($user->getTutorial() == 8)) {
            $user->setTutorial(9);
        }
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-vaisseaux-leger/{usePlanet}", name="research_light_ship", requirements={"usePlanet"="\d+"})
     */
    public function researchLightShipAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getLightShip() + 1;
        $userBt = $user->getBitcoin();

        if(($userBt < ($level * 9000) || $user->getIndustry() < 3) ||
            ($level == 4 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 860 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('lightShip');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 9000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-vaisseaux-lourd/{usePlanet}", name="research_heavy_ship", requirements={"usePlanet"="\d+"})
     */
    public function researchHeavyShipAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getHeavyShip() + 1;
        $userBt = $user->getBitcoin();

        if(($userBt < ($level * 42000) || $user->getIndustry() < 5) ||
            ($level == 4 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 1200 / $user->getScientistProduction())) . 'S')); // X100 NORMAL GAME
        $user->setSearch('heavyShip');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 42000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }
}