<?php

namespace App\Controller\Connected\Alliance;

use App\Entity\Commander;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\UserAllianceType;
use App\Form\Front\AllianceImageType;
use App\Form\Front\AllianceAddType;
use App\Form\Front\AlliancePactType;
use App\Form\Front\AllianceGradeType;
use App\Form\Front\AllianceDefconType;
use App\Form\Front\VoteType;
use App\Form\Front\ExchangeType;
use App\Form\Front\UserAttrGradeType;
use App\Entity\Grade;
use App\Entity\Alliance;
use App\Entity\Offer;
use App\Entity\Exchange;
use App\Entity\Pna;
use App\Entity\Allied;
use App\Entity\User;
use App\Entity\War;
use App\Entity\Planet;
use DateTime;
use Dateinterval;
use App\Entity\Salon;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class AllianceController extends AbstractController
{
    /**
     * @Route("/alliance/{usePlanet}", name="ally", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     */
    public function allyAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $em = $doctrine->getManager();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if($commander->getAlliance()) {
            $ally = $commander->getAlliance();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }
        $form_allyImage = $this->createForm(AllianceImageType::class, $ally);
        $form_allyImage->handleRequest($request);

        $form_vote = $this->createForm(VoteType::class, null, ["allyId" => $commander->getAlliance()->getId()]);
        $form_vote->handleRequest($request);

        if ($form_allyImage->isSubmitted() && $form_allyImage->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $ally->setImageName(null);
            $em->flush();
        }

        if ($form_vote->isSubmitted() && $form_vote->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if ($commander->getVoteName()) {
                $unVoteUser = $doctrine->getRepository(User::class)->findOneBy(['username' => $commander->getVoteName()]);
                $unVoteUser->setVoteAlliance($unVoteUser->getVoteAlliance() - 1);
            }
            $commander->setVoteName($form_vote->get('commander')->getData()->getUsername());
            $form_vote->get('commander')->getData()->setVoteAlliance($form_vote->get('commander')->getData()->getVoteAlliance() + 1);
            $em->flush();

            $leader = $doctrine->getRepository(Commander::class)
                ->createQueryBuilder('c')
                ->join('c.grade', 'g')
                ->where('g.placement = 1')
                ->andWhere('c.ally = :ally')
                ->setParameters(['ally' => $commander->getAlliance()])
                ->getQuery()
                ->getOneOrNullResult();

            if ($leader) {
                $newLeader = $doctrine->getRepository(Commander::class)
                    ->createQueryBuilder('c')
                    ->where('c.voteAlliance > :vote and c.id != :commander')
                    ->andWhere('c.ally = :ally')
                    ->setParameters(['vote' => $leader->getVoteAlliance(), 'ally' => $commander->getAlliance(), 'commander' => $leader->getId()])
                    ->orderBy('u.voteAlliance', 'DESC')
                    ->getQuery()
                    ->getOneOrNullResult();

                if ($newLeader) {
                    $tmpGrade = $newLeader->getGrade();
                    $newLeader->setGrade($leader->getGrade());
                    $leader->setGrade($tmpGrade);
                    $em->flush();
                }
            } else {
                $newLeader = $doctrine->getRepository(Commander::class)
                    ->createQueryBuilder('c')
                    ->andWhere('c.ally = :ally')
                    ->setParameters(['ally' => $commander->getAlliance()])
                    ->orderBy('c.voteAlliance', 'DESC')
                    ->getQuery()
                    ->setMaxResults(1)
                    ->getOneOrNullResult();

                $tmpGrade = $doctrine->getRepository(Grade::class)
                    ->createQueryBuilder('g')
                    ->where('g.placement = 1')
                    ->andWhere('g.ally = :ally')
                    ->setParameters(['ally' => $commander->getAlliance()])
                    ->getQuery()
                    ->setMaxResults(1)
                    ->getOneOrNullResult();

                if ($newLeader && $tmpGrade) {
                    $newLeader->setGrade($tmpGrade);
                    $em->flush();
                }
            }
        }

        $userVotes = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->where('c.voteAlliance > 0')
            ->andWhere('c.ally = :ally')
            ->setParameters(['ally' => $commander->getAlliance()])
            ->orderBy('c.voteAlliance', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('connected/ally.html.twig', [
            'form_allyImage' => $form_allyImage->createView(),
            'form_allyVote' => $form_vote->createView(),
            'usePlanet' => $usePlanet,
            'userVotes' => $userVotes
        ]);
    }

    /**
     * @Route("/attribution-grade/{newGradeUser}/{usePlanet}", name="ally_addUser_grade", requirements={"newGradeUser"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Commander $newGradeUser
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function allyAddUserGradeAction(ManagerRegistry $doctrine, Request $request, Commander $newGradeUser, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $commander->getAlliance();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $form_userAttrGrade = $this->createForm(UserAttrGradeType::class, null, ["allyId" => $commander->getAlliance()->getId()]);
        $form_userAttrGrade->handleRequest($request);

        if (($form_userAttrGrade->isSubmitted() && $form_userAttrGrade->isValid())) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if ($ally->getPolitic() != 'fascism' && $form_userAttrGrade->get('grade')->getData()->getPlacement() != 1) {
                if (($commander->getGrade()->getPlacement() == 1 && $newGradeUser->getId() == $commander->getId()) && $form_userAttrGrade->get('grade')->getData()->getPlacement() != 1) {
                    return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
                }
                if ($newGradeUser != $commander && $form_userAttrGrade->get('grade')->getData()->getPlacement() == 1 && $ally->getPolitic() != 'communism') {
                    $grade = $doctrine->getRepository(Grade::class)->findOneBy(['ally' => $ally->getId(), 'placement' => 5]);
                    $commander->setGrade($grade);
                }
                $newGradeUser->setGrade($form_userAttrGrade->get('grade')->getData());
                $em->flush();
            }
            return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->render('connected/ally/grade.html.twig', [
            'form_userAttrGrade' => $form_userAttrGrade->createView(),
            'usePlanet' => $usePlanet,
            'idUser' => $newGradeUser->getId(),
        ]);
    }

    /**
     * @Route("/cherche-alliance/{usePlanet}", name="ally_blank", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function noAllianceAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();
        $em = $doctrine->getManager();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if($commander->getAlliance()) {
            return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
        } else {
            $ally = new Alliance();
        }

        $form_ally = $this->createForm(UserAllianceType::class, $ally);
        $form_ally->handleRequest($request);

        if ($form_ally->isSubmitted() && $form_ally->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if($commander->getAllianceBan() > $now) {
                return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
            }

            $ally->addCommander($commander);
            $ally->setMaxMembers(1);
            $ally->setImageName('democrat.webp');
            $ally->setBitcoin(1);
            $ally->setPdg(1);
            $em->persist($ally);
            $em->flush();

            if ($form_ally->get('politic')->getData() == 'democrat') {
                $grade = new Grade($ally, "Président", 1, true, true, true, true, true, true, true);
                $sGrade = new Grade($ally, "Ministre", 2, true, false, false, false, false, false, false);
                $mGrade = new Grade($ally, "Citoyen", 3, false, false, false, false, false, false, false);
                $lGrade = new Grade($ally, "Mineur", 5, false, false, false, false, false, false, false);
                $ally->setMaxMembers(3);
                $ally->setImageName('democrat.webp');
                $ally->setBitcoin(25000);
                $ally->setPdg(0);
            } elseif ($form_ally->get('politic')->getData() == 'fascism') {
                $grade = new Grade($ally, "Führer", 1, true, true, true, true, true, true, true);
                $sGrade = new Grade($ally, "Reichsführer", 2, true, false, false, false, false, false, false);
                $mGrade = new Grade($ally, "Soldat", 3, false, false, false, false, false, false, false);
                $lGrade = new Grade($ally, "Apirant", 5, false, false, false, false, false, false, false);
                $ally->setMaxMembers(2);
                $ally->setImageName('fascism.webp');
                $ally->setBitcoin(15000);
                $ally->setPdg(2000);
            } elseif ($form_ally->get('politic')->getData() == 'communism'){
                $grade = new Grade($ally, "Père des peuples", 1, true, true, true, true, true, true, true);
                $mGrade = new Grade($ally, "Nomenklatura", 1, true, true, true, true, true, true, true);
                $sGrade = new Grade($ally, "Camarade", 1, true, true, true, true, true, true, true);
                $lGrade = new Grade($ally, "Goulag", 2, false, false, false, false, false, false, false);
                $ally->setMaxMembers(4);
                $ally->setImageName('communism.webp');
                $ally->setBitcoin(0);
                $ally->setPdg(250);
                $ally->setTaxe(75);
            }
            $em->persist($grade);
            $em->persist($sGrade);
            $em->persist($mGrade);
            $em->persist($lGrade);

            $ally->addGrade($grade);
            $ally->addGrade($sGrade);
            $ally->addGrade($mGrade);
            $ally->addGrade($lGrade);

            $server = $usePlanet->getSector()->getGalaxy()->getServer();

            $salon = new Salon($ally->getName(), $server);
            $salon->setServer($server);
            $salon->addAlliance($ally);
            $em->persist($salon);

            $salonPublic = new Salon('Ambassade - ' . $ally->getTag(), $server);
            $salonPublic->addAlliance($ally);
            $salonPublic->setServer($server);
            $em->persist($salonPublic);

            $commander->setAlliance($ally);
            $commander->setVoteAlliance(1);
            $commander->setJoinAllianceAt($now);
            $commander->setGrade($grade);
            $em->persist($ally);
            $quest = $commander->checkQuests('ally_join');
            if($quest) {
                $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + $quest->getGain());
                $commander->removeQuest($quest);
            }
            $em->flush();

            return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
        }

        if(($user->getTutorial() == 17)) {
            $user->setTutorial(18);
            $em->flush();
        }

        return $this->render('connected/ally/noAlliance.html.twig', [
            'form_ally' => $form_ally->createView(),
            'usePlanet' => $usePlanet
        ]);
    }

    /**
     * @Route("/supprimer-alliance/{usePlanet}", name="remove_ally", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function removeAllianceAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $now->add(new DateInterval('PT' . 172800 . 'S'));
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $commander->getAlliance();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if ($ally->getPolitic() != 'fascism') {
            foreach ($ally->getCommanders() as $user) {
                $user->setAlliance(null);
                $commander->setGrade(null);
                $user->setPoliticArmement(0);
                $user->setPoliticCostScientist(0);
                $user->setPoliticArmor(0);
                $user->setPoliticBarge(0);
                $user->setPoliticCargo(0);
                $user->setPoliticColonisation(0);
                $user->setPoliticCostSoldier(0);
                $user->setPoliticCostTank(0);
                $user->setPoliticInvade(0);
                $user->setPoliticTrader(0);
                $user->setPoliticPdg(0);
                $user->setPoliticProd(0);
                $user->setPoliticRecycleur(0);
                $user->setPoliticSearch(0);
                $user->setPoliticSoldierAtt(0);
                $user->setPoliticSoldierSale(0);
                $user->setPoliticTankDef(0);
                $user->setPoliticWorker(0);
                $user->setPoliticWorkerDef(0);
                $user->setAllianceBan($now);
            }
            foreach ($ally->getFleets() as $fleet) {
                $fleet->setAlliance(null);
            }
            foreach ($ally->getGrades() as $grade) {
                $em->remove($grade);
            }
            foreach ($ally->getSalons() as $salon) {
                foreach ($salon->getViews() as $view) {
                    $em->remove($view);
                }
                foreach ($salon->getContents() as $content) {
                    $em->remove($content);
                }
                foreach ($salon->getViews() as $view) {
                    $em->remove($view);
                }
                $em->remove($salon);
            }
            foreach ($ally->getExchanges() as $exchange) {
                $em->remove($exchange);
            }

            foreach ($ally->getPnas() as $pna) {
                $em->remove($pna);
            }

            foreach ($ally->getWars() as $war) {
                $em->remove($war);
            }

            foreach ($ally->getAllieds() as $allied) {
                $em->remove($allied);
            }

            foreach ($ally->getOffers() as $offer) {
                $em->remove($offer);
            }
            $em->flush();

            $pnas = $doctrine->getRepository(Pna::class)
                ->createQueryBuilder('p')
                ->where('p.allyTag = :allytag')
                ->setParameters(['allytag' => $ally->getTag()])
                ->getQuery()
                ->getResult();

            $pacts = $doctrine->getRepository(Allied::class)
                ->createQueryBuilder('a')
                ->where('a.allyTag = :allytag')
                ->setParameters(['allytag' => $ally->getTag()])
                ->getQuery()
                ->getResult();

            $wars = $doctrine->getRepository(War::class)
                ->createQueryBuilder('w')
                ->where('w.allyTag = :allytag')
                ->setParameters(['allytag' => $ally->getTag()])
                ->getQuery()
                ->getResult();

            foreach ($pnas as $pna) {
                $em->remove($pna);
            }

            foreach ($pacts as $pact) {
                $em->remove($pact);
            }

            foreach ($wars as $war) {
                $em->remove($war);
            }
            $ally->setImageName(null);
            $em->remove($ally);
            $em->flush();
        }

        return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/quitter-alliance/{usePlanet}", name="leave_ally", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function leaveAllianceAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $now->add(new DateInterval('PT' . 172800 . 'S'));
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $commander->getAlliance();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if ($ally->getPolitic() == 'democrat') {
            if ($commander->getGrade()->getPlacement() == 1 || count($commander->getAlliance()->getCommanders()) == 1) {
                return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
            }
            $user->setPoliticArmement(0);
            $user->setPoliticCostScientist(0);
            $user->setPoliticArmor(0);
            $user->setPoliticBarge(0);
            $user->setPoliticCargo(0);
            $user->setPoliticColonisation(0);
            $user->setPoliticCostSoldier(0);
            $user->setPoliticCostTank(0);
            $user->setPoliticInvade(0);
            $user->setPoliticTrader(0);
            $user->setPoliticPdg(0);
            $user->setPoliticProd(0);
            $user->setPoliticRecycleur(0);
            $user->setPoliticSearch(0);
            $user->setPoliticSoldierAtt(0);
            $user->setPoliticSoldierSale(0);
            $user->setScientistProduction($commander->getScientistProduction() - ($commander->getPoliticSearch() / 10));
            $user->setPoliticTankDef(0);
            $user->setPoliticWorker(0);
            $user->setPoliticWorkerDef(0);
            $user->setAlliance(null);
            $user->setJoinAllianceAt(null);
            $commander->setGrade(null);
            $user->setAllianceBan($now);

            $em->flush();
        }

        return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/exclusion-alliance/{kicked}/{usePlanet}", name="ally_kick", requirements={"kicked"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param User $kicked
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function kickAllianceAction(ManagerRegistry $doctrine, User $kicked, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $now->add(new DateInterval('PT' . 172800 . 'S'));
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $commander->getAlliance();
        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if ($commander->getGrade()->getCanKick() == 1) {
            if ($commander->getGrade()->getCanKick() == 0 || $kicked->getAlliance() != $commander->getAlliance() || ($ally->getPolitic() == 'fascism' and count($ally->getWars()) > 0 or count($ally->getPeaces()) > 0)) {
                return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
            }
            $kicked->setPoliticArmement(0);
            $kicked->setPoliticCostScientist(0);
            $kicked->setPoliticArmor(0);
            $kicked->setPoliticBarge(0);
            $kicked->setPoliticCargo(0);
            $kicked->setPoliticColonisation(0);
            $kicked->setPoliticCostSoldier(0);
            $kicked->setPoliticCostTank(0);
            $kicked->setPoliticInvade(0);
            $kicked->setPoliticTrader(0);
            $kicked->setPoliticPdg(0);
            $kicked->setPoliticProd(0);
            $kicked->setPoliticRecycleur(0);
            $kicked->setPoliticSearch(0);
            $kicked->setPoliticSoldierAtt(0);
            $kicked->setPoliticSoldierSale(0);
            $kicked->setPoliticTankDef(0);
            $kicked->setPoliticWorker(0);
            $kicked->setPoliticWorkerDef(0);
            $ally = $commander->getAlliance();
            $ally->removeUser($kicked);
            $kicked->setAlliance(null);
            $kicked->setJoinAllianceAt(null);
            $kicked->setGrade(null);
            $kicked->setAllianceBan($now);

            $em->flush();
        }

        return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rejoindre-alliance/{offer}/{usePlanet}", name="ally_accept", requirements={"offer"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Offer $offer
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function allyAcceptAction(ManagerRegistry $doctrine, Offer $offer, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if($commander->getAlliance()) {
            return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
        }
        if($commander->getAllianceBan() > $now) {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }

        $ally = $offer->getAlliance();
        $ally->addCommander($commander);
        $user->setAlliance($ally);
        $user->setJoinAllianceAt($now);
        $commander->setGrade($ally->getNewMember());
        $em->remove($offer);
        $quest = $commander->checkQuests('ally_join');
        if($quest) {
            $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + $quest->getGain());
            $commander->removeQuest($quest);
        }

        $em->flush();

        return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/refuser-alliance/{offer}/{usePlanet}", name="ally_refuse", requirements={"offer"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Offer $offer
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function allyRefusetAction(ManagerRegistry $doctrine, Offer $offer, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $em->remove($offer);
        $em->flush();

        return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/annuler-alliance/{offer}/{usePlanet}", name="ally_cancel", requirements={"offer"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Offer $offer
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function allyCanceltAction(ManagerRegistry $doctrine, Offer $offer, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $em->remove($offer);
        $em->flush();

        return $this->redirectToRoute('ally_page_add', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/bye-bye-les-losers/{usePlanet}", name="ally_page_exit", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function exitPageAllianceAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $commander->getAlliance();
        $em = $doctrine->getManager();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }
        if ($ally->getPolitic() != 'fascism') {
            if ($commander->getAlliance()) {
                $ally = $commander->getAlliance();
            } else {
                return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
            }
            $form_allyImage = $this->createForm(AllianceImageType::class, $ally);
            $form_allyImage->handleRequest($request);

            if ($form_allyImage->isSubmitted() && $form_allyImage->isValid()) {
                $this->get("security.csrf.token_manager")->refreshToken("task_item");
                $user->setPoliticArmement(0);
                $user->setPoliticCostScientist(0);
                $user->setPoliticArmor(0);
                $user->setPoliticBarge(0);
                $user->setPoliticCargo(0);
                $user->setPoliticColonisation(0);
                $user->setPoliticCostSoldier(0);
                $user->setPoliticCostTank(0);
                $user->setPoliticInvade(0);
                $user->setPoliticTrader(0);
                $user->setPoliticPdg(0);
                $user->setPoliticProd(0);
                $user->setPoliticRecycleur(0);
                $user->setPoliticSearch(0);
                $user->setPoliticSoldierAtt(0);
                $user->setScientistProduction($commander->getScientistProduction() - ($commander->getPoliticSearch() / 10));
                $user->setPoliticSoldierSale(0);
                $user->setPoliticTankDef(0);
                $user->setPoliticWorker(0);
                $user->setPoliticWorkerDef(0);
                $em->persist($commander);
                $em->flush();

                return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
            }
        }

        return $this->render('connected/ally/exit.html.twig', [
            'form_allyImage' => $form_allyImage->createView(),
            'usePlanet' => $usePlanet,
        ]);
    }

    /**
     * @Route("/reserve-commune/{usePlanet}", name="ally_page_bank", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function bankPageAllianceAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $em = $doctrine->getManager();
        $now = new DateTime();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if($commander->getAlliance()) {
            $ally = $commander->getAlliance();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }
        $exchanges = $doctrine->getRepository(Exchange::class)
            ->createQueryBuilder('e')
            ->andWhere('e.ally = :ally')
            ->setParameters(['ally' => $commander->getAlliance()])
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $form_exchange = $this->createForm(ExchangeType::class);
        $form_exchange->handleRequest($request);

        if ($form_exchange->isSubmitted() && $form_exchange->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $amountExchange = abs($form_exchange->get('amount')->getData());
            if ($form_exchange->get('valueType')->getData() == 1) {
                if ($form_exchange->get('exchangeType')->getData() == 1) {
                    if ($amountExchange <= $commander->getBitcoin()) {
                        $commander->setBitcoin($commander->getBitcoin() - $amountExchange);
                        $ally->setBitcoin($ally->getBitcoin() + $amountExchange);
                        $exchange = new Exchange($ally, $commander->getUsername(), 0, 1, $amountExchange, $form_exchange->get('content')->getData());
                        $em->persist($exchange);
                    }
                } else {
                    if ($amountExchange <= $ally->getBitcoin()) {
                        $acepted = $commander->getGrade()->getPlacement() == 1 || $ally->getPolitic() == 'communism' ? 1 : 0;
                        $exchange = new Exchange($ally, $commander->getUsername(), 0, $acepted, -$amountExchange, $form_exchange->get('content')->getData());
                        if ($acepted) {
                            $commander->setBitcoin($commander->getBitcoin() + $amountExchange);
                            $ally->setBitcoin($ally->getBitcoin() - $amountExchange);
                        }
                        $em->persist($exchange);
                    }
                }
            } else {
                if($form_exchange->get('exchangeType')->getData() == 1) {
                    if($amountExchange <= $commander->getRank()->getWarPoint()) {
                        $commander->getRank()->setWarPoint(($commander->getRank()->getWarPoint() - $amountExchange));
                        $ally->setPdg($ally->getPdg() + $amountExchange);
                        $exchange = new Exchange($ally, $commander->getUsername(), 1, 1, $amountExchange, $form_exchange->get('content')->getData());
                        $em->persist($exchange);
                    }
                } else {
                    if($amountExchange <= $ally->getPdg()) {
                        $acepted = $commander->getGrade()->getPlacement() == 1 || $ally->getPolitic() == 'communism' ? 1 : 0;
                        $exchange = new Exchange($ally, $commander->getUsername(), 1, $acepted, -$amountExchange, $form_exchange->get('content')->getData());
                        if ($acepted) {
                            $commander->getRank()->setWarPoint(($commander->getRank()->getWarPoint() + $amountExchange));
                            $ally->setPdg($ally->getPdg() - $amountExchange);
                        }
                        $em->persist($exchange);
                    }
                }
            }
            $em->flush();
            return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->render('connected/ally/bank.html.twig', [
            'form_exchange' => $form_exchange->createView(),
            'usePlanet' => $usePlanet,
            'exchanges' => $exchanges,
        ]);
    }

    /**
     * @Route("/accepter-echange/{id}/{usePlanet}", name="ally_accept_exchange", requirements={"id"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Exchange $id
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function allyAcceptExchangeAction(ManagerRegistry $doctrine, Exchange $id, Planet $usePlanet): RedirectResponse
    {
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $em = $doctrine->getManager();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }
        $userExchange = $doctrine->getRepository(User::class)->findOneByUsername($id->getName());

        if($commander->getAlliance() && $commander->getGrade()->getPlacement() == 1) {
            $amountExchange = abs($id->getAmount());
            if ($id->getType() == 0 && $amountExchange <= $id->getAlliance()->getBitcoin()) {
                $userExchange->setBitcoin($commander->getBitcoin() + $id->getAmount());
                $id->getAlliance()->setBitcoin($id->getAlliance()->getBitcoin() - $amountExchange);
                $id->setAccepted(1);
                $em->flush();
            } elseif ($amountExchange <= $id->getAlliance()->getPdg()) {
                $userExchange->getRank()->setWarPoint(($userExchange->getRank()->getWarPoint() + $id->getAmount()));
                $id->getAlliance()->setPdg($id->getAlliance()->getPdg() - $amountExchange);
                $id->setAccepted(1);
                $em->flush();
            }
        } else {
            return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/refuser-echange/{id}/{usePlanet}", name="ally_refuse_exchange", requirements={"id"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Exchange $id
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function allyRefuseExchangeAction(ManagerRegistry $doctrine, Exchange $id, Planet $usePlanet): RedirectResponse
    {
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $em = $doctrine->getManager();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if($commander->getAlliance() && $commander->getGrade()->getPlacement() == 1) {
            $em->remove($id);
            $em->flush();
        } else {
            return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/ajouter-des-membres/{usePlanet}", name="ally_page_add", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     */
    public function addPageAllianceAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();
        $em = $doctrine->getManager();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        $maxMembers = count($commander->getAlliance()->getCommanders()) + count($commander->getAlliance()->getOffers());
        if($commander->getAlliance()) {
            $ally = $commander->getAlliance();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_allyAdd = $this->createForm(AllianceAddType::class);
        $form_allyAdd->handleRequest($request);

        if (($form_allyAdd->isSubmitted() && $form_allyAdd->isValid()) && $commander->getGrade()->getCanRecruit() == 1) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if($maxMembers >= $ally->getMaxMembers()) {
                $this->addFlash("fail", "Vous avez atteint le nombre maximum d'invitations.");
                return $this->redirectToRoute('ally_page_add', ['usePlanet' => $usePlanet->getId()]);
            }
            $userOffer = $doctrine->getRepository(User::class)
                ->createQueryBuilder('u')
                ->leftJoin('u.offers', 'pr')
                ->where('u.username = :username')
                ->andWhere('c.ally is null')
                ->andWhere('pr.ally is null or pr.ally != :ally')
                ->setParameters(['username' => $form_allyAdd->get('nameUser')->getData(), 'ally' => $commander->getAlliance()])
                ->getQuery()
                ->getOneOrNullResult();

            if($userOffer) {
                $offer = new Offer($ally, $userOffer);
                $em->persist($offer);
                $ally->addOffer($offer);
                $userOffer->addOffer($offer);
                $em->flush();
            }
            return $this->redirectToRoute('ally_page_add', ['usePlanet' => $usePlanet->getId()]);
        }

        $activityAt = new DateTime();
        $activityAt->sub(new DateInterval('PT' . 5184000 . 'S'));
        $usersRecruitable = $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->join('c.planets', 'p')
            ->leftJoin('c.offers', 'pr')
            ->select('c.username, c.id, count(DISTINCT p) as planets, c.imageName')
            ->groupBy('c.id')
            ->where('c.activityAt > :date')
            ->andWhere('c.ally is null')
            ->andWhere('c.rank is not null')
            ->andWhere('pr is null')
            ->setParameters(['date' => $activityAt])
            ->orderBy('c.activityAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('connected/ally/add.html.twig', [
            'usePlanet' => $usePlanet,
            'form_allyAdd' => $form_allyAdd->createView(),
            'usersRecrutable' => $usersRecruitable,
        ]);
    }

    /**
     * @Route("/alliance-level/{usePlanet}", name="ally_level", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function allylevelAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $commander->getAlliance();
        $em = $doctrine->getManager();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if($ally->getLevel() == 10) {
            return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
        }
        $array = $ally->getLevelCost();
        if($commander->getGrade()->getPlacement() == 1 && $ally->getBitcoin() >= $array[1] && $ally->getPdg() >= $array[2]) {
            $ally->setLevel($ally->getLevel() + 1);
            $ally->setMaxMembers($array[0]);
            $ally->setBitcoin($ally->getBitcoin() - $array[1]);
            $ally->setPdg($ally->getPdg() - $array[2]);
            $em->flush();
        } else {
            return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/administration-alliance/{usePlanet}", name="ally_page_admin", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function adminPageAllianceAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $em = $doctrine->getManager();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if($commander->getAlliance()) {
            $ally = $commander->getAlliance();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_allyDecon = $this->createForm(AllianceDefconType::class,$ally);
        $form_allyDecon->handleRequest($request);

        $form_allyGrade = $this->createForm(AllianceGradeType::class);
        $form_allyGrade->handleRequest($request);

        if (($form_allyDecon->isSubmitted() && $form_allyDecon->isValid())) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $em->flush();
        }

        if (($form_allyGrade->isSubmitted() && $form_allyGrade->isValid()) && $ally->getPolitic() != 'fascism') {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $grade = new Grade($ally, $form_allyGrade->get('name')->getData(), $form_allyGrade->get('placement')->getData(), $form_allyGrade->get('canRecruit')->getData(), $form_allyGrade->get('canKick')->getData(), $form_allyGrade->get('canWar')->getData(), $form_allyGrade->get('canPeace')->getData(), $form_allyGrade->get('canEdit')->getData(), $form_allyGrade->get('seeMembers')->getData(), $form_allyGrade->get('useFleets')->getData());
            if ($commander->getAlliance()->getPolitic() == 'communism') {
                $grade->setPlacement(1);
            }

            $em->persist($grade);
            $ally->addGrade($grade);
            $em->flush();

            return $this->redirectToRoute('ally_page_admin', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->render('connected/ally/admin.html.twig', [
            'usePlanet' => $usePlanet,
            'form_allyGrade' => $form_allyGrade->createView(),
            'form_allyDefcon' => $form_allyDecon->createView()
        ]);
    }

    /**
     * @Route("/ambassade-interne/{usePlanet}", name="ally_page_pacts", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     */
    public function pactPageAllianceAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();
        $em = $doctrine->getManager();

        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if($commander->getAlliance()) {
            $ally = $commander->getAlliance();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }
        $waitingPna = $doctrine->getRepository(Pna::class)
            ->createQueryBuilder('pna')
            ->where('pna.allyTag = :tag')
            ->andWhere('pna.accepted = false')
            ->setParameters(['tag' => $ally->getTag()])
            ->getQuery()
            ->getResult();

        $waitingAllied = $doctrine->getRepository(Allied::class)
            ->createQueryBuilder('al')
            ->where('al.allyTag = :tag')
            ->andWhere('al.accepted = false')
            ->setParameters(['tag' => $ally->getTag()])
            ->getQuery()
            ->getResult();

        $form_allyPact = $this->createForm(AlliancePactType::class);
        $form_allyPact->handleRequest($request);


        if (($form_allyPact->isSubmitted() && $form_allyPact->isValid())) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $allyPact = $doctrine->getRepository(Alliance::class, ['usePlanet' => $usePlanet->getId()])
                ->createQueryBuilder('a')
                ->where('a.tag = :tag')
                ->setParameter('tag', $form_allyPact->get('allyName')->getData())
                ->getQuery()
                ->getOneOrNullResult();

            if((!$allyPact || $commander->getAlliance()->getAlreadyPact($allyPact->getTag())) || $allyPact == $ally) {
                return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
            }
            if($form_allyPact->get('pactType')->getData() == 2 && $commander->getGrade()->getCanPeace() == 1) {
                $pna = new Pna($ally, $allyPact->getTag(), false);
                $em->persist($pna);
                $ally->addAlliancePna($pna);
            } elseif($form_allyPact->get('pactType')->getData() == 1  && $commander->getGrade()->getCanPeace() == 1) {
                if ($ally->getPolitic() == $allyPact->getPolitic() || $ally->getPolitic() == 'democrat' || $allyPact->getPolitic() == 'democrat') {
                    $allied = new Allied($ally, $allyPact->getTag(), false);
                    $em->persist($allied);
                    $ally->addAllianceAllied($allied);
                } else {
                    $this->addFlash("fail", "La politique de cette alliance vous est hostile.");
                    return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
                }
            } elseif($form_allyPact->get('pactType')->getData() == 3 && $commander->getGrade()->getCanWar() == 1) {
                $war = new War($ally, $allyPact->getTag(), true);
                $war2 = new War($allyPact, $ally->getTag(), true);
                $em->persist($war);
                $em->persist($war2);
                $ally->addAllianceWar($war);
                $allyPact->addAllianceWar($war2);
            }
            $em->flush();
            return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
        }

        $allAlliances = $doctrine->getRepository(Alliance::class)->findAll();

        return $this->render('connected/ally/pact.html.twig', [
            'waitingPna' => $waitingPna,
            'usePlanet' => $usePlanet,
            'waitingAllied' => $waitingAllied,
            'form_allyPact' => $form_allyPact->createView(),
            'allAlliances' => $allAlliances
        ]);
    }
}