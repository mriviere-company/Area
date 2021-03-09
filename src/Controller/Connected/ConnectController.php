<?php

namespace App\Controller\Connected;

use App\Entity\Character;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Rank;
use App\Entity\Report;
use App\Entity\Ships;
use App\Entity\Galaxy;
use App\Entity\Server;
use Dateinterval;
use Exception;
use DateTime;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER', 'ROLE_ADMIN')")
 */
class ConnectController extends AbstractController
{
    /**
     * @Route("/connection/{galaxy}/{server}", name="connect_server", requirements={"galaxy"="\d+", "server"="\d+"})
     * @Route("/connection/{server}", name="connected_server", requirements={"server"="\d+"})
     * @param Galaxy|null $galaxy
     * @param Server $server
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function connectServerAction(Galaxy $galaxy = null, Server $server)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $character = $user->getCharacter($server);

        if (!$character) {
            $character = new Character($user, $user->getUsername(), $server);
            $em->persist($character);
            $em->flush();
        }

        $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($character);

        if($usePlanet) {
            return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
        }

        $planet = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->where('p.character is null')
            ->andWhere('p.ground = 25')
            ->andWhere('p.sky = 5')
            ->andWhere('g.id = :id')
            ->setParameters(['id' => $galaxy])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if($planet) {
            $planet->setCharacter($character);
            $planet->setName('Nova Terra');
            $planet->setSonde(10);
            $planet->setRadar(1);
            $planet->setGroundPlace(10);
            $planet->setSkyPlace(1);
            $planet->setMiner(3);
            $planet->setNbProduction(66);
            $planet->setWtProduction(45);
            $planet->setExtractor(3);
            $planet->setSpaceShip(1);
            $planet->setHunter(5);
            $planet->setNiobium(12000);
            $planet->setWater(10000);
            $planet->setFregate(2);
            $planet->setWorker(25000);
            $planet->setSoldier(20);
            $planet->setColonizer(1);
            $character->addPlanet($planet);
            foreach ($planet->getFleets() as $fleet) {
                if ($fleet->getCharacter()->getZombie() == 1) {
                    $em->remove($fleet);
                } else {
                    $fleet->setPlanet($fleet->getCharacter()->getFirstPlanetFleet());
                }
            }
        } else {
            $this->addFlash("full", "Cette galaxie est déjà pleine !");
            return $this->redirectToRoute('server_select');
        }

        $salon = $em->getRepository('App:Salon')
            ->createQueryBuilder('s')
            ->where('s.name = :name')
            ->andWhere('s.server = :server')
            ->setParameters(['name' => 'Public', 'server' => $server])
            ->getQuery()
            ->getOneOrNullResult();

        if (!$character->getShip()) {
            $ships = new Ships();
            $character->setShip($ships);
            $ships->setCharacter($character);
            $em->persist($ships);
        }
        $rank = new Rank($character);
        $em->persist($rank);
        $character->setRank($rank);
        $character->setDailyConnect($now);
        $nextZombie = new DateTime();
        $nextZombie->add(new DateInterval('PT' . 144 . 'H'));
        $character->setZombieAt($nextZombie);
        $character->setGameOver(null);
        $salon->removeCharacter($character);
        $salon->addCharacter($character);
        foreach ($character->getQuests() as $quest) {
            $character->removeQuest($quest);
        }
        $questOne = $em->getRepository('App:Quest')->findOneById(2);
        $questTwo = $em->getRepository('App:Quest')->findOneById(4);
        $questTree = $em->getRepository('App:Quest')->findOneById(50);
        $character->addQuest($questOne);
        $character->addQuest($questTwo);
        $character->addQuest($questTree);

        $report = new Report();
        $report->setSendAt($now);
        $report->setCharacter($character);
        $report->setTitle("Bienvenu parmis nous "  . $character->getUsername() . " !");
        $report->setImageName("welcome_report.jpg");
        $report->setContent("Une épidémie s'est déclaré sur la Terre et en ce moment même il est fort a parier qu'elle est aux mains des hordes zombies. Vous et quelques autres commandant de vaisseaux spatiaux avez eu la chance de fuir avec un certains nombre de travailleurs/soldats. Remontez notre civilisation et préparez vous, les Zombies ne sont pas arrivés par hasard sur Terre... Bon courage commandant. (Pour recevoir de l'aide : La page Salon ou rendez-vous sur le discord)");
        $em->persist($report);
        $character->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('overview', ['usePlanet' => $planet->getId()]);
    }
    /**
     * @Route("/selection-serveur/", name="server_select")
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function serverInterfaceAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $servers = $em->getRepository('App:Server')
            ->createQueryBuilder('s')
            ->leftJoin('s.characters', 'c')
            ->select('s.id, count(DISTINCT c.id) as characters, s.open, s.pvp')
            ->groupBy('s.id')
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();

        $galaxys = $em->getRepository('App:Galaxy')
            ->createQueryBuilder('g')
            ->join('g.server', 'ss')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->leftJoin('p.character', 'c')
            ->select('g.id, g.position, count(DISTINCT c.id) as characters, ss.id as server')
            ->groupBy('g.id')
            ->orderBy('g.position', 'ASC')
            ->getQuery()
            ->getResult();

        if ($user->getRoles()[0] == 'ROLE_MODO' || $user->getRoles()[0] == 'ROLE_ADMIN') {
            return $this->render('admin/administration.html.twig', [
                'galaxys' => $galaxys,
                'servers' => $servers
            ]);
        } else {
            return $this->render('connected/play.html.twig', [
                'galaxys' => $galaxys,
                'servers' => $servers
            ]);
        }
    }
}