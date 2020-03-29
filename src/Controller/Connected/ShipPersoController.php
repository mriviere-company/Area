<?php

namespace App\Controller\Connected;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\ShipPersoType;
use App\Entity\Planet;
use DateTime;
use Dateinterval;
use DateTimeZone;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class ShipPersoController extends AbstractController
{
    /**
     * @Route("/vaisseaux-personalisation/{usePlanet}", name="ship_perso", requirements={"usePlanet"="\d+"})
     */
    public function spatialAction(Request $request, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $ship = $user->getShip();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $form_shipPerso = $this->createForm(ShipPersoType::class);
        $form_shipPerso->handleRequest($request);

        if ($ship->getLastUpdate() < $now && $ship->getLastUpdate() != null) {
            $ship->setLastUpdate(null);
            $ship->setMax(40);
            $em->flush();
        }

        if ($form_shipPerso->isSubmitted() && $form_shipPerso->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $points = abs($form_shipPerso->get('armor')->getData()) + abs($form_shipPerso->get('shield')->getData()) + abs($form_shipPerso->get('accurate')->getData()) + abs($form_shipPerso->get('missile')->getData()) + abs($form_shipPerso->get('laser')->getData()) + abs($form_shipPerso->get('plasma')->getData());
            $armor = abs($form_shipPerso->get('armor')->getData()) * 5;
            $shield = abs($form_shipPerso->get('shield')->getData());
            $accurate = 0;
            $missile = abs($form_shipPerso->get('missile')->getData()) * 3;
            $laser = abs($form_shipPerso->get('laser')->getData()) * 2;
            $plasma = abs($form_shipPerso->get('plasma')->getData());

            if ($ship->getMax() - $points >= 0 && $ship->getLastUpdate() < $now) {
                if ($form_shipPerso->get('ship')->getData() == 'hunter' && $points <= $ship->getPointHunter()) {
                    $ship->setArmorHunter($ship->getArmorHunter() + $armor);
                    $ship->setMissileHunter($ship->getMissileHunter() + $missile);
                    $ship->setAccurateHunter($ship->getAccurateHunter() + $accurate);
                    $ship->setPointHunter($ship->getPointHunter() - $points);
                }
                if ($form_shipPerso->get('ship')->getData() == 'hunterHeavy' && $points <= $ship->getPointHunterHeavy()) {
                    $ship->setArmorHunterHeavy($ship->getArmorHunterHeavy() + $armor);
                    $ship->setMissileHunterHeavy($ship->getMissileHunterHeavy() + $missile);
                    $ship->setAccurateHunterHeavy($ship->getAccurateHunterHeavy() + $accurate);
                    $ship->setPointHunterHeavy($ship->getPointHunterHeavy() - $points);
                }
                if ($form_shipPerso->get('ship')->getData() == 'hunterWar' && $points <= $ship->getPointHunterWar()) {
                    $ship->setArmorHunterWar($ship->getArmorHunterWar() + $armor);
                    $ship->setMissileHunterWar($ship->getMissileHunterWar() + $missile);
                    $ship->setLaserHunterWar($ship->getLaserHunterWar() + $laser);
                    $ship->setPlasmaHunterWar($ship->getPlasmaHunterWar() + $plasma);
                    $ship->setAccurateHunterWar($ship->getAccurateHunterWar() + $accurate);
                    $ship->setPointHunterWar($ship->getPointHunterWar() - $points);
                }
                if ($form_shipPerso->get('ship')->getData() == 'corvet' && $points <= $ship->getPointCorvet()) {
                    $ship->setArmorCorvet($ship->getArmorCorvet() + $armor);
                    $ship->setMissileCorvet($ship->getMissileCorvet() + $missile);
                    $ship->setShieldCorvet($ship->getShieldCorvet() + $shield);
                    $ship->setAccurateCorvet($ship->getAccurateCorvet() + $accurate);
                    $ship->setPointCorvet($ship->getPointCorvet() - $points);
                }
                if ($form_shipPerso->get('ship')->getData() == 'corvetLaser' && $points <= $ship->getPointCorvetLaser()) {
                    $ship->setArmorCorvetLaser($ship->getArmorCorvetLaser() + $armor);
                    $ship->setMissileCorvetLaser($ship->getMissileCorvetLaser() + $missile);
                    $ship->setShieldCorvetLaser($ship->getShieldCorvetLaser() + $shield);
                    $ship->setLaserCorvetLaser($ship->getLaserCorvetLaser() + $laser);
                    $ship->setAccurateCorvetLaser($ship->getAccurateCorvetLaser() + $accurate);
                    $ship->setPointCorvetLaser($ship->getPointCorvetLaser() - $points);
                }
                if ($form_shipPerso->get('ship')->getData() == 'corvetWar' && $points <= $ship->getPointCorvetWar()) {
                    $ship->setArmorCorvetWar($ship->getArmorCorvetWar() + $armor);
                    $ship->setMissileCorvetWar($ship->getMissileCorvetWar() + $missile);
                    $ship->setShieldCorvetWar($ship->getShieldCorvetWar() + $shield);
                    $ship->setLaserCorvetWar($ship->getLaserCorvetWar() + $laser);
                    $ship->setAccurateCorvetWar($ship->getAccurateCorvetWar() + $accurate);
                    $ship->setPointCorvetWar($ship->getPointCorvetWar() - $points);
                }
                if ($form_shipPerso->get('ship')->getData() == 'fregate' && $points <= $ship->getPointFregate()) {
                    $ship->setArmorFregate($ship->getArmorFregate() + $armor);
                    $ship->setMissileFregate($ship->getMissileFregate() + $missile);
                    $ship->setShieldFregate($ship->getShieldFregate() + $shield);
                    $ship->setLaserFregate($ship->getLaserFregate() + $laser);
                    $ship->setAccurateFregate($ship->getAccurateFregate() + $accurate);
                    $ship->setPointFregate($ship->getPointFregate() - $points);
                }
                if ($form_shipPerso->get('ship')->getData() == 'fregatePlasma' && $points <= $ship->getPointFregatePlasma()) {
                    $ship->setArmorFregatePlasma($ship->getArmorFregatePlasma() + $armor);
                    $ship->setMissileFregatePlasma($ship->getMissileFregatePlasma() + $missile);
                    $ship->setShieldFregatePlasma($ship->getShieldFregatePlasma() + $shield);
                    $ship->setLaserFregatePlasma($ship->getLaserFregatePlasma() + $laser);
                    $ship->setPlasmaFregatePlasma($ship->getPlasmaFregatePlasma() + $plasma);
                    $ship->setAccurateFregatePlasma($ship->getAccurateFregatePlasma() + $accurate);
                    $ship->setPointFregatePlasma($ship->getPointFregatePlasma() - $points);
                }
                if ($form_shipPerso->get('ship')->getData() == 'croiser' && $points <= $ship->getPointCroiser()) {
                    $ship->setArmorCroiser($ship->getArmorCroiser() + $armor);
                    $ship->setMissileCroiser($ship->getMissileCroiser() + $missile);
                    $ship->setShieldCroiser($ship->getShieldCroiser() + $shield);
                    $ship->setLaserCroiser($ship->getLaserCroiser() + $laser);
                    $ship->setPlasmaCroiser($ship->getPlasmaCroiser() + $plasma);
                    $ship->setAccurateCroiser($ship->getAccurateCroiser() + $accurate);
                    $ship->setPointCroiser($ship->getPointCroiser() - $points);
                }
                if ($form_shipPerso->get('ship')->getData() == 'ironClad' && $points <= $ship->getPointIronClad()) {
                    $ship->setArmorIronClad($ship->getArmorIronClad() + $armor);
                    $ship->setMissileIronClad($ship->getMissileIronClad() + $missile);
                    $ship->setShieldIronClad($ship->getShieldIronClad() + $shield);
                    $ship->setLaserIronClad($ship->getLaserIronClad() + $laser);
                    $ship->setPlasmaIronClad($ship->getPlasmaIronClad() + $plasma);
                    $ship->setAccurateIronClad($ship->getAccurateIronClad() + $accurate);
                    $ship->setPointIronClad($ship->getPointIronClad() - $points);
                }
                if ($form_shipPerso->get('ship')->getData() == 'destroyer' && $points <= $ship->getPointDestroyer()) {
                    $ship->setArmorDestroyer($ship->getArmorDestroyer() + $armor);
                    $ship->setMissileDestroyer($ship->getMissileDestroyer() + $missile);
                    $ship->setShieldDestroyer($ship->getShieldDestroyer() + $shield);
                    $ship->setLaserDestroyer($ship->getLaserDestroyer() + $laser);
                    $ship->setPlasmaDestroyer($ship->getPlasmaDestroyer() + $plasma);
                    $ship->setAccurateDestroyer($ship->getAccurateDestroyer() + $accurate);
                    $ship->setPointDestroyer($ship->getPointDestroyer() - $points);
                }
                $ship->setMax($ship->getMax() - $points);
                if ($ship->getMax() == 0) {
                    $now->add(new DateInterval('PT' . 24 . 'H'));
                    $ship->setLastUpdate($now);
                }
                if(($user->getTutorial() == 19)) {
                    $user->setTutorial(20);
                }
                $em->flush();
            } else {
                $this->addFlash("fail", "Vous ne pouvez dépensez que 40 points par jours.");
                return $this->redirectToRoute('ship_perso', ['usePlanet' => $usePlanet->getId()]);
            }
            return $this->redirectToRoute('ship_perso', ['usePlanet' => $usePlanet->getId()]);
        }

        if(($user->getTutorial() == 18)) {
            $user->setTutorial(19);
            $em->flush();
        }

        return $this->render('connected/ship_perso.html.twig', [
            'usePlanet' => $usePlanet,
            'formObject' => $form_shipPerso,
        ]);
    }
}