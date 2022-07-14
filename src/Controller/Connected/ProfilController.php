<?php

namespace App\Controller\Connected;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use App\Entity\Character;
use App\Entity\Ally;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class ProfilController extends AbstractController
{
    /**
     * @Route("/profil-joueur/{characterProfil}/{usePlanet}", name="user_profil", requirements={"usePlanet"="\d+", "characterProfil"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Character $characterProfil
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function userProfilAction(ManagerRegistry $doctrine, Character $characterProfil, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $galaxys = $em->getRepository('App:Galaxy')
            ->createQueryBuilder('g')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->select('g.position')
            ->groupBy('g.id')
            ->where('p.character = :character')
            ->setParameters(['character' => $character])
            ->getQuery()
            ->getResult();

        return $this->render('connected/profil/player.html.twig', [
            'usePlanet' => $usePlanet,
            'characterProfil' => $characterProfil,
            'galaxys' => $galaxys
        ]);
    }

    /**
     * @Route("/profil-alliance/{allyCharacter}/{usePlanet}", name="ally_profil", requirements={"usePlanet"="\d+", "allyCharacter"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Ally $allyCharacter
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function allyProfilAction(ManagerRegistry $doctrine, Ally $allyCharacter, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }
        $galaxys = $em->getRepository('App:Galaxy')
            ->createQueryBuilder('g')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->join('p.character', 'c')
            ->select('g.position')
            ->groupBy('g.id')
            ->where('c.ally = :ally')
            ->setParameters(['ally' => $allyCharacter])
            ->getQuery()
            ->getResult();

        return $this->render('connected/profil/ally.html.twig', [
            'usePlanet' => $usePlanet,
            'allyPage' => $allyCharacter,
            'galaxys' => $galaxys
        ]);
    }

    /**
     * @Route("/profil-joueur-popup/{characterProfil}/{usePlanet}", name="user_profil_modal", requirements={"usePlanet"="\d+", "characterProfil"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Character $characterProfil
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function userProfilModalAction(ManagerRegistry $doctrine, Character $characterProfil, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $galaxys = $em->getRepository('App:Galaxy')
            ->createQueryBuilder('g')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->select('g.position')
            ->groupBy('g.id')
            ->where('p.character = :character')
            ->setParameters(['character' => $character])
            ->getQuery()
            ->getResult();

        return $this->render('connected/profil/modal_user.html.twig', [
            'usePlanet' => $usePlanet,
            'characterProfil' => $characterProfil,
            'galaxys' => $galaxys
        ]);
    }

    /**
     * @Route("/profil-alliance-popup/{allyCharacter}/{usePlanet}", name="ally_profil_modal", requirements={"usePlanet"="\d+", "allyCharacter"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Ally $allyCharacter
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function allyProfilModalAction(ManagerRegistry $doctrine, Ally $allyCharacter, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }
        $galaxys = $em->getRepository('App:Galaxy')
            ->createQueryBuilder('g')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->join('p.character', 'c')
            ->select('g.position')
            ->groupBy('g.id')
            ->where('c.ally = :ally')
            ->setParameters(['ally' => $allyCharacter])
            ->getQuery()
            ->getResult();

        return $this->render('connected/profil/modal_ally.html.twig', [
            'usePlanet' => $usePlanet,
            'allyPage' => $allyCharacter,
            'galaxys' => $galaxys
        ]);
    }
}