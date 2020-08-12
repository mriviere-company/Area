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
class SociologicController extends AbstractController
{
    /**
     * @Route("/rechercher-aeroponique/{usePlanet}", name="research_aeroponic", requirements={"usePlanet"="\d+"})
     */
    public function researchAeroponicAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getDemography() + 1;
        $userBt = $user->getBitcoin();

        if(($userBt < ($level * 16000)) ||
            ($level == 1 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 480 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('aeroponicFarm');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 16000));
        if(($user->getTutorial() == 8)) {
            $user->setTutorial(9);
        }
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-demographie/{usePlanet}", name="research_demography", requirements={"usePlanet"="\d+"})
     */
    public function researchDemographyAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getDemography() + 1;
        $userBt = $user->getBitcoin();

        if(($userBt < ($level * 8000)) ||
            ($level == 6 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 480 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('demography');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 8000));
        if(($user->getTutorial() == 8)) {
            $user->setTutorial(9);
        }
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-discipline/{usePlanet}", name="research_discipline", requirements={"usePlanet"="\d+"})
     */
    public function researchDisciplineAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getDiscipline() + 1;
        $userBt = $user->getBitcoin();

        if(($userBt < ($level * 11700) || $user->getDemography() == 0) ||
            ($level == 4 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 930 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('discipline');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 11700));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-barbele/{usePlanet}", name="research_barbed", requirements={"usePlanet"="\d+"})
     */
    public function researchBarbedAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getBarbed() + 1;
        $userBt = $user->getBitcoin();

        if(($userBt < ($level * 200000) || $user->getDiscipline() != 3) ||
            ($level == 6 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 1200 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('barbed');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 200000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-tank/{usePlanet}", name="research_tank", requirements={"usePlanet"="\d+"})
     */
    public function researchTankAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getTank() + 1;
        $userBt = $user->getBitcoin();

        if(($userBt < ($level * 40000) || $user->getDiscipline() != 3) ||
            ($level == 2 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 2000 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('tank');
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * 40000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rechercher-expansion/{usePlanet}", name="research_expansion", requirements={"usePlanet"="\d+"})
     */
    public function researchExpansionAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getExpansion() + 1;
        $userPdg = $user->getRank()->getWarPoint();

        if(($userPdg < ($level * 75000) || $user->getTerraformation() <= 17) ||
            ($level == 3 || $user->getSearchAt() > $now)) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * 6000 / $user->getScientistProduction())) . 'S'));
        $user->setSearch('expansion');
        $user->setSearchAt($now);
        $user->getRank()->setWarPoint($userPdg - ($level * 75000));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }
}