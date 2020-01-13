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
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip  = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if ($user && $user->getUsername() == 'Dev' || $ip == '77.141.214.214'
            || $ip == '92.154.96.135' || stripos(strtoupper($u_agent), 'BOT') !== FALSE) {
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

        if (stripos(strtoupper($u_agent), 'ANDROID') !== FALSE) {
            $track->setComputer('Android');
            $track->setBrowser($u_agent);
        } elseif (stripos(strtoupper($u_agent), 'WINDOWS') !== FALSE) {
            $track->setComputer('Windows');
            $track->setBrowser($u_agent);
        } elseif (stripos(strtoupper($u_agent), 'MACINTOSH') !== FALSE) {
            $track->setComputer('Mac');
            $track->setBrowser($u_agent);
        } elseif (stripos(strtoupper($u_agent), 'LINUX') !== FALSE) {
            $track->setComputer('Linux');
            $track->setBrowser($u_agent);
        } elseif (stripos(strtoupper($u_agent), 'IPHONE') !== FALSE) {
            $track->setComputer('Iphone');
            $track->setBrowser($u_agent);
        } elseif (stripos(strtoupper($u_agent), 'IPAD') !== FALSE) {
            $track->setComputer('Ipad');
            $track->setBrowser($u_agent);
        } elseif (stripos(strtoupper($u_agent), 'FACEBOOK') !== FALSE) {
            $track->setComputer('Facebook');
            $track->setBrowser($u_agent);
        } else {
            $track->setBrowser($u_agent);
        }

        $track->setBrowser($u_agent);

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
        if (stripos(strtoupper($referer), 'FACEBOOK') !== FALSE) {
            $track->setPreviousPage('Facebook');
        } elseif (stripos(strtoupper($referer), 'GOOGLE') !== FALSE) {
            $track->setPreviousPage('Google');
        } elseif (stripos(strtoupper($referer), 'JEUXVIDEO') !== FALSE) {
            $track->setPreviousPage('JeuxVideo');
        } elseif (stripos(strtoupper($referer), 'BING') !== FALSE) {
            $track->setPreviousPage('Bing');
        } elseif (stripos(strtoupper($referer), 'MRIVIERE') !== FALSE) {
            $track->setPreviousPage('Mriviere');
        } else {
            $track->setPreviousPage($referer);
        }

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