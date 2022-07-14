<?php

namespace App\Controller\Connected;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Planet;
use App\Entity\Report;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class ReportController extends AbstractController
{
    /**
     * @Route("/rapport/{usePlanet}", name="report", requirements={"usePlanet"="\d+"})
     * @Route("/rapport/{id}/{usePlanet}", name="report_id", requirements={"id"="\w+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param string $id
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     */
    public function reportAction(ManagerRegistry $doctrine, string $id = 'defaut', Planet $usePlanet): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if ($id == 'defaut') {
            $reports = $em->getRepository('App:Report')
                ->createQueryBuilder('r')
                ->where('r.character = :character')
                ->setParameters(['character' => $character])
                ->orderBy('r.sendAt', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $reports = $em->getRepository('App:Report')
                ->createQueryBuilder('r')
                ->where('r.character = :character')
                ->andWhere('r.type = :type')
                ->setParameters(['character' => $character, 'type' => $id])
                ->orderBy('r.sendAt', 'DESC')
                ->getQuery()
                ->getResult();
        }

        if ($id != 'defaut') {
            foreach($reports as $report) {
                if($report->getType() == $id && $report->getNewReport() == 2) {
                    $report->setNewReport(1);
                }
            }
        } else {
            foreach($reports as $report) {
                if($report->getType() == $id && $report->getNewReport() == 2) {
                    $report->setNewReport(1);
                }
            }
        }

        if(($user->getTutorial() == 1)) {
            $user->setTutorial(2);
        }
        if(($user->getTutorial() == 23)) {
            $user->setTutorial(24);
        }

        $em->flush();

        return $this->render('connected/report.html.twig', [
            'usePlanet' => $usePlanet,
            'reports' => $reports,
            'reportPage' => $id
        ]);
    }

    /**
     * @Route("/report-view/{report}/{usePlanet}", name="report_view", requirements={ "report"="\d+", "usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @param Report $report
     * @return RedirectResponse
     */
    public function reportViewAction(ManagerRegistry $doctrine, Planet $usePlanet, Report $report): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character || $report->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $report->setNewReport(0);

        $em->flush();

        return $this->redirectToRoute('report_id', ['id' => $report->getType(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/supprimer-rapport/{report}/{usePlanet}", name="report_delete", requirements={"usePlanet"="\d+", "report"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @param Report $report
     * @return RedirectResponse
     */
    public function reportDeleteAction(ManagerRegistry $doctrine, Planet $usePlanet, Report $report): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character || $report->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $report->setImageName(null);
        $path = $report->getType();
        $em->remove($report);
        $em->flush();

        return $this->redirectToRoute('report_id', ['id' => $path, 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/rapport-share/{id}/{usePlanet}", name="report_share", requirements={"usePlanet"="\d+", "id"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @param Report $id
     * @return RedirectResponse
     */
    public function reportShareAction(ManagerRegistry $doctrine, Planet $usePlanet, Report $id): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }
        if ($character == $id->getCharacter()) {
            $alpha = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-'];
            $newShareKey = $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)]
                . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)]
                . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)];

            $id->setShareKey($newShareKey);
            $em->flush();
        }

        return $this->redirectToRoute('report_id', ['id' => $id->getType(), 'usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/report-view-all/{usePlanet}/", name="report_all_view", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function reportAllViewAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $reports = $em->getRepository('App:Report')
            ->createQueryBuilder('r')
            ->where('r.newReport > 0')
            ->andWhere('r.character = :character')
            ->setParameters(['character' => $character])
            ->getQuery()
            ->getResult();

        foreach($reports as $report) {
            $report->setNewReport(0);
        }
        $em->flush();

        return $this->redirectToRoute('report', ['usePlanet' => $usePlanet->getId()]);
    }
}