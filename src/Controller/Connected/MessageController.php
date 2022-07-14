<?php

namespace App\Controller\Connected;

use App\Entity\Character;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function messageAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet): RedirectResponse|Response
    {
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if($character->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $em = $doctrine->getManager();
        $now = new DateTime();
        $nowDel = new DateTime();
        $nowDel->sub(new DateInterval('PT' . 1209600 . 'S'));

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
            ->setParameters(['id' => $character->getId()])
            ->orderBy('m.sendAt', 'DESC')
            ->getQuery()
            ->getResult();

        $messagesR = $em->getRepository('App:Message')
            ->createQueryBuilder('m')
            ->where('m.character = :character')
            ->setParameters(['character' => $character])
            ->orderBy('m.sendAt', 'DESC')
            ->getQuery()
            ->getResult();

        $character->setViewMessage(true);

        $em->flush();

        $form_message = $this->createForm(MessageType::class);
        $form_message->handleRequest($request);

        if ($form_message->isSubmitted() && $form_message->isValid() && abs($form_message->get('bitcoin')->getData()) < $character->getBitcoin() &&
            ($character->getSalonBan() > $now || $character->getSalonBan() == null)) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");
            $recever = $form_message->get('character')->getData();
            $message = new Message($form_message->get('character')->getData(), nl2br($form_message->get('title')->getData()), nl2br($form_message->get('content')->getData()), abs($form_message->get('bitcoin')->getData()), $character->getId(), $form_message->get('anonymous')->getData() ? $character->getUsername() : null);
            $recever->setBitcoin($recever->getBitcoin() + abs($form_message->get('bitcoin')->getData()));
            $recever->setViewMessage(false);
            $character->setBitcoin($character->getBitcoin() - abs($form_message->get('bitcoin')->getData()));
            $em->persist($message);
            $quest = $character->checkQuests('private_message');
            if($quest) {
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                $character->removeQuest($quest);
            }

            $em->flush();

            return $this->redirectToRoute('message', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->render('connected/message.html.twig', [
            'usePlanet' => $usePlanet,
            'messages' => $messages,
            'messagesR' => $messagesR,
            'form_message' => $form_message->createView(),
        ]);
    }

    /**
     * @Route("/repondre/{userRecever}/{usePlanet}", name="message_responde", requirements={"usePlanet"="\d+", "userRecever"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @param Planet $usePlanet
     * @param Character $userRecever
     * @return RedirectResponse|Response
     */
    public function messageRespondeAction(ManagerRegistry $doctrine, Request $request, Planet $usePlanet, Character $userRecever): RedirectResponse|Response
    {
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if($character->getGameOver()) {
            return $this->redirectToRoute('game_over');
        }
        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }
        $now = new DateTime();

        if($character->getSalonBan() > $now) {
            return $this->redirectToRoute('message', ['usePlanet' => $usePlanet->getId()]);
        }
        $em = $doctrine->getManager();

        $form_message = $this->createForm(MessageRespondeType::class);
        $form_message->handleRequest($request);

        if ($form_message->isSubmitted() && $form_message->isValid() && abs($form_message->get('bitcoin')->getData()) < $character->getBitcoin()) {
            $this->get("security.csrf.token_manager")->refreshToken("task_item");

            $message = new Message($userRecever, nl2br($form_message->get('title')->getData()), nl2br($form_message->get('content')->getData()), abs($form_message->get('bitcoin')->getData()), $character->getId(), $form_message->get('anonymous')->getData() ? $character->getUsername() : null);

            $userRecever->setBitcoin($userRecever->getBitcoin() + abs($form_message->get('bitcoin')->getData()));
            $userRecever->setViewMessage(false);
            $character->setBitcoin($character->getBitcoin() - abs($form_message->get('bitcoin')->getData()));
            $em->persist($message);
            $quest = $character->checkQuests('private_message');
            if($quest) {
                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                $character->removeQuest($quest);
            }

            $em->flush();
            return $this->redirectToRoute('message', ['usePlanet' => $usePlanet->getId()]);
        }

        return $this->render('connected/profil/user_responde.html.twig', [
            'usePlanet' => $usePlanet,
            'form_message' => $form_message->createView(),
            'userRecever' => $userRecever,
        ]);
    }

    /**
     * @Route("/message-view/{message}/{usePlanet}", name="message_view", requirements={"usePlanet"="\d+", "message"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @param Message $message
     * @return RedirectResponse
     */
    public function messageViewAction(ManagerRegistry $doctrine, Planet $usePlanet, Message $message): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        if ($user == $message->getUser()) {
            $message->setNewMessage(false);
            $em->flush();
        }

        return $this->redirectToRoute('message', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/message-share/{message}/{usePlanet}", name="message_share", requirements={"usePlanet"="\d+", "message"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @param Message $message
     * @return RedirectResponse
     */
    public function messageShareAction(ManagerRegistry $doctrine, Planet $usePlanet, Message $message): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }
        if ($user == $message->getUser()) {
            $alpha = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-'];
            $newShareKey = $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)]
                . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)]
                . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)] . $alpha[rand(0, 36)];

        $message->setShareKey($newShareKey);
            $em->flush();
        }

        return $this->redirectToRoute('message', ['usePlanet' => $usePlanet->getId()]);
    }

    /**
     * @Route("/message-view-all/{usePlanet}/", name="message_all_view", requirements={"usePlanet"="\d+"})
     * @param ManagerRegistry $doctrine
     * @param Planet $usePlanet
     * @return RedirectResponse
     */
    public function messageAllViewAction(ManagerRegistry $doctrine, Planet $usePlanet): RedirectResponse
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();
        $character = $user->getCharacter($usePlanet->getSector()->getGalaxy()->getServer());

        if ($usePlanet->getCharacter() != $character) {
            return $this->redirectToRoute('home');
        }

        $messages = $em->getRepository('App:Message')
            ->createQueryBuilder('m')
            ->where('m.newMessage = true')
            ->andWhere('m.character = :character')
            ->setParameters(['character' => $character])
            ->getQuery()
            ->getResult();

        foreach($messages as $message) {
            $message->setNewMessage(false);
        }

        $em->flush();

        return $this->redirectToRoute('message', ['usePlanet' => $usePlanet->getId()]);
    }
}