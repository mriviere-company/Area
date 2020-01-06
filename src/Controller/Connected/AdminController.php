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
            ->groupBy('t.previousPage')
            ->where('t.date < :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrPreviousPage', 'DESC')
            ->getQuery()
            ->getResult();

        $browsers = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(t.browser) as browser, count(t.browser) as nbrBrowser')
            ->groupBy('t.browser')
            ->where('t.date < :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrBrowser', 'DESC')
            ->getQuery()
            ->getResult();

        $pages = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(t.page) as page, count(t.page) as nbrPage')
            ->groupBy('t.page')
            ->where('t.date < :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrPage', 'DESC')
            ->getQuery()
            ->getResult();

        $hosts = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(t.host) as host, count(t.host) as nbrHost')
            ->groupBy('t.host')
            ->where('t.date < :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrHost', 'DESC')
            ->getQuery()
            ->getResult();

        $ips = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(t.ip) as ip, count(t.ip) as nbrIp')
            ->groupBy('t.ip')
            ->where('t.date < :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrIp', 'DESC')
            ->getQuery()
            ->getResult();

        $usernames = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(t.username) as username, count(t.username) as nbrUsername')
            ->groupBy('t.username')
            ->where('t.date < :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrUsername', 'DESC')
            ->getQuery()
            ->setMaxResults(10)
            ->getResult();

        return $this->render('connected/admin/dashboard.html.twig', [
            'usePlanet' => $usePlanet,
            'referers' => $referers,
            'browsers' => $browsers,
            'pages' => $pages,
            'hosts' => $hosts,
            'ips' => $ips,
            'usernames' => $usernames
        ]);
    }
}