<?php

namespace App\Controller;

use App\Form\Front\UserOptionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class TopMenuController
 * @package App\Controller
 */
class TopMenuController extends AbstractController
{
    /**
     * @Route("/reglement/{_locale}", name="rules", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/reglement", name="rules_noSlash")
     */
    public function rulesAction(): Response
    {
        return $this->render('anonymous/rules.html.twig');
    }

    /**
     * @Route("/medias/{_locale}", name="medias", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/medias", name="medias_noSlash")
     */
    public function mediasAction(): Response
    {
        return $this->render('anonymous/medias.html.twig');
    }

    /**
     * @Route("/but-du-jeu/{_locale}", name="point_game", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/but-du-jeu", name="point_game_noSlash")
     */
    public function pointGameAction(): Response
    {
        return $this->render('anonymous/point_game.html.twig');
    }

    /**
     * @Route("/crypto-currencie/{_locale}", name="crypto", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/crypto-currencie", name="crypto_noSlash")
     */
    public function cryptoAction(): Response
    {
        return $this->render('anonymous/crypto.html.twig');
    }

    /**
     * @Route("/mes-options/{_locale}", name="parameters", defaults={"_locale" = "fr"}, requirements={"_locale" = "fr|en|de"})
     * @Route("/mes-options", name="parameters_noSlash")
     * @param Request $request
     * @return Response
     */
    public function optionsAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $form_parameters = $this->createForm(UserOptionType::class, null, [
            "username" => $user->getUsername(),
            "newletter" => $user->getNewletter() ? true : false,
            "connectLast" => $user->getConnectLast() ? true : false,
            "wallet_address" => $user->getWalletAddress()
        ]);
        $form_parameters->handleRequest($request);

        if ($form_parameters->isSubmitted() && $form_parameters->isValid()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            if(password_verify($form_parameters->get('oldPassword')->getData(), $user->getPassword())) {
                if(count($form_parameters->get('password')->getData()) == 1 && $form_parameters->get('password')->getData() == $form_parameters->get('confirmPassword')->getData()) {
                    $user->setPassword(password_hash($form_parameters->get('password')->getData(), PASSWORD_BCRYPT));
                }
            }
            $user->setUsername($form_parameters->get('username')->getData());
            $user->setConnectLast($form_parameters->get('connect_last')->getData());
            $user->setWalletAddress($form_parameters->get('wallet_address')->getData());
            $user->setNewletter($form_parameters->get('newletter')->getData());

            $em->flush();
        }

        return $this->render('anonymous/parameters.html.twig', [
            'form_parameters' => $form_parameters->createView()
        ]);
    }
}
