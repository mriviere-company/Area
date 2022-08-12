<?php

namespace App\Controller\Connected;

use App\Entity\Commander;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
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
     * @param ManagerRegistry $doctrine
     * @param Galaxy|null $galaxy
     * @param Server $server
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     */
    public function connectServerAction(ManagerRegistry $doctrine, Galaxy $galaxy = null, Server $server): RedirectResponse|Response
    {
        $em = $doctrine->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $commander = $user->getCommander($server);

        if (!$commander) {
            $commander = new Commander($user, $user->getUsername(), $server);
            $em->persist($commander);
            $em->flush();
        }

        $usePlanet = $doctrine->getRepository(Planet::class)->findByFirstPlanet($commander);

        if($usePlanet) {
            return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
        }

        $planet = $doctrine->getRepository(Planet::class)
            ->createQueryBuilder('p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->where('p.commander is null')
            ->andWhere('p.ground = 25')
            ->andWhere('p.sky = 5')
            ->andWhere('g.id = :id')
            ->setParameters(['id' => $galaxy])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if($planet) {
            $planet->setCommander($commander);
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
            $commander->addPlanet($planet);
            foreach ($planet->getFleets() as $fleet) {
                if ($fleet->getCommander()->getZombie() == 1) {
                    $em->remove($fleet);
                } else {
                    $fleet->setPlanet($fleet->getCommander()->getFirstPlanetFleet());
                }
            }
        } else {
            $this->addFlash("full", "Cette galaxie est déjà pleine !");
            return $this->redirectToRoute('server_select');
        }

        $salon = $doctrine->getRepository(Salon::class)
            ->createQueryBuilder('s')
            ->where('s.name = :name')
            ->andWhere('s.server = :server')
            ->setParameters(['name' => 'Public', 'server' => $server])
            ->getQuery()
            ->getOneOrNullResult();

        if (!$commander->getShip()) {
            $ships = new Ships();
            $commander->setShip($ships);
            $ships->setCommander($commander);
            $em->persist($ships);
        }
        $rank = new Rank($commander);
        $em->persist($rank);
        $commander->setRank($rank);
        $commander->setDailyConnect($now);
        $nextZombie = new DateTime();
        $nextZombie->add(new DateInterval('PT' . 144 . 'H'));
        $commander->setZombieAt($nextZombie);
        $commander->setGameOver(null);
        $salon->removeCommander($commander);
        $salon->addCommander($commander);
        foreach ($commander->getQuests() as $quest) {
            $commander->removeQuest($quest);
        }
        $questOne = $doctrine->getRepository(Quest::class)->findOneById(2);
        $questTwo = $doctrine->getRepository(Quest::class)->findOneById(4);
        $questTree = $doctrine->getRepository(Quest::class)->findOneById(50);
        $commander->addQuest($questOne);
        $commander->addQuest($questTwo);
        $commander->addQuest($questTree);

        $report = new Report();
        $report->setSendAt($now);
        $report->setCommander($commander);
        $report->setTitle("Bienvenu parmis nous "  . $commander->getUsername() . " !");
        $report->setImageName("welcome_report.webp");
        $report->setContent("Une épidémie s'est déclaré sur la Terre et en ce moment même il est fort a parier qu'elle est aux mains des hordes zombies. Vous et quelques autres commandant de vaisseaux spatiaux avez eu la chance de fuir avec un certains nombre de travailleurs/soldats. Remontez notre civilisation et préparez vous, les Zombies ne sont pas arrivés par hasard sur Terre... Bon courage commandant. (Pour recevoir de l'aide : La page Salon ou rendez-vous sur le discord)");
        $em->persist($report);
        $commander->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('overview', ['usePlanet' => $planet->getId()]);
    }

    /**
     * @Route("/selection-serveur/", name="server_select")
     * @param ManagerRegistry $doctrine
     * @return Response
     */
    public function serverInterfaceAction(ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $user = $this->getUser();

        $servers = $doctrine->getRepository(Server::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.commanders', 'c')
            ->select('s.id, count(DISTINCT c.id) as commanders, s.open, s.pvp, s.name')
            ->groupBy('s.id')
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();

        $galaxys = $doctrine->getRepository(Galaxy::class)
            ->createQueryBuilder('g')
            ->join('g.server', 'ss')
            ->join('g.sectors', 's')
            ->join('s.planets', 'p')
            ->leftJoin('p.commander', 'c')
            ->select('g.id, g.position, count(DISTINCT c.id) as commanders, ss.id as server')
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