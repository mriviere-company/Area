<?php

namespace App\Controller\Security;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class SecurityController
 * @package App\Controller\Security
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/enregistrement", name="register")
     * @Route("/enregistrement/", name="register_noSlash")
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param MailerInterface $mailer
     * @return RedirectResponse
     * @throws NonUniqueResultException
     * @throws TransportExceptionInterface
     */
    public function registerAction(ManagerRegistry $doctrine, Request $request, MailerInterface $mailer): RedirectResponse
    {
        $em = $doctrine->getManager();

        if ($_POST) {
            $userSameName = $doctrine->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.username = :username')
                ->setParameters(['username' => $_POST['_username']])
                ->getQuery()
                ->getOneOrNullResult();


            $userSameEmail = $doctrine->getRepository(User::class)
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
            $userIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
            $encryptedIp = openssl_encrypt($userIp, "AES-256-CBC", "my personal ip", 0, hex2bin('34857d973953e44afb49ea9d61104d8c'));


            $userSameIp = $doctrine->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.ipAddress = :ip')
                ->setParameters(['ip' => $encryptedIp])
                ->getQuery()
                ->getOneOrNullResult();

            if($userSameIp) {
                $this->addFlash("fail", "Vous avez déjà le compte : " . $userSameIp->getUsername());
                $userSameIp->setCheat($userSameIp->getCheat() + 1);
                $em->flush();
                return $this->redirectToRoute('home');
            }

            $user = new User($_POST['_username'], $_POST['_email'], password_hash($_POST['_password'], PASSWORD_BCRYPT), $encryptedIp, false);
            $em->persist($user);
            $em->flush();

            $message = (new Email())
                ->subject('Confirmation inscription')
                ->from('support@areauniverse.eu')
                ->to($_POST['_email'])
                ->text(
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
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @return RedirectResponse
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function registerGhostAction(ManagerRegistry $doctrine, Request $request): RedirectResponse
    {
        $em = $doctrine->getManager();

        $userIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        $encryptedIp = openssl_encrypt($userIp, "AES-256-CBC", "my personal ip", 0, hex2bin('34857d973953e44afb49ea9d61104d8c'));

        $userSameIp = $doctrine->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.ipAddress = :ip')
            ->setParameters(['ip' => $encryptedIp])
            ->getQuery()
            ->getOneOrNullResult();

        if($userSameIp) {
            $this->addFlash("fail", "Vous avez déjà le compte : " . $userSameIp->getUsername());
            $userSameIp->setCheat($userSameIp->getCheat() + 1);
            $em->flush();
            return $this->redirectToRoute('home');
        }

        $number = $doctrine->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('count(u)')
            ->getQuery()
            ->getSingleScalarResult();

        $user = new User('Test' . $number, 'Test' . $number . '@areauniverse.eu', password_hash('connected', PASSWORD_BCRYPT), $encryptedIp, false);
        $em->persist($user);
        $em->flush();

        $token = new UsernamePasswordToken(
            $user,
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
    public function loginAction(ManagerRegistry $doctrine): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();

        if($user) {
            if ($user->getRoles()[0] == 'ROLE_MODO' || $user->getRoles()[0] == 'ROLE_ADMIN') {
                return $this->redirectToRoute('server_select');
            }
            if ($user->getId() === 220) {
                $user->setTutorial(1);
                $em->flush();
                return $this->redirectToRoute('game_over');
            }

            if (!$user->getSpecUsername()) {
                $userIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
                $encryptedIp = openssl_encrypt($userIp, "AES-256-CBC", "my personal ip", 0, hex2bin('34857d973953e44afb49ea9d61104d8c'));

                $userSameIp = $doctrine->getRepository(User::class)
                    ->createQueryBuilder('u')
                    ->where('u.ipAddress = :ip')
                    ->andWhere('u.username != :user')
                    ->setParameters(['user' => $user->getUsername(), 'ip' => $encryptedIp])
                    ->getQuery()
                    ->getOneOrNullResult();

                if($userSameIp) {
                    $this->addFlash("fail", "Vous avez déjà le compte : " . $userSameIp->getUsername());
                    return $this->redirectToRoute('home');
                }
            } else {
                $user->setIpAddress(null);
                $em->flush();
            }
            if ($user->getConnectLast()) {
                $commander = $user->getMainCommander();
                $usePlanet = $doctrine->getRepository(Planet::class)->findByFirstPlanet($commander);
                return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
            }
            return $this->redirectToRoute('server_select');
        }

        $this->addFlash("fail", "Le mot de passe est incorrect.");
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/au-revoir", name="erase_cookie")
     * @Route("/au-revoir/", name="erase_cookie_noSlash")
     */
    public function eraseCookieAction(ManagerRegistry $doctrine): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();

        $rememberMes = $doctrine->getRepository(RemembermeToken::class)
            ->createQueryBuilder('r')
            ->where('r.username =:username')
            ->setParameters(['username' => $user->getUsername()])
            ->getQuery()
            ->getResult();

        if($rememberMes) {
            foreach($rememberMes as $rememberMe) {
                $em->remove($rememberMe);
            }
            $em->flush();
        }
        return $this->redirectToRoute('logout');
    }

    /**
     * @Route("/logout", name="logout")
     * @Route("/logout/", name="logout_noSlash")
     */
    public function logoutAction()
    {
    }
}