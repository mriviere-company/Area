<?php

namespace App\Controller\Connected;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Rank;
use App\Entity\Report;
use App\Entity\Ships;
use DateTime;
use Dateinterval;

/**
 * @Route("/connect")
 * @Security("is_granted('ROLE_USER')")
 */
class ConnectController extends AbstractController
{
    /**
     * @Route("/connection/{id}", name="connect_server", requirements={"id"="\d+"})
     */
    public function connectServerAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $now = new DateTime();
        $user = $this->getUser();
        $usePlanet = $em->getRepository('App:Planet')->findByFirstPlanet($user);

        if($usePlanet) {
            return $this->redirectToRoute('overview', ['usePlanet' => $usePlanet->getId()]);
        }

        $planet = $em->getRepository('App:Planet')
            ->createQueryBuilder('p')
            ->join('p.sector', 's')
            ->join('s.galaxy', 'g')
            ->where('p.user is null')
            ->andWhere('p.ground = 25')
            ->andWhere('p.sky = 5')
            ->andWhere('g.id = :id')
            ->setParameters(['id' => $id])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if($planet) {
            $planet->setUser($user);
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
            $user->addPlanet($planet);
            foreach ($planet->getFleets() as $fleet) {
                if ($fleet->getUser()->getZombie() == 1) {
                    $em->remove($fleet);
                } else {
                    $fleet->setPlanet($fleet->getUser()->getFirstPlanetFleet());
                }
            }
        } else {
            $this->addFlash("full", "Cette galaxie est déjà pleine !");
            $servers = $em->getRepository('App:Server')
                ->createQueryBuilder('s')
                ->select('s.id, s.open, s.pvp')
                ->groupBy('s.id')
                ->orderBy('s.id', 'ASC')
                ->getQuery()
                ->getResult();

            $galaxys = $em->getRepository('App:Galaxy')
                ->createQueryBuilder('g')
                ->join('g.server', 'ss')
                ->join('g.sectors', 's')
                ->join('s.planets', 'p')
                ->leftJoin('p.user', 'u')
                ->select('g.id, g.position, count(DISTINCT u.id) as users, ss.id as server')
                ->groupBy('g.id')
                ->orderBy('g.position', 'ASC')
                ->getQuery()
                ->getResult();

            return $this->render('connected/play.html.twig', [
                'galaxys' => $galaxys,
                'servers' => $servers
            ]);
        }

        $salon = $em->getRepository('App:Salon')
            ->createQueryBuilder('s')
            ->where('s.name = :name')
            ->setParameters(['name' => 'Public'])
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user->getShip()) {
            $ships = new Ships();
            $user->setShip($ships);
            $em->persist($ships);
        }
        $rank = new Rank();
        $em->persist($rank);
        $user->setRank($rank);
        $user->setTutorial(1);
        $user->setZombieAtt(1);
        $user->setDailyConnect($now);
        $user->setBitcoin(500);
        $nextZombie = new DateTime();
        $nextZombie->add(new DateInterval('PT' . 144 . 'H'));
        $user->setZombieAt($nextZombie);
        $user->setGameOver(null);
        $salon->removeUser($user);
        $salon->addUser($user);
        foreach ($user->getQuests() as $quest) {
            $user->removeQuest($quest);
        }
        $questOne = $em->getRepository('App:Quest')->findOneById(2);
        $questTwo = $em->getRepository('App:Quest')->findOneById(4);
        $questTree = $em->getRepository('App:Quest')->findOneById(50);
        $user->addQuest($questOne);
        $user->addQuest($questTwo);
        $user->addQuest($questTree);

        $report = new Report();
        $report->setSendAt($now);
        $report->setUser($user);
        $report->setTitle("Bienvenu parmis nous "  . $user->getUsername() . " !");
        $report->setImageName("welcome_report.jpg");
        $report->setContent("Une épidémie s'est déclaré sur la Terre et en ce moment même il est fort a parier qu'elle est aux mains des hordes zombies. Vous et quelques autres commandant de vaisseaux spatiaux avez eu la chance de fuir avec un certains nombre de travailleurs/soldats. Remontez notre civilisation et préparez vous, les Zombies ne sont pas arrivés par hasard sur Terre... Bon courage commandant. (Pour recevoir de l'aide : La page Salon ou rendez-vous sur le discord)");
        $em->persist($report);
        $user->setViewReport(false);

        $em->flush();

        return $this->redirectToRoute('login');
    }
}