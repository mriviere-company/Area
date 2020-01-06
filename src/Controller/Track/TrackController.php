<?php

namespace App\Controller\Connected\Execute;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Track;
use DateTimeZone;
use DateTime;

class TrackController extends AbstractController
{
    public function trackAction()
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $track = new Track();

        $track->setDate($now);
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip  = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $track->setIp($ip);

        $host = gethostbyaddr($ip);
        $track->setHost($host);

        $track->setBrowser($_SERVER['HTTP_USER_AGENT']);

        if (isset($_SERVER['HTTP_REFERER'])) {
            if (preg_match($_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER'])) {
                $referer ='';
            }
            else {
                $referer = $_SERVER['HTTP_REFERER'];
            }
        }
        else {
            $referer ='';
        }
        $track->setPreviousPage($referer);

        if ($_SERVER['QUERY_STRING'] == "") {
            $page_courante = $_SERVER['PHP_SELF'];
        }
        else {
            $page_courante = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
        }

        $track->setPage($page_courante);

        if ($this->getUser()) {
            $track->setUsername($this->getUser()->getUserName());
        }

        $em->persist($track);
        $em->flush();

        return new Response ("");
    }
}