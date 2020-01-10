<?php

namespace App\Controller\Connected;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Planet;
use DateTime;
use DateTimeZone;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/administration-dashboard/{usePlanet}", name="admin_dashboard", requirements={"usePlanet"="\d+"})
     */
    public function adminDashboardAction(Planet $usePlanet, $date = NULL)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$date) {
            $date = new DateTime();
            $date->setTimezone(new DateTimeZone('Europe/Paris'));
        }
        $user = $this->getUser();
        if ($user->getUsername() != 'Dev') {
            return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
        }

        $referers = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(t.previousPage) as previousPage, count(t.previousPage) as nbrPreviousPage')
            ->groupBy('previousPage')
            ->where('t.date < :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrPreviousPage', 'DESC')
            ->getQuery()
            ->getResult();

        $computers = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(t.computer) as computer, count(t.computer) as nbrComputer')
            ->groupBy('computer')
            ->where('t.date < :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrComputer', 'DESC')
            ->getQuery()
            ->getResult();

        $pages = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(t.page) as page, count(t.page) as nbrPage')
            ->groupBy('page')
            ->where('t.date < :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrPage', 'DESC')
            ->getQuery()
            ->getResult();

        $ip = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('count(DISTINCT t.ip) as nbrIp')
            ->where('t.date < :date')
            ->setParameters(['date' => $date])
            ->getQuery()
            ->getSingleScalarResult();

        $usernames = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(t.username) as username, count(t.username) as nbrUsername')
            ->groupBy('username')
            ->where('t.date < :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrUsername', 'DESC')
            ->getQuery()
            ->setMaxResults(10)
            ->getResult();

        return $this->render('connected/admin/dashboard.html.twig', [
            'usePlanet' => $usePlanet,
            'referers' => $referers,
            'computers' => $computers,
            'pages' => $pages,
            'ip' => $ip,
            'usernames' => $usernames
        ]);
    }
}