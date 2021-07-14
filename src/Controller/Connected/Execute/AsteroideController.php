<?php

namespace App\Controller\Connected\Execute;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Destination;
use App\Entity\Fleet;
use Dateinterval;
use DateTime;

/**
 * Class AsteroideController
 * @package App\Controller\Connected\Execute
 */
class AsteroideController extends AbstractController
{
    /**
     * @param $asteroides
     * @param $em
     * @return Response
     * @throws Exception
     */
    public function AsteroideAction($asteroides, $em): Response
    {
        $nowAste = new DateTime();

        $nowAste->add(new DateInterval('PT' . (25000 * rand(1, 4)) . 'S'));
        foreach ($asteroides as $asteroide) {

            $asteroide->setRecycleAt($nowAste);
            $asteroide->setNbCdr($asteroide->getNbCdr() + rand(4000, 40000));
            $asteroide->setWtCdr($asteroide->getWtCdr() + rand(3000, 30000));


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

                    $timeAttAst = new DateTime();
                    $timeAttAst->add(new DateInterval('PT' . 24 . 'H'));
                    $fleet = new Fleet();
                    $alea = rand(10, 100);
                    $fleet->setHunterWar(300 * $alea);
                    $fleet->setCorvetWar(50 * $alea);
                    $fleet->setFregatePlasma(3 * $alea);
                    $fleet->setDestroyer(1 * $alea);
                    $fleet->setCharacter($iaPlayer);
                    $fleet->setPlanet($planetZb);
                    $destination = new Destination($fleet, $newAsteroides);
                    $em->persist($destination);
                    $fleet->setDestination($destination);
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