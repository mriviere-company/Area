<?php

namespace App\Controller\Connected\Ally;

use App\Entity\Character;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     */
    public function allyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if($character->getAlly()) {
            $ally = $character->getAlly();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }
        $form_allyImage = $this->createForm(AllyImageType::class, $ally);
        $form_allyImage->handleRequest($request);

        $form_vote = $this->createForm(VoteType::class, null, ["allyId" => $character->getAlly()->getId()]);
        $form_vote->handleRequest($request);

        if ($form_allyImage->isSubmitted() && $form_allyImage->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $ally->setImageName(null);
            $em->flush();
        }

        if ($form_vote->isSubmitted() && $form_vote->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if ($character->getVoteName()) {
                $unVoteUser = $em->getRepository('App:User')->findOneBy(['username' => $character->getVoteName()]);
                $unVoteUser->setVoteAlly($unVoteUser->getVoteAlly() - 1);
            }
            $character->setVoteName($form_vote->get('character')->getData()->getUsername());
            $form_vote->get('character')->getData()->setVoteAlly($form_vote->get('character')->getData()->getVoteAlly() + 1);
            $em->flush();

            $leader = $em->getRepository('App:Character')
                ->createQueryBuilder('c')
                ->join('c.grade', 'g')
                ->where('g.placement = 1')
                ->andWhere('c.ally = :ally')
                ->setParameters(['ally' => $character->getAlly()])
                ->getQuery()
                ->getOneOrNullResult();

            if ($leader) {
                $newLeader = $em->getRepository('App:Character')
                    ->createQueryBuilder('c')
                    ->where('c.voteAlly > :vote and c.id != :character')
                    ->andWhere('c.ally = :ally')
                    ->setParameters(['vote' => $leader->getVoteAlly(), 'ally' => $character->getAlly(), 'character' => $leader->getId()])
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
                $newLeader = $em->getRepository('App:Character')
                    ->createQueryBuilder('c')
                    ->andWhere('c.ally = :ally')
                    ->setParameters(['ally' => $character->getAlly()])
                    ->orderBy('c.voteAlly', 'DESC')
                    ->getQuery()
                    ->setMaxResults(1)
                    ->getOneOrNullResult();

                $tmpGrade = $em->getRepository('App:Grade')
                    ->createQueryBuilder('g')
                    ->where('g.placement = 1')
                    ->andWhere('g.ally = :ally')
                    ->setParameters(['ally' => $character->getAlly()])
                    ->getQuery()
                    ->setMaxResults(1)
                    ->getOneOrNullResult();

                if ($newLeader && $tmpGrade) {
                    $newLeader->setGrade($tmpGrade);
                    $em->flush();
                }
            }
        }

        $userVotes = $em->getRepository('App:Character')
            ->createQueryBuilder('c')
            ->where('c.voteAlly > 0')
            ->andWhere('c.ally = :ally')
            ->setParameters(['ally' => $character->getAlly()])
            ->orderBy('c.voteAlly', 'DESC')
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
     * @param Request $request
     * @param Character $newGradeUser
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function allyAddUserGradeAction(Request $request, Character $newGradeUser, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $character->getAlly();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $form_userAttrGrade = $this->createForm(UserAttrGradeType::class, null, ["allyId" => $character->getAlly()->getId()]);
        $form_userAttrGrade->handleRequest($request);

        if (($form_userAttrGrade->isSubmitted() && $form_userAttrGrade->isValid())) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if ($ally->getPolitic() == 'fascism' && $form_userAttrGrade->get('grade')->getData()->getPlacement() == 1) {
            } else {
                if (($character->getGrade()->getPlacement() == 1 && $newGradeUser->getId() == $character->getId()) && $form_userAttrGrade->get('grade')->getData()->getPlacement() != 1) {
                    return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
                }
                if ($newGradeUser != $character && $form_userAttrGrade->get('grade')->getData()->getPlacement() == 1 && $ally->getPolitic() != 'communism') {
                    $grade = $em->getRepository('App:Grade')->findOneBy(['ally' => $ally->getId(), 'placement' => 5]);
                    $character->setGrade($grade);
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
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function noAllyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if($character->getAlly()) {
            return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
        } else {
            $ally = new Ally();
        }

        $form_ally = $this->createForm(UserAllyType::class, $ally);
        $form_ally->handleRequest($request);

        if ($form_ally->isSubmitted() && $form_ally->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if($character->getAllyBan() > $now) {
                return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
            }

            $ally->addCharacter($character);
            $ally->setMaxMembers(1);
            $ally->setImageName('democrat.jpg');
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
                $ally->setImageName('democrat.jpg');
                $ally->setBitcoin(25000);
                $ally->setPdg(0);
            } elseif ($form_ally->get('politic')->getData() == 'fascism') {
                $grade = new Grade($ally, "Führer", 1, true, true, true, true, true, true, true);
                $sGrade = new Grade($ally, "Reichsführer", 2, true, false, false, false, false, false, false);
                $mGrade = new Grade($ally, "Soldat", 3, false, false, false, false, false, false, false);
                $lGrade = new Grade($ally, "Apirant", 5, false, false, false, false, false, false, false);
                $ally->setMaxMembers(2);
                $ally->setImageName('fascism.jpg');
                $ally->setBitcoin(15000);
                $ally->setPdg(2000);
            } elseif ($form_ally->get('politic')->getData() == 'communism'){
                $grade = new Grade($ally, "Père des peuples", 1, true, true, true, true, true, true, true);
                $mGrade = new Grade($ally, "Nomenklatura", 1, true, true, true, true, true, true, true);
                $sGrade = new Grade($ally, "Camarade", 1, true, true, true, true, true, true, true);
                $lGrade = new Grade($ally, "Goulag", 2, false, false, false, false, false, false, false);
                $ally->setMaxMembers(4);
                $ally->setImageName('communism.jpg');
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
            $salon->addAlly($ally);
            $em->persist($salon);

            $salonPublic = new Salon('Ambassade - ' . $ally->getSigle(), $server);
            $salonPublic->addAlly($ally);
            $salonPublic->setServer($server);
            $em->persist($salonPublic);

            $character->setAlly($ally);
            $character->setVoteAlly(1);
            $character->setJoinAllyAt($now);
            $character->setGrade($grade);
            $em->persist($ally);
            $quest = $character->checkQuests('ally_join');
            if($quest) {
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                $character->removeQuest($quest);
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
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function removeAllyAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->add(new DateInterval('PT' . 172800 . 'S'));
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $character->getAlly();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if ($ally->getPolitic() != 'fascism') {
            foreach ($ally->getCharacters() as $user) {
                $user->setAlly(null);
                $character->setGrade(null);
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
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function leaveAllyAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->add(new DateInterval('PT' . 172800 . 'S'));
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $character->getAlly();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if ($ally->getPolitic() == 'democrat') {
            if ($character->getGrade()->getPlacement() == 1 || count($character->getAlly()->getCharacters()) == 1) {
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
            $user->setScientistProduction($character->getScientistProduction() - ($character->getPoliticSearch() / 10));
            $user->setPoliticTankDef(0);
            $user->setPoliticWorker(0);
            $user->setPoliticWorkerDef(0);
            $user->setAlly(null);
            $user->setJoinAllyAt(null);
            $character->setGrade(null);
            $user->setAllyBan($now);

            $em->flush();
        }

        return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/exclusion-alliance/{kicked}/{usePlanet}", name="ally_kick", requirements={"kicked"="\d+", "usePlanet"="\d+"})
     * @param User $kicked
     * @param Planet $usePlanet
     * @return RedirectResponse
     * @throws Exception
     */
    public function kickAllyAction(User $kicked, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->add(new DateInterval('PT' . 172800 . 'S'));
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $character->getAlly();
        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if ($character->getGrade()->getCanKick() == 1) {
            if ($character->getGrade()->getCanKick() == 0 || $kicked->getAlly() != $character->getAlly() || ($ally->getPolitic() == 'fascism' and count($ally->getWars()) > 0 or count($ally->getPeaces()) > 0)) {
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
            $ally = $character->getAlly();
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
     * @param Proposal $proposal
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function allyAcceptAction(Proposal $proposal, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if($character->getAlly()) {
            return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
        }
        if($character->getAllyBan() > $now) {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }

        $ally = $proposal->getAlly();
        $ally->addCharacter($character);
        $user->setAlly($ally);
        $user->setJoinAllyAt($now);
        $character->setGrade($ally->getNewMember());
        $em->remove($proposal);
        $quest = $character->checkQuests('ally_join');
        if($quest) {
            $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
            $character->removeQuest($quest);
        }

        $em->flush();

        return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/refuser-alliance/{proposal}/{usePlanet}", name="ally_refuse", requirements={"proposal"="\d+", "usePlanet"="\d+"})
     * @param Proposal $proposal
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function allyRefusetAction(Proposal $proposal, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $em->remove($proposal);
        $em->flush();

        return $this->redirectToRoute('ally', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/annuler-alliance/{proposal}/{usePlanet}", name="ally_cancel", requirements={"proposal"="\d+", "usePlanet"="\d+"})
     * @param Proposal $proposal
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function allyCanceltAction(Proposal $proposal, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $em->remove($proposal);
        $em->flush();

        return $this->redirectToRoute('ally_page_add', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/bye-bye-les-losers/{usePlanet}", name="ally_page_exit", requirements={"usePlanet"="\d+"})
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function exitPageAllyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $character->getAlly();
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }
        if ($ally->getPolitic() != 'fascism') {
            if ($character->getAlly()) {
                $ally = $character->getAlly();
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
                $user->setScientistProduction($character->getScientistProduction() - ($character->getPoliticSearch() / 10));
                $user->setPoliticSoldierSale(0);
                $user->setPoliticTankDef(0);
                $user->setPoliticWorker(0);
                $user->setPoliticWorkerDef(0);
                $em->persist($character);
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
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function bankPageAllyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if($character->getAlly()) {
            $ally = $character->getAlly();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }
        $exchanges = $em->getRepository('App:Exchange')
            ->createQueryBuilder('e')
            ->andWhere('e.ally = :ally')
            ->setParameters(['ally' => $character->getAlly()])
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
                    if ($amountExchange <= $character->getBitcoin()) {
                        $character->setBitcoin($character->getBitcoin() - $amountExchange);
                        $ally->setBitcoin($ally->getBitcoin() + $amountExchange);
                        $exchange = new Exchange($ally, $character->getUserName(), 0, 1, $amountExchange, $form_exchange->get('content')->getData());
                        $em->persist($exchange);
                    }
                } else {
                    if ($amountExchange <= $ally->getBitcoin()) {
                        $acepted = $character->getGrade()->getPlacement() == 1 || $ally->getPolitic() == 'communism' ? 1 : 0;
                        $exchange = new Exchange($ally, $character->getUserName(), 0, $acepted, -$amountExchange, $form_exchange->get('content')->getData());
                        if ($acepted) {
                            $character->setBitcoin($character->getBitcoin() + $amountExchange);
                            $ally->setBitcoin($ally->getBitcoin() - $amountExchange);
                        }
                        $em->persist($exchange);
                    }
                }
            } else {
                if($form_exchange->get('exchangeType')->getData() == 1) {
                    if($amountExchange <= $character->getRank()->getWarPoint()) {
                        $character->getRank()->setWarPoint(($character->getRank()->getWarPoint() - $amountExchange));
                        $ally->setPdg($ally->getPdg() + $amountExchange);
                        $exchange = new Exchange($ally, $character->getUserName(), 1, 1, $amountExchange, $form_exchange->get('content')->getData());
                        $em->persist($exchange);
                    }
                } else {
                    if($amountExchange <= $ally->getPdg()) {
                        $acepted = $character->getGrade()->getPlacement() == 1 || $ally->getPolitic() == 'communism' ? 1 : 0;
                        $exchange = new Exchange($ally, $character->getUserName(), 1, $acepted, -$amountExchange, $form_exchange->get('content')->getData());
                        if ($acepted) {
                            $character->getRank()->setWarPoint(($character->getRank()->getWarPoint() + $amountExchange));
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
     * @param Exchange $id
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function allyAcceptExchangeAction(Exchange $id, Planet $usePlanet)
    {
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }
        $userExchange = $em->getRepository('App:User')->findOneByUsername($id->getName());

        if($character->getAlly() && $character->getGrade()->getPlacement() == 1) {
            $amountExchange = abs($id->getAmount());
            if ($id->getType() == 0 && $amountExchange <= $id->getAlly()->getBitcoin()) {
                $userExchange->setBitcoin($character->getBitcoin() + $id->getAmount());
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
     * @param Exchange $id
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function allyRefuseExchangeAction(Exchange $id, Planet $usePlanet)
    {
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if($character->getAlly() && $character->getGrade()->getPlacement() == 1) {
            $em->remove($id);
            $em->flush();
        } else {
            return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/ajouter-des-membres/{usePlanet}", name="ally_page_add", requirements={"usePlanet"="\d+"})
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function addPageAllyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $maxMembers = count($character->getAlly()->getCharacters()) + count($character->getAlly()->getProposals());
        if($character->getAlly()) {
            $ally = $character->getAlly();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_allyAdd = $this->createForm(AllyAddType::class);
        $form_allyAdd->handleRequest($request);

        if (($form_allyAdd->isSubmitted() && $form_allyAdd->isValid()) && $character->getGrade()->getCanRecruit() == 1) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if($maxMembers >= $ally->getMaxMembers()) {
                $this->addFlash("fail", "Vous avez atteint le nombre maximum d'invitations.");
                return $this->redirectToRoute('ally_page_add', ['usePlanet' => $usePlanet->getId()]);
            }
            $userProposal = $em->getRepository('App:User')
                ->createQueryBuilder('u')
                ->leftJoin('u.proposals', 'pr')
                ->where('u.username = :username')
                ->andWhere('c.ally is null')
                ->andWhere('pr.ally is null or pr.ally != :ally')
                ->setParameters(['username' => $form_allyAdd->get('nameUser')->getData(), 'ally' => $character->getAlly()])
                ->getQuery()
                ->getOneOrNullResult();

            if($userProposal) {
                $proposal = new Proposal($ally, $userProposal);
                $em->persist($proposal);
                $ally->addProposal($proposal);
                $userProposal->addProposal($proposal);
                $em->flush();
            }
            return $this->redirectToRoute('ally_page_add', ['usePlanet' => $usePlanet->getId()]);
        }

        $lastActivity = new DateTime();
        $lastActivity->sub(new DateInterval('PT' . 5184000 . 'S'));
        $usersRecruitable = $em->getRepository('App:Character')
            ->createQueryBuilder('c')
            ->join('c.planets', 'p')
            ->leftJoin('c.proposals', 'pr')
            ->select('c.username, c.id, count(DISTINCT p) as planets, c.imageName')
            ->groupBy('c.id')
            ->where('c.lastActivity > :date')
            ->andWhere('c.ally is null')
            ->andWhere('c.rank is not null')
            ->andWhere('pr is null')
            ->setParameters(['date' => $lastActivity])
            ->orderBy('c.lastActivity', 'DESC')
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
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function allylevelAction(Planet $usePlanet)
    {
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $ally = $character->getAlly();
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if($ally->getLevel() == 10) {
            return $this->redirectToRoute('ally_page_bank', ['usePlanet' => $usePlanet->getId()]);
        }
        $array = $ally->getLevelCost();
        if($character->getGrade()->getPlacement() == 1 && $ally->getBitcoin() >= $array[1] && $ally->getPdg() >= $array[2]) {
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
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function adminPageAllyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if($character->getAlly()) {
            $ally = $character->getAlly();
        } else {
            return $this->redirectToRoute('ally_blank', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_allyDecon = $this->createForm(AllyDefconType::class,$ally);
        $form_allyDecon->handleRequest($request);

        $form_allyGrade = $this->createForm(AllyGradeType::class);
        $form_allyGrade->handleRequest($request);

        if (($form_allyDecon->isSubmitted() && $form_allyDecon->isValid())) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $em->flush();
        }

        if (($form_allyGrade->isSubmitted() && $form_allyGrade->isValid()) && $ally->getPolitic() != 'fascism') {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $grade = new Grade($ally, $form_allyGrade->get('name')->getData(), $form_allyGrade->get('placement')->getData(), $form_allyGrade->get('canRecruit')->getData(), $form_allyGrade->get('canKick')->getData(), $form_allyGrade->get('canWar')->getData(), $form_allyGrade->get('canPeace')->getData(), $form_allyGrade->get('canEdit')->getData(), $form_allyGrade->get('seeMembers')->getData(), $form_allyGrade->get('useFleets')->getData());
            if ($character->getAlly()->getPolitic() == 'communism') {
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
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function pactPageAllyAction(Request $request, Planet $usePlanet)
    {
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();
        $em = $this->getDoctrine()->getManager();

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if($character->getAlly()) {
            $ally = $character->getAlly();
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

            if((!$allyPact || $character->getAlly()->getAlreadyPact($allyPact->getSigle())) || $allyPact == $ally) {
                return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
            }
            if($form_allyPact->get('pactType')->getData() == 2 && $character->getGrade()->getCanPeace() == 1) {
                $pna = new Pna($ally, $allyPact->getSigle(), false);
                $em->persist($pna);
                $ally->addAllyPna($pna);
            } elseif($form_allyPact->get('pactType')->getData() == 1  && $character->getGrade()->getCanPeace() == 1) {
                if ($ally->getPolitic() == $allyPact->getPolitic() || $ally->getPolitic() == 'democrat' || $allyPact->getPolitic() == 'democrat') {
                    $allied = new Allied($ally, $allyPact->getSigle(), false);
                    $em->persist($allied);
                    $ally->addAllyAllied($allied);
                } else {
                    $this->addFlash("fail", "La politique de cette alliance vous est hostile.");
                    return $this->redirectToRoute('ally_page_pacts', ['usePlanet' => $usePlanet->getId()]);
                }
            } elseif($form_allyPact->get('pactType')->getData() == 3 && $character->getGrade()->getCanWar() == 1) {
                $war = new War($ally, $allyPact->getSigle(), true);
                $war2 = new War($allyPact, $ally->getSigle(), true);
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