<?php

namespace App\Controller\Connected;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\ConfirmType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\Planet;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class ConfirmController extends AbstractController
{
    /**
     * @Route("/confirmation-compte/{usePlanet}", name="confirm_account", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @param MailerInterface $mailer
     * @return RedirectResponse|Response
     * @throws TransportExceptionInterface
     */
    public function confirmAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet, MailerInterface $mailer): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();

        if($user->getConfirmed() == 1) {
            return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_confirm = $this->createForm(ConfirmType::class);
        $form_confirm->handleRequest($request);

        if ($form_confirm->isSubmitted() && $form_confirm->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $user->setConfirmed(1);
            $user->setEmail($form_confirm->get('email')->getData());
            $user->setPassword(password_hash($form_confirm->get('password')->getData(), PASSWORD_BCRYPT));
            $user->setUsername($form_confirm->get('username')->getData());
            $em->flush();

            $message = (new Email())
                ->subject('Confirmation inscription')
                ->from('support@areauniverse.eu')
                ->to($form_confirm->get('email')->getData())
                ->text(
                    $this->renderView(
                        'emails/registration.html.twig',
                        [
                            'password' => $form_confirm->get('password')->getData(),
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

        return $this->render('connected/confirm.html.twig', [
            'form_confirm' => $form_confirm->createView(),
            'usePlanet' => $usePlanet,
        ]);
    }
}