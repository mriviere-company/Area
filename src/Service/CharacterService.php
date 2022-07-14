<?php

namespace App\Service;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CharacterService extends AbstractController
{
    public function characterAction(ManagerRegistry $doctrine)
    {
        $user = $this->getUser();
        $character = $user->getMainCharacter();
        $em = $doctrine->getManager();

        return $em->getRepository('App:Character')
            ->createQueryBuilder('c')
            ->where('c.user = :user')
            ->setParameters(['character' => $character])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}