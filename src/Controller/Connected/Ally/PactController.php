<?php

namespace App\Controller\Connected\Ally;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\AllyPeaceType;
use App\Entity\Pna;
use App\Entity\Allied;
use App\Entity\Planet;
use App\Entity\Peace;
use App\Entity\War;
use DateTime;
use Dateinterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class PactController extends AbstractController
{
    /**
     * @Route("/accepter-pacte/{pact}/{usePlanet}", name="ally_acceptAllied", requirements={"pact"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Allied $pact
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function pactAcceptAction(ManagerRegistry $doctrine, Allied $pact, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $ally = $commander->getAlly();

        $allied = new Allied($ally, $pact->getAlly()->getSigle(), true);
        $pact->setAccepted(true);
        $em->persist($allied);
        $ally->addAllyAllied($allied);

        $em->flush();

        return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/refuser-pacte/{pact}/{usePlanet}", name="ally_refuseAllied", requirements={"pact"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Allied $pact
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function pactRefuseAction(ManagerRegistry $doctrine, Allied $pact, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $em->remove($pact);

        $em->flush();

        return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/accepter-pna/{pact}/{usePlanet}", name="ally_acceptPna", requirements={"pact"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Pna $pact
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function pnaAcceptAction(ManagerRegistry $doctrine, Pna $pact, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $ally = $commander->getAlly();

        $pna = new Pna($ally, $pact->getAlly()->getSigle(), true);
        $pact->setAccepted(true);
        $em->persist($pna);
        $ally->addAllyPna($pna);

        $em->flush();

        return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/refuser-pna/{pact}/{usePlanet}", name="ally_refusePna", requirements={"pact"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Pna $pact
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function pnaRefuseAction(ManagerRegistry $doctrine, Pna $pact, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $em->remove($pact);

        $em->flush();

        return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-pna/{pact}/{usePlanet}", name="ally_remove_pna", requirements={"pact"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Pna $pact
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws NonUniqueResultException
     */
    public function allyPnaRefuseAction(ManagerRegistry $doctrine, Pna $pact, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $ally = $commander->getAlly();
        $otherAlly = $doctrine->getRepository(Ally::class)
            ->createQueryBuilder('a')
            ->where('a.sigle = :sigle')
            ->setParameter('sigle', $pact->getAllyTag())
            ->getQuery()
            ->getOneOrNullResult();

        $pact2 = $doctrine->getRepository(Pna::class)
            ->createQueryBuilder('pna')
            ->where('pna.allyTag = :allytag')
            ->andWhere('pna.ally = :ally')
            ->setParameters([
                'allytag' => $ally->getSigle(),
                'ally' => $otherAlly])
            ->getQuery()
            ->getOneOrNullResult();

        if($pact2) {
            $em->remove($pact2);
        }
        $em->remove($pact);
        $em->flush();

        return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-pacte/{pact}/{usePlanet}", name="ally_remove_pact", requirements={"pact"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Allied $pact
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws NonUniqueResultException
     */
    public function allyPactRefuseAction(ManagerRegistry $doctrine, Allied $pact, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();
        $now->add(new DateInterval('PT' . 43200 . 'S'));

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $otherAlly = $doctrine->getRepository(Ally::class)
            ->createQueryBuilder('a')
            ->where('a.sigle = :sigle')
            ->setParameter('sigle', $pact->getAllyTag())
            ->getQuery()
            ->getOneOrNullResult();

        $pact2 = $doctrine->getRepository(Allied::class)
            ->createQueryBuilder('al')
            ->where('al.allyTag = :allytag')
            ->andWhere('al.ally = :ally')
            ->setParameters([
                'allytag' => $commander->getAlly()->getSigle(),
                'ally' => $otherAlly])
            ->getQuery()
            ->getOneOrNullResult();

        if($pact2) {
            $pact2->setDismissAt($now);
            $pact2->setDismissBy($commander->getAlly()->getSigle());
            $pact->setDismissAt($now);
            $pact->setDismissBy($commander->getAlly()->getSigle());
        } else {
            $em->remove($pact);
        }
        $em->flush();

        return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/faire-la-paix/{war}/{usePlanet}", name="ally_make_peace", requirements={"war"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param War $war
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function allyMakePeaceAction(ManagerRegistry $doctrine, Request $request, War $war, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $commander->getAlly();
        $now = new DateTime();
        $now->add(new DateInterval('PT' . 864000 . 'S'));

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $waitingPeaces = $doctrine->getRepository(Peace::class)
            ->createQueryBuilder('p')
            ->where('p.allyTag = :sigle')
            ->andWhere('p.accepted = false')
            ->setParameters(['sigle' => $ally->getSigle()])
            ->getQuery()
            ->getResult();

        $form_peace = $this->createForm(AllyPeaceType::class);
        $form_peace->handleRequest($request);

        if (($form_peace->isSubmitted() && $form_peace->isValid())) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $peace = new Peace($ally, $war->getAllyTag(), $form_peace->get('type')->getData(), $form_peace->get('planetNbr')->getData(), $form_peace->get('taxeNbr')->getData(), $form_peace->get('pdgNbr')->getData(), false);
            $em->persist($peace);
            $em->flush();

            $form_peace->get('type')->getData();
        }

        return $this->render('connected/ally/makePeace.html.twig', [
            'form_peace' => $form_peace->createView(),
            'usePlanet' => $usePlanet,
            'waitingPeaces' => $waitingPeaces,
        ]);
    }

    /**
     * @Route("/accepter-paix/{id}/{usePlanet}", name="ally_accept_peace", requirements={"id"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param int $id
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws NonUniqueResultException
     */
    public function allyAcceptPeaceAction(ManagerRegistry $doctrine, int $id, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $commander->getAlly();
        $now = new DateTime();
        $now->add(new DateInterval('PT' . 864000 . 'S'));

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $peace = $doctrine->getRepository(Peace::class)
            ->createQueryBuilder('p')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        $otherPeaces = $doctrine->getRepository(Peace::class)
            ->createQueryBuilder('p')
            ->where('p.id != :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();

        $otherAlly = $doctrine->getRepository(Ally::class)
            ->createQueryBuilder('a')
            ->where('a.id = :id')
            ->setParameter('id', $peace->getAlly()->getId())
            ->getQuery()
            ->getOneOrNullResult();

        $war = $doctrine->getRepository(War::class)
            ->createQueryBuilder('w')
            ->where('w.ally = :ally')
            ->andWhere('w.allyTag = :sigle')
            ->setParameter('ally', $ally)
            ->setParameters(['ally' => $ally, 'sigle' => $otherAlly->getSigle()])
            ->getQuery()
            ->getOneOrNullResult();

        $war2 = $doctrine->getRepository(War::class)
            ->createQueryBuilder('w')
            ->where('w.ally = :ally')
            ->andWhere('w.allyTag = :sigle')
            ->setParameters(['ally' => $otherAlly, 'sigle' => $ally->getSigle()])
            ->getQuery()
            ->getOneOrNullResult();

        if ($peace->getType() == 0) {
            $type = 1;

        } else {
            $type = 0;

        }
        if ($war && $war2) {
            $em->remove($war);
            $em->remove($war2);
        }
        foreach ($otherPeaces as $otherPeace) {
            $em->remove($otherPeace);
        }
        $peace2 = new Peace($otherAlly, $peace->getAlly()->getSigle(), $type, $peace->getPlanet(), $peace->getTaxe(), $peace->getPdg(), true);
        $peace->setSignedAt($now);
        $peace->setAccepted(true);
        $em->persist($peace2);
        $em->flush();

        return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/refuser-paix/{peace}/{usePlanet}", name="ally_remove_peace", requirements={"peace"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Peace $peace
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws NonUniqueResultException
     */
    public function allyRemovePeaceAction(ManagerRegistry $doctrine, Peace $peace, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }


        $otherAlly = $doctrine->getRepository(Ally::class)
            ->createQueryBuilder('a')
            ->where('a.sigle = :sigle')
            ->setParameter('sigle', $peace->getAllyTag())
            ->getQuery()
            ->getOneOrNullResult();

        $peace2 = $doctrine->getRepository(Allied::class)
            ->createQueryBuilder('al')
            ->where('al.allyTag = :allytag')
            ->andWhere('al.ally = :ally')
            ->setParameters([
                'allytag' => $commander->getAlly()->getSigle(),
                'ally' => $otherAlly])
            ->getQuery()
            ->getOneOrNullResult();

        if($peace2)
            $em->remove($peace2);
        $em->remove($peace);
        $em->flush();

        return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
    }
}