<?php

namespace App\Controller\Connected\Building;

use App\Entity\Construction;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use DateTime;
use Dateinterval;
use DateTimeZone;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class ProductionController extends AbstractController
{
    /**
     * @Route("/contruire-mine/{usePlanet}", name="building_add_mine", requirements={"usePlanet"="\d+"})
     */
    public function buildingAddMineAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getMiner() + 1;
        $usePlanetNb = $usePlanet->getNiobium();
        $usePlanetWt = $usePlanet->getWater();
        $newGround = $usePlanet->getGroundPlace() + 1;
        if(($usePlanetNb < ($level * 450) || $usePlanetWt < ($level * 200)) ||
            ($newGround > $usePlanet->getGround())) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        if ($usePlanet->getConstructAt() > $now) {
            $level = $level + $usePlanet->getConstructionsLike('miner');
            $construction = new Construction();
            $construction->setConstruct('miner');
            $construction->setConstructTime($level * 180);
            $construction->setPlanet($usePlanet);
            $usePlanet->setNiobium($usePlanetNb - ($level * 450));
            $usePlanet->setWater($usePlanetWt - ($level * 200));
            $usePlanet->setGroundPlace($newGround);
            $em->persist($construction);
            if(($user->getTutorial() == 6)) {
                $user->setTutorial(7);
            }
        } else {
            $now->add(new DateInterval('PT' . ($level * 180) . 'S'));
            $usePlanet->setNiobium($usePlanetNb - ($level * 450));
            $usePlanet->setWater($usePlanetWt - ($level * 200));
            $usePlanet->setGroundPlace($newGround);
            $usePlanet->setConstruct('miner');
            $usePlanet->setConstructAt($now);
            if(($user->getTutorial() == 5)) {
                $user->setTutorial(6);
            }
        }
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-mine/{usePlanet}", name="building_remove_mine", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveMineAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getMiner();
        $newGround = $usePlanet->getGroundPlace() - 1;
        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 60 . 'S'));
        $usePlanet->setMiner($level - 1);
        $usePlanet->setNbProduction($usePlanet->getNbProduction() - ($level * 1.06));
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/contruire-puit/{usePlanet}", name="building_add_extract", requirements={"usePlanet"="\d+"})
     */
    public function buildingAddExtractAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getExtractor() + 1;
        $usePlanetNb = $usePlanet->getNiobium();
        $usePlanetWt = $usePlanet->getWater();
        $newGround = $usePlanet->getGroundPlace() + 1;
        if(($usePlanetNb < ($level * 200) || $usePlanetWt < ($level * 500)) ||
            ($newGround > $usePlanet->getGround())) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        if ($usePlanet->getConstructAt() > $now) {
            $level = $level + $usePlanet->getConstructionsLike('extractor');
            $construction = new Construction();
            $construction->setConstruct('extractor');
            $construction->setConstructTime($level * 180);
            $construction->setPlanet($usePlanet);
            $usePlanet->setNiobium($usePlanetNb - ($level * 200));
            $usePlanet->setWater($usePlanetWt - ($level * 500));
            $usePlanet->setGroundPlace($newGround);
            $em->persist($construction);
            if(($user->getTutorial() == 6)) {
                $user->setTutorial(7);
            }
        } else {
            $now->add(new DateInterval('PT' . ($level * 180) . 'S'));
            $usePlanet->setNiobium($usePlanetNb - ($level * 200));
            $usePlanet->setWater($usePlanetWt - ($level * 500));
            $usePlanet->setGroundPlace($newGround);
            $usePlanet->setConstruct('extractor');
            $usePlanet->setConstructAt($now);
            if(($user->getTutorial() == 5)) {
                $user->setTutorial(6);
            }
        }
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-puit/{usePlanet}", name="building_remove_extract", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveExtractAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getExtractor();
        $newGround = $usePlanet->getGroundPlace() - 1;
        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 60 . 'S'));
        $usePlanet->setExtractor($level - 1);
        $usePlanet->setWtProduction($usePlanet->getWtProduction() - ($level * 1.05));
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/contruire-stockage-niobium/{usePlanet}", name="building_add_niobiumStock", requirements={"usePlanet"="\d+"})
     */
    public function buildingAddNiobiumStockAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getNiobiumStock() + 1;
        $usePlanetNb = $usePlanet->getNiobium();
        $usePlanetWt = $usePlanet->getWater();
        $newGround = $usePlanet->getGroundPlace() + 3;
        if(($usePlanetNb < ($level * 150000) || $usePlanetWt < ($level * 100000)) ||
            ($usePlanet->getConstructAt() > $now || $newGround > $usePlanet->getGround()) ||
        $user->getCargo() < 2) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . ($level * 2160) . 'S'));
        $usePlanet->setNiobium($usePlanetNb - ($level * 150000));
        $usePlanet->setWater($usePlanetWt - ($level * 100000));
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('niobiumStock');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-stockage-niobium/{usePlanet}", name="building_remove_niobiumStock", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveNiobiumStockAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getNiobiumStock();
        $newGround = $usePlanet->getGroundPlace() - 3;
        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setNiobiumStock($level - 1);
        $usePlanet->setNiobiumMax($usePlanet->getNiobiumMax() - 5000000);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/contruire-stockage-eau/{usePlanet}", name="building_add_waterStock", requirements={"usePlanet"="\d+"})
     */
    public function buildingAddWaterStockAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getWaterStock() + 1;
        $usePlanetNb = $usePlanet->getNiobium();
        $usePlanetWt = $usePlanet->getWater();
        $newGround = $usePlanet->getGroundPlace() + 3;
        if(($usePlanetNb < ($level * 110000) || $usePlanetWt < ($level * 180000)) ||
            ($usePlanet->getConstructAt() > $now || $newGround > $usePlanet->getGround()) ||
            $user->getCargo() < 2) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . ($level * 2160) . 'S'));
        $usePlanet->setNiobium($usePlanetNb - ($level * 110000));
        $usePlanet->setWater($usePlanetWt - ($level * 180000));
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('waterStock');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/detruire-stockage-eau/{usePlanet}", name="building_remove_waterStock", requirements={"usePlanet"="\d+"})
     */
    public function buildingRemoveWaterStockAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $level = $usePlanet->getWaterStock();
        $newGround = $usePlanet->getGroundPlace() - 3;
        if($level == 0 || $usePlanet->getConstructAt() > $now) {
            return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
        }
        $now->add(new DateInterval('PT' . 180 . 'S'));
        $usePlanet->setWaterMax($usePlanet->getWaterMax() - 5000000);
        $usePlanet->setWaterStock($level - 1);
        $usePlanet->setGroundPlace($newGround);
        $usePlanet->setConstruct('destruct');
        $usePlanet->setConstructAt($now);
        $em->flush();

        return $this->redirectToRoute('building', ['usePlanet' => $usePlanet->getId()]);
    }
}