<?php

namespace App\Controller\Connected\Research;

use Doctrine\Persistence\ManagerRegistry;
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
     * @param ManagerRegistry $doctrine
     * @param string $search
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function researchAllianceAction(ManagerRegistry $doctrine, string $search, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $level = $commander->getWhichResearch($search) + 1;
        $commanderBt = $commander->getBitcoin();
        $cost = $commander->getResearchCost($search);
        $time = $commander->getResearchTime($search);

        if(($commanderBt < ($level * $cost)) ||
            ($level == 6 || $commander->getSearchAt() > $now) ||
            $commander->getWhichResearch($search) === 0) {
            return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
        }

        $now->add(new DateInterval('PT' . round(($level * $time / $commander->getScientistProduction())) . 'S'));
        $commander->setSearch($search);
        $commander->setSearchAt($now);
        $commander->setBitcoin($commanderBt - ($level * $cost));
        $em->flush();

        return $this->redirectToRoute('search', ['usePlanet' => $usePlanet->getId()]);
    }
}