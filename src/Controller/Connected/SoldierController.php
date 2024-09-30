<?php

namespace App\Controller\Connected;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\CaserneRecruitType;
use App\Entity\Planet;
use DateTime;
use Dateinterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class SoldierController extends AbstractController
{
    /**
     * @Route("/entrainement/{usePlanet}", name="soldier", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function soldierAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $commander = $user->getCommander($usePlanet->getSector()->getGalaxy()->getServer());
        $now = new DateTime();

        if($commander->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getCommander() != $commander) {
            return $this->redirectToRoute('home');
        }

        if ($usePlanet->getSoldierAt() && $usePlanet->getSoldierAt() < $now) {
            $this->forward('App\Controller\Connected\Execute\PlanetController::soldierOneAction', [
                'planetSoldier'  => $usePlanet,
                'em'  => $em
            ]);
        }

        if ($usePlanet->getTankAt() && $usePlanet->getTankAt() < $now) {
            $this->forward('App\Controller\Connected\Execute\PlanetController::tankOneAction', [
                'planetTank'  => $usePlanet,
                'em'  => $em
            ]);
        }

        if ($usePlanet->getScientistAt() && $usePlanet->getScientistAt() < $now) {
            $this->forward('App\Controller\Connected\Execute\PlanetController::scientistOneAction', [
                'planetScientist'  => $usePlanet,
                'em'  => $em
            ]);
        }

        $form_caserneRecruit = $this->createForm(CaserneRecruitType::class);
        $form_caserneRecruit->handleRequest($request);

        if ($form_caserneRecruit->isSubmitted() && $form_caserneRecruit->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if ($form_caserneRecruit->get('soldier')->getData()) {
                $now = new DateTime();
                $nbrSoldier = abs($form_caserneRecruit->get('soldier')->getData());
                if ($commander->getAlliance()) {
                    if ($commander->getPoliticSoldierSale() > 0) {
                        $price = 8 - ($commander->getPoliticSoldierSale() * 5);
                    } else {
                        $price = 8;
                    }
                }
                if($nbrSoldier * $price > $commander->getBitcoin() ||
                    ($nbrSoldier * 2 > $usePlanet->getWorker() || ($usePlanet->getSoldier() + $nbrSoldier) > $usePlanet->getSoldierMax()) ||
                    ($usePlanet->getWorker() < 10000)) {
                    if ($nbrSoldier * 2 > $usePlanet->getWorker()) {
                        $this->addFlash("fail", "Vous n'avez pas assez de travailleurs.");
                    } elseif ($usePlanet->getSoldier() + $nbrSoldier > $usePlanet->getSoldierMax()) {
                        $this->addFlash("fail", "Vous dépassez la limite de soldats sur la planète.");
                    } elseif ($usePlanet->getWorker() < 10000) {
                        $this->addFlash("fail", "Une planète ne peut avoir moins de 10000 travailleurs.");
                    } elseif ($nbrSoldier * $price > $commander->getBitcoin()) {
                        $this->addFlash("fail", "Vous ne disposez pas d'assez de bitcoins.");
                    } else {
                        $this->addFlash("fail", "Vous n'avez pas toutes les conditions requises.");
                    }
                    return $this->redirectToRoute('soldier', ['usePlanet' => $usePlanet->getId()]);
                }
                if($usePlanet->getSoldierAt()) {
                    if ($usePlanet->getSoldier() + $usePlanet->getSoldierAtNbr() + $nbrSoldier > $usePlanet->getSoldierMax()) {
                        $this->addFlash("fail", "Vous dépassez la limite de soldats sur la planète.");
                        return $this->redirectToRoute('soldier', ['usePlanet' => $usePlanet->getId()]);
                    }
                    $tmpSoldier = $usePlanet->getSoldierAtNbr();
                    $now = clone $usePlanet->getSoldierAt();
                    $now->add(new DateInterval('PT' . round(($nbrSoldier) / 10) . 'S'));  // X10 NORMAL GAME
                    $usePlanet->setSoldierAtNbr($tmpSoldier + $nbrSoldier);
                    $usePlanet->setSoldierAt($now);
                } else {
                    $now->add(new DateInterval('PT' . round($nbrSoldier / 10) . 'S')); // X10 NORMAL GAME
                    $usePlanet->setSoldierAtNbr($nbrSoldier);
                    $usePlanet->setSoldierAt($now);
                }
                $usePlanet->setWorker($usePlanet->getWorker() - ($nbrSoldier * 2));
                $commander->setBitcoin($commander->getBitcoin() - ($nbrSoldier * $price));
                $quest = $commander->checkQuests('soldier');
                if($quest) {
                    $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + $quest->getGain());
                    $commander->removeQuest($quest);
                }
            }
            if ($form_caserneRecruit->get('tank')->getData() && ($usePlanet->getBunker() > 0 || $usePlanet->getCaserne() > 0) && $usePlanet->getLightUsine() > 0 && $commander->getTank() == 1) {
                $now = new DateTime();
                $nbrTank = abs($form_caserneRecruit->get('tank')->getData());
                if($nbrTank * 60 > $commander->getBitcoin() ||
                    $nbrTank * 5 > $usePlanet->getWorker() ||
                    $nbrTank * 400 > $usePlanet->getNiobium() ||
                    ($usePlanet->getWorker() < 10000 || ($usePlanet->getTank() + $nbrTank) > 500)) {
                    if ($nbrTank * 5 > $usePlanet->getWorker()) {
                        $this->addFlash("fail", "Vous n'avez pas assez de travailleurs.");
                    } elseif ($nbrTank * 60 > $commander->getBitcoin()) {
                        $this->addFlash("fail", "Vous ne disposez pas d'assez de bitcoins.");
                    } elseif ($nbrTank * 400 > $usePlanet->getNiobium()) {
                        $this->addFlash("fail", "Vous ne disposez pas d'assez de niobiums.");
                    } elseif ($usePlanet->getTank() + $nbrTank > 500) {
                        $this->addFlash("fail", "Vous dépassez la limite de tanks sur la planète.");
                    } elseif ($usePlanet->getWorker() < 10000) {
                        $this->addFlash("fail", "Une planète ne peut avoir moins de 10000 travailleurs.");
                    } else {
                        $this->addFlash("fail", "Vous n'avez pas toutes les conditions requises.");
                    }
                    return $this->redirectToRoute('soldier', ['usePlanet' => $usePlanet->getId()]);
                }
                if($usePlanet->getTankAt()) {
                    if ($usePlanet->getTank() + $usePlanet->getTankAtNbr() + $nbrTank > 500) {
                        $this->addFlash("fail", "Vous dépassez la limite de tanks sur la planète.");
                        return $this->redirectToRoute('soldier', ['usePlanet' => $usePlanet->getId()]);
                    }
                    $tmpTank = $usePlanet->getTankAtNbr();
                    $now = clone $usePlanet->getTankAt();
                    $now->add(new DateInterval('PT' . round(($nbrTank) * 900) . 'S'));
                    $usePlanet->setTankAtNbr($tmpTank + $nbrTank);
                } else {
                    $now->add(new DateInterval('PT' . round($nbrTank * 900) . 'S'));
                    $usePlanet->setTankAtNbr($nbrTank);
                }
                $usePlanet->setNiobium($usePlanet->getNiobium() - ($nbrTank * 400));
                $usePlanet->setWorker($usePlanet->getWorker() - ($nbrTank * 5));
                $commander->setBitcoin($commander->getBitcoin() - ($nbrTank * 60));
                $usePlanet->setTankAt($now);
                $quest = $commander->checkQuests('tank');
                if($quest) {
                    $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + $quest->getGain());
                    $commander->removeQuest($quest);
                }
            }
            if ($form_caserneRecruit->get('nuclear')->getData() && $usePlanet->getNuclearBase() > 0) {
                $now = new DateTime();
                $nbrNuclear = abs($form_caserneRecruit->get('nuclear')->getData());
                if($nbrNuclear * 500 > $commander->getBitcoin() ||
                    $nbrNuclear * 500 > $usePlanet->getUranium() ||
                    ($usePlanet->getWorker() < 10000 || ($usePlanet->getNuclearBomb() + $nbrNuclear) > $usePlanet->getNuclearBase())) {
                    if ($nbrNuclear * 500 > $usePlanet->getUranium()) {
                        $this->addFlash("fail", "Vous n'avez pas assez d'uranium.");
                    } elseif ($nbrNuclear * 500 > $commander->getBitcoin()) {
                        $this->addFlash("fail", "Vous ne disposez pas d'assez de bitcoins.");
                    } elseif ($usePlanet->getNuclearBomb() + $nbrNuclear > $usePlanet->getNuclearBase()) {
                        $this->addFlash("fail", "Vous dépassez la limite de bombes nucléaires sur la planète.");
                    } elseif ($usePlanet->getWorker() < 10000) {
                        $this->addFlash("fail", "Une planète ne peut avoir moins de 10000 travailleurs.");
                    } else {
                        $this->addFlash("fail", "Vous n'avez pas toutes les conditions requises.");
                    }
                    return $this->redirectToRoute('soldier', ['usePlanet' => $usePlanet->getId()]);
                }
                if($usePlanet->getNuclearAt()) {
                    if ($usePlanet->getNuclearBomb() + $usePlanet->getNuclearAtNbr() + $nbrNuclear > $usePlanet->getNuclearBase()) {
                        $this->addFlash("fail", "Vous dépassez la limite de bombes nucléaires sur la planète.");
                        return $this->redirectToRoute('soldier', ['usePlanet' => $usePlanet->getId()]);
                    }
                    $tmpNuclear = $usePlanet->getNuclearAtNbr();
                    $now = clone $usePlanet->getNuclearAt();
                    $now->add(new DateInterval('PT' . round(($nbrNuclear + $tmpNuclear) * 24) . 'H'));
                    $usePlanet->setNuclearAtNbr($usePlanet->getNuclearAtNbr() + $nbrNuclear);
                } else {
                    $now->add(new DateInterval('PT' . round($nbrNuclear * 24) . 'H'));
                    $usePlanet->setNuclearAtNbr($nbrNuclear);
                }
                $usePlanet->setUranium($usePlanet->getUranium() - ($nbrNuclear * 500));
                $commander->setBitcoin($commander->getBitcoin() - ($nbrNuclear * 500));
                $usePlanet->setNuclearAt($now);
                $quest = $commander->checkQuests('nuclear');
                if($quest) {
                    $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + $quest->getGain());
                    $commander->removeQuest($quest);
                }
            }
            if ($form_caserneRecruit->get('scientist')->getData() && $usePlanet->getCenterSearch() > 0) {
                $now = new DateTime();
                $nbrScientist = abs($form_caserneRecruit->get('scientist')->getData());
                if($nbrScientist > $commander->getBitcoin() / 25 ||
                    $nbrScientist * 10 > $usePlanet->getWorker() ||
                    ($usePlanet->getWorker() < 10000 || ($usePlanet->getScientist() + $nbrScientist) > $usePlanet->getScientistMax())) {
                    if ($nbrScientist * 10 > $usePlanet->getWorker()) {
                        $this->addFlash("fail", "Vous n'avez pas assez de travailleurs.");
                    } elseif ($nbrScientist > $commander->getBitcoin() / 25) {
                        $this->addFlash("fail", "Vous ne disposez pas d'assez de bitcoins.");
                    } elseif ($usePlanet->getScientist() + $nbrScientist > $usePlanet->getScientistMax()) {
                        $this->addFlash("fail", "Vous dépassez la limite de scientifiques sur la planète.");
                    } elseif ($usePlanet->getWorker() < 10000) {
                        $this->addFlash("fail", "Une planète ne peut avoir moins de 10000 travailleurs.");
                    } else {
                        $this->addFlash("fail", "Vous n'avez pas toutes les conditions requises.");
                    }
                    return $this->redirectToRoute('soldier', ['usePlanet' => $usePlanet->getId()]);
                }
                if($usePlanet->getScientistAt()) {
                    if ($usePlanet->getScientist() + $usePlanet->getScientistAtNbr() + $nbrScientist > $usePlanet->getScientistMax()) {
                        $this->addFlash("fail", "Vous dépassez la limite de scientifiques sur la planète.");
                        return $this->redirectToRoute('soldier', ['usePlanet' => $usePlanet->getId()]);
                    }
                    $tmpScientist = $usePlanet->getScientistAtNbr();
                    $now = clone $usePlanet->getScientistAt();
                    $now->add(new DateInterval('PT' . round((($nbrScientist + $tmpScientist) * 60)/ $commander->getScientistProduction()) . 'S'));
                    $usePlanet->setScientistAtNbr($usePlanet->getScientistAtNbr() + $nbrScientist);
                } else {
                    $now->add(new DateInterval('PT' . round(($nbrScientist * 60)/ $commander->getScientistProduction()) . 'S'));
                    $usePlanet->setScientistAtNbr($nbrScientist);
                }
                $usePlanet->setWorker($usePlanet->getWorker() - ($nbrScientist * 10));
                $commander->setBitcoin($commander->getBitcoin() - ($nbrScientist * 25));
                $usePlanet->setScientistAt($now);
                $quest = $commander->checkQuests('scientist');
                if($quest) {
                    $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + $quest->getGain());
                    $commander->removeQuest($quest);
                }
            }
            $em->flush();
            return $this->redirectToRoute('soldier', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->render('connected/soldier.html.twig', [
            'usePlanet' => $usePlanet,
            'form_caserneRecruit' => $form_caserneRecruit->createView()
        ]);
    }
}