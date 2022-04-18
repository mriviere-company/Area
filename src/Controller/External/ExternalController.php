<?php

namespace App\Controller\External;

use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ExternalController extends AbstractController
{
    /**
     * @Route("/nouveau-password/{key}", name="recoveryPw", requirements={"key"="\d+"})
     * @param MailerInterface $mailer
     * @param $key
     * @return RedirectResponse
     * @throws NonUniqueResultException
     */
    public function recoveryPwAction(MailerInterface $mailer, $key): RedirectResponse
    {
        $userId = $key; //fixmr decrypt
        $em = $this->getDoctrine()->getManager();
        if($this->getUser()) {
            return $this->redirectToRoute('home');
        }
        $userPw = $em->getRepository('App:User')
            ->createQueryBuilder('u')
            ->where('u.id = :user')
            ->setParameter('user', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        if($userPw) {
            $alpha = ['a', '8', 'c', '&', 'e', 'f', 'g', '5', 'i', '-', 'k', 'l', '(', 'n', 'o', 'M', 'A', 'F', ':', 'w', 'Z'];
            $newPassword = $alpha[rand(0, 14)] . $alpha[rand(0, 14)] . $alpha[rand(0, 14)] . $alpha[rand(0, 14)] . $alpha[rand(0, 14)] . $alpha[rand(0, 14)] . $alpha[rand(0, 14)] . $alpha[rand(0, 14)] . $alpha[rand(0, 14)];
            $userPw->setPassword(password_hash($newPassword, PASSWORD_BCRYPT));
            $em->flush();

            $message = (new Email())
                ->subject('Nouveau mot de passe')
                ->from('support@areauniverse.eu')
                ->to($userPw->getEmail())
                ->text(
                    $this->renderView(
                        'emails/recoveryPw.html.twig',
                        ['password' => $newPassword]
                    ),
                    'text/html'
                );

            $mailer->send($message);
        }
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/confirmation-email/{key}", name="confirmEmail", requirements={"key"=".+"})
     * @Route("/confirmation-email/{key}/", name="confirmEmail_noSlash", requirements={"key"=".+"})
     * @param Request $request
     * @param $key
     * @return RedirectResponse
     */
    public function confirmEmailAction(Request $request, $key): RedirectResponse
    {
        $userId = decrypt($key); //fixmr decrypt
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('App:User')->find(['id' => $userId]);
        $user->setEmailConfirm(1);
        $em->flush();

        $token = new UsernamePasswordToken(
            $user,
            null,
            'main',
            $user->getRoles()
        );

        $this->get('security.token_storage')->setToken($token);
        $request->getSession()->set('main', serialize($token));

        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/desactiver-newletter/{key}", name="deactivate_newletter", requirements={"key"=".+"})
     * @Route("/desactiver-newletter/{key}/", name="deactivate_newletter_noSlash", requirements={"key"=".+"})
     * @param Request $request
     * @param $key
     * @return RedirectResponse
     */
    public function deactivateNewletterAction(Request $request, $key): RedirectResponse
    {
        $userId = decrypt($key); //fixmr decrypt
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('App:User')->find(['id' => $userId]);
        $user->setNewletter(1);
        $em->flush();

        $token = new UsernamePasswordToken(
            $user,
            null,
            'main',
            $user->getRoles()
        );

        $this->get('security.token_storage')->setToken($token);
        $request->getSession()->set('main', serialize($token));

        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/mail-admin", name="mail_while")
     * @Route("/mail-admin/", name="mail_while_noSlash")
     * @param MailerInterface $mailer
     * @return RedirectResponse
     */
    public function mailAdminAction(MailerInterface $mailer): RedirectResponse
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('App:User')->findAll();

        foreach($users as $user) {
            if (stripos(strtoupper($user->getEmail()), 'FAKE') == FALSE) {
                $message = (new Email())
                    ->subject('Area Universe de retour en 2022')
                    ->from('support@areauniverse.eu')
                    ->to($user->getEmail())
                    ->text(
                        $this->renderView(
                            'emails/new_server.html.twig',
                            [
                                'username' => $user->getUsername()
                            ]
                        ),
                        'text/html'
                    );
                $mailer->send($message);
            }
        }

        return $this->redirectToRoute('home');
    }
}