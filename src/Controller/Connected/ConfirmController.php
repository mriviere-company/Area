<?php

namespace App\Controller\Connected;

use Swift_Mailer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\ConfirmType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\Planet;
use DateTime;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class ConfirmController extends AbstractController
{
    /**
     * @Route("/confirmation-compte/{usePlanet}", name="confirm_account", requirements={"usePlanet"="\d+"})
     * @param Request $request
     * @param Planet $usePlanet
     * @param Swift_Mailer $mailer
     * @return RedirectResponse|Response
     */
    public function confirmAction(Request $request, Planet $usePlanet, Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if($user->getConfirmed() == 1) {
            return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_confirm = $this->createForm(ConfirmType::class, null);
        $form_confirm->handleRequest($request);

        if ($form_confirm->isSubmitted() && $form_confirm->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $user->setConfirmed(1);
            $user->setEmail($form_confirm->get('email')->getData());
            $user->setPassword(password_hash($form_confirm->get('password')->getData(), PASSWORD_BCRYPT));
            $user->setUsername($form_confirm->get('username')->getData());
            $em->flush();

            $message = (new \Swift_Message('Confirmation inscription'))
                ->setFrom('support@areauniverse.eu')
                ->setTo($form_confirm->get('email')->getData())
                ->setBody(
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

        return $this->render('connected/confirm.html.twig', [
            'form_confirm' => $form_confirm->createView(),
            'usePlanet' => $usePlanet,
        ]);
    }
}