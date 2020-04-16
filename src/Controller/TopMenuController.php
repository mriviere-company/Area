<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\UserContactType;

class TopMenuController extends AbstractController
{
    /**
     * @Route("/reglement/{_locale}", name="rules", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/reglement", name="rules_noSlash")
     */
    public function rulesAction()
    {
        $em = $this->getDoctrine()->getManager();

        if($this->getUser()) {
            $usePlanet = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.user', 'u')
                ->where('u.username = :user')
                ->setParameters(['user' => $this->getUser()->getUsername()])
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();
        } else {
            $usePlanet = null;
        }

        return $this->render('anonymous/rules.html.twig', [
            'usePlanet' => $usePlanet,
        ]);
    }

    /**
     * @Route("/medias/{_locale}", name="medias", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/medias", name="medias_noSlash")
     */
    public function mediasAction()
    {
        $em = $this->getDoctrine()->getManager();

        if($this->getUser()) {
            $usePlanet = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.user', 'u')
                ->where('u.username = :user')
                ->setParameters(['user' => $this->getUser()->getUsername()])
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();
        } else {
            $usePlanet = null;
        }

        return $this->render('anonymous/medias.html.twig', [
            'usePlanet' => $usePlanet,
        ]);
    }

    /**
     * @Route("/but-du-jeu/{_locale}", name="point_game", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/but-du-jeu", name="point_game_noSlash")
     */
    public function pointGameAction()
    {
        $em = $this->getDoctrine()->getManager();

        if($this->getUser()) {
            $usePlanet = $em->getRepository('App:Planet')
                ->createQueryBuilder('p')
                ->join('p.user', 'u')
                ->where('u.username = :user')
                ->setParameters(['user' => $this->getUser()->getUsername()])
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();
        } else {
            $usePlanet = null;
        }

        return $this->render('anonymous/point_game.html.twig', [
            'usePlanet' => $usePlanet,
        ]);
    }

    /**
     * @Route("/nous-contacter/{_locale}", name="contact", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/nous-contacter", name="contact_noSlash")
     */
    public function contactAction(Request $request, \Swift_Mailer $mailer)
    {
        $form_contact = $this->createForm(UserContactType::class);
        $form_contact->handleRequest($request);

        if ($form_contact->isSubmitted()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $message = (new \Swift_Message('Reclamation joueur'))
                ->setFrom('support@areauniverse.eu')
                ->setTo('areauniverse.game@gmail.com')
                ->setBody(
                    $this->renderView(
                        'emails/contact.html.twig',
                        ['text' => $form_contact->get('text')->getData(), 'email' => $form_contact->get('email')->getData()]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            return $this->redirectToRoute('home');
        }

        return $this->render('anonymous/contact.html.twig', [
            'form_contact' => $form_contact->createView(),
        ]);
    }

    /**
     * @Route("/classement-joueurs/{_locale}", name="contact", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/classement-joueurs", name="contact_noSlash")
     */
    public function rankUserUncoAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->join('u.planets', 'p')
            ->select('a.id as alliance, a.sigle as sigle, count(DISTINCT p) as planets, u.id, u.username, r.point as point, r.oldPoint as oldPoint, r.position as position, r.oldPosition as oldPosition, r.warPoint as warPoint, u.createdAt, a.politic as politic')
            ->leftJoin('u.rank', 'r')
            ->leftJoin('u.ally', 'a')
            ->groupBy('u.id')
            ->where('u.rank is not null')
            ->andWhere('u.id != 1')
            ->andWhere('r.point > 200')
            ->orderBy('point', 'DESC')
            ->getQuery()
            ->setMaxResults(100)
            ->getResult();

        $nbrPlayers = $em->getRepository('App:Rank')
            ->createQueryBuilder('r')
            ->select('count(r.id) as nbrPlayer')
            ->getQuery()
            ->getSingleScalarResult();

        $otherPoints = $em->getRepository('App:Stats')
            ->createQueryBuilder('s')
            ->select('count(s) as numbers, sum(DISTINCT s.pdg) as allPdg, sum(DISTINCT s.points) as allPoint, s.date')
            ->groupBy('s.date')
            ->getQuery()
            ->getResult();

        return $this->render('anonymous/rank.html.twig', [
            'users' => $users,
            'nbrPlayers' => $nbrPlayers - 100,
            'otherPoints' => $otherPoints
        ]);
    }
}
