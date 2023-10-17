<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AlliancesController
 * @package App\Controller\Connected\Execute
 */
class AlliancesController extends AbstractController
{
    /**
     * @param $pacts
     * @param $em
     * @return Response
     */
    public function pactsAction($pacts, $em): Response
    {
        foreach ($pacts as $pact) {
            $otherAlliance = $doctrine->getRepository(Alliance::class)
                ->createQueryBuilder('a')
                ->where('a.tag = :tag')
                ->setParameter('tag', $pact->getAllianceTag())
                ->getQuery()
                ->getOneOrNullResult();

            $pact2 = $doctrine->getRepository(Allied::class)
                ->createQueryBuilder('al')
                ->where('al.allyTag = :allytag')
                ->andWhere('al.ally = :ally')
                ->setParameters([
                    'allytag' => $pact->getDismissBy(),
                    'ally' => $otherAlliance])
                ->getQuery()
                ->getOneOrNullResult();

            $salons = $doctrine->getRepository(Salon::class)
                ->createQueryBuilder('s')
                ->where('s.name = :tag1')
                ->orWhere('s.name = :tag2')
                ->setParameters(['tag1' => $otherAlliance->getTag() . " - " . $pact->getDismissBy(), 'tag2' => $pact->getDismissBy() . " - " . $otherAlliance->getTag()])
                ->getQuery()
                ->getResult();

            foreach($salons as $salon) {
                foreach($salon->getContents() as $content) {
                    $em->remove($content);
                }
                foreach ($salon->getViews() as $view) {
                    $em->remove($view);
                }
                $em->remove($salon);
            }

            if($pact2) {
                $em->remove($pact2);
            }
            $em->remove($pact);
        }
        $em->flush();
        echo "Flush -> " . count($pacts) . " ";

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }

    /**
     * @param $peaces
     * @param $em
     * @return Response
     */
    public function peacesAction($peaces, $em): Response
    {
        foreach ($peaces as $peace) {
            $em->remove($peace);
        }
        echo "Flush -> " . count($peaces) . " ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}