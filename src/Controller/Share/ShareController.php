<?php

namespace App\Controller\Share;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ShareController
 * @package App\Controller\Share
 */
class ShareController extends AbstractController
{
    /**
     * @Route("/rapport/share/{id}", name="report_share_display")
     * @param ManagerRegistry $doctrine
     * @param string $key
     * @return Response
     * @throws NonUniqueResultException
     */
    public function reportSharePageAction(ManagerRegistry $doctrine, string $key): Response
    {
        $em = $doctrine->getManager();

        $report = $em->getRepository('App:Report')
            ->createQueryBuilder('r')
            ->andWhere('r.shareKey = :key')
            ->setParameters(['key' => $key])
            ->getQuery()
            ->getOneOrNullResult();

        return $this->render('connected/share/report_share.html.twig', [
            'report' => $report,
        ]);
    }

    /**
     * @Route("/message/share/{id}", name="message_share_display")
     * @param ManagerRegistry $doctrine
     * @param string $key
     * @return Response
     * @throws NonUniqueResultException
     */
    public function messageSharePageAction(ManagerRegistry $doctrine, string $key): Response
    {
        $em = $doctrine->getManager();

        $message = $em->getRepository('App:Message')
            ->createQueryBuilder('r')
            ->andWhere('r.shareKey = :key')
            ->setParameters(['key' => $key])
            ->getQuery()
            ->getOneOrNullResult();

        return $this->render('connected/share/message_share.html.twig', [
            'message' => $message,
        ]);
    }

}