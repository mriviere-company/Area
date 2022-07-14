<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class PlanetRepository extends EntityRepository
{
    /**
     * @param $id
     * @param $character
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findByCurrentPlanet($id, $character): mixed
    {
        return $this->createQueryBuilder('p')
            ->where('p.id = :id')
            ->andWhere('p.character = :character')
            ->setParameters(['id' => $id, 'character' => $character])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $character
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findByFirstPlanet($character): mixed
    {
        if ($character) {
            $request = $this->createQueryBuilder('p')
                ->where('p.character = :character')
                ->setParameters(['character' => $character]);
            if ($character->getOrderPlanet() == 'alpha') {
                $request->orderBy('p.name', 'ASC');
            } elseif ($character->getOrderPlanet() == 'colo') {
                $request->orderBy('p.nbColo', 'ASC');
            } else {
                $request->orderBy('p.id', 'ASC');
            }
            return $request->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();
        }
        return null;
    }
}