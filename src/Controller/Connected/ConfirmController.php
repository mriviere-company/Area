<?php

namespace App\Controller\Connected;

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
     */
    public function confirmAction(Request $request, Planet $usePlanet, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();

        if($user->getConfirmed() == 1) {
            return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_confirm = $this->createForm(ConfirmType::class, $user);
        $form_confirm->handleRequest($request);

        if ($form_confirm->isSubmitted() && $form_confirm->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $user->setConfirmed(1);
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
            $request->getSession()->set('main', serialize($token));

            return $this->redirectToRoute('login');
        }

        return $this->render('connected/confirm.html.twig', [
            'form_confirm' => $form_confirm->createView(),
            'usePlanet' => $usePlanet,
        ]);
    }
}