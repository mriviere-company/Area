<?php

namespace App\Controller\Connected;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use App\Entity\Commander;
use App\Entity\Ally;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class ProfilController extends AbstractController
{
    /**
     * @Route("/profil-joueur/{commanderProfil}/{usePlanet}", name="user_profil", requirements={"usePlanet"="\d+", "commanderProfil"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Commander $commanderProfil
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function userProfilAction(ManagerRegistry $doctrine, Commander $commanderProfil, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $galaxys = $doctrine->getRepository(Galaxy::class)
            ->createQueryBuilder('g')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->select('g.position')
            ->groupBy('g.id')
            ->where('p.commander = :commander')
            ->setParameters(['commander' => $commander])
            ->getQuery()
            ->getResult();

        return $this->render('connected/profil/player.html.twig', [
            'usePlanet' => $usePlanet,
            'commanderProfil' => $commanderProfil,
            'galaxys' => $galaxys
        ]);
    }

    /**
     * @Route("/profil-alliance/{allyCommander}/{usePlanet}", name="ally_profil", requirements={"usePlanet"="\d+", "allyCommander"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Ally $allyCommander
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function allyProfilAction(ManagerRegistry $doctrine, Ally $allyCommander, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }
        $galaxys = $doctrine->getRepository(Galaxy::class)
            ->createQueryBuilder('g')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->join('p.commander', 'c')
            ->select('g.position')
            ->groupBy('g.id')
            ->where('c.ally = :ally')
            ->setParameters(['ally' => $allyCommander])
            ->getQuery()
            ->getResult();

        return $this->render('connected/profil/ally.html.twig', [
            'usePlanet' => $usePlanet,
            'allyPage' => $allyCommander,
            'galaxys' => $galaxys
        ]);
    }

    /**
     * @Route("/profil-joueur-popup/{commanderProfil}/{usePlanet}", name="user_profil_modal", requirements={"usePlanet"="\d+", "commanderProfil"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Commander $commanderProfil
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function userProfilModalAction(ManagerRegistry $doctrine, Commander $commanderProfil, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $galaxys = $doctrine->getRepository(Galaxy::class)
            ->createQueryBuilder('g')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->select('g.position')
            ->groupBy('g.id')
            ->where('p.commander = :commander')
            ->setParameters(['commander' => $commander])
            ->getQuery()
            ->getResult();

        return $this->render('connected/profil/modal_user.html.twig', [
            'usePlanet' => $usePlanet,
            'commanderProfil' => $commanderProfil,
            'galaxys' => $galaxys
        ]);
    }

    /**
     * @Route("/profil-alliance-popup/{allyCommander}/{usePlanet}", name="ally_profil_modal", requirements={"usePlanet"="\d+", "allyCommander"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Ally $allyCommander
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function allyProfilModalAction(ManagerRegistry $doctrine, Ally $allyCommander, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }
        $galaxys = $doctrine->getRepository(Galaxy::class)
            ->createQueryBuilder('g')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->join('p.commander', 'c')
            ->select('g.position')
            ->groupBy('g.id')
            ->where('c.ally = :ally')
            ->setParameters(['ally' => $allyCommander])
            ->getQuery()
            ->getResult();

        return $this->render('connected/profil/modal_ally.html.twig', [
            'usePlanet' => $usePlanet,
            'allyPage' => $allyCommander,
            'galaxys' => $galaxys
        ]);
    }
}