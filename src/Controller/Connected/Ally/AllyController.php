<?php

namespace App\Controller\Connected\Ally;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\UserAllyType;
use App\Form\Front\AllyImageType;
use App\Form\Front\AllyAddType;
use App\Form\Front\AllyPactType;
use App\Form\Front\AllyGradeType;
use App\Form\Front\AllyDefconType;
use App\Form\Front\VoteType;
use App\Form\Front\ExchangeType;
use App\Form\Front\UserAttrGradeType;
use App\Entity\Grade;
use App\Entity\Ally;
use App\Entity\Proposal;
use App\Entity\Exchange;
use App\Entity\Pna;
use App\Entity\Allied;
use App\Entity\User;
use App\Entity\War;
use App\Entity\Planet;
use DateTime;
use DateTimeZone;
use Dateinterval;
use App\Entity\Salon;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class AllyController extends AbstractController
{
    /**
     * @Route("/alliance/{usePlanet}", name="ally", requirements={"usePlanet"="\d+"})
     */
    public function allyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($user->getAlly()) {
            $ally = $user->getAlly();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }
        $form_allyImage = $this->createForm(AllyImageType::class, $ally);
        $form_allyImage->handleRequest($request);

        $form_vote = $this->createForm(VoteType::class, null, ["allyId" => $user->getAlly()->getId()]);
        $form_vote->handleRequest($request);

        if ($form_allyImage->isSubmitted() && $form_allyImage->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $ally->setImageName(null);
            $em->flush();
        }

        if ($form_vote->isSubmitted() && $form_vote->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if ($user->getVoteName()) {
                $unVoteUser = $em->getRepository('App:User')->findOneBy(['username' => $user->getVoteName()]);
                $unVoteUser->setVoteAlly($unVoteUser->getVoteAlly() - 1);
            }
            $user->setVoteName($form_vote->get('user')->getData()->getUsername());
            $form_vote->get('user')->getData()->setVoteAlly($form_vote->get('user')->getData()->getVoteAlly() + 1);
            $em->flush();

            $leader = $em->getRepository('App:User')
                ->createQueryBuilder('u')
                ->join('u.grade', 'g')
                ->where('g.placement = :one')
                ->andWhere('u.ally = :ally')
                ->setParameters(['one' => 1, 'ally' => $user->getAlly()])
                ->getQuery()
                ->getOneOrNullResult();

            if ($leader) {
                $newLeader = $em->getRepository('App:User')
                    ->createQueryBuilder('u')
                    ->where('u.voteAlly > :vote and u.id != :user')
                    ->andWhere('u.ally = :ally')
                    ->setParameters(['vote' => $leader->getVoteAlly(), 'ally' => $user->getAlly(), 'user' => $leader->getId()])
                    ->orderBy('u.voteAlly', 'DESC')
                    ->getQuery()
                    ->getOneOrNullResult();

                if ($newLeader) {
                    $tmpGrade = $newLeader->getGrade();
                    $newLeader->setGrade($leader->getGrade());
                    $leader->setGrade($tmpGrade);
                    $em->flush();
                }
            } else {
                $newLeader = $em->getRepository('App:User')
                    ->createQueryBuilder('u')
                    ->andWhere('u.ally = :ally')
                    ->setParameters(['ally' => $user->getAlly()])
                    ->orderBy('u.voteAlly', 'DESC')
                    ->getQuery()
                    ->setMaxResults(1)
                    ->getOneOrNullResult();

                $tmpGrade = $em->getRepository('App:Grade')
                    ->createQueryBuilder('g')
                    ->where('g.placement = :top')
                    ->andWhere('g.ally = :ally')
                    ->setParameters(['top' => 1, 'ally' => $user->getAlly()])
                    ->getQuery()
                    ->setMaxResults(1)
                    ->getOneOrNullResult();

                if ($newLeader && $tmpGrade) {
                    $newLeader->setGrade($tmpGrade);
                    $em->flush();
                }
            }
        }

        $userVotes = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->where('u.voteAlly > :vote')
            ->andWhere('u.ally = :ally')
            ->setParameters(['vote' => 0, 'ally' => $user->getAlly()])
            ->orderBy('u.voteAlly', 'DESC')
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
     */
    public function allyAddUserGradeAction(Request $request, User $newGradeUser, Planet $usePlanet)
    {
        $user = $this->getUser();
        $ally = $user->getAlly();
        $em = $this->getDoctrine()->getManager();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $form_userAttrGrade = $this->createForm(UserAttrGradeType::class, null, ["allyId" => $user->getAlly()->getId()]);
        $form_userAttrGrade->handleRequest($request);

        if (($form_userAttrGrade->isSubmitted() && $form_userAttrGrade->isValid())) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if ($ally->getPolitic() == 'fascism' && $form_userAttrGrade->get('grade')->getData()->getPlacement() == 1) {
            } else {
                if (($user->getGrade()->getPlacement() == 1 && $newGradeUser->getId() == $user->getId()) && $form_userAttrGrade->get('grade')->getData()->getPlacement() != 1) {
                    return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
                }
                if ($newGradeUser != $user && $form_userAttrGrade->get('grade')->getData()->getPlacement() == 1 && $ally->getPolitic() != 'communism') {
                    $grade = $em->getRepository('App:Grade')->findOneBy(['ally' => $ally->getId(), 'placement' => 5]);
                    $user->setGrade($grade);
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
     */
    public function noAllyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($user->getAlly()) {
            return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
        } else {
            $ally = new Ally();
        }

        $form_ally = $this->createForm(UserAllyType::class, $ally);
        $form_ally->handleRequest($request);

        if ($form_ally->isSubmitted() && $form_ally->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if($user->getAllyBan() > $now) {
                return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
            }
            $grade = new Grade();

            $ally->addUser($user);
            $ally->setCreatedAt($now);
            $ally->setMaxMembers(1);
            $ally->setImageName('democrat.jpg');
            $ally->setBitcoin(1);
            $ally->setPdg(1);
            $em->persist($ally);
            $em->flush();
            $mGrade = new Grade();
            $mGrade->setAlly($ally);
            $mGrade->addUser($user);

            $grade->setAlly($ally);
            if ($form_ally->get('politic')->getData() == 'democrat') {
                $grade->setName("Président");
                $mGrade->setPlacement(5);
                $mGrade->setName("Citoyen");
                $ally->setMaxMembers(3);
                $ally->setImageName('democrat.jpg');
                $ally->setBitcoin(25000);
                $ally->setPdg(0);
            } elseif ($form_ally->get('politic')->getData() == 'fascism') {
                $grade->setName("Führer");
                $sGrade = new Grade();
                $sGrade->setAlly($ally);
                $sGrade->addUser($user);
                $sGrade->setPlacement(2);
                $sGrade->setName("Reichsführer");
                $sGrade->setCanRecruit(true);
                $sGrade->setCanKick(false);
                $sGrade->setCanWar(false);
                $sGrade->setCanPeace(false);
                $mGrade->setPlacement(5);
                $mGrade->setName("Soldat");
                $ally->setMaxMembers(2);
                $ally->setImageName('fascism.jpg');
                $ally->setBitcoin(15000);
                $ally->setPdg(2000);
                $em->persist($sGrade);
            } elseif ($form_ally->get('politic')->getData() == 'communism'){
                $grade->setName("Père des peuples");
                $mGrade->setPlacement(1);
                $mGrade->setName("Camarade");
                $ally->setMaxMembers(4);
                $ally->setImageName('communism.jpg');
                $ally->setBitcoin(0);
                $ally->setPdg(250);
                $ally->setTaxe(50);
            }
            $grade->addUser($user);
            $grade->setPlacement(1);
            $grade->setCanRecruit(true);
            $grade->setCanKick(true);
            $grade->setCanWar(true);
            $grade->setCanPeace(true);
            $em->persist($grade);
            $em->persist($mGrade);

            $ally->addGrade($mGrade);

            $salon = new Salon();
            $salon->setName($ally->getName());
            $salon->addAlly($ally);
            $em->persist($salon);

            $salonPublic = new Salon();
            $salonPublic->setName('Ambassade - ' . $ally->getSigle());
            $salonPublic->addAlly($ally);
            $em->persist($salonPublic);

            $ally->addGrade($grade);
            $user->setAlly($ally);
            $user->setVoteAlly(1);
            $user->setJoinAllyAt($now);
            $user->setGrade($grade);
            $em->persist($ally);
            $quest = $user->checkQuests('ally_join');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }
            $em->flush();

            return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
        }

        if(($user->getTutorial() == 17)) {
            $user->setTutorial(18);
            $em->flush();
        }

        return $this->render('connected/ally/noAlly.html.twig', [
            'form_ally' => $form_ally->createView(),
            'usePlanet' => $usePlanet
        ]);
    }

    /**
     * @Route("/supprimer-alliance/{usePlanet}", name="remove_ally", requirements={"usePlanet"="\d+"})
     */
    public function removeAllyAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $now->add(new DateInterval('PT' . 172800 . 'S'));
        $user = $this->getUser();
        $ally = $user->getAlly();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if ($ally->getPolitic() != 'fascism') {
            foreach ($ally->getUsers() as $user) {
                $user->setAlly(null);
                $user->setGrade(null);
                $user->setPoliticArmement(0);
                $user->setPoliticCostScientist(0);
                $user->setPoliticArmor(0);
                $user->setPoliticBarge(0);
                $user->setPoliticCargo(0);
                $user->setPoliticColonisation(0);
                $user->setPoliticCostSoldier(0);
                $user->setPoliticCostTank(0);
                $user->setPoliticInvade(0);
                $user->setPoliticMerchant(0);
                $user->setPoliticPdg(0);
                $user->setPoliticProd(0);
                $user->setPoliticRecycleur(0);
                $user->setPoliticSearch(0);
                $user->setPoliticSoldierAtt(0);
                $user->setPoliticSoldierSale(0);
                $user->setPoliticTankDef(0);
                $user->setPoliticWorker(0);
                $user->setPoliticWorkerDef(0);
                $user->setAllyBan($now);
            }
            foreach ($ally->getFleets() as $fleet) {
                $fleet->setAlly(null);
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

            foreach ($ally->getProposals() as $proposal) {
                $em->remove($proposal);
            }
            $em->flush();

            $pnas = $em->getRepository('App:Pna')
                ->createQueryBuilder('p')
                ->where('p.allyTag = :allytag')
                ->setParameters(['allytag' => $ally->getSigle()])
                ->getQuery()
                ->getResult();

            $pacts = $em->getRepository('App:Allied')
                ->createQueryBuilder('a')
                ->where('a.allyTag = :allytag')
                ->setParameters(['allytag' => $ally->getSigle()])
                ->getQuery()
                ->getResult();

            $wars = $em->getRepository('App:War')
                ->createQueryBuilder('w')
                ->where('w.allyTag = :allytag')
                ->setParameters(['allytag' => $ally->getSigle()])
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
     */
    public function leaveAllyAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $now->add(new DateInterval('PT' . 172800 . 'S'));
        $user = $this->getUser();
        $ally = $user->getAlly();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if ($ally->getPolitic() == 'democrat') {
            if ($user->getGrade()->getPlacement() == 1 || count($user->getAlly()->getUsers()) == 1) {
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
            $user->setPoliticMerchant(0);
            $user->setPoliticPdg(0);
            $user->setPoliticProd(0);
            $user->setPoliticRecycleur(0);
            $user->setPoliticSearch(0);
            $user->setPoliticSoldierAtt(0);
            $user->setPoliticSoldierSale(0);
            $user->setScientistProduction($user->getScientistProduction() - ($user->getPoliticSearch() / 10));
            $user->setPoliticTankDef(0);
            $user->setPoliticWorker(0);
            $user->setPoliticWorkerDef(0);
            $user->setAlly(null);
            $user->setJoinAllyAt(null);
            $user->setGrade(null);
            $user->setAllyBan($now);

            $em->flush();
        }

        return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/exclusion-alliance/{kicked}/{usePlanet}", name="ally_kick", requirements={"kicked"="\d+", "usePlanet"="\d+"})
     */
    public function kickAllyAction(User $kicked, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $now->add(new DateInterval('PT' . 172800 . 'S'));
        $user = $this->getUser();
        $ally = $user->getAlly();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if ($user->getGrade()->getCanKick() == 1) {
            if ($user->getGrade()->getCanKick() == 0 || $kicked->getAlly() != $user->getAlly() || ($ally->getPolitic() == 'fascism' and count($ally->getWars()) > 0 or count($ally->getPeaces()) > 0)) {
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
            $kicked->setPoliticMerchant(0);
            $kicked->setPoliticPdg(0);
            $kicked->setPoliticProd(0);
            $kicked->setPoliticRecycleur(0);
            $kicked->setPoliticSearch(0);
            $kicked->setPoliticSoldierAtt(0);
            $kicked->setPoliticSoldierSale(0);
            $kicked->setPoliticTankDef(0);
            $kicked->setPoliticWorker(0);
            $kicked->setPoliticWorkerDef(0);
            $ally = $user->getAlly();
            $ally->removeUser($kicked);
            $kicked->setAlly(null);
            $kicked->setJoinAllyAt(null);
            $kicked->setGrade(null);
            $kicked->setAllyBan($now);

            $em->flush();
        }

        return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rejoindre-alliance/{proposal}/{usePlanet}", name="ally_accept", requirements={"proposal"="\d+", "usePlanet"="\d+"})
     */
    public function allyAcceptAction(Proposal $proposal, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($user->getAlly()) {
            return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
        }
        if($user->getAllyBan() > $now) {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }

        $ally = $proposal->getAlly();
        $ally->addUser($user);
        $user->setAlly($ally);
        $user->setJoinAllyAt($now);
        $user->setGrade($ally->getNewMember());
        $em->remove($proposal);
        $quest = $user->checkQuests('ally_join');
        if($quest) {
            $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
            $user->removeQuest($quest);
        }

        $em->flush();

        return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/refuser-alliance/{proposal}/{usePlanet}", name="ally_refuse", requirements={"proposal"="\d+", "usePlanet"="\d+"})
     */
    public function allyRefusetAction(Proposal $proposal, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $em->remove($proposal);
        $em->flush();

        return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/annuler-alliance/{proposal}/{usePlanet}", name="ally_cancel", requirements={"proposal"="\d+", "usePlanet"="\d+"})
     */
    public function allyCanceltAction(Proposal $proposal, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $em->remove($proposal);
        $em->flush();

        return $this->redirectToRoute('ally_page_add', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/bye-bye-les-losers/{usePlanet}", name="ally_page_exit", requirements={"usePlanet"="\d+"})
     */
    public function exitPageAllyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $ally = $user->getAlly();
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }
        if ($ally->getPolitic() != 'fascism') {
            if ($user->getAlly()) {
                $ally = $user->getAlly();
            } else {
                return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
            }
            $form_allyImage = $this->createForm(AllyImageType::class, $ally);
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
                $user->setPoliticMerchant(0);
                $user->setPoliticPdg(0);
                $user->setPoliticProd(0);
                $user->setPoliticRecycleur(0);
                $user->setPoliticSearch(0);
                $user->setPoliticSoldierAtt(0);
                $user->setScientistProduction($user->getScientistProduction() - ($user->getPoliticSearch() / 10));
                $user->setPoliticSoldierSale(0);
                $user->setPoliticTankDef(0);
                $user->setPoliticWorker(0);
                $user->setPoliticWorkerDef(0);
                $em->persist($user);
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
     */
    public function bankPageAllyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($user->getAlly()) {
            $ally = $user->getAlly();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }
        $exchanges = $em->getRepository('App:Exchange')
            ->createQueryBuilder('e')
            ->andWhere('e.ally = :ally')
            ->setParameters(['ally' => $user->getAlly()])
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
                    if ($amountExchange <= $user->getBitcoin()) {
                        $user->setBitcoin($user->getBitcoin() - $amountExchange);
                        $ally->setBitcoin($ally->getBitcoin() + $amountExchange);
                        $exchange = new Exchange();
                        $exchange->setAlly($ally);
                        $exchange->setCreatedAt($now);
                        $exchange->setType(0);
                        $exchange->setAccepted(1);
                        $exchange->setContent($form_exchange->get('content')->getData());
                        $exchange->setAmount($amountExchange);
                        $exchange->setName($user->getUserName());
                        $em->persist($exchange);
                    }
                } else {
                    if ($amountExchange <= $ally->getBitcoin()) {
                        $exchange = new Exchange();
                        $exchange->setAlly($ally);
                        $exchange->setCreatedAt($now);
                        $exchange->setType(0);
                        if ($user->getGrade()->getPlacement() == 1 || $ally->getPolitic() == 'communism') {
                            $exchange->setAccepted(1);
                            $user->setBitcoin($user->getBitcoin() + $amountExchange);
                            $ally->setBitcoin($ally->getBitcoin() - $amountExchange);
                        } else {
                            $exchange->setAccepted(0);
                        }
                        $exchange->setContent($form_exchange->get('content')->getData());
                        $exchange->setAmount(-$amountExchange);
                        $exchange->setName($user->getUserName());
                        $em->persist($exchange);
                    }
                }
            } else {
                if($form_exchange->get('exchangeType')->getData() == 1) {
                    if($amountExchange <= $user->getRank()->getWarPoint()) {
                        $user->getRank()->setWarPoint(($user->getRank()->getWarPoint() - $amountExchange));
                        $ally->setPdg($ally->getPdg() + $amountExchange);
                        $exchange = new Exchange();
                        $exchange->setAlly($ally);
                        $exchange->setType(1);
                        $exchange->setAccepted(1);
                        $exchange->setContent($form_exchange->get('content')->getData());
                        $exchange->setCreatedAt($now);
                        $exchange->setAmount($amountExchange);
                        $exchange->setName($user->getUserName());
                        $em->persist($exchange);
                    }
                } else {
                    if($amountExchange <= $ally->getPdg()) {
                        $exchange = new Exchange();
                        $exchange->setAlly($ally);
                        $exchange->setType(1);
                        if ($user->getGrade()->getPlacement() == 1 || $ally->getPolitic() == 'communism') {
                            $exchange->setAccepted(1);
                            $user->getRank()->setWarPoint(($user->getRank()->getWarPoint() + $amountExchange));
                            $ally->setPdg($ally->getPdg() - $amountExchange);
                        } else {
                            $exchange->setAccepted(0);
                        }
                        $exchange->setContent($form_exchange->get('content')->getData());
                        $exchange->setCreatedAt($now);
                        $exchange->setAmount(-$amountExchange);
                        $exchange->setName($user->getUserName());
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
     */
    public function allyAcceptExchangeAction(Exchange $id, Planet $usePlanet)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }
        $userExchange = $em->getRepository('App:User')->findOneByUsername($id->getName());

        if($user->getAlly() && $user->getGrade()->getPlacement() == 1) {
            $amountExchange = abs($id->getAmount());
            if ($id->getType() == 0 && $amountExchange <= $id->getAlly()->getBitcoin()) {
                $userExchange->setBitcoin($user->getBitcoin() + $id->getAmount());
                $id->getAlly()->setBitcoin($id->getAlly()->getBitcoin() - $amountExchange);
                $id->setAccepted(1);
                $em->flush();
            } elseif ($amountExchange <= $id->getAlly()->getPdg()) {
                $userExchange->getRank()->setWarPoint(($userExchange->getRank()->getWarPoint() + $id->getAmount()));
                $id->getAlly()->setPdg($id->getAlly()->getPdg() - $amountExchange);
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
     */
    public function allyRefuseExchangeAction(Exchange $id, Planet $usePlanet)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($user->getAlly() && $user->getGrade()->getPlacement() == 1) {
            $em->remove($id);
            $em->flush();
        } else {
            return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/ajouter-des-membres/{usePlanet}", name="ally_page_add", requirements={"usePlanet"="\d+"})
     */
    public function addPageAllyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $maxMembers = count($user->getAlly()->getUsers()) + count($user->getAlly()->getProposals());
        if($user->getAlly()) {
            $ally = $user->getAlly();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_allyAdd = $this->createForm(AllyAddType::class);
        $form_allyAdd->handleRequest($request);

        if (($form_allyAdd->isSubmitted() && $form_allyAdd->isValid()) && $user->getGrade()->getCanRecruit() == 1) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if($maxMembers >= $ally->getMaxMembers()) {
                $this->addFlash("fail", "Vous avez atteint le nombre maximum d'invitations.");
                return $this->redirectToRoute('ally_page_add', ['usePlanet' => $usePlanet->getId()]);
            }
            $userProposal = $em->getRepository('App:User')
                ->createQueryBuilder('u')
                ->leftJoin('u.proposals', 'pr')
                ->where('u.username = :username')
                ->andWhere('u.ally is null')
                ->andWhere('pr.ally is null or pr.ally != :ally')
                ->setParameters(['username' => $form_allyAdd->get('nameUser')->getData(), 'ally' => $user->getAlly()])
                ->getQuery()
                ->getOneOrNullResult();

            if($userProposal) {
                $proposal = new Proposal();
                $proposal->setUser($userProposal);
                $proposal->setAlly($ally);
                $proposal->setProposalAt($now);
                $em->persist($proposal);
                $ally->addProposal($proposal);
                $userProposal->addProposal($proposal);
                $em->flush();
            }
            return $this->redirectToRoute('ally_page_add', ['usePlanet' => $usePlanet->getId()]);
        }

        $lastActivity = new DateTime();
        $lastActivity->setTimezone(new DateTimeZone('Europe/Paris'));
        $lastActivity->sub(new DateInterval('PT' . 5184000 . 'S'));
        $usersRecruitable = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.planets', 'p')
            ->leftJoin('u.proposals', 'pr')
            ->select('u.username, u.id, count(DISTINCT p) as planets, u.imageName')
            ->groupBy('u.id')
            ->where('u.lastActivity > :date')
            ->andWhere('u.ally is null')
            ->andWhere('u.rank is not null')
            ->andWhere('pr is null')
            ->setParameters(['date' => $lastActivity])
            ->orderBy('u.lastActivity', 'DESC')
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
     */
    public function allylevelAction(Planet $usePlanet)
    {
        $user = $this->getUser();
        $ally = $user->getAlly();
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($ally->getLevel() == 10) {
            return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
        }
        $array = $ally->getLevelCost();
        if($user->getGrade()->getPlacement() == 1 && $ally->getBitcoin() >= $array[1] && $ally->getPdg() >= $array[2]) {
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
     */
    public function adminPageAllyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $grade = new Grade();
        if($user->getAlly()) {
            $ally = $user->getAlly();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_allyDecon = $this->createForm(AllyDefconType::class,$ally);
        $form_allyDecon->handleRequest($request);

        $form_allyGrade = $this->createForm(AllyGradeType::class,$grade);
        $form_allyGrade->handleRequest($request);

        if (($form_allyDecon->isSubmitted() && $form_allyDecon->isValid())) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $em->flush();
        }

        if (($form_allyGrade->isSubmitted() && $form_allyGrade->isValid()) && $ally->getPolitic() != 'fascism') {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if ($user->getAlly()->getPolitic() == 'communism') {
                $grade->setPlacement(1);
            }
            $grade->setAlly($ally);
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
     */
    public function pactPageAllyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($user->getAlly()) {
            $ally = $user->getAlly();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }
        $waitingPna = $em->getRepository('App:Pna')
            ->createQueryBuilder('pna')
            ->where('pna.allyTag = :sigle')
            ->andWhere('pna.accepted = false')
            ->setParameters(['sigle' => $ally->getSigle()])
            ->getQuery()
            ->getResult();

        $waitingAllied = $em->getRepository('App:Allied')
            ->createQueryBuilder('al')
            ->where('al.allyTag = :sigle')
            ->andWhere('al.accepted = false')
            ->setParameters(['sigle' => $ally->getSigle()])
            ->getQuery()
            ->getResult();

        $form_allyPact = $this->createForm(AllyPactType::class);
        $form_allyPact->handleRequest($request);


        if (($form_allyPact->isSubmitted() && $form_allyPact->isValid())) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $allyPact = $em->getRepository('App:Ally', ['usePlanet' => $usePlanet->getId()])
                ->createQueryBuilder('a')
                ->where('a.sigle = :sigle')
                ->setParameter('sigle', $form_allyPact->get('allyName')->getData())
                ->getQuery()
                ->getOneOrNullResult();

            if((!$allyPact || $user->getAlly()->getAlreadyPact($allyPact->getSigle())) || $allyPact == $ally) {
                return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
            }
            if($form_allyPact->get('pactType')->getData() == 2 && $user->getGrade()->getCanPeace() == 1) {
                $pna = new Pna();
                $pna->setAlly($ally);
                $pna->setAllyTag($allyPact->getSigle());
                $pna->setSignedAt($now);
                $em->persist($pna);
                $ally->addAllyPna($pna);
            } elseif($form_allyPact->get('pactType')->getData() == 1  && $user->getGrade()->getCanPeace() == 1) {
                if ($ally->getPolitic() == $allyPact->getPolitic() || $ally->getPolitic() == 'democrat' || $allyPact->getPolitic() == 'democrat') {
                    $allied = new Allied();
                    $allied->setAlly($ally);
                    $allied->setAllyTag($allyPact->getSigle());
                    $allied->setSignedAt($now);
                    $em->persist($allied);
                    $ally->addAllyAllied($allied);
                } else {
                    $this->addFlash("fail", "La politique de cette alliance vous est hostile.");
                    return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
                }
            } elseif($form_allyPact->get('pactType')->getData() == 3 && $user->getGrade()->getCanWar() == 1) {
                $war = new War();
                $war2 = new War();
                $war2->setAlly($allyPact);
                $war2->setAllyTag($ally->getSigle());
                $war2->setSignedAt($now);
                $war2->setAccepted(true);
                $war->setAccepted(true);
                $war->setAlly($ally);
                $war->setAllyTag($allyPact->getSigle());
                $war->setSignedAt($now);
                $em->persist($war);
                $em->persist($war2);
                $ally->addAllyWar($war);
                $allyPact->addAllyWar($war2);
            }
            $em->flush();
            return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
        }

        $allAllys = $em->getRepository('App:Ally')->findAll();

        return $this->render('connected/ally/pact.html.twig', [
            'waitingPna' => $waitingPna,
            'usePlanet' => $usePlanet,
            'waitingAllied' => $waitingAllied,
            'form_allyPact' => $form_allyPact->createView(),
            'allAllys' => $allAllys
        ]);
    }
}