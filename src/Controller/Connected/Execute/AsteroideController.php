<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Destination;
use App\Entity\Fleet;
use DateTimeZone;
use Dateinterval;
use DateTime;

class AsteroideController extends AbstractController
{
    public function AsteroideAction($asteroides, $em)
    {
        $nowAste = new DateTime();
        $nowAste->setTimezone(new DateTimeZone('Europe/Paris'));

        $nowAste->add(new DateInterval('PT' . (25000 * rand(1, 4)) . 'S'));
        foreach ($asteroides as $asteroide) {

            $asteroide->setRecycleAt($nowAste);
            $asteroide->setNbCdr($asteroide->getNbCdr() + rand(50000, 500000));
            $asteroide->setWtCdr($asteroide->getWtCdr() + rand(40000, 400000));


            if (rand(1, 50) == 50) {
                $asteroide->setCdr(false);
                $asteroide->setEmpty(true);
                $asteroide->setImageName(null);
                $asteroide->setRecycleAt(null);
                $asteroide->setName('Vide');
                $newAsteroides = $em->getRepository('App:Planet')
                    ->createQueryBuilder('p')
                    ->join('p.sector', 's')
                    ->join('s.galaxy', 'g')
                    ->where('p.empty = true')
                    ->andWhere('s.position = :rand')
                    ->andWhere('g.position = :galaxy')
                    ->setParameters(['rand' => rand(1, 100), 'galaxy' => $asteroide->getSector()->getGalaxy()->getPosition()])
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();

                if ($newAsteroides) {
                    $newAsteroides->setEmpty(false);
                    $newAsteroides->setCdr(true);
                    $newAsteroides->setImageName('cdr.png');
                    $newAsteroides->setName('Astéroïdes');
                    $iaPlayer = $em->getRepository('App:User')->findOneBy(['zombie' => 1]);
                    $planetZb = $em->getRepository('App:Planet')
                        ->createQueryBuilder('p')
                        ->where('p.user = :user')
                        ->andWhere('p.radarAt is null and p.brouilleurAt is null')
                        ->setParameters(['user' => $iaPlayer])
                        ->orderBy('p.ground', 'ASC')
                        ->getQuery()
                        ->setMaxresults(1)
                        ->getOneOrNullResult();

                    $planetBis = $em->getRepository('App:Planet')
                        ->createQueryBuilder('p')
                        ->leftJoin('p.missions', 'm')
                        ->where('p.user = :user')
                        ->andWhere('p.radarAt is null and p.brouilleurAt is null and m.soldier is not null')
                        ->setParameters(['user' => $iaPlayer])
                        ->orderBy('p.ground', 'ASC')
                        ->getQuery()
                        ->setMaxresults(1)
                        ->getOneOrNullResult();

                    if ($planetBis) {
                        $planetZb = $planetBis;
                    }

                    $timeAttAst = new DateTime();
                    $timeAttAst->setTimezone(new DateTimeZone('Europe/Paris'));
                    $timeAttAst->add(new DateInterval('PT' . 24 . 'H'));
                    $fleet = new Fleet();
                    $alea = rand(10, 100);
                    $fleet->setHunterWar(300 * $alea);
                    $fleet->setCorvetWar(50 * $alea);
                    $fleet->setFregatePlasma(3 * $alea);
                    $fleet->setDestroyer(1 * $alea);
                    $fleet->setUser($iaPlayer);
                    $fleet->setPlanet($planetZb);
                    $destination = new Destination();
                    $destination->setFleet($fleet);
                    $destination->setPlanet($newAsteroides);
                    $em->persist($destination);
                    $fleet->setFlightTime($timeAttAst);
                    $fleet->setAttack(1);
                    $fleet->setName('Horde');
                    $fleet->setSignature($fleet->getNbrSignatures());
                    $em->persist($fleet);
                }
            }
        }
        echo "Flush -> " . count($asteroides) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}