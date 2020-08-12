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
class PoliticController extends AbstractController
{
    /**
     * @Route("/lancer-recherche/{search}/{usePlanet}", name="research_ally", requirements={"search"="\w+", "usePlanet"="\d+"})
     */
    public function researchAllyAction(Planet $usePlanet, $search)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $user->getWhichResearch($search) + 1;
        $userBt = $user->getBitcoin();
        $cost = $user->getResearchCost($search);
        $time = $user->getResearchTime($search);

        if(($userBt < ($level * $cost)) ||
            ($level == 6 || $user->getSearchAt() > $now) ||
            $user->getWhichResearch($search) === 0) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * $time / $user->getScientistProduction())) . 'S'));
        $user->setSearch($search);
        $user->setSearchAt($now);
        $user->setBitcoin($userBt - ($level * $cost));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }
}