<?php

namespace App\Controller\Connected;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        $user = $this->getUser();
        if ($user->getUsername() != 'Dev') {
            return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
        }

        $referers = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(previousPage), count(previousPage) as nbrPreviousPage')
            ->groupBy('t.username')
            ->where('t.date = :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrPreviousPage', 'DESC')
            ->getQuery()
            ->getResult();

        $browsers = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(browser), count(browser) as nbrBrowser')
            ->groupBy('t.username')
            ->where('t.date = :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrBrowser', 'DESC')
            ->getQuery()
            ->getResult();

        $pages = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(page), count(page) as nbrPage')
            ->groupBy('t.username')
            ->where('t.date = :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrPage', 'DESC')
            ->getQuery()
            ->getResult();

        $hosts = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(host), count(host) as nbrHost')
            ->groupBy('t.username')
            ->where('t.date = :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrHost', 'DESC')
            ->getQuery()
            ->getResult();

        $ips = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(ip), count(ip) as nbrIp')
            ->groupBy('t.username')
            ->where('t.date = :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrIp', 'DESC')
            ->getQuery()
            ->getResult();

        $usernames = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(username), count(username) as nbrUsername')
            ->groupBy('t.id')
            ->where('t.date = :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrUsername', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('connected/admin/dashboard.html.twig', [
            'referers' => $referers,
            'browsers' => $browsers,
            'pages' => $pages,
            'hosts' => $hosts,
            'ips' => $ips,
            'usernames' => $usernames
        ]);
    }
}