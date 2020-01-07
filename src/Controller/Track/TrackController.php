<?php

namespace App\Controller\Track;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Track;
use DateTimeZone;
use DateTime;

class TrackController extends AbstractController
{
    public function trackAction($currentPage)
    {
        $user = $this->getUser();
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip  = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if ($user && $user->getUsername() == 'Dev' || $ip == '77.141.214.214' || $ip == '92.154.96.135') {
            return new Response ("");
        }
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone('Europe/Paris'));
        $track = new Track();

        if ($user) {
            $track->setUsername($this->getUser()->getUserName());
        }

        $track->setDate($now);
        $track->setIp($ip);

        $track->setBrowser($_SERVER['HTTP_USER_AGENT']);

        if (isset($_SERVER['HTTP_REFERER'])) {
            if (preg_match('/' . $_SERVER['HTTP_HOST'] . '/', $_SERVER['HTTP_REFERER'])) {
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

/*        if(isset($_SERVER['QUERY_STRING'])) {
            if ($_SERVER['QUERY_STRING'] == "") {
                $page_courante = $_SERVER['PHP_SELF'];
            } else {
                $page_courante = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            $page_courante = $_SERVER['PHP_SELF'];
        }*/

        $track->setPage($currentPage);

        $em->persist($track);
        $em->flush();

        return new Response ("");
    }
}