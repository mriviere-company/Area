<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="item")
 * @ORM\Entity
 */
class Item
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="warPoint",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $warPoint;

    /**
     * @ORM\Column(name="bitcoin",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $bitcoin;

    /**
     * @ORM\Column(name="sonde",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $sonde;

    /**
     * @ORM\Column(name="cargoI",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $cargoI;

    /**
     * @ORM\Column(name="cargoV",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $cargoV;

    /**
     * @ORM\Column(name="cargoX",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $cargoX;

    /**
     * @ORM\Column(name="colonizer",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $colonizer;

    /**
     * @ORM\Column(name="recycleur",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $recycleur;

    /**
     * @ORM\Column(name="barge",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $barge;

    /**
     * @ORM\Column(name="moonMaker",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $moonMaker;

    /**
     * @ORM\Column(name="radarShip",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $radarShip;

    /**
     * @ORM\Column(name="brouilleurShip",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $brouilleurShip;

    /**
     * @ORM\Column(name="motherShip",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $motherShip;

    /**
     * @ORM\Column(name="hunter",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $hunter;

    /**
     * @ORM\Column(name="hunterHeavy",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $hunterHeavy;

    /**
     * @ORM\Column(name="hunterWar",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $hunterWar;

    /**
     * @ORM\Column(name="corvet",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $corvet;

    /**
     * @ORM\Column(name="corvetLaser",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $corvetLaser;

    /**
     * @ORM\Column(name="corvetWar",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $corvetWar;

    /**
     * @ORM\Column(name="fregate",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $fregate;

    /**
     * @ORM\Column(name="fregatePlasma",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $fregatePlasma;

    /**
     * @ORM\Column(name="croiser",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $croiser;

    /**
     * @ORM\Column(name="ironClad",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $ironClad;

    /**
     * @ORM\Column(name="destroyer",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $destroyer;

    /**
     * @ORM\Column(name="soldier",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $soldier;

    /**
     * @ORM\Column(name="tank",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $tank;

    /**
     * @ORM\Column(name="worker",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $worker;

    /**
     * @ORM\Column(name="scientist",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $scientist;

    /**
     * @ORM\Column(name="nuclear_bomb",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $nuclearBomb;

    /**
     * @ORM\Column(name="niobium",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $niobium;

    /**
     * @ORM\Column(name="water",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $water;

    /**
     * @ORM\Column(name="food",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $food;

    /**
     * @ORM\Column(name="uranium",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $uranium;

    /**
     * @ORM\Column(name="teleport",type="boolean")
     */
    protected $teleport;

    /**
     * @ORM\Column(name="speedup",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $speedup;

    /**
     * @ORM\Column(name="noobShield",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $noobShield;

    /**
     * @ORM\Column(name="heroeXp",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $heroeXp;

    /**
     * @ORM\Column(name="heroeStar",type="boolean")
     */
    protected $heroeStar;

    /**
     * @ORM\Column(name="resetShip",type="boolean")
     */
    protected $resetShip;

    /**
     * @ORM\Column(name="sword",type="string", length=35, nullable=true)
     */
    protected $sword;

    /**
     * @ORM\Column(name="body",type="string", length=35, nullable=true)
     */
    protected $body;

    /**
     * @ORM\Column(name="foot",type="string", length=35, nullable=true)
     */
    protected $foot;

    /**
     * @ORM\Column(name="head",type="string", length=35, nullable=true)
     */
    protected $head;

    /**
     * @ORM\Column(name="gun",type="string", length=35, nullable=true)
     */
    protected $gun;

    /**
     * @ORM\Column(name="rarity",type="smallint", options={"unsigned":true})
     */
    protected $rarity;

    public function __construct()
    {
        $this->rarity = 0;
        $this->resetShip = false;
        $this->teleport = false;
        $this->heroeStar = false;
        $this->sword = null;
        $this->body = null;
        $this->foot = null;
        $this->head = null;
        $this->gun = null;
        $this->heroeXp = null;
        $this->noobShield = null;
        $this->speedup = null;
        $this->warPoint = null;
        $this->bitcoin = null;
        $this->water = null;
        $this->niobium = null;
        $this->food = null;
        $this->uranium = null;
        $this->scientist = null;
        $this->worker = null;
        $this->soldier = null;
        $this->tank = null;
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
        $this->nuclearBomb = null;
    }

    /**
     * @return mixed
     */
    public function getWarPoint()
    {
        return $this->warPoint;
    }

    /**
     * @param mixed $warPoint
     */
    public function setWarPoint($warPoint): void
    {
        $this->warPoint = $warPoint;
    }

    /**
     * @return null
     */
    public function getBitcoin()
    {
        return $this->bitcoin;
    }

    /**
     * @param null $bitcoin
     */
    public function setBitcoin($bitcoin): void
    {
        $this->bitcoin = $bitcoin;
    }

    /**
     * @return null
     */
    public function getSonde()
    {
        return $this->sonde;
    }

    /**
     * @param null $sonde
     */
    public function setSonde($sonde): void
    {
        $this->sonde = $sonde;
    }

    /**
     * @return null
     */
    public function getCargoI()
    {
        return $this->cargoI;
    }

    /**
     * @param null $cargoI
     */
    public function setCargoI($cargoI): void
    {
        $this->cargoI = $cargoI;
    }

    /**
     * @return null
     */
    public function getCargoV()
    {
        return $this->cargoV;
    }

    /**
     * @param null $cargoV
     */
    public function setCargoV($cargoV): void
    {
        $this->cargoV = $cargoV;
    }

    /**
     * @return null
     */
    public function getCargoX()
    {
        return $this->cargoX;
    }

    /**
     * @param null $cargoX
     */
    public function setCargoX($cargoX): void
    {
        $this->cargoX = $cargoX;
    }

    /**
     * @return null
     */
    public function getColonizer()
    {
        return $this->colonizer;
    }

    /**
     * @param null $colonizer
     */
    public function setColonizer($colonizer): void
    {
        $this->colonizer = $colonizer;
    }

    /**
     * @return null
     */
    public function getRecycleur()
    {
        return $this->recycleur;
    }

    /**
     * @param null $recycleur
     */
    public function setRecycleur($recycleur): void
    {
        $this->recycleur = $recycleur;
    }

    /**
     * @return null
     */
    public function getBarge()
    {
        return $this->barge;
    }

    /**
     * @param null $barge
     */
    public function setBarge($barge): void
    {
        $this->barge = $barge;
    }

    /**
     * @return null
     */
    public function getMoonMaker()
    {
        return $this->moonMaker;
    }

    /**
     * @param null $moonMaker
     */
    public function setMoonMaker($moonMaker): void
    {
        $this->moonMaker = $moonMaker;
    }

    /**
     * @return null
     */
    public function getRadarShip()
    {
        return $this->radarShip;
    }

    /**
     * @param null $radarShip
     */
    public function setRadarShip($radarShip): void
    {
        $this->radarShip = $radarShip;
    }

    /**
     * @return null
     */
    public function getJammerShip()
    {
        return $this->brouilleurShip;
    }

    /**
     * @param null $brouilleurShip
     */
    public function setJammerShip($brouilleurShip): void
    {
        $this->brouilleurShip = $brouilleurShip;
    }

    /**
     * @return null
     */
    public function getMotherShip()
    {
        return $this->motherShip;
    }

    /**
     * @param null $motherShip
     */
    public function setMotherShip($motherShip): void
    {
        $this->motherShip = $motherShip;
    }

    /**
     * @return null
     */
    public function getHunter()
    {
        return $this->hunter;
    }

    /**
     * @param null $hunter
     */
    public function setHunter($hunter): void
    {
        $this->hunter = $hunter;
    }

    /**
     * @return null
     */
    public function getHunterHeavy()
    {
        return $this->hunterHeavy;
    }

    /**
     * @param null $hunterHeavy
     */
    public function setHunterHeavy($hunterHeavy): void
    {
        $this->hunterHeavy = $hunterHeavy;
    }

    /**
     * @return null
     */
    public function getHunterWar()
    {
        return $this->hunterWar;
    }

    /**
     * @param null $hunterWar
     */
    public function setHunterWar($hunterWar): void
    {
        $this->hunterWar = $hunterWar;
    }

    /**
     * @return null
     */
    public function getCorvet()
    {
        return $this->corvet;
    }

    /**
     * @param null $corvet
     */
    public function setCorvet($corvet): void
    {
        $this->corvet = $corvet;
    }

    /**
     * @return null
     */
    public function getCorvetLaser()
    {
        return $this->corvetLaser;
    }

    /**
     * @param null $corvetLaser
     */
    public function setCorvetLaser($corvetLaser): void
    {
        $this->corvetLaser = $corvetLaser;
    }

    /**
     * @return null
     */
    public function getCorvetWar()
    {
        return $this->corvetWar;
    }

    /**
     * @param null $corvetWar
     */
    public function setCorvetWar($corvetWar): void
    {
        $this->corvetWar = $corvetWar;
    }

    /**
     * @return null
     */
    public function getFregate()
    {
        return $this->fregate;
    }

    /**
     * @param null $fregate
     */
    public function setFregate($fregate): void
    {
        $this->fregate = $fregate;
    }

    /**
     * @return null
     */
    public function getFregatePlasma()
    {
        return $this->fregatePlasma;
    }

    /**
     * @param null $fregatePlasma
     */
    public function setFregatePlasma($fregatePlasma): void
    {
        $this->fregatePlasma = $fregatePlasma;
    }

    /**
     * @return null
     */
    public function getCroiser()
    {
        return $this->croiser;
    }

    /**
     * @param null $croiser
     */
    public function setCroiser($croiser): void
    {
        $this->croiser = $croiser;
    }

    /**
     * @return null
     */
    public function getIronClad()
    {
        return $this->ironClad;
    }

    /**
     * @param null $ironClad
     */
    public function setIronClad($ironClad): void
    {
        $this->ironClad = $ironClad;
    }

    /**
     * @return null
     */
    public function getDestroyer()
    {
        return $this->destroyer;
    }

    /**
     * @param null $destroyer
     */
    public function setDestroyer($destroyer): void
    {
        $this->destroyer = $destroyer;
    }

    /**
     * @return null
     */
    public function getSoldier()
    {
        return $this->soldier;
    }

    /**
     * @param null $soldier
     */
    public function setSoldier($soldier): void
    {
        $this->soldier = $soldier;
    }

    /**
     * @return null
     */
    public function getTank()
    {
        return $this->tank;
    }

    /**
     * @param null $tank
     */
    public function setTank($tank): void
    {
        $this->tank = $tank;
    }

    /**
     * @return null
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * @param null $worker
     */
    public function setWorker($worker): void
    {
        $this->worker = $worker;
    }

    /**
     * @return null
     */
    public function getScientist()
    {
        return $this->scientist;
    }

    /**
     * @param null $scientist
     */
    public function setScientist($scientist): void
    {
        $this->scientist = $scientist;
    }

    /**
     * @return null
     */
    public function getNuclearBomb()
    {
        return $this->nuclearBomb;
    }

    /**
     * @param null $nuclearBomb
     */
    public function setNuclearBomb($nuclearBomb): void
    {
        $this->nuclearBomb = $nuclearBomb;
    }

    /**
     * @return null
     */
    public function getNiobium()
    {
        return $this->niobium;
    }

    /**
     * @param null $niobium
     */
    public function setNiobium($niobium): void
    {
        $this->niobium = $niobium;
    }

    /**
     * @return null
     */
    public function getWater()
    {
        return $this->water;
    }

    /**
     * @param null $water
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
     * @return null
     */
    public function getUranium()
    {
        return $this->uranium;
    }

    /**
     * @param null $uranium
     */
    public function setUranium($uranium): void
    {
        $this->uranium = $uranium;
    }

    /**
     * @return false
     */
    public function getTeleport()
    {
        return $this->teleport;
    }

    /**
     * @param false $teleport
     */
    public function setTeleport($teleport): void
    {
        $this->teleport = $teleport;
    }

    /**
     * @return null
     */
    public function getSpeedup()
    {
        return $this->speedup;
    }

    /**
     * @param null $speedup
     */
    public function setSpeedup($speedup): void
    {
        $this->speedup = $speedup;
    }

    /**
     * @return null
     */
    public function getNoobShield()
    {
        return $this->noobShield;
    }

    /**
     * @param null $noobShield
     */
    public function setNoobShield($noobShield): void
    {
        $this->noobShield = $noobShield;
    }

    /**
     * @return null
     */
    public function getHeroeXp()
    {
        return $this->heroeXp;
    }

    /**
     * @param null $heroeXp
     */
    public function setHeroeXp($heroeXp): void
    {
        $this->heroeXp = $heroeXp;
    }

    /**
     * @return false
     */
    public function getHeroeStar()
    {
        return $this->heroeStar;
    }

    /**
     * @param false $heroeStar
     */
    public function setHeroeStar($heroeStar): void
    {
        $this->heroeStar = $heroeStar;
    }

    /**
     * @return false
     */
    public function getResetShip()
    {
        return $this->resetShip;
    }

    /**
     * @param false $resetShip
     */
    public function setResetShip($resetShip): void
    {
        $this->resetShip = $resetShip;
    }

    /**
     * @return null
     */
    public function getSword()
    {
        return $this->sword;
    }

    /**
     * @param null $sword
     */
    public function setSword($sword): void
    {
        $this->sword = $sword;
    }

    /**
     * @return null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param null $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

    /**
     * @return null
     */
    public function getFoot()
    {
        return $this->foot;
    }

    /**
     * @param null $foot
     */
    public function setFoot($foot): void
    {
        $this->foot = $foot;
    }

    /**
     * @return null
     */
    public function getHead()
    {
        return $this->head;
    }

    /**
     * @param null $head
     */
    public function setHead($head): void
    {
        $this->head = $head;
    }

    /**
     * @return null
     */
    public function getGun()
    {
        return $this->gun;
    }

    /**
     * @param null $gun
     */
    public function setGun($gun): void
    {
        $this->gun = $gun;
    }

    /**
     * @return int
     */
    public function getRarity(): int
    {
        return $this->rarity;
    }

    /**
     * @param int $rarity
     */
    public function setRarity(int $rarity): void
    {
        $this->rarity = $rarity;
    }

    public function getId()
    {
        return $this->id;
    }
}
