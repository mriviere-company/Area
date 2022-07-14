<?php

namespace App\EventListener;

use App\Entity\Character;
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
class CharacterEvent implements EventSubscriberInterface
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
                $character = $user instanceof User ? $user->getMainCharacter() : null;
                $now = new DateTime();
                $twentyFour = new DateTime();
                $twentyFour->sub(new DateInterval('PT' . 86400 . 'S'));

                if ($character instanceof Character) {
                    if ($twentyFour > $character->getDailyConnect()) {
                        $character->setDailyConnect($now);
                        $bonus = (($character->getLevel() + 1) * 50) * rand(1,5);
                        $character->setBitcoin($character->getBitcoin() + $bonus);
                        $character->setViewReport(false);
                        $reportDaily = new Report();
                        $reportDaily->setType('economic');
                        $reportDaily->setSendAt($now);
                        $reportDaily->setCharacter($character);
                        $reportDaily->setTitle("Bonus de connexion");
                        $reportDaily->setImageName("sell_report.webp");
                        $reportDaily->setContent("«Ah vous voilà de retour, voici l'argent.»<br>-Dépose <span class='text-vert'>+" . number_format($bonus) . "</span> bitcoins sur la table.<br>«Toutes les 24h comme convenu ?<br>Bien à demain.»");
                        $this->em->persist($reportDaily);
                    }
                    if ($character->getSearchAt()) {
                        if ($character->getSearchAt()->format('U') < $now->format('U')) {
                            $research = $character->getSearch();
                            if ($research == 'onde') {
                                $character->setOnde($character->getOnde() + 1);
                            } elseif ($research == 'industry') {
                                $character->setIndustry($character->getIndustry() + 1);
                            } elseif ($research == 'discipline') {
                                $character->setDiscipline($character->getDiscipline() + 1);
                            } elseif ($research == 'barbed') {
                                $character->setBarbed($character->getBarbed() + 1);
                            } elseif ($research == 'tank') {
                                $character->setTank($character->getTank() + 1);
                            } elseif ($research == 'expansion') {
                                $character->setExpansion($character->getExpansion() + 1);
                            } elseif ($research == 'aeroponicFarm') {
                                $character->setAeroponicFarm($character->getAeroponicFarm() + 1);
                            } elseif ($research == 'hyperespace') {
                                $character->setHyperespace(1);
                            } elseif ($research == 'barge') {
                                $character->setBarge(1);
                            } elseif ($research == 'utility') {
                                $character->setUtility($character->getUtility() + 1);
                            } elseif ($research == 'demography') {
                                $character->setDemography($character->getDemography() + 1);
                            } elseif ($research == 'terraformation') {
                                $character->setTerraformation($character->getTerraformation() + 1);
                            } elseif ($research == 'cargo') {
                                $character->setCargo($character->getCargo() + 1);
                                if ($character->getCargo() == 3) {
                                    foreach ($character->getPlanets() as $planet) {
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
                                    foreach ($character->getFleets() as $fleet) {
                                        if ($fleet->getCargoI() != 0) {
                                            $fleet->setCargoV(round($fleet->getCargoI() / 2));
                                            $fleet->setCargoI(0);
                                            $this->em->flush();
                                        }
                                    }
                                } elseif ($character->getCargo() == 5){
                                    foreach ($character->getPlanets() as $planet) {
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
                                    foreach ($character->getFleets() as $fleet) {
                                        if ($fleet->getCargoV() != 0) {
                                            $fleet->setCargoX(round($fleet->getCargoV() / 2));
                                            $fleet->setCargoV(0);
                                            $this->em->flush();
                                        }
                                    }
                                }
                            } elseif ($research == 'recycleur') {
                                $character->setRecycleur(1);
                            } elseif ($research == 'armement') {
                                $character->setArmement($character->getArmement() + 1);
                            } elseif ($research == 'missile') {
                                $character->setMissile($character->getMissile() + 1);
                            } elseif ($research == 'laser') {
                                $character->setLaser($character->getLaser() + 1);
                            } elseif ($research == 'plasma') {
                                $character->setPlasma($character->getPlasma() + 1);
                            } elseif ($research == 'lightShip') {
                                $character->setLightShip($character->getLightShip() + 1);
                            } elseif ($research == 'heavyShip') {
                                $character->setHeavyShip($character->getHeavyShip() + 1);
                            }
                            if ($character->getAlly()) {
                                if ($research == 'prod_ally') {
                                    $character->setPoliticProd($character->getPoliticProd() + 1);
                                } elseif ($research == 'recycleur_ally') {
                                    $character->setPoliticRecycleur($character->getPoliticRecycleur() + 1);
                                } elseif ($research == 'worker_ally') {
                                    $character->setPoliticWorker($character->getPoliticWorker() + 1);
                                } elseif ($research == 'armement_ally') {
                                    $character->setPoliticArmement($character->getPoliticArmement() + 1);
                                } elseif ($research == 'worker_def_ally') {
                                    $character->setPoliticWorkerDef($character->getPoliticWorkerDef() + 1);
                                } elseif ($research == 'search_ally') {
                                    $character->setScientistProduction($character->getScientistProduction() + 0.1);
                                    $character->setPoliticSearch($character->getPoliticSearch() + 1);
                                } elseif ($research == 'cost_scientist_ally') {
                                    $character->setPoliticCostScientist($character->getPoliticCostScientist() + 1);
                                } elseif ($research == 'barge_ally') {
                                    $character->setPoliticBarge($character->getPoliticBarge() + 1);
                                } elseif ($research == 'soldier_att_ally') {
                                    $character->setPoliticSoldierAtt($character->getPoliticSoldierAtt() + 1);
                                } elseif ($research == 'cargo_ally') {
                                    $character->setPoliticCargo($character->getPoliticCargo() + 1);
                                } elseif ($research == 'cost_soldier_ally') {
                                    $character->setPoliticCostSoldier($character->getPoliticCostSoldier() + 1);
                                } elseif ($research == 'armor_ally') {
                                    $character->setPoliticArmor($character->getPoliticArmor() + 1);
                                } elseif ($research == 'soldier_sale_ally') {
                                    $character->setPoliticSoldierSale($character->getPoliticSoldierSale() + 1);
                                } elseif ($research == 'cost_tank_ally') {
                                    $character->setPoliticCostTank($character->getPoliticCostTank() + 1);
                                } elseif ($research == 'merchant_ally') {
                                    $character->setPoliticMerchant($character->getPoliticMerchant() + 1);
                                } elseif ($research == 'tank_def_ally') {
                                    $character->setPoliticTankDef($character->getPoliticTankDef() + 1);
                                } elseif ($research == 'invade_ally') {
                                    $character->setPoliticInvade($character->getPoliticInvade() + 1);
                                } elseif ($research == 'colonisation_ally') {
                                    $character->setPoliticColonisation($character->getPoliticColonisation() + 1);
                                } elseif ($research == 'pdg_ally') {
                                    $character->setPoliticPdg($character->getPoliticPdg() + 1);
                                }
                            }
                            $character->setSearch(null);
                            $character->setSearchAt(null);
                            $quest = $character->checkQuests('research');
                            if($quest) {
                                $character->getRank()->setWarPoint($character->getRank()->getWarPoint() + $quest->getGain());
                                $character->removeQuest($quest);
                            }
                            $this->em->flush();
                        }
                    }
                }
            }
        }
    }
}