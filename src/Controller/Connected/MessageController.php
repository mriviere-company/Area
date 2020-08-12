<?php

namespace App\Controller\Connected;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Front\MessageType;
use App\Form\Front\MessageRespondeType;
use App\Entity\Message;
use App\Entity\Planet;
use DateTime;
use DateInterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class MessageController extends AbstractController
{
    /**
     * @Route("/message/{usePlanet}", name="message", requirements={"usePlanet"="\d+"})
     */
    public function messageAction(Request $request, Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $message = new Message();
        $now = new DateTime();
        $nowDel = new DateTime();
        $nowDel->sub(new DateInterval('PT' . 1209600 . 'S'));

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $removeMessages = $em->getRepository('App:Message')
            ->createQueryBuilder('m')
            ->where('m.sendAt < :now')
            ->setParameters(['now' => $nowDel])
            ->getQuery()
            ->getResult();

        if($removeMessages) {
            foreach($removeMessages as $removeMessage) {
                $em->remove($removeMessage);
            }
        }
        $em->flush();

        $messages = $em->getRepository('App:Message')
            ->createQueryBuilder('m')
            ->where('m.idSender = :id')
            ->setParameters(['id' => $user->getId()])
            ->orderBy('m.sendAt', 'DESC')
            ->getQuery()
            ->getResult();

        $messagesR = $em->getRepository('App:Message')
            ->createQueryBuilder('m')
            ->where('m.user = :user')
            ->setParameters(['user' => $user])
            ->orderBy('m.sendAt', 'DESC')
            ->getQuery()
            ->getResult();

        $user->setViewMessage(true);

        $em->flush();

        $form_message = $this->createForm(MessageType::class, $message);
        $form_message->handleRequest($request);

        if ($form_message->isSubmitted() && $form_message->isValid() && abs($form_message->get('bitcoin')->getData()) < $user->getBitcoin() &&
            ($user->getSalonBan() > $now || $user->getSalonBan() == null)) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $recever = $form_message->get('user')->getData();
            if ($form_message->get('anonymous')->getData() == false) {
                $message->setSender($user->getUsername());
            }
            $message->setIdSender($user->getId());
            $message->setContent(nl2br($form_message->get('content')->getData()));
            $message->setSendAt($now);
            $recever->setBitcoin($recever->getBitcoin() + abs($form_message->get('bitcoin')->getData()));
            $recever->setViewMessage(false);
            $user->setBitcoin($user->getBitcoin() - abs($form_message->get('bitcoin')->getData()));
            $em->persist($message);
            $quest = $user->checkQuests('private_message');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }

            $em->flush();

            $form_message = null;
            $form_message = $this->createForm(MessageType::class);
        }

        return $this->render('connected/message.html.twig', [
            'usePlanet' => $usePlanet,
            'messages' => $messages,
            'messagesR' => $messagesR,
            'form_message' => $form_message->createView(),
        ]);
    }

    /**
     * @Route("/repondre/{id}/{usePlanet}", name="message_responde", requirements={"usePlanet"="\d+", "id"="\d+"})
     */
    public function messageRespondeAction(Request $request, Planet $usePlanet, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $message = new Message();
        $now = new DateTime();

        if($user->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if($user->getSalonBan() > $now) {
            return $this->redirectToRoute('message', ['usePlanet' => $usePlanet->getId()]);
        }

        $form_message = $this->createForm(MessageRespondeType::class, $message);
        $form_message->handleRequest($request);

        if ($form_message->isSubmitted() && $form_message->isValid() && abs($form_message->get('bitcoin')->getData()) < $user->getBitcoin()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $userRecever = $em->getRepository('App:User')->find(['id' => $id]);

            if ($form_message->get('anonymous')->getData() == false) {
                $message->setSender($user->getUsername());
            }
            $message->setIdSender($user->getId());
            $message->setUser($userRecever);
            $message->setContent(nl2br($form_message->get('content')->getData()));
            $message->setSendAt($now);
            $userRecever->setBitcoin($userRecever->getBitcoin() + abs($form_message->get('bitcoin')->getData()));
            $userRecever->setViewMessage(false);
            $user->setBitcoin($user->getBitcoin() - abs($form_message->get('bitcoin')->getData()));
            $em->persist($message);
            $quest = $user->checkQuests('private_message');
            if($quest) {
                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                $user->removeQuest($quest);
            }

            $em->flush();
            return $this->redirectToRoute('message', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->render('connected/profil/user_responde.html.twig', [
            'usePlanet' => $usePlanet,
            'form_message' => $form_message->createView(),
            'id' => $id,
        ]);
    }

    /**
     * @Route("/message-view/{id}/{usePlanet}", name="message_view", requirements={"usePlanet"="\d+", "id"="\d+"})
     */
    public function messageViewAction(Planet $usePlanet, Message $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        if ($user == $id->getUser()) {
            $id->setNewMessage(false);
            $em->flush();
        }

        return $this->redirectToRoute('message', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/message-share/{id}/{usePlanet}", name="message_share", requirements={"usePlanet"="\d+", "id"="\d+"})
     */
    public function messageShareAction(Planet $usePlanet, Message $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }
        if ($user == $id->getUser()) {
            $alpha = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-'];
            $newShareKey = $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)]
                . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)]
                . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)];

            $id->setShareKey($newShareKey);
            $em->flush();
        }

        return $this->redirectToRoute('message', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/message-view-all/{usePlanet}/", name="message_all_view", requirements={"usePlanet"="\d+"})
     */
    public function messageAllViewAction(Planet $usePlanet)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if ($usePlanet->getUser() != $user) {
            return $this->redirectToRoute('home');
        }

        $messages = $em->getRepository('App:Message')
            ->createQueryBuilder('m')
            ->where('m.newMessage = true')
            ->andWhere('m.user = :user')
            ->setParameters(['user' => $user])
            ->getQuery()
            ->getResult();

        foreach($messages as $message) {
            $message->setNewMessage(false);
        }

        $em->flush();

        return $this->redirectToRoute('message', ['usePlanet' => $usePlanet->getId()]);
    }
}