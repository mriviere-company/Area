<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CharacterService extends AbstractController
{
    public function characterAction()
    {
        $user = $this->getUser();
        $character = $user->getMainCharacter();
        $em = $this->getDoctrine()->getManager();
        $character = $em->getRepository('App:Character')
            ->createQueryBuilder('c')
            ->where('c.user = :user')
            ->setParameters(['character' => $character])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        var_dump($character); exit;
        return $character;
    }
}