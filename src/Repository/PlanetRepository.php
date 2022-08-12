<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class PlanetRepository extends EntityRepository
{
    /**
     * @param $id
     * @param $commander
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findByCurrentPlanet($id, $commander): mixed
    {
        return $this->createQueryBuilder('p')
            ->where('p.id = :id')
            ->andWhere('p.commander = :commander')
            ->setParameters(['id' => $id, 'commander' => $commander])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $commander
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findByFirstPlanet($commander): mixed
    {
        if ($commander) {
            $request = $this->createQueryBuilder('p')
                ->where('p.commander = :commander')
                ->setParameters(['commander' => $commander]);
            if ($commander->getOrderPlanet() == 'alpha') {
                $request->orderBy('p.name', 'ASC');
            } elseif ($commander->getOrderPlanet() == 'colo') {
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