<?php

namespace App\Controller\Connected\Research;

use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @param string $search
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchAllyAction(string $search, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $level = $character->getWhichResearch($search) + 1;
        $characterBt = $character->getBitcoin();
        $cost = $character->getResearchCost($search);
        $time = $character->getResearchTime($search);

        if(($characterBt < ($level * $cost)) ||
            ($level == 6 || $character->getSearchAt() > $now) ||
            $character->getWhichResearch($search) === 0) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * $time / $character->getScientistProduction())) . 'S'));
        $character->setSearch($search);
        $character->setSearchAt($now);
        $character->setBitcoin($characterBt - ($level * $cost));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }
}