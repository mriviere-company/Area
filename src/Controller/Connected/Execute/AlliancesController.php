<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AlliancesController extends AbstractController
{
    public function pactsAction($pacts, $em)
    {
        foreach ($pacts as $pact) {
            $otherAlly = $em->getRepository('App:Ally')
                ->createQueryBuilder('a')
                ->where('a.sigle = :sigle')
                ->setParameter('sigle', $pact->getAllyTag())
                ->getQuery()
                ->getOneOrNullResult();

            $pact2 = $em->getRepository('App:Allied')
                ->createQueryBuilder('al')
                ->where('al.allyTag = :allytag')
                ->andWhere('al.ally = :ally')
                ->setParameters([
                    'allytag' => $pact->getDismissBy(),
                    'ally' => $otherAlly])
                ->getQuery()
                ->getOneOrNullResult();

            $salons = $em->getRepository('App:Salon')
                ->createQueryBuilder('s')
                ->where('s.name = :sigle1')
                ->orWhere('s.name = :sigle2')
                ->setParameters(['sigle1' => $otherAlly->getSigle() . " - " . $pact->getDismissBy(), 'sigle2' => $pact->getDismissBy() . " - " . $otherAlly->getSigle()])
                ->getQuery()
                ->getResult();

            foreach($salons as $salon) {
                foreach($salon->getContents() as $content) {
                    $em->remove($content);
                }
                $em->remove($salon);
            }

            if($pact2) {
                $em->remove($pact2);
            }
            $em->remove($pact);
        }
        $em->flush();
        echo "Flush ";

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
    public function peacesAction($peaces, $em)
    {
        foreach ($peaces as $peace) {
            $em->remove($peace);
        }
        echo "Flush ";

        $em->flush();

        return new Response ("<span style='color:#008000'>OK</span><br/>");
    }
}