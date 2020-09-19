<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\Report;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use DateTime;
use Dateinterval;

/**
 * Class KernelControllerListener
 *
 *      app.listener.kernel_controller:
 *          class: AppBundle\EventListener\KernelControllerListener
 *          arguments: ['@security.token_storage', '@doctrine.orm.entity_manager']
 *          tags:
 *              - { name: kernel.event_subscriber }
 *
 *
 * @package AppBundle\EventListener
 */
class UserEvent implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $token;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * KernelControllerListener constructor.
     * @param TokenStorageInterface $token
     * @param EntityManagerInterface $em
     */
    public function __construct(TokenStorageInterface $token, EntityManagerInterface $em)
    {
        $this->token = $token;
        $this->em = $em;
    }
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::CONTROLLER => "onCoreController");
    }
    /**
     * @param ControllerEvent $event
     */
    public function onCoreController(ControllerEvent $event)
    {
        if($event->getRequestType() == HttpKernel::MASTER_REQUEST)
        {
            if($this->token->getToken()) {
                $user = $this->token->getToken()->getUser();
                $now = new DateTime();
                $twentyfour = new DateTime();
                $twentyfour->sub(new DateInterval('PT' . 86400 . 'S'));

                if ($user instanceof User) {
                    if ($twentyfour > $user->getDailyConnect()) {
                        $user->setDailyConnect($now);
                        $bonus = (($user->getLevel() + 1) * 5000) * rand(1,5);
                        $user->setBitcoin($user->getBitcoin() + $bonus);
                        $user->setViewReport(false);
                        $reportDaily = new Report();
                        $reportDaily->setType('economic');
                        $reportDaily->setSendAt($now);
                        $reportDaily->setUser($user);
                        $reportDaily->setTitle("Bonus de connexion");
                        $reportDaily->setImageName("sell_report.jpg");
                        $reportDaily->setContent("«Ah vous voilà de retour, voici l'argent.»<br>-Dépose <span class='text-vert'>+" . number_format($bonus) . "</span> bitcoins sur la table.<br>«Toutes les 24h comme convenu ?<br>Bien à demain.»");
                        $this->em->persist($reportDaily);
                    }
                    if ($user->getSearchAt()) {
                        if ($user->getSearchAt()->format('U') < $now->format('U')) {
                            $research = $user->getSearch();
                            if ($research == 'onde') {
                                $user->setOnde($user->getOnde() + 1);
                            } elseif ($research == 'industry') {
                                $user->setIndustry($user->getIndustry() + 1);
                            } elseif ($research == 'discipline') {
                                $user->setDiscipline($user->getDiscipline() + 1);
                            } elseif ($research == 'barbed') {
                                $user->setBarbed($user->getBarbed() + 1);
                            } elseif ($research == 'tank') {
                                $user->setTank($user->getTank() + 1);
                            } elseif ($research == 'expansion') {
                                $user->setExpansion($user->getExpansion() + 1);
                            } elseif ($research == 'aeroponicFarm') {
                                $user->setAeroponicFarm($user->getAeroponicFarm() + 1);
                            } elseif ($research == 'hyperespace') {
                                $user->setHyperespace(1);
                            } elseif ($research == 'barge') {
                                $user->setBarge(1);
                            } elseif ($research == 'utility') {
                                $user->setUtility($user->getUtility() + 1);
                            } elseif ($research == 'demography') {
                                $user->setDemography($user->getDemography() + 1);
                            } elseif ($research == 'terraformation') {
                                $user->setTerraformation($user->getTerraformation() + 1);
                            } elseif ($research == 'cargo') {
                                $user->setCargo($user->getCargo() + 1);
                                if ($user->getCargo() == 3) {
                                    foreach ($user->getPlanets() as $planet) {
                                        if ($planet->getCargoI() != 0) {
                                            $planet->setCargoV(round($planet->getCargoI() / 2));
                                            $planet->setCargoI(0);
                                            $this->em->flush($planet);
                                        }
                                        if ($planet->getProduct()) {
                                            $product = $planet->getProduct();
                                            if ($product->getCargoI() != 0) {
                                                $product->setCargoV(round($product->getCargoI() / 2));
                                                $product->setCargoI(0);
                                                $this->em->flush($product);
                                            }
                                        }
                                    }
                                    foreach ($user->getFleets() as $fleet) {
                                        if ($fleet->getCargoI() != 0) {
                                            $fleet->setCargoV(round($fleet->getCargoI() / 2));
                                            $fleet->setCargoI(0);
                                            $this->em->flush($fleet);
                                        }
                                    }
                                } elseif ($user->getCargo() == 5){
                                    foreach ($user->getPlanets() as $planet) {
                                        if ($planet->getCargoV() != 0) {
                                            $planet->setCargoX(round($planet->getCargoV() / 2));
                                            $planet->setCargoV(0);
                                            $this->em->flush($planet);
                                        }
                                        if ($planet->getProduct()) {
                                            $product = $planet->getProduct();
                                            if ($product->getCargoV() != 0) {
                                                $product->setCargoX(round($product->getCargoV() / 2));
                                                $product->setCargoV(0);
                                                $this->em->flush($product);
                                            }
                                        }
                                    }
                                    foreach ($user->getFleets() as $fleet) {
                                        if ($fleet->getCargoV() != 0) {
                                            $fleet->setCargoX(round($fleet->getCargoV() / 2));
                                            $fleet->setCargoV(0);
                                            $this->em->flush($fleet);
                                        }
                                    }
                                }
                            } elseif ($research == 'recycleur') {
                                $user->setRecycleur(1);
                            } elseif ($research == 'armement') {
                                $user->setArmement($user->getArmement() + 1);
                            } elseif ($research == 'missile') {
                                $user->setMissile($user->getMissile() + 1);
                            } elseif ($research == 'laser') {
                                $user->setLaser($user->getLaser() + 1);
                            } elseif ($research == 'plasma') {
                                $user->setPlasma($user->getPlasma() + 1);
                            } elseif ($research == 'lightShip') {
                                $user->setLightShip($user->getLightShip() + 1);
                            } elseif ($research == 'heavyShip') {
                                $user->setHeavyShip($user->getHeavyShip() + 1);
                            }
                            if ($user->getAlly()) {
                                if ($research == 'prod_ally') {
                                    $user->setPoliticProd($user->getPoliticProd() + 1);
                                } elseif ($research == 'recycleur_ally') {
                                    $user->setPoliticRecycleur($user->getPoliticRecycleur() + 1);
                                } elseif ($research == 'worker_ally') {
                                    $user->setPoliticWorker($user->getPoliticWorker() + 1);
                                } elseif ($research == 'armement_ally') {
                                    $user->setPoliticArmement($user->getPoliticArmement() + 1);
                                } elseif ($research == 'worker_def_ally') {
                                    $user->setPoliticWorkerDef($user->getPoliticWorkerDef() + 1);
                                } elseif ($research == 'search_ally') {
                                    $user->setScientistProduction($user->getScientistProduction() + 0.1);
                                    $user->setPoliticSearch($user->getPoliticSearch() + 1);
                                } elseif ($research == 'cost_scientist_ally') {
                                    $user->setPoliticCostScientist($user->getPoliticCostScientist() + 1);
                                } elseif ($research == 'barge_ally') {
                                    $user->setPoliticBarge($user->getPoliticBarge() + 1);
                                } elseif ($research == 'soldier_att_ally') {
                                    $user->setPoliticSoldierAtt($user->getPoliticSoldierAtt() + 1);
                                } elseif ($research == 'cargo_ally') {
                                    $user->setPoliticCargo($user->getPoliticCargo() + 1);
                                } elseif ($research == 'cost_soldier_ally') {
                                    $user->setPoliticCostSoldier($user->getPoliticCostSoldier() + 1);
                                } elseif ($research == 'armor_ally') {
                                    $user->setPoliticArmor($user->getPoliticArmor() + 1);
                                } elseif ($research == 'soldier_sale_ally') {
                                    $user->setPoliticSoldierSale($user->getPoliticSoldierSale() + 1);
                                } elseif ($research == 'cost_tank_ally') {
                                    $user->setPoliticCostTank($user->getPoliticCostTank() + 1);
                                } elseif ($research == 'merchant_ally') {
                                    $user->setPoliticMerchant($user->getPoliticMerchant() + 1);
                                } elseif ($research == 'tank_def_ally') {
                                    $user->setPoliticTankDef($user->getPoliticTankDef() + 1);
                                } elseif ($research == 'invade_ally') {
                                    $user->setPoliticInvade($user->getPoliticInvade() + 1);
                                } elseif ($research == 'colonisation_ally') {
                                    $user->setPoliticColonisation($user->getPoliticColonisation() + 1);
                                } elseif ($research == 'pdg_ally') {
                                    $user->setPoliticPdg($user->getPoliticPdg() + 1);
                                }
                            }
                            $user->setSearch(null);
                            $user->setSearchAt(null);
                            $quest = $user->checkQuests('research');
                            if($quest) {
                                $user->getRank()->setWarPoint($user->getRank()->getWarPoint() + $quest->getGain());
                                $user->removeQuest($quest);
                            }
                            $this->em->flush($user);
                        }
                    }
                }
            }
        }
    }
}