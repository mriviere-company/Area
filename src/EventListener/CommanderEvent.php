<?php

namespace App\EventListener;

use App\Entity\Commander;
use App\Entity\User;
use App\Entity\Report;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
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
class CommanderEvent implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private TokenStorageInterface $token;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;
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
    public static function getSubscribedEvents(): array
    {
        return array(KernelEvents::CONTROLLER => "onCoreController");
    }
    /**
     * @param ControllerEvent $event
     */
    public function onCoreController(ControllerEvent $event)
    {
        if($event->getRequestType() == HttpKernelInterface::MAIN_REQUEST)
        {
            if($this->token->getToken()) {
                $user = $this->token->getToken()->getUser();
                $commander = $user instanceof User ? $user->getMainCommander() : null;
                $now = new DateTime();
                $twentyFour = new DateTime();
                $twentyFour->sub(new DateInterval('PT' . 86400 . 'S'));

                if ($commander instanceof Commander) {
                    if ($twentyFour > $commander->getDailyConnect()) {
                        $commander->setDailyConnect($now);
                        $bonus = (($commander->getLevel() + 1) * 50) * rand(1,5);
                        $commander->setBitcoin($commander->getBitcoin() + $bonus);
                        $commander->setViewReport(false);
                        $reportDaily = new Report();
                        $reportDaily->setType('economic');
                        $reportDaily->setSendAt($now);
                        $reportDaily->setCommander($commander);
                        $reportDaily->setTitle("Bonus de connexion");
                        $reportDaily->setImageName("sell_report.webp");
                        $reportDaily->setContent("«Ah vous voilà de retour, voici l'argent.»<br>-Dépose <span class='text-vert'>+" . number_format($bonus) . "</span> bitcoins sur la table.<br>«Toutes les 24h comme convenu ?<br>Bien à demain.»");
                        $this->em->persist($reportDaily);
                    }
                    if ($commander->getSearchAt()) {
                        if ($commander->getSearchAt()->format('U') < $now->format('U')) {
                            $research = $commander->getSearch();
                            if ($research == 'onde') {
                                $commander->setOnde($commander->getOnde() + 1);
                            } elseif ($research == 'industry') {
                                $commander->setIndustry($commander->getIndustry() + 1);
                            } elseif ($research == 'discipline') {
                                $commander->setDiscipline($commander->getDiscipline() + 1);
                            } elseif ($research == 'barbed') {
                                $commander->setBarbed($commander->getBarbed() + 1);
                            } elseif ($research == 'tank') {
                                $commander->setTank($commander->getTank() + 1);
                            } elseif ($research == 'expansion') {
                                $commander->setExpansion($commander->getExpansion() + 1);
                            } elseif ($research == 'aeroponicFarm') {
                                $commander->setAeroponicFarm($commander->getAeroponicFarm() + 1);
                            } elseif ($research == 'hyperespace') {
                                $commander->setHyperespace(1);
                            } elseif ($research == 'barge') {
                                $commander->setBarge(1);
                            } elseif ($research == 'utility') {
                                $commander->setUtility($commander->getUtility() + 1);
                            } elseif ($research == 'demography') {
                                $commander->setDemography($commander->getDemography() + 1);
                            } elseif ($research == 'terraformation') {
                                $commander->setTerraformation($commander->getTerraformation() + 1);
                            } elseif ($research == 'cargo') {
                                $commander->setCargo($commander->getCargo() + 1);
                                if ($commander->getCargo() == 3) {
                                    foreach ($commander->getPlanets() as $planet) {
                                        if ($planet->getCargoI() != 0) {
                                            $planet->setCargoV(round($planet->getCargoI() / 2));
                                            $planet->setCargoI(0);
                                            $this->em->flush();
                                        }
                                        if ($planet->getProduct()) {
                                            $product = $planet->getProduct();
                                            if ($product->getCargoI() != 0) {
                                                $product->setCargoV(round($product->getCargoI() / 2));
                                                $product->setCargoI(0);
                                                $this->em->flush();
                                            }
                                        }
                                    }
                                    foreach ($commander->getFleets() as $fleet) {
                                        if ($fleet->getCargoI() != 0) {
                                            $fleet->setCargoV(round($fleet->getCargoI() / 2));
                                            $fleet->setCargoI(0);
                                            $this->em->flush();
                                        }
                                    }
                                } elseif ($commander->getCargo() == 5){
                                    foreach ($commander->getPlanets() as $planet) {
                                        if ($planet->getCargoV() != 0) {
                                            $planet->setCargoX(round($planet->getCargoV() / 2));
                                            $planet->setCargoV(0);
                                            $this->em->flush();
                                        }
                                        if ($planet->getProduct()) {
                                            $product = $planet->getProduct();
                                            if ($product->getCargoV() != 0) {
                                                $product->setCargoX(round($product->getCargoV() / 2));
                                                $product->setCargoV(0);
                                                $this->em->flush();
                                            }
                                        }
                                    }
                                    foreach ($commander->getFleets() as $fleet) {
                                        if ($fleet->getCargoV() != 0) {
                                            $fleet->setCargoX(round($fleet->getCargoV() / 2));
                                            $fleet->setCargoV(0);
                                            $this->em->flush();
                                        }
                                    }
                                }
                            } elseif ($research == 'recycleur') {
                                $commander->setRecycleur(1);
                            } elseif ($research == 'armement') {
                                $commander->setArmement($commander->getArmement() + 1);
                            } elseif ($research == 'missile') {
                                $commander->setMissile($commander->getMissile() + 1);
                            } elseif ($research == 'laser') {
                                $commander->setLaser($commander->getLaser() + 1);
                            } elseif ($research == 'plasma') {
                                $commander->setPlasma($commander->getPlasma() + 1);
                            } elseif ($research == 'lightShip') {
                                $commander->setLightShip($commander->getLightShip() + 1);
                            } elseif ($research == 'heavyShip') {
                                $commander->setHeavyShip($commander->getHeavyShip() + 1);
                            }
                            if ($commander->getAlly()) {
                                if ($research == 'prod_ally') {
                                    $commander->setPoliticProd($commander->getPoliticProd() + 1);
                                } elseif ($research == 'recycleur_ally') {
                                    $commander->setPoliticRecycleur($commander->getPoliticRecycleur() + 1);
                                } elseif ($research == 'worker_ally') {
                                    $commander->setPoliticWorker($commander->getPoliticWorker() + 1);
                                } elseif ($research == 'armement_ally') {
                                    $commander->setPoliticArmement($commander->getPoliticArmement() + 1);
                                } elseif ($research == 'worker_def_ally') {
                                    $commander->setPoliticWorkerDef($commander->getPoliticWorkerDef() + 1);
                                } elseif ($research == 'search_ally') {
                                    $commander->setScientistProduction($commander->getScientistProduction() + 0.1);
                                    $commander->setPoliticSearch($commander->getPoliticSearch() + 1);
                                } elseif ($research == 'cost_scientist_ally') {
                                    $commander->setPoliticCostScientist($commander->getPoliticCostScientist() + 1);
                                } elseif ($research == 'barge_ally') {
                                    $commander->setPoliticBarge($commander->getPoliticBarge() + 1);
                                } elseif ($research == 'soldier_att_ally') {
                                    $commander->setPoliticSoldierAtt($commander->getPoliticSoldierAtt() + 1);
                                } elseif ($research == 'cargo_ally') {
                                    $commander->setPoliticCargo($commander->getPoliticCargo() + 1);
                                } elseif ($research == 'cost_soldier_ally') {
                                    $commander->setPoliticCostSoldier($commander->getPoliticCostSoldier() + 1);
                                } elseif ($research == 'armor_ally') {
                                    $commander->setPoliticArmor($commander->getPoliticArmor() + 1);
                                } elseif ($research == 'soldier_sale_ally') {
                                    $commander->setPoliticSoldierSale($commander->getPoliticSoldierSale() + 1);
                                } elseif ($research == 'cost_tank_ally') {
                                    $commander->setPoliticCostTank($commander->getPoliticCostTank() + 1);
                                } elseif ($research == 'merchant_ally') {
                                    $commander->setPoliticMerchant($commander->getPoliticMerchant() + 1);
                                } elseif ($research == 'tank_def_ally') {
                                    $commander->setPoliticTankDef($commander->getPoliticTankDef() + 1);
                                } elseif ($research == 'invade_ally') {
                                    $commander->setPoliticInvade($commander->getPoliticInvade() + 1);
                                } elseif ($research == 'colonisation_ally') {
                                    $commander->setPoliticColonisation($commander->getPoliticColonisation() + 1);
                                } elseif ($research == 'pdg_ally') {
                                    $commander->setPoliticPdg($commander->getPoliticPdg() + 1);
                                }
                            }
                            $commander->setSearch(null);
                            $commander->setSearchAt(null);
                            $quest = $commander->checkQuests('research');
                            if($quest) {
                                $commander->getRank()->setWarPoint($commander->getRank()->getWarPoint() + $quest->getGain());
                                $commander->removeQuest($quest);
                            }
                            $this->em->flush();
                        }
                    }
                }
            }
        }
    }
}