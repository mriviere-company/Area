<?php

namespace App\Service;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommanderService extends AbstractController
{
    public function commanderAction(ManagerRegistry $doctrine)
    {
        $user = $this->getUser();
        $commander = $user->getMainCommander();
        $em = $doctrine->getManager();

        return $doctrine->getRepository(Commander::class)
            ->createQueryBuilder('c')
            ->where('c.user = :user')
            ->setParameters(['commander' => $commander])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}