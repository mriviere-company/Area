<?php

namespace App\Controller\Connected;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Planet;
use DateTime;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/administration-dashboard/{usePlanet}", name="admin_dashboard", requirements={"usePlanet"="\d+"})
     * @param Planet $usePlanet
     * @param null $date
     * @return RedirectResponse|Response
     */
    public function adminDashboardAction(Planet $usePlanet, $date = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$date) {
            $date = new DateTime();
        }

        if ($user->getUsername() != 'Dev') {
            return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
        }

        $referers = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(t.previousPage) as previousPage, count(DISTINCT CONCAT(t.previousPage, t.ip)) as nbrPreviousPage')
            ->groupBy('previousPage')
            ->where('t.date < :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrPreviousPage', 'DESC')
            ->getQuery()
            ->getResult();

        $computers = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(t.computer) as computer, count(DISTINCT CONCAT(t.computer, t.ip)) as nbrComputer')
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
            ->setMaxResults(10)
            ->getResult();

        $uniquePages = $em->getRepository('App:Track')
            ->createQueryBuilder('t')
            ->select('DISTINCT(t.page) as uniquePage, count(DISTINCT CONCAT(t.page, t.ip)) as nbrPage')
            ->groupBy('uniquePage')
            ->where('t.date < :date')
            ->setParameters(['date' => $date])
            ->orderBy('nbrPage', 'DESC')
            ->getQuery()
            ->setMaxResults(10)
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
            'uniquePages' => $uniquePages,
            'ip' => $ip,
            'usernames' => $usernames
        ]);
    }
}