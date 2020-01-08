<?php

namespace App\Controller\Security;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use DateTime;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SecurityController extends AbstractController
{
    /**
     * @Route("/enregistrement", name="register")
     * @Route("/enregistrement/", name="register_noSlash")
     */
    public function registerAction(Request $request, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();
        $user = new User();

        if ($_POST) {
            $userSameName = $em->getRepository('App:User')
                ->createQueryBuilder('u')
                ->where('u.username = :username')
                ->setParameters(['username' => $_POST['_username']])
                ->getQuery()
                ->getOneOrNullResult();


            $userSameEmail = $em->getRepository('App:User')
                ->createQueryBuilder('u')
                ->orWhere('u.email = :email')
                ->setParameters(['email' => $_POST['_email']])
                ->getQuery()
                ->getOneOrNullResult();

            if($userSameName) {
                if (strtoupper($userSameName->getUsername()) == strtoupper($_POST['_username'])) {
                    $this->addFlash("fail", "Ce pseudo existe déjà sur le jeu.");
                    return $this->redirectToRoute('home');
                }
            }
            if($userSameEmail) {
                if (strtoupper($userSameEmail->getEmail()) == strtoupper($_POST['_email'])) {
                    $this->addFlash("fail", "Cet email est déjà utilisé sur le compte - " . $userSameEmail->getUsername());
                    return $this->redirectToRoute('home');
                }
            }
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $userIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $userIp = $_SERVER['REMOTE_ADDR'];
            }

            $userSameIp = $em->getRepository('App:User')
                ->createQueryBuilder('u')
                ->where('u.ipAddress = :ip')
                ->setParameters(['ip' => $userIp])
                ->getQuery()
                ->getOneOrNullResult();

            if($userSameIp) {
                $this->addFlash("fail", "Vous avez déjà le compte : " . $userSameIp->getUsername());
                $userSameIp->setCheat($userSameIp->getCheat() + 1);
                $em->flush();
                return $this->redirectToRoute('home');
            }

            $now = new DateTime();
            $now->setTimezone(new DateTimeZone('Europe/Paris'));
            $user->setUsername($_POST['_username']);
            $user->setEmail($_POST['_email']);
            $user->setCreatedAt($now);
            $user->setPassword(password_hash($_POST['_password'], PASSWORD_BCRYPT));
            $user->setIpAddress($userIp);
            $em->persist($user);
            $em->flush();

            $message = (new \Swift_Message('Confirmation inscription'))
                ->setFrom('support@areauniverse.eu')
                ->setTo($_POST['_email'])
                ->setBody(
                    $this->renderView(
                        'emails/registration.html.twig',
                        [
                            'password' => $_POST['_password'],
                            'username' => $user->getUsername(),
                            'key' => $user->getId() //fixmr encrypt
                        ]
                    ),
                    'text/html'
                );

            $mailer->send($message);

            $token = new UsernamePasswordToken(
                $user,
                null,
                'main',
                $user->getRoles()
            );

           $this->get('security.token_storage')->setToken($token);
           $request->getSession()->set('_security_main', serialize($token));

           $event = new InteractiveLoginEvent($request, $token);
           $dispatcher = new EventDispatcher();
           $dispatcher->dispatch($event, "security.interactive_login");

           return $this->redirectToRoute('login');
        }
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/enregistrement-anonyme", name="register_ghost")
     * @Route("/enregistrement-anonyme/", name="register_ghost_noSlash")
     */
    public function registerGhostAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $userIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $userIp = $_SERVER['REMOTE_ADDR'];
        }

        $userSameIp = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->where('u.ipAddress = :ip')
            ->setParameters(['ip' => $userIp])
            ->getQuery()
            ->getOneOrNullResult();

        if($userSameIp) {
            $this->addFlash("fail", "Vous avez déjà le compte : " . $userSameIp->getUsername());
            $userSameIp->setCheat($userSameIp->getCheat() + 1);
            $em->flush();
            return $this->redirectToRoute('home');
        }

        $number = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->select('count(u)')
            ->getQuery()
            ->getSingleScalarResult();

        $user = new User();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $user->setUsername('Test' . $number);
        $user->setEmail('Test' . $number . '@areauniverse.eu');
        $user->setCreatedAt($now);
        $user->setPassword(password_hash('connected', PASSWORD_BCRYPT));
        $user->setIpAddress($userIp);
        $user->setConfirmed(0);
        $em->persist($user);
        $em->flush();

        $token = new UsernamePasswordToken(
            $user,
            null,
            'main',
            $user->getRoles()
        );

        $this->get('security.token_storage')->setToken($token);
        $request->getSession()->set('_security_main', serialize($token));

        $event = new InteractiveLoginEvent($request, $token);
        $dispatcher = new EventDispatcher();
        $dispatcher->dispatch($event, "security.interactive_login");

        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/login", name="login")
     * @Route("/login/", name="login_noSlash")
     */
    public function loginAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if($user) {
            if ($user->getBot() == 1) {
                $user->setBot(0);
                $em->flush();
            }
            if($user->getRoles()[0] == 'ROLE_PRIVATE') {
                return $this->redirectToRoute('private_home');
            }

            if($user->getGameOver()) {
                return $this->redirectToRoute('game_over');
            }

            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($user->getUsername());

            if($usePlanet) {
                return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
            } else {
                $servers = $em->getRepository('App:Server')
                    ->createQueryBuilder('s')
                    ->select('s.id, s.open, s.pvp')
                    ->groupBy('s.id')
                    ->orderBy('s.id', 'ASC')
                    ->getQuery()
                    ->getResult();

                $galaxys = $em->getRepository('App:Galaxy')
                    ->createQueryBuilder('g')
                    ->join('g.server', 'ss')
                    ->join('g.sectors', 's')
                    ->join('s.planets', 'p')
                    ->leftJoin('p.user', 'u')
                    ->select('g.id, g.position, count(DISTINCT u.id) as users, ss.id as server')
                    ->groupBy('g.id')
                    ->orderBy('g.position', 'ASC')
                    ->getQuery()
                    ->getResult();

                return $this->render('connected/play.html.twig', [
                    'galaxys' => $galaxys,
                    'servers' => $servers
                ]);
            }
        }

        $this->addFlash("fail", "Le mot de passe est incorrect.");
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/logout", name="logout")
     * @Route("/logout/", name="logout_noSlash")
     */
    public function logoutAction()
    {
    }

    /**
     * @Route("/deconnexion", name="disconnect_me")
     * @Route("/deconnexion/", name="disconnect_me_noSlash")
     */
    public function disconnectMeAction()
    {
        return $this->redirectToRoute('logout');
    }

    /**
     * @Route("/login-redirect", name="login_redirect")
     * @Route("/login-redirect/", name="login_redirect_noSlash")
     */
    public function loginRedirectAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if (!$user->getSpecUsername()) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $userIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $userIp = $_SERVER['REMOTE_ADDR'];
        }
        if ($user->getBot() == 1) {
            $user->setBot(0);
            $em->flush();
        }

        $userSameIp = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->where('u.ipAddress = :ip')
            ->andWhere('u.username != :user')
            ->setParameters(['user' => $user->getUsername(), 'ip' => $userIp])
            ->getQuery()
            ->getOneOrNullResult();

        if($userSameIp && $this->getUser()->getRoles()[0] == 'ROLE_USER') {
            $this->addFlash("fail", "Vous avez déjà le compte : " . $userSameIp->getUsername());
            return $this->redirectToRoute('home');
        }
        } else {
            $userIp = null;
            $user->setIpAddress(null);
        }

        if ($this->getUser()->getRoles()[0] == 'ROLE_USER') {
            $em = $this->getDoctrine()->getManager();
            $now = new DateTime();
            $now->setTimezone(new DateTimeZone('Europe/Paris'));

            if($user->getGameOver()) {
                return $this->redirectToRoute('game_over');
            }
            $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($this->getUser()->getUsername());

            $user->setIpAddress($userIp);
            $user->setLastActivity($now);
            $em->flush();

            if($usePlanet) {
                return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
            } else {

                $servers = $em->getRepository('App:Server')
                    ->createQueryBuilder('s')
                    ->select('s.id, s.open, s.pvp')
                    ->groupBy('s.id')
                    ->orderBy('s.id', 'ASC')
                    ->getQuery()
                    ->getResult();

                $galaxys = $em->getRepository('App:Galaxy')
                    ->createQueryBuilder('g')
                    ->join('g.server', 'ss')
                    ->join('g.sectors', 's')
                    ->join('s.planets', 'p')
                    ->leftJoin('p.user', 'u')
                    ->select('g.id, g.position, count(DISTINCT u.id) as users, ss.id as server')
                    ->groupBy('g.id')
                    ->orderBy('g.position', 'ASC')
                    ->getQuery()
                    ->getResult();

                return $this->render('connected/play.html.twig', [
                    'galaxys' => $galaxys,
                    'servers' => $servers
                ]);
            }
        }
        if ($this->getUser()->getRoles()[0] == 'ROLE_MODO' || $this->getUser()->getRoles()[0] == 'ROLE_ADMIN') {
            return $this->redirectToRoute('administration');
        }
        return $this->redirectToRoute('logout');
    }
}