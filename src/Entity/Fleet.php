<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="fleet")
 * @ORM\Entity(repositoryClass="App\Repository\FleetRepository")
 */
class Fleet
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name",type="string", length=20)
     * @Assert\NotBlank(message = "required")
     * @Assert\Regex(
     *     pattern="/[a-zA-Z0-9]/")
     */
    protected $name;

    /**
     * @ORM\Column(name="attack",type="boolean")
     * @Assert\NotBlank(message = "required")
     */
    protected $attack;

    /**
     * @ORM\Column(name="fightAt",type="datetime", nullable=true)
     */
    protected $fightAt;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="fleets", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Ally", inversedBy="fleets", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="ally_id", referencedColumnName="id", nullable=true)
     */
    protected $ally;

    /**
     * @ORM\ManyToOne(targetEntity="Fleet_List", inversedBy="fleets", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="fleet_list_id", referencedColumnName="id")
     */
    protected $fleetList;

    /**
     * @ORM\ManyToOne(targetEntity="Planet", inversedBy="fleets", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="planet_id", referencedColumnName="id")
     */
    protected $planet;

    /**
     * @ORM\OneToOne(targetEntity="Destination", mappedBy="fleet", fetch="EXTRA_LAZY", cascade={"persist"})
     */
    protected $destination;

    /**
     * @ORM\OneToOne(targetEntity="Commander", inversedBy="fleet", fetch="EXTRA_LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="commander_id", referencedColumnName="id")
     */
    protected $commander;

    /**
     * @ORM\Column(name="flightTime",type="datetime", nullable=true)
     */
    protected $flightTime;

    /**
     * @ORM\Column(name="cancelFlight",type="datetime", nullable=true)
     */
    protected $cancelFlight;

    /**
     * @ORM\Column(name="flightType",type="string", nullable=true)
     */
    protected $flightType;

    /**
     * @ORM\Column(name="recycleAt",type="datetime", nullable=true)
     */
    protected $recycleAt;

    /**
     * @ORM\Column(name="signature",type="bigint")
     */
    protected $signature;

    /**
     * @ORM\Column(name="sonde",type="bigint", nullable=true)
     */
    protected $sonde;

    /**
     * @ORM\Column(name="cargoI",type="integer", nullable=true)
     */
    protected $cargoI;

    /**
     * @ORM\Column(name="cargoV",type="integer", nullable=true)
     */
    protected $cargoV;

    /**
     * @ORM\Column(name="cargoX",type="bigint", nullable=true)
     */
    protected $cargoX;

    /**
     * @ORM\Column(name="colonizer",type="smallint", nullable=true)
     */
    protected $colonizer;

    /**
     * @ORM\Column(name="recycleur",type="integer", nullable=true)
     */
    protected $recycleur;

    /**
     * @ORM\Column(name="barge",type="integer", nullable=true)
     */
    protected $barge;

    /**
     * @ORM\Column(name="moonMaker",type="smallint", nullable=true)
     */
    protected $moonMaker;

    /**
     * @ORM\Column(name="radarShip",type="integer", nullable=true)
     */
    protected $radarShip;

    /**
     * @ORM\Column(name="brouilleurShip",type="integer", nullable=true)
     */
    protected $brouilleurShip;

    /**
     * @ORM\Column(name="motherShip",type="smallint", nullable=true)
     */
    protected $motherShip;

    /**
     * @ORM\Column(name="hunter",type="bigint", nullable=true)
     */
    protected $hunter;

    /**
     * @ORM\Column(name="hunterHeavy",type="bigint", nullable=true)
     */
    protected $hunterHeavy;

    /**
     * @ORM\Column(name="hunterWar",type="bigint", nullable=true)
     */
    protected $hunterWar;

    /**
     * @ORM\Column(name="corvet",type="bigint", nullable=true)
     */
    protected $corvet;

    /**
     * @ORM\Column(name="corvetLaser",type="bigint", nullable=true)
     */
    protected $corvetLaser;

    /**
     * @ORM\Column(name="corvetWar",type="bigint", nullable=true)
     */
    protected $corvetWar;

    /**
     * @ORM\Column(name="fregate",type="bigint", nullable=true)
     */
    protected $fregate;

    /**
     * @ORM\Column(name="fregatePlasma",type="bigint", nullable=true)
     */
    protected $fregatePlasma;

    /**
     * @ORM\Column(name="croiser",type="bigint", nullable=true)
     */
    protected $croiser;

    /**
     * @ORM\Column(name="ironClad",type="bigint", nullable=true)
     */
    protected $ironClad;

    /**
     * @ORM\Column(name="destroyer",type="bigint", nullable=true)
     */
    protected $destroyer;

    /**
     * @ORM\Column(name="soldier",type="integer", nullable=true)
     */
    protected $soldier;

    /**
     * @ORM\Column(name="tank",type="smallint", nullable=true)
     */
    protected $tank;

    /**
     * @ORM\Column(name="worker",type="integer", nullable=true)
     */
    protected $worker;

    /**
     * @ORM\Column(name="scientist",type="smallint", nullable=true)
     */
    protected $scientist;

    /**
     * @ORM\Column(name="nuclear_bomb",type="smallint", nullable=true)
     */
    protected $nuclearBomb;

    /**
     * @ORM\Column(name="niobium",type="bigint", nullable=true)
     */
    protected $niobium;

    /**
     * @ORM\Column(name="water",type="bigint", nullable=true)
     */
    protected $water;

    /**
     * @ORM\Column(name="food",type="bigint", nullable=true)
     */
    protected $food;

    /**
     * @ORM\Column(name="uranium",type="integer", nullable=true)
     */
    protected $uranium;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->attack = false;
        $this->water = null;
        $this->niobium = null;
        $this->food = null;
        $this->uranium = null;
        $this->scientist = null;
        $this->worker = null;
        $this->soldier = null;
        $this->tank = null;
        $this->attack = 0;
        $this->destroyer = null;
        $this->ironClad = null;
        $this->croiser = null;
        $this->fregate = null;
        $this->fregatePlasma = null;
        $this->corvet = null;
        $this->corvetLaser = null;
        $this->corvetWar = null;
        $this->hunter = null;
        $this->hunterHeavy = null;
        $this->hunterWar = null;
        $this->moonMaker = null;
        $this->motherShip = null;
        $this->radarShip = null;
        $this->brouilleurShip = null;
        $this->barge = null;
        $this->recycleur = null;
        $this->cargoI = null;
        $this->cargoV = null;
        $this->cargoX = null;
        $this->colonizer = null;
        $this->sonde = null;
        $this->fightAt = null;
        $this->flightTime = null;
        $this->cancelFlight = null;
        $this->flightType = null;
        $this->ally = null;
        $this->nuclearBomb = null;
        $this->signature = 1;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFleetTags(): string
    {
        $attack = '';
        if($this->getAttack() == 1) {
            $attack = "<span class='text-rouge'> [Attaque]</span>";
        }
        if($this->getUser()->getAlly()) {
            $return = "<span class='text-orange'>[" . $this->getUser()->getAlly()->getSigle() . "]" . " " . $this->getUser()->getAlly()->getName() . "</span> - " . $this->getUser()->getUserName() . " -> " . $this->getName() . $attack;
        } else {
            $return = $this->getUser()->getUserName();
        }
        return $return;
    }

    /**
     * @param $armor
     * @return string
     */
    public function getShipsReport($armor): string
    {
        $ships = '';
        if($this->getMotherShip() == 1) {
            $armor = $armor * 0.05;
        }
        if($this->getHunter()) {
            $new = (($this->getHunter() * 15) - $armor) / 15;
            if($new < 0) {
                $armor = $new * -15;
                $new = $this->getHunter();
            } else {
                $armor = 0;
                $new = $this->getHunter() - $new;
            }
            $ships = "Chasseurs : " . number_format($this->getHunter()) . " <span class='float-right'>Perte : <span class=\"text-rouge\">" . number_format(round($new)) . "</span></span><br>";
        }
        if($this->getHunterHeavy()) {
            if($armor != 0) {
                $new = (($this->getHunterHeavy() * 25) - $armor) / 25;
                if ($new < 0) {
                    $armor = $new * -25;
                    $new = $this->getHunterHeavy();
                } else {
                    $armor = 0;
                    $new = $this->getHunterHeavy() - $new;
                }
            } else {
                $new = 0;
            }
            $ships = $ships . "Chasseurs lourds : " . number_format($this->getHunterHeavy()) . " <span class='float-right'>Perte : <span class=\"text-rouge\">" . number_format(round($new)) . "</span></span><br>";
        }
        if($this->getHunterWar()) {
            if($armor != 0) {
                $new = (($this->getHunterWar() * 53) - $armor) / 53;
                if($new < 0) {
                    $armor = $new * -53;
                    $new = $this->getHunterWar();
                } else {
                    $armor = 0;
                    $new = $this->getHunterWar() - $new;
                }
            } else {
                $new = 0;
            }
            $ships = $ships . "Chasseur a plasma : " . number_format($this->getHunterWar()) . " <span class='float-right'>Perte : <span class=\"text-rouge\">" . number_format(round($new)) . "</span></span><br>";
        }
        if($this->getCorvet()) {
            if($armor != 0) {
                $new = (($this->getCorvet() * 74) - $armor) / 74;
                if($new < 0) {
                    $armor = $new * -74;
                    $new = $this->getCorvet();
                } else {
                    $armor = 0;
                    $new = $this->getCorvet() - $new;
                }
            } else {
                $new = 0;
            }
            $ships = $ships . "Corvettes : " . number_format($this->getCorvet()) . " <span class='float-right'>Perte : <span class=\"text-rouge\">" . number_format(round($new)) . "</span></span><br>";
        }
        if($this->getCorvetLaser()) {
            if($armor != 0) {
                $new = (($this->getCorvetLaser() * 115) - $armor) / 115;
                if ($new < 0) {
                    $armor = $new * -115;
                    $new = $this->getCorvetLaser();
                } else {
                    $armor = 0;
                    $new = $this->getCorvetLaser() - $new;
                }
            } else {
                $new = 0;
            }
            $ships = $ships . "Corvettes à laser : " . number_format($this->getCorvetLaser()) . " <span class='float-right'>Perte : <span class=\"text-rouge\">" . number_format(round($new)) . "</span></span><br>";
        }
        if($this->getCorvetWar()) {
            if($armor != 0) {
                $new = (($this->getCorvetWar() * 135) - $armor) / 135;
                if ($new < 0) {
                    $armor = $new * -135;
                    $new = $this->getCorvetWar();
                } else {
                    $armor = 0;
                    $new = $this->getCorvetWar() - $new;
                }
            } else {
                $new = 0;
            }
            $ships = $ships . "Corvettes Armageddon : " . number_format($this->getCorvetWar()) . " <span class='float-right'>Perte : <span class=\"text-rouge\">" . number_format(round($new)) . "</span></span><br>";
        }
        if($this->getFregate()) {
            if($armor != 0) {
                $new = (($this->getFregate() * 168) - $armor) / 168;
                if ($new < 0) {
                    $armor = $new * -168;
                    $new = $this->getFregate();
                } else {
                    $armor = 0;
                    $new = $this->getFregate() - $new;
                }
            } else {
                $new = 0;
            }
            $ships = $ships . "Frégates : " . number_format($this->getFregate()) . " <span class='float-right'>Perte : <span class=\"text-rouge\">" . number_format(round($new)) . "</span></span><br>";
        }
        if($this->getFregatePlasma()) {
            if($armor != 0) {
                $new = (($this->getFregatePlasma() * 451) - $armor) / 451;
                if($new < 0) {
                    $armor = $new * -451;
                    $new = $this->getFregatePlasma();
                } else {
                    $armor = 0;
                    $new = $this->getFregatePlasma() - $new;
                }
            } else {
                $new = 0;
            }
            $ships = $ships . "Frégates a plasma : " . number_format($this->getFregatePlasma()) . " <span class='float-right'>Perte : <span class=\"text-rouge\">" . number_format(round($new)) . "</span></span><br>";
        }
        if($this->getCroiser()) {
            if($armor != 0) {
                $new = (($this->getCroiser() * 957) - $armor) / 957;
                if ($new < 0) {
                    $armor = $new * -957;
                    $new = $this->getCroiser();
                } else {
                    $armor = 0;
                    $new = $this->getCroiser() - $new;
                }
            } else {
                $new = 0;
            }
            $ships = $ships . "Croiseurs : " . number_format($this->getCroiser()) . " <span class='float-right'>Perte : <span class=\"text-rouge\">" . number_format(round($new)) . "</span></span><br>";
        }
        if($this->getDestroyer()) {
            if($armor != 0) {
                $new = (($this->getDestroyer() * 5176) - $armor) / 5176;
                if ($new < 0) {
                    $armor = $new * -5176;
                    $new = $this->getDestroyer();
                } else {
                    $armor = 0;
                    $new = $this->getDestroyer() - $new;
                }
            } else {
                $new = 0;
            }
            $ships = $ships . "Destroyers : " . number_format($this->getDestroyer()) . " <span class='float-right'>Perte : <span class=\"text-rouge\">" . number_format(round($new)) . "</span></span><br>";
        }
        if($this->getIronClad()) {
            if($armor != 0) {
                $new = (($this->getIronClad() * 2415) - $armor) / 2415;
                if ($new < 0) {
                    $new = $this->getIronClad();
                } else {
                    $new = $this->getIronClad() - $new;
                }
            } else {
                $new = 0;
            }
            $ships = $ships . "Cuirassés : " . number_format($this->getIronClad()) . " <span class='float-right'>Perte : <span class=\"text-rouge\">" . number_format(round($new)) . "</span></span><br>";
        }
        return $ships;
    }

    /**
     * @return string
     */
    public function getShipsReportNoLost(): string
    {
        $ships = '';
        if($this->getHunter()) {
            $ships = "Chasseurs : " . number_format($this->getHunter()) . " <span class='float-right'>Perte : Aucune</span><br>";
        }
        if($this->getHunterHeavy()) {
            $ships = $ships . "Chasseurs lourds : " . number_format($this->getHunterHeavy()) . " <span class='float-right'>Perte : Aucune</span><br>";
        }
        if($this->getHunterWar()) {
            $ships = $ships . "Chasseurs à plasma : " . number_format($this->getHunterWar()) . " <span class='float-right'>Perte : Aucune</span><br>";
        }
        if($this->getCorvet()) {
            $ships = $ships . "Corvettes : " . number_format($this->getCorvet()) . " <span class='float-right'>Perte : Aucune</span><br>";
        }
        if($this->getCorvetLaser()) {
            $ships = $ships . "Corvettes à laser : " . number_format($this->getCorvetLaser()) . " <span class='float-right'>Perte : Aucune</span><br>";
        }
        if($this->getCorvetWar()) {
            $ships = $ships . "Corvettes Armageddon : " . number_format($this->getCorvetWar()) . " <span class='float-right'>Perte : Aucune</span><br>";
        }
        if($this->getFregate()) {
            $ships = $ships . "Frégates : " . number_format($this->getFregate()) . " <span class='float-right'>Perte : Aucune</span><br>";
        }
        if($this->getFregatePlasma()) {
            $ships = $ships . "Frégates a plasma : " . number_format($this->getFregatePlasma()) . " <span class='float-right'>Perte : Aucune</span><br>";
        }
        if($this->getCroiser()) {
            $ships = $ships . "Croiseurs : " . number_format($this->getCroiser()) . " <span class='float-right'>Perte : Aucune</span><br>";
        }
        if($this->getIronClad()) {
            $ships = $ships . "Cuirassés : " . number_format($this->getIronClad()) . " <span class='float-right'>Perte : Aucune</span><br>";
        }
        if($this->getDestroyer()) {
            $ships = $ships . "Destroyers : " . number_format($this->getDestroyer()) . " <span class='float-right'>Perte : Aucune</span><br>";
        }

        return $ships;
    }

    /**
     * @return string
     */
    public function getShipsLoseReport(): string
    {
        $ships = '';
        if($this->getSonde()) {
            $ships = "<small>Sonde : " . number_format($this->getSonde()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span></small><br>";
        }
        if($this->getColonizer()) {
            $ships = $ships . "<small>Colonisateur : " . number_format($this->getColonizer()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span></small><br>";
        }
        if($this->getCargoI()) {
            $ships = $ships . "<small>Cargo I : " . number_format($this->getCargoI()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span></small><br>";
        }
        if($this->getCargoV()) {
            $ships = $ships . "<small>Cargo V : " . number_format($this->getCargoV()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span></small><br>";
        }
        if($this->getCargoX()) {
            $ships = $ships . "<small>Cargo X : " . number_format($this->getCargoX()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span></small><br>";
        }
        if($this->getRecycleur()) {
            $ships = $ships . "<small>Recycleur : " . number_format($this->getRecycleur()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span></small><br>";
        }
        if($this->getBarge()) {
            $ships = $ships . "<small>Barge : " . number_format($this->getBarge()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span></small><br>";
        }
        if($this->getMoonMaker()) {
            $ships = $ships . "<small>Lunar : " . number_format($this->getMoonMaker()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span></small><br>";
        }
        if($this->getRadarShip()) {
            $ships = $ships . "<small>Vaisseau radar : " . number_format($this->getRadarShip()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span></small><br>";
        }
        if($this->getBrouilleurShip()) {
            $ships = $ships . "<small>Vaisseau brouilleur : " . number_format($this->getBrouilleurShip()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span></small><br>";
        }
        if($this->getMotherShip()) {
            $ships = $ships . "<small>Vaisseau mère : " . number_format($this->getMotherShip()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span></small><br>";
        }
        if($this->getHunter()) {
            $ships = $ships . "Chasseurs : " . number_format($this->getHunter()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span><br>";
        }
        if($this->getHunterHeavy()) {
            $ships = $ships . "Chasseurs lourds : " . number_format($this->getHunterHeavy()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span><br>";
        }
        if($this->getHunterWar()) {
            $ships = $ships . "Chasseurs à plasma : " . number_format($this->getHunterWar()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span><br>";
        }
        if($this->getCorvet()) {
            $ships = $ships . "Corvettes : " . number_format($this->getCorvet()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span><br>";
        }
        if($this->getCorvetLaser()) {
            $ships = $ships . "Corvettes à laser : " . number_format($this->getCorvetLaser()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span><br>";
        }
        if($this->getCorvetWar()) {
            $ships = $ships . "Corvettes Armageddon : " . number_format($this->getCorvetWar()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span><br>";
        }
        if($this->getFregate()) {
            $ships = $ships . "Frégates : " . number_format($this->getFregate()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span><br>";
        }
        if($this->getFregatePlasma()) {
            $ships = $ships . "Frégates a plasma : " . number_format($this->getFregatePlasma()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span><br>";
        }
        if($this->getCroiser()) {
            $ships = $ships . "Croiseurs : " . number_format($this->getCroiser()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span><br>";
        }
        if($this->getIronClad()) {
            $ships = $ships . "Cuirassés : " . number_format($this->getIronClad()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span><br>";
        }
        if($this->getDestroyer()) {
            $ships = $ships . "Destroyers : " . number_format($this->getDestroyer()) . " <span class='float-right'>Perte : " . "<span class=\"text-rouge\">" . "Totale</span></span><br>";
        }

        return $ships;
    }

    /**
     * @return mixed
     */
    public function getAllianceUser()
    {
        if($this->getUser()->getAlly()) {
            $uAlly = $this->getUser()->getAlly()->getUsers();
            $uFleet = $this->getPlanet()->getUser();
            foreach ($uAlly as $user) {
                if ($uFleet == $user) {
                    return null;
                }
            }
        } elseif ($this->getUser() == $this->getPlanet()->getUser()) {
            return null;
        }
        return 'ok';
    }

    /**
     * @return int
     */
    public function getCargoFull(): float
    {
        $worker = $this->getWorker();
        $soldier = $this->getSoldier();
        $scientist = $this->getScientist();
        $niobium = $this->getNiobium();
        $water = $this->getWater();
        $food = $this->getFood();
        $uranium = $this->getUranium();

        $nbr = $food + $worker + $soldier + $scientist + $niobium + $water + $uranium;
        return $nbr;
    }

    /**
     * @return int
     */
    public function getCargoPlace(): float
    {
        $barge = $this->getBarge() * 1000;
        $recycleur = $this->getRecycleur() * 5000;
        $hunterHeavy = $this->getHunterHeavy() * 35;
        $fregate = $this->getFregate() * 250;
        if ($this->getUser()->getPoliticCargo() > 0) {
            $cargoI = ($this->getCargoI() * 30000) * (1 + ($this->getUser()->getPoliticCargo() / 5));
            $cargoV = ($this->getCargoV() * 100000) * (1 + ($this->getUser()->getPoliticCargo() / 5));
            $cargoX = ($this->getCargoX() * 250000) * (1 + ($this->getUser()->getPoliticCargo() / 5));
        } else {
            $cargoI = $this->getCargoI() * 30000;
            $cargoV = $this->getCargoV() * 100000;
            $cargoX = $this->getCargoX() * 250000;
        }

        $nbr = $barge + $recycleur + $cargoI + $cargoV + $cargoX + $hunterHeavy + $fregate;
        if($this->getMotherShip() == 1) {
            $nbr = $nbr * 1.10;
        }
        return $nbr;
    }

    /**
     * @return int
     */
    public function getArmor(): float
    {
        $hunter = $this->getUser()->getShip()->getArmorHunter() * $this->getHunter();
        $hunterHeavy = $this->getUser()->getShip()->getArmorHunterHeavy() * $this->getHunterHeavy();
        $hunterWar = $this->getUser()->getShip()->getArmorHunterWar() * $this->getHunterWar();
        $corvet = $this->getUser()->getShip()->getArmorCorvet() * $this->getCorvet();
        $corvetLaser = $this->getUser()->getShip()->getArmorCorvetLaser() * $this->getCorvetLaser();
        $corvetWar = $this->getUser()->getShip()->getArmorCorvetWar() * $this->getCorvetWar();
        $fregate = $this->getUser()->getShip()->getArmorFregate() * $this->getFregate();
        $fregatePlasma = $this->getUser()->getShip()->getArmorFregatePlasma() * $this->getFregatePlasma();
        $croiser = $this->getUser()->getShip()->getArmorCroiser() * $this->getCroiser();
        $ironClad = $this->getUser()->getShip()->getArmorIronClad() * $this->getIronClad();
        $destroyer = $this->getUser()->getShip()->getArmorDestroyer() * $this->getDestroyer();
        if($this->getMotherShip() == 1) {
            $motherShip = 20000;
        } else {
            $motherShip = 0;
        }

        $nbr = $motherShip + $hunterWar + $corvetWar +  $fregate + $hunter + $hunterHeavy + $corvet + $corvetLaser + $fregatePlasma + $croiser + $ironClad + $destroyer ;
        if($this->getMotherShip() == 1) {
            $nbr = $nbr * 1.05;
        }
        return $nbr;
    }

    /**
     * @return int
     */
    public function getShield(): float
    {
        $corvet = $this->getUser()->getShip()->getShieldCorvet() * $this->getCorvet();
        $corvetLaser = $this->getUser()->getShip()->getShieldCorvetLaser() * $this->getCorvetLaser();
        $corvetWar = $this->getUser()->getShip()->getShieldCorvetWar() * $this->getCorvetWar();
        $fregate = $this->getUser()->getShip()->getShieldFregate() * $this->getFregate();
        $fregatePlasma = $this->getUser()->getShip()->getShieldFregatePlasma() * $this->getFregatePlasma();
        $croiser = $this->getUser()->getShip()->getShieldCroiser() * $this->getCroiser();
        $ironClad = $this->getUser()->getShip()->getShieldIronClad() * $this->getIronClad();
        $destroyer = $this->getUser()->getShip()->getShieldDestroyer() * $this->getDestroyer();
        $motherShip = $this->getMotherShip() * 5000;

        $nbr = $motherShip + $corvetWar + $fregate + $corvet + $corvetLaser + $fregatePlasma + $croiser + $ironClad + $destroyer ;
        if($this->getMotherShip() == 1) {
            $nbr = $nbr * 1.05;
        }
        return $nbr;
    }

    /**
     * @return int
     */
    public function getAccurate(): float
    {
        $hunter = $this->getUser()->getShip()->getAccurateHunter() * $this->getHunter();
        $hunterHeavy = $this->getUser()->getShip()->getAccurateHunterHeavy() * $this->getHunterHeavy();
        $hunterWar = $this->getUser()->getShip()->getAccurateHunterWar() * $this->getHunterWar();
        $corvet = $this->getUser()->getShip()->getAccurateCorvet() * $this->getCorvet();
        $corvetLaser = $this->getUser()->getShip()->getAccurateCorvetLaser() * $this->getCorvetLaser();
        $corvetWar = $this->getUser()->getShip()->getAccurateCorvetWar() * $this->getCorvetWar();
        $fregate = $this->getUser()->getShip()->getAccurateFregate() * $this->getFregate();
        $fregatePlasma = $this->getUser()->getShip()->getAccurateFregatePlasma() * $this->getFregatePlasma();
        $croiser = $this->getUser()->getShip()->getAccurateCroiser() * $this->getCroiser();
        $ironClad = $this->getUser()->getShip()->getAccurateIronClad() * $this->getIronClad();
        $destroyer = $this->getUser()->getShip()->getAccurateDestroyer() * $this->getDestroyer();

        $nbr = $hunterWar + $corvetWar + $fregate + $hunter + $hunterHeavy + $corvet + $corvetLaser + $fregatePlasma + $croiser + $ironClad + $destroyer ;
        return $nbr;
    }

    /**
     * @return int
     */
    public function getMissile(): float
    {
        $hunter = $this->getUser()->getShip()->getMissileHunter() * $this->getHunter();
        $hunterHeavy = $this->getUser()->getShip()->getMissileHunterHeavy() * $this->getHunterHeavy();
        $hunterWar = $this->getUser()->getShip()->getMissileHunterWar() * $this->getHunterWar();
        $corvet = $this->getUser()->getShip()->getMissileCorvet() * $this->getCorvet();
        $corvetLaser = $this->getUser()->getShip()->getMissileCorvetLaser() * $this->getCorvetLaser();
        $corvetWar = $this->getUser()->getShip()->getMissileCorvetWar() * $this->getCorvetWar();
        $fregate = $this->getUser()->getShip()->getMissileFregate() * $this->getFregate();
        $fregatePlasma = $this->getUser()->getShip()->getMissileFregatePlasma() * $this->getFregatePlasma();
        $croiser = $this->getUser()->getShip()->getMissileCroiser() * $this->getCroiser();
        $ironClad = $this->getUser()->getShip()->getMissileIronClad() * $this->getIronClad();
        $destroyer = $this->getUser()->getShip()->getMissileDestroyer() * $this->getDestroyer();

        $nbr = $hunterWar + $corvetWar + $fregate + $hunter + $hunterHeavy + $corvet + $corvetLaser + $fregatePlasma + $croiser + $ironClad + $destroyer ;
        return $nbr;
    }

    /**
     * @return int
     */
    public function getLaser(): float
    {
        $hunterWar = $this->getUser()->getShip()->getLaserHunterWar() * $this->getHunterWar();
        $corvetLaser = $this->getUser()->getShip()->getLaserCorvetLaser() * $this->getCorvetLaser();
        $corvetWar = $this->getUser()->getShip()->getLaserCorvetWar() * $this->getCorvetWar();
        $fregate = $this->getUser()->getShip()->getLaserFregate() * $this->getFregate();
        $fregatePlasma = $this->getUser()->getShip()->getLaserFregatePlasma() * $this->getFregatePlasma();
        $croiser = $this->getUser()->getShip()->getLaserCroiser() * $this->getCroiser();
        $ironClad = $this->getUser()->getShip()->getLaserIronClad() * $this->getIronClad();
        $destroyer = $this->getUser()->getShip()->getLaserDestroyer() * $this->getDestroyer();

        $nbr = $hunterWar + $corvetWar + $fregate + $corvetLaser + $fregatePlasma + $croiser + $ironClad + $destroyer ;
        return $nbr;
    }

    /**
     * @return int
     */
    public function getPlasma(): float
    {
        $hunterWar = $this->getUser()->getShip()->getPlasmaHunterWar() * $this->getHunterWar();
        $fregatePlasma = $this->getUser()->getShip()->getPlasmaFregatePlasma() * $this->getFregatePlasma();
        $croiser = $this->getUser()->getShip()->getPlasmaCroiser() * $this->getCroiser();
        $ironClad = $this->getUser()->getShip()->getPlasmaIronClad() * $this->getIronClad();
        $destroyer = $this->getUser()->getShip()->getPlasmaDestroyer() * $this->getDestroyer();

        $nbr = $fregatePlasma + $croiser + $ironClad + $destroyer + $hunterWar;
        if($this->getMotherShip() == 1) {
            $nbr = $nbr * 1.10;
        }
        return $nbr;
    }

    /**
     * @return int
     */
    public function getNbrShips(): float
    {
        $sonde = $this->getSonde();
        $colonizer = $this->getColonizer();
        $recycleur = $this->getRecycleur();
        $cargoI = $this->getCargoI();
        $cargoV = $this->getCargoV();
        $cargoX = $this->getCargoX();
        $barge = $this->getBarge();
        $moonMaker = $this->getMoonMaker();
        $radarShip = $this->getRadarShip();
        $brouilleurShip = $this->getBrouilleurShip();
        $motherShip = $this->getMotherShip();
        $hunter = $this->getHunter();
        $hunterHeavy = $this->getHunterHeavy();
        $hunterWar = $this->getHunterWar();
        $corvet = $this->getCorvet();
        $corvetLaser = $this->getCorvetLaser();
        $corvetWar = $this->getCorvetWar();
        $fregate = $this->getFregate();
        $fregatePlasma = $this->getFregatePlasma();
        $croiser = $this->getCroiser();
        $ironClad = $this->getIronClad();
        $destroyer = $this->getDestroyer();
        $nuclear = $this->getNuclearBomb();

        $nbr = $motherShip + $brouilleurShip + $radarShip + $moonMaker + $hunterWar + $corvetWar + $fregate + $colonizer + $barge + $hunter + $recycleur + $sonde + $cargoI + $cargoV + $cargoX + $hunterHeavy + $corvet + $corvetLaser + $fregatePlasma + $croiser + $ironClad + $destroyer + $nuclear;
        return $nbr;
    }

    /**
     * @return int
     */
    public function getNbrSignatures(): float
    {
        $sonde = $this->getSonde();
        $colonizer = $this->getColonizer() * 200;
        $recycleur = $this->getRecycleur() * 80;
        $cargoI = $this->getCargoI() * 50;
        $cargoV = $this->getCargoV() * 120;
        $cargoX = $this->getCargoX() * 250;
        $barge = $this->getBarge() * 50;
        $moonMaker = $this->getMoonMaker() * 50000;
        $radarShip = $this->getRadarShip() * 500;
        $brouilleurShip = $this->getBrouilleurShip() * 1000;
        $motherShip = $this->getMotherShip() * 20000;
        $hunter = $this->getHunter() * 5;
        $hunterHeavy = $this->getHunterHeavy() * 8;
        $hunterWar = $this->getHunterWar() * 15;
        $corvet = $this->getCorvet() * 25;
        $corvetLaser = $this->getCorvetLaser() * 40;
        $corvetWar = $this->getCorvetWar() * 45;
        $fregate = $this->getFregate() * 60;
        $fregatePlasma = $this->getFregatePlasma() * 150;
        $croiser = $this->getCroiser() * 300;
        $ironClad = $this->getIronClad() * 700;
        $destroyer = $this->getDestroyer() * 1500;
        $nuclear = $this->getNuclearBomb() * 250000;

        $nbr = $motherShip + $brouilleurShip + $radarShip + $moonMaker + $hunterWar + $corvetWar + $fregate + $colonizer + $barge + $hunter + $recycleur + $sonde + $cargoI + $cargoV + $cargoX + $hunterHeavy + $corvet + $corvetLaser + $fregatePlasma + $croiser + $ironClad + $destroyer + $nuclear;
        if($this->getMotherShip() == 1) {
            $nbr = $nbr * 0.9;
        }
        return $nbr;
    }

    /**
     * @return int
     */
    public function putNewSignature(): float
    {
        $sonde = $this->getSonde();
        $colonizer = $this->getColonizer() * 200;
        $recycleur = $this->getRecycleur() * 80;
        $cargoI = $this->getCargoI() * 50;
        $cargoV = $this->getCargoV() * 120;
        $cargoX = $this->getCargoX() * 250;
        $barge = $this->getBarge() * 50;
        $moonMaker = $this->getMoonMaker() * 50000;
        $radarShip = $this->getRadarShip() * 500;
        $brouilleurShip = $this->getBrouilleurShip() * 1000;
        $motherShip = $this->getMotherShip() * 20000;
        $hunter = $this->getHunter() * 5;
        $hunterHeavy = $this->getHunterHeavy() * 8;
        $hunterWar = $this->getHunterWar() * 15;
        $corvet = $this->getCorvet() * 25;
        $corvetLaser = $this->getCorvetLaser() * 40;
        $corvetWar = $this->getCorvetWar() * 45;
        $fregate = $this->getFregate() * 60;
        $fregatePlasma = $this->getFregatePlasma() * 150;
        $croiser = $this->getCroiser() * 300;
        $ironClad = $this->getIronClad() * 700;
        $destroyer = $this->getDestroyer() * 1500;
        $nuclear = $this->getNuclearBomb() * 250000;

        $nbr = $motherShip + $brouilleurShip + $radarShip + $moonMaker + $hunterWar + $corvetWar + $fregate + $colonizer + $barge + $hunter + $recycleur + $sonde + $cargoI + $cargoV + $cargoX + $hunterHeavy + $corvet + $corvetLaser + $fregatePlasma + $croiser + $ironClad + $destroyer + $nuclear;
        if($this->getMotherShip() == 1) {
            $nbr = $nbr * 0.9;
        }
        return $nbr;
    }

    /**
     * @return float
     */
    public function getSpeed(): float
    {
        if($this->getMoonMaker()) {
            return 5;
        }
        if($this->getBarge()) {
            return 3;
        }
        if($this->getColonizer()) {
            return 2;
        }
        if($this->getRecycleur() || $this->getIronClad()) {
            return 1.5;
        }
        if($this->getDestroyer()) {
            return 1.4;
        }
        if($this->getCroiser()) {
            return 1.3;
        }
        if($this->getFregatePlasma()) {
            return 1.2;
        }
        if($this->getFregate()) {
            return 1.1;
        }
        if($this->getCorvetLaser() || $this->getMotherShip()) {
            return 1;
        }
        if($this->getCorvet() || $this->getCorvetWar()) {
            return 0.7;
        }
        if($this->getCargoX()) {
            return 0.6;
        }
        if($this->getHunter() || $this->getHunterHeavy() || $this->getHunterWar() || $this->getCargoV()) {
            return 0.5;
        }
        if($this->getCargoI()) {
            return 0.4;
        }
        if($this->getSonde() || $this->getBrouilleurShip() || $this->getRadarShip()) {
            return 0.01;
        }

        return 1;
    }

    /**
     * @return mixed
     */
    public function getCargosFleet()
    {
        $return = [];
        if ($this->niobium > 0) {
            $return[] = [number_format($this->niobium), 'fleet.niobium'];
        }
        if ($this->water > 0) {
            $return[] = [number_format($this->water), 'fleet.water'];
        }
        if ($this->uranium > 0) {
            $return[] = [number_format($this->uranium), 'fleet.uranium'];
        }
        if ($this->worker > 0) {
            $return[] = [number_format($this->worker), 'fleet.worker'];
        }
        if ($this->soldier > 0) {
            $return[] = [number_format($this->soldier), 'fleet.soldier'];
        }
        if ($this->scientist > 0) {
            $return[] = [number_format($this->scientist), 'fleet.scientist'];
        }
        return $return;
    }

    /**
     * @return mixed
     */
    public function getShipsFleet()
    {
        $return = [];
        if ($this->sonde > 0) {
            $return[] = [number_format($this->sonde), 'sonde'];
        }
        if ($this->colonizer > 0) {
            $return[] = [number_format($this->colonizer), 'colonizer'];
        }
        if ($this->cargoI > 0) {
            $return[] = [number_format($this->cargoI), 'cargoI'];
        }
        if ($this->cargoV > 0) {
            $return[] = [number_format($this->cargoV), 'cargoV'];
        }
        if ($this->cargoX > 0) {
            $return[] = [number_format($this->cargoX), 'cargoX'];
        }
        if ($this->barge > 0) {
            $return[] = [number_format($this->barge), 'barges'];
        }
        if ($this->recycleur > 0) {
            $return[] = [number_format($this->recycleur), 'recycleurs'];
        }
        if ($this->radarShip > 0) {
            $return[] = [number_format($this->radarShip), 'radarShip'];
        }
        if ($this->brouilleurShip > 0) {
            $return[] = [number_format($this->brouilleurShip), 'brouilleurShip'];
        }
        if ($this->moonMaker > 0) {
            $return[] = [number_format($this->moonMaker), 'moonMaker'];
        }
        if ($this->motherShip > 0) {
            $return[] = [number_format($this->motherShip), 'motherShip'];
        }
        if ($this->hunter > 0) {
            $return[] = [number_format($this->hunter), 'hunter'];
        }
        if ($this->hunterHeavy > 0) {
            $return[] = [number_format($this->hunterHeavy), 'hunterHeavy'];
        }
        if ($this->hunterWar > 0) {
            $return[] = [number_format($this->hunterWar), 'hunterWar'];
        }
        if ($this->corvet > 0) {
            $return[] = [number_format($this->corvet), 'corvet'];
        }
        if ($this->corvetLaser > 0) {
            $return[] = [number_format($this->corvetLaser), 'corvetLaser'];
        }
        if ($this->corvetWar > 0) {
            $return[] = [number_format($this->corvetWar), 'corvetWar'];
        }
        if ($this->fregate > 0) {
            $return[] = [number_format($this->fregate), 'fregate'];
        }
        if ($this->fregatePlasma > 0) {
            $return[] = [number_format($this->fregatePlasma), 'fregatePlasma'];
        }
        if ($this->croiser > 0) {
            $return[] = [number_format($this->croiser), 'croiser'];
        }
        if ($this->ironClad > 0) {
            $return[] = [number_format($this->ironClad), 'ironClad'];
        }
        if ($this->destroyer > 0) {
            $return[] = [number_format($this->destroyer), 'destroyer'];
        }
        if ($this->nuclearBomb > 0) {
            $return[] = [number_format($this->nuclearBomb), 'nuclearBomb'];
        }
        return $return;
    }

    /**
     * @param $armor
     */
    public function setFleetWinRatio($armor): void
    {
        if($this->getMotherShip() == 1) {
            $armor = $armor * 0.05;
        }
        if($armor > 0) {
            if ($this->getHunter()) {
                $new = (($this->getHunter() * 15) - $armor) / 15;
                if($new < 0) {
                    $armor = $new * -15;
                } else {
                    $armor = 0;
                }
                if($new < 0) {
                    $new = 0;
                }
                $this->setHunter(round($new));
            }
            if ($this->getCorvet() && $armor != 0) {
                $new = (($this->getCorvet() * 74) - $armor) / 74;
                if($new < 0) {
                    $armor = $new * -74;
                } else {
                    $armor = 0;
                }
                if($new < 0) {
                    $new = 0;
                }
                $this->setCorvet(round($new));
            }
            if ($this->getHunterHeavy() && $armor != 0) {
                $new = (($this->getHunterHeavy() * 25) - $armor) / 25;
                if($new < 0) {
                    $armor = $new * -25;
                } else {
                    $armor = 0;
                }
                if($new < 0) {
                    $new = 0;
                }
                $this->setHunterHeavy(round($new));
            }
            if ($this->getFregate() && $armor != 0) {
                $new = (($this->getFregate() * 168) - $armor) / 168;
                if($new < 0) {
                    $armor = $new * -168;
                } else {
                    $armor = 0;
                }
                if($new < 0) {
                    $new = 0;
                }
                $this->setFregate(round($new));
            }
            if ($this->getHunterWar() && $armor != 0) {
                $new = (($this->getHunterWar() * 53) - $armor) / 53;
                if($new < 0) {
                    $armor = $new * -53;
                } else {
                    $armor = 0;
                }
                if($new < 0) {
                    $new = 0;
                }
                $this->setHunterWar(round($new));
            }
            if ($this->getCorvetLaser() && $armor != 0) {
                $new = (($this->getCorvetLaser() * 115) - $armor) / 115;
                if($new < 0) {
                    $armor = $new * -115;
                } else {
                    $armor = 0;
                }
                if($new < 0) {
                    $new = 0;
                }
                $this->setCorvetLaser(round($new));
            }
            if ($this->getCorvetWar() && $armor != 0) {
                $new = (($this->getCorvetWar() * 135) - $armor) / 135;
                if($new < 0) {
                    $armor = $new * -135;
                } else {
                    $armor = 0;
                }
                if($new < 0) {
                    $new = 0;
                }
                $this->setCorvetWar(round($new));
            }
            if ($this->getFregatePlasma() && $armor != 0) {
                $new = (($this->getFregatePlasma() * 451) - $armor) / 451;
                if($new < 0) {
                    $armor = $new * -451;
                } else {
                    $armor = 0;
                }
                if($new < 0) {
                    $new = 0;
                }
                $this->setFregatePlasma(round($new));
            }
            if ($this->getCroiser() && $armor != 0) {
                $new = (($this->getCroiser() * 957) - $armor) / 957;
                if($new < 0) {
                    $armor = $new * -957;
                } else {
                    $armor = 0;
                }
                if($new < 0) {
                    $new = 0;
                }
                $this->setCroiser(round($new));
            }
            if ($this->getDestroyer() && $armor != 0) {
                $new = (($this->getDestroyer() * 5176) - $armor) / 5176;
                if($new < 0) {
                    $armor = $new * -5176;
                } else {
                    $armor = 0;
                }
                if($new < 0) {
                    $new = 0;
                }
                $this->setDestroyer(round($new));
            }
            if ($this->getIronClad() && $armor != 0) {
                $new = (($this->getIronClad() * 2415) - $armor) / 2415;
                if($new < 0) {
                    $new = 0;
                }
                $this->setIronClad(round($new));
            }
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function getAttack(): bool
    {
        return $this->attack;
    }

    /**
     * @param mixed $attack
     */
    public function setAttack($attack): void
    {
        $this->attack = $attack;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getAlly()
    {
        return $this->ally;
    }

    /**
     * @param mixed $ally
     */
    public function setAlly($ally): void
    {
        $this->ally = $ally;
    }

    /**
     * @return mixed
     */
    public function getPlanet()
    {
        return $this->planet;
    }

    /**
     * @param mixed $planet
     */
    public function setPlanet($planet): void
    {
        $this->planet = $planet;
    }

    /**
     * @return mixed
     */
    public function getFleetList()
    {
        return $this->fleetList;
    }

    /**
     * @param mixed $fleetList
     */
    public function setFleetList($fleetList): void
    {
        $this->fleetList = $fleetList;
    }

    /**
     * @return mixed
     */
    public function getFlightTime()
    {
        return $this->flightTime;
    }

    /**
     * @param mixed $flightTime
     */
    public function setFlightTime($flightTime): void
    {
        $this->flightTime = $flightTime;
    }

    /**
     * @return mixed
     */
    public function getSonde()
    {
        return $this->sonde;
    }

    /**
     * @param mixed $sonde
     */
    public function setSonde($sonde): void
    {
        $this->sonde = $sonde;
    }

    /**
     * @return mixed
     */
    public function getColonizer()
    {
        return $this->colonizer;
    }

    /**
     * @param mixed $colonizer
     */
    public function setColonizer($colonizer): void
    {
        $this->colonizer = $colonizer;
    }

    /**
     * @return mixed
     */
    public function getRecycleur()
    {
        return $this->recycleur;
    }

    /**
     * @param mixed $recycleur
     */
    public function setRecycleur($recycleur): void
    {
        $this->recycleur = $recycleur;
    }

    /**
     * @return mixed
     */
    public function getBarge()
    {
        return $this->barge;
    }

    /**
     * @param mixed $barge
     */
    public function setBarge($barge): void
    {
        $this->barge = $barge;
    }

    /**
     * @return mixed
     */
    public function getHunter()
    {
        return $this->hunter;
    }

    /**
     * @param mixed $hunter
     */
    public function setHunter($hunter): void
    {
        $this->hunter = $hunter;
    }

    /**
     * @return mixed
     */
    public function getFregate()
    {
        return $this->fregate;
    }

    /**
     * @param mixed $fregate
     */
    public function setFregate($fregate): void
    {
        $this->fregate = $fregate;
    }

    /**
     * @return mixed
     */
    public function getSoldier()
    {
        return $this->soldier;
    }

    /**
     * @param mixed $soldier
     */
    public function setSoldier($soldier): void
    {
        $this->soldier = $soldier;
    }

    /**
     * @return mixed
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * @param mixed $worker
     */
    public function setWorker($worker): void
    {
        $this->worker = $worker;
    }

    /**
     * @return mixed
     */
    public function getRecycleAt()
    {
        return $this->recycleAt;
    }

    /**
     * @param mixed $recycleAt
     */
    public function setRecycleAt($recycleAt): void
    {
        $this->recycleAt = $recycleAt;
    }

    /**
     * @return mixed
     */
    public function getScientist()
    {
        return $this->scientist;
    }

    /**
     * @param mixed $scientist
     */
    public function setScientist($scientist): void
    {
        $this->scientist = $scientist;
    }

    /**
     * @return mixed
     */
    public function getFightAt()
    {
        return $this->fightAt;
    }

    /**
     * @param mixed $fightAt
     */
    public function setFightAt($fightAt): void
    {
        $this->fightAt = $fightAt;
    }

    /**
     * @return mixed
     */
    public function getNiobium()
    {
        return $this->niobium;
    }

    /**
     * @param mixed $niobium
     */
    public function setNiobium($niobium): void
    {
        $this->niobium = $niobium;
    }

    /**
     * @return mixed
     */
    public function getWater()
    {
        return $this->water;
    }

    /**
     * @param mixed $water
     */
    public function setWater($water): void
    {
        $this->water = $water;
    }

    /**
     * @return null
     */
    public function getFood()
    {
        return $this->food;
    }

    /**
     * @param null $food
     */
    public function setFood($food): void
    {
        $this->food = $food;
    }

    /**
     * @return mixed
     */
    public function getCargoI()
    {
        return $this->cargoI;
    }

    /**
     * @param mixed $cargoI
     */
    public function setCargoI($cargoI): void
    {
        $this->cargoI = $cargoI;
    }

    /**
     * @return mixed
     */
    public function getCargoV()
    {
        return $this->cargoV;
    }

    /**
     * @param mixed $cargoV
     */
    public function setCargoV($cargoV): void
    {
        $this->cargoV = $cargoV;
    }

    /**
     * @return mixed
     */
    public function getCargoX()
    {
        return $this->cargoX;
    }

    /**
     * @param mixed $cargoX
     */
    public function setCargoX($cargoX): void
    {
        $this->cargoX = $cargoX;
    }

    /**
     * @return mixed
     */
    public function getHunterHeavy()
    {
        return $this->hunterHeavy;
    }

    /**
     * @param mixed $hunterHeavy
     */
    public function setHunterHeavy($hunterHeavy): void
    {
        $this->hunterHeavy = $hunterHeavy;
    }

    /**
     * @return mixed
     */
    public function getCorvet()
    {
        return $this->corvet;
    }

    /**
     * @param mixed $corvet
     */
    public function setCorvet($corvet): void
    {
        $this->corvet = $corvet;
    }

    /**
     * @return mixed
     */
    public function getCorvetLaser()
    {
        return $this->corvetLaser;
    }

    /**
     * @param mixed $corvetLaser
     */
    public function setCorvetLaser($corvetLaser): void
    {
        $this->corvetLaser = $corvetLaser;
    }

    /**
     * @return mixed
     */
    public function getFregatePlasma()
    {
        return $this->fregatePlasma;
    }

    /**
     * @param mixed $fregatePlasma
     */
    public function setFregatePlasma($fregatePlasma): void
    {
        $this->fregatePlasma = $fregatePlasma;
    }

    /**
     * @return mixed
     */
    public function getCroiser()
    {
        return $this->croiser;
    }

    /**
     * @param mixed $croiser
     */
    public function setCroiser($croiser): void
    {
        $this->croiser = $croiser;
    }

    /**
     * @return mixed
     */
    public function getIronClad()
    {
        return $this->ironClad;
    }

    /**
     * @param mixed $ironClad
     */
    public function setIronClad($ironClad): void
    {
        $this->ironClad = $ironClad;
    }

    /**
     * @return mixed
     */
    public function getFlightType()
    {
        return $this->flightType;
    }

    /**
     * @param mixed $flightType
     */
    public function setFlightType($flightType): void
    {
        $this->flightType = $flightType;
    }

    /**
     * @return mixed
     */
    public function getDestroyer()
    {
        return $this->destroyer;
    }

    /**
     * @param mixed $destroyer
     */
    public function setDestroyer($destroyer): void
    {
        $this->destroyer = $destroyer;
    }

    /**
     * @return mixed
     */
    public function getHunterWar()
    {
        return $this->hunterWar;
    }

    /**
     * @param mixed $hunterWar
     */
    public function setHunterWar($hunterWar): void
    {
        $this->hunterWar = $hunterWar;
    }

    /**
     * @return mixed
     */
    public function getCorvetWar()
    {
        return $this->corvetWar;
    }

    /**
     * @param mixed $corvetWar
     */
    public function setCorvetWar($corvetWar): void
    {
        $this->corvetWar = $corvetWar;
    }

    /**
     * @return mixed
     */
    public function getCommander()
    {
        return $this->commander;
    }

    /**
     * @param mixed $commander
     */
    public function setCommander($commander): void
    {
        $this->commander = $commander;
    }

    /**
     * @return mixed
     */
    public function getMoonMaker()
    {
        return $this->moonMaker;
    }

    /**
     * @param mixed $moonMaker
     */
    public function setMoonMaker($moonMaker): void
    {
        $this->moonMaker = $moonMaker;
    }

    /**
     * @return mixed
     */
    public function getRadarShip()
    {
        return $this->radarShip;
    }

    /**
     * @param mixed $radarShip
     */
    public function setRadarShip($radarShip): void
    {
        $this->radarShip = $radarShip;
    }

    /**
     * @return mixed
     */
    public function getBrouilleurShip()
    {
        return $this->brouilleurShip;
    }

    /**
     * @param mixed $brouilleurShip
     */
    public function setBrouilleurShip($brouilleurShip): void
    {
        $this->brouilleurShip = $brouilleurShip;
    }

    /**
     * @return mixed
     */
    public function getMotherShip()
    {
        return $this->motherShip;
    }

    /**
     * @param mixed $motherShip
     */
    public function setMotherShip($motherShip): void
    {
        $this->motherShip = $motherShip;
    }

    /**
     * @return mixed
     */
    public function getTank()
    {
        return $this->tank;
    }

    /**
     * @param mixed $tank
     */
    public function setTank($tank): void
    {
        $this->tank = $tank;
    }

    /**
     * @return mixed
     */
    public function getCancelFlight()
    {
        return $this->cancelFlight;
    }

    /**
     * @return mixed
     */
    public function getUranium()
    {
        return $this->uranium;
    }

    /**
     * @param mixed $uranium
     */
    public function setUranium($uranium): void
    {
        $this->uranium = $uranium;
    }

    /**
     * @return mixed
     */
    public function getNuclearBomb()
    {
        return $this->nuclearBomb;
    }

    /**
     * @param mixed $nuclearBomb
     */
    public function setNuclearBomb($nuclearBomb): void
    {
        $this->nuclearBomb = $nuclearBomb;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param mixed $destination
     */
    public function setDestination($destination): void
    {
        $this->destination = $destination;
    }

    /**
     * @return mixed
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param mixed $signature
     */
    public function setSignature($signature): void
    {
        $this->signature = $signature;
    }

    /**
     * @param mixed $cancelFlight
     */
    public function setCancelFlight($cancelFlight): void
    {
        $this->cancelFlight = $cancelFlight;
    }
}
