<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class PlanetRepository extends EntityRepository
{
    /**
     * @param $id
     * @param $user
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByCurrentPlanet($id, $user) {
        return $this->createQueryBuilder('p')
            ->where('p.id = :id')
            ->andWhere('p.user = :user')
            ->setParameters(['id' => $id, 'user' => $user])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $user
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByFirstPlanet($user) {
        $request = $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->where('u.username = :user')
            ->setParameters(['user' => $user->getUsername()]);
        if ($user->getOrderPlanet() == 'alpha') {
            $request->orderBy('p.name', 'ASC');
        } elseif ($user->getOrderPlanet() == 'colo') {
            $request->orderBy('p.nbColo', 'ASC');
        } else {
            $request->orderBy('p.id', 'ASC');
        }
        return $request->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}