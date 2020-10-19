<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Table(name="planet")
 * @ORM\Entity(repositoryClass="App\Repository\PlanetRepository")
 * @Vich\Uploadable
 */
class Planet
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name",type="string", length=20, nullable=true)
     * @Assert\Regex(
     *     pattern="/[a-zA-Z0-9]/")
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="planets", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(name="lastActivity",type="datetime", nullable=true)
     */
    protected $lastActivity;

    /**
     * @ORM\Column(name="nbColo",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $nbColo;

    /**
     * @ORM\OneToOne(targetEntity="Commander", inversedBy="planet", fetch="EXTRA_LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="commander_id", referencedColumnName="id")
     */
    protected $commander;

    /**
     * @ORM\Column(name="niobium",type="integer", options={"unsigned":true})
     */
    protected $niobium;

    /**
     * @ORM\Column(name="water",type="integer", options={"unsigned":true})
     */
    protected $water;

    /**
     * @ORM\Column(name="food",type="integer", options={"unsigned":true})
     */
    protected $food;

    /**
     * @ORM\Column(name="uranium",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $uranium;

    /**
     * @ORM\Column(name="nbCdr",type="bigint", options={"unsigned":true})
     */
    protected $nbCdr;

    /**
     * @ORM\Column(name="wtCdr",type="bigint", options={"unsigned":true})
     */
    protected $wtCdr;

    /**
     * @ORM\Column(name="signature",type="bigint", options={"unsigned":true})
     */
    protected $signature;

    /**
     * @ORM\Column(name="shipProduction",type="decimal", precision=28, scale=5, options={"unsigned":true})
     */
    protected $shipProduction;

    /**
     * @ORM\OneToOne(targetEntity="Product", mappedBy="planet", fetch="EXTRA_LAZY", cascade={"persist"})
     */
    protected $product;

    /**
     * @ORM\Column(name="workerProduction",type="decimal", precision=28, scale=5, options={"unsigned":true})
     */
    protected $workerProduction;

    /**
     * @ORM\Column(name="niobiumMax",type="integer", options={"unsigned":true})
     */
    protected $niobiumMax;

    /**
     * @ORM\Column(name="waterMax",type="integer", options={"unsigned":true})
     */
    protected $waterMax;

    /**
     * @ORM\Column(name="foodMax",type="integer", options={"unsigned":true})
     */
    protected $foodMax;

    /**
     * @ORM\Column(name="soldierMax",type="integer", options={"unsigned":true})
     */
    protected $soldierMax;

    /**
     * @ORM\Column(name="scientistMax",type="smallint", options={"unsigned":true})
     */
    protected $scientistMax;

    /**
     * @ORM\Column(name="nuclear_bomb",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $nuclearBomb;

    /**
     * @ORM\Column(name="workerMax",type="integer", options={"unsigned":true})
     */
    protected $workerMax;

    /**
     * @ORM\Column(name="nbProduction",type="decimal", precision=28, scale=5)
     */
    protected $nbProduction;

    /**
     * @ORM\Column(name="wtProduction",type="decimal", precision=28, scale=5, options={"unsigned":true})
     */
    protected $wtProduction;

    /**
     * @ORM\Column(name="fdProduction",type="decimal", precision=28, scale=5, options={"unsigned":true})
     */
    protected $fdProduction;

    /**
     * @ORM\OneToMany(targetEntity="Fleet", mappedBy="planet", fetch="EXTRA_LAZY")
     */
    protected $fleets;

    /**
     * @ORM\OneToMany(targetEntity="Destination", mappedBy="planet", fetch="EXTRA_LAZY")
     */
    protected $destinations;

    /**
     * @ORM\OneToMany(targetEntity="Construction", mappedBy="planet", fetch="EXTRA_LAZY")
     */
    protected $constructions;

    /**
     * @ORM\Column(name="construct",type="string", nullable=true)
     */
    protected $construct;

    /**
     * @ORM\Column(name="constructAt",type="datetime", nullable=true)
     */
    protected $constructAt;

    /**
     * @ORM\Column(name="recycleAt",type="datetime", nullable=true)
     */
    protected $recycleAt;

    /**
     * @ORM\Column(name="scientistAt",type="datetime", nullable=true)
     */
    protected $scientistAt;

    /**
     * @ORM\Column(name="scientistAtNbr",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $scientistAtNbr;

    /**
     * @ORM\Column(name="soldierAt",type="datetime", nullable=true)
     */
    protected $soldierAt;

    /**
     * @ORM\Column(name="nuclearAt",type="datetime", nullable=true)
     */
    protected $nuclearAt;

    /**
     * @ORM\Column(name="nuclearAtNbr",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $nuclearAtNbr;

    /**
     * @ORM\Column(name="soldierAtNbr",type="integer", nullable=true, options={"unsigned":true})
     */
    protected $soldierAtNbr;

    /**
     * @ORM\Column(name="tankAt",type="datetime", nullable=true)
     */
    protected $tankAt;

    /**
     * @ORM\Column(name="tankAtNbr",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $tankAtNbr;

    /**
     * @ORM\Column(name="miner",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $miner;

    /**
     * @ORM\Column(name="niobiumStock",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $niobiumStock;

    /**
     * @ORM\Column(name="extractor",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $extractor;

    /**
     * @ORM\Column(name="waterStock",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $waterStock;

    /**
     * @ORM\Column(name="farm",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $farm;

    /**
     * @ORM\Column(name="aeroponicFarm",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $aeroponicFarm;

    /**
     * @ORM\Column(name="silos",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $silos;

    /**
     * @ORM\Column(name="spaceShip",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $spaceShip;

    /**
     * @ORM\Column(name="centerSearch",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $centerSearch;

    /**
     * @ORM\Column(name="metropole",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $metropole;

    /**
     * @ORM\Column(name="city",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $city;

    /**
     * @ORM\Column(name="caserne",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $caserne;

    /**
     * @ORM\Column(name="island",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $island;

    /**
     * @ORM\Column(name="orbital",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $orbital;

    /**
     * @ORM\Column(name="bunker",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $bunker;

    /**
     * @ORM\Column(name="nuclear_base",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $nuclearBase;

    /**
     * @ORM\Column(name="radar",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $radar;

    /**
     * @ORM\Column(name="skyRadar",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $skyRadar;

    /**
     * @ORM\Column(name="skyBrouilleur",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $skyBrouilleur;

    /**
     * @ORM\Column(name="lightUsine",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $lightUsine;

    /**
     * @ORM\Column(name="heavyUsine",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $heavyUsine;

    /**
     * @ORM\Column(name="sonde",type="integer", nullable=true, options={"unsigned":true})
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
     * @ORM\Column(name="soldier",type="integer", options={"unsigned":true})
     */
    protected $soldier;

    /**
     * @ORM\Column(name="tank",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $tank;

    /**
     * @ORM\Column(name="worker",type="integer", options={"unsigned":true})
     */
    protected $worker;

    /**
     * @ORM\Column(name="scientist",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $scientist;

    /**
     * @ORM\ManyToOne(targetEntity="Sector", inversedBy="planets", fetch="EXTRA_LAZY")
     */
    protected $sector;

    /**
     * @ORM\Column(name="position",type="smallint", options={"unsigned":true})
     * @Assert\NotBlank(message = "required")
     */
    protected $position;

    /**
     * @ORM\Column(name="ground",type="smallint", options={"unsigned":true})
     */
    protected $ground;

    /**
     * @ORM\Column(name="groundPlace",type="smallint", options={"unsigned":true})
     */
    protected $groundPlace;

    /**
     * @ORM\Column(name="sky",type="smallint", options={"unsigned":true})
     */
    protected $sky;

    /**
     * @ORM\Column(name="skyPlace",type="smallint", options={"unsigned":true})
     */
    protected $skyPlace;

    /**
     * @ORM\Column(name="empty",type="boolean")
     */
    protected $empty;

    /**
     * @ORM\Column(name="cdr",type="boolean")
     */
    protected $cdr;

    /**
     * @ORM\Column(name="merchant",type="boolean")
     */
    protected $merchant;

    /**
     * @ORM\Column(name="moon",type="boolean")
     */
    protected $moon;

    /**
     * @ORM\Column(name="auto_seller",type="boolean")
     */
    protected $autoSeller;

    /**
     * @ORM\Column(name="radarAt",type="datetime", nullable=true)
     */
    protected $radarAt;

    /**
     * @ORM\Column(name="brouilleurAt",type="datetime", nullable=true)
     */
    protected $brouilleurAt;

    /**
     * @Assert\File(
     *     maxSize="400k",
     *     mimeTypes={"image/png", "image/jpeg", "image/bmp"}
     * )
     * @Vich\UploadableField(mapping="planet_img", fileNameProperty="imageName" )
     *
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(name="imageName",type="string", length=20, nullable=true)
     */
    protected $imageName;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $updatedAt;

    public function __construct()
    {
        $this->destinations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fleets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->constructions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->centerSearch = null;
        $this->imageFile = null;
        $this->brouilleurAt = null;
        $this->radarAt = null;
        $this->moon = 0;
        $this->merchant = 0;
        $this->cdr = 0;
        $this->empty = 0;
        $this->cdr = 0;
        $this->name = 'Inhabitée';
        $this->niobium = 200;
        $this->water = 140;
        $this->food = 1000;
        $this->nbCdr = 0;
        $this->wtCdr = 0;
        $this->shipProduction = 1;
        $this->workerProduction = 11;
        $this->niobiumMax = 13000;
        $this->waterMax = 10000;
        $this->foodMax = 15000;
        $this->soldierMax = 20;
        $this->tank = null;
        $this->scientistMax = 0;
        $this->workerMax = 25000;
        $this->nbProduction = 7;
        $this->wtProduction = 6;
        $this->fdProduction = 9;
        $this->miner = null;
        $this->niobiumStock = null;
        $this->extractor = null;
        $this->waterStock = null;
        $this->farm = null;
        $this->aeroponicFarm = null;
        $this->silos = null;
        $this->spaceShip = null;
        $this->metropole = null;
        $this->city = null;
        $this->caserne = null;
        $this->bunker = null;
        $this->island = null;
        $this->orbital = null;
        $this->radar = null;
        $this->nuclearBase = null;
        $this->skyRadar = null;
        $this->skyBrouilleur = null;
        $this->lightUsine = null;
        $this->heavyUsine = null;
        $this->sonde = null;
        $this->cargoI = null;
        $this->cargoV = null;
        $this->cargoX = null;
        $this->colonizer = null;
        $this->recycleur = null;
        $this->barge = null;
        $this->moonMaker = null;
        $this->radarShip = null;
        $this->brouilleurShip = null;
        $this->motherShip = null;
        $this->hunter = null;
        $this->hunterHeavy = null;
        $this->hunterWar = null;
        $this->corvet = null;
        $this->corvetLaser = null;
        $this->corvetWar = null;
        $this->fregate = null;
        $this->fregatePlasma = null;
        $this->croiser = null;
        $this->ironClad = null;
        $this->destroyer = null;
        $this->soldier = 5;
        $this->worker = 1000;
        $this->scientist = null;
        $this->groundPlace = 0;
        $this->skyPlace = 0;
        $this->ground = 0;
        $this->sky = 0;
        $this->recycleAt = null;
        $this->autoSeller = 0;
        $this->uranium = null;
        $this->nuclearBomb = null;
        $this->nuclearAt = null;
        $this->nuclearAtNbr = null;
        $this->signature = 0;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setImageFile(?File $image = null): void
    {
        $this->imageFile = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setRestartAll(): void
    {
        $this->centerSearch = null;
        $this->name = 'Inhabitée';
        $this->lastActivity = null;
        $this->niobium = 200;
        $this->water = 140;
        $this->food = 1000;
        $this->shipProduction = 1;
        $this->workerProduction = 1;
        $this->niobiumMax = 13000;
        $this->waterMax = 10000;
        $this->foodMax = 15000;
        $this->soldierMax = 20;
        $this->tank = null;
        $this->scientistMax = 0;
        $this->workerMax = 25000;
        $this->nbProduction = 6;
        $this->wtProduction = 5;
        $this->fdProduction = 20;
        $this->miner = null;
        $this->niobiumStock = null;
        $this->extractor = null;
        $this->waterStock = null;
        $this->farm = null;
        $this->aeroponicFarm = null;
        $this->silos = null;
        $this->spaceShip = null;
        $this->metropole = null;
        $this->city = null;
        $this->caserne = null;
        $this->bunker = null;
        $this->island = null;
        $this->orbital = null;
        $this->radar = null;
        $this->nuclearBase = null;
        $this->skyRadar = null;
        $this->skyBrouilleur = null;
        $this->lightUsine = null;
        $this->heavyUsine = null;
        $this->scientist = null;
        $this->groundPlace = 0;
        $this->skyPlace = 0;
    }

    /**
     * @return int
     */
    public function getShipOn(): int
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

        $nbr = $corvetWar + $hunterWar + $motherShip + $brouilleurShip + $radarShip + $moonMaker + $fregate + $colonizer + $barge + $hunter + $recycleur + $sonde + $cargoI + $cargoV + $cargoX + $hunterHeavy + $corvet + $corvetLaser + $fregatePlasma + $croiser + $ironClad + $destroyer ;
        return $nbr;
    }

    /**
     * @return int
     */
    public function getNbrSignatures(): float
    {
        $sonde = $this->getSonde();
        $colonizer = $this->getColonizer() * 20;
        $recycleur = $this->getRecycleur() * 8;
        $cargoI = $this->getCargoI() * 5;
        $cargoV = $this->getCargoV() * 9;
        $cargoX = $this->getCargoX() * 14;
        $barge = $this->getBarge() * 12;
        $moonMaker = $this->getMoonMaker() * 10000;
        $radarShip = $this->getRadarShip() * 100;
        $brouilleurShip = $this->getBrouilleurShip() * 200;
        $motherShip = $this->getMotherShip() * 4000;
        $hunter = $this->getHunter();
        $hunterHeavy = $this->getHunterHeavy() * 2;
        $hunterWar = $this->getHunterWar() * 3;
        $corvet = $this->getCorvet() * 5;
        $corvetLaser = $this->getCorvetLaser() * 8;
        $corvetWar = $this->getCorvetWar() * 9;
        $fregate = $this->getFregate() * 12;
        $fregatePlasma = $this->getFregatePlasma() * 30;
        $croiser = $this->getCroiser() * 60;
        $ironClad = $this->getIronClad() * 140;
        $destroyer = $this->getDestroyer() * 300;
        $nuclear = $this->getNuclearBomb() * 50000;

        $nbr = $corvetWar + $hunterWar + $motherShip + $brouilleurShip + $radarShip + $radarShip + $moonMaker + $fregate + $colonizer + $barge + $hunter + $recycleur + $sonde + $cargoI + $cargoV + $cargoX + $hunterHeavy + $corvet + $corvetLaser + $fregatePlasma + $croiser + $ironClad + $destroyer + $nuclear;
        return $nbr;
    }

    /**
     * @return int
     */
    public function getNbrSignaturesRegroup(): float
    {
        $sonde = $this->getSonde();
        $colonizer = $this->getColonizer() * 20;
        $recycleur = $this->getRecycleur() * 8;
        $cargoI = $this->getCargoI() * 5;
        $cargoV = $this->getCargoV() * 9;
        $cargoX = $this->getCargoX() * 14;
        $barge = $this->getBarge() * 12;
        $moonMaker = $this->getMoonMaker() * 10000;
        $radarShip = $this->getRadarShip() * 100;
        $brouilleurShip = $this->getBrouilleurShip() * 200;
        $motherShip = $this->getMotherShip() * 4000;
        $hunter = $this->getHunter();
        $hunterHeavy = $this->getHunterHeavy() * 2;
        $hunterWar = $this->getHunterWar() * 3;
        $corvet = $this->getCorvet() * 5;
        $corvetLaser = $this->getCorvetLaser() * 8;
        $corvetWar = $this->getCorvetWar() * 9;
        $fregate = $this->getFregate() * 12;
        $fregatePlasma = $this->getFregatePlasma() * 30;
        $croiser = $this->getCroiser() * 60;
        $ironClad = $this->getIronClad() * 140;
        $destroyer = $this->getDestroyer() * 300;

        $nbr = $corvetWar + $hunterWar + $motherShip + $brouilleurShip + $radarShip + $radarShip + $moonMaker + $fregate + $colonizer + $barge + $hunter + $recycleur + $sonde + $cargoI + $cargoV + $cargoX + $hunterHeavy + $corvet + $corvetLaser + $fregatePlasma + $croiser + $ironClad + $destroyer;
        return $nbr;
    }

    /**
     * @return int
     */
    public function getBuildingPoint(): int
    {
        $extractor = $this->getExtractor() * 15;
        $miner = $this->getMiner() * 15;
        $farm = $this->getFarm() * 15;
        $aeroponicFarm = $this->getAeroponicFarm() * 30;
        $niobiumStock = $this->getNiobiumStock() * 45;
        $waterStock = $this->getWaterStock() * 45;
        $silos = $this->getSilos() * 45;
        $caserne = $this->getCaserne() * 25;
        $bunker = $this->getBunker() * 130;
        $center = $this->getCenterSearch() * 30;
        $city = $this->getCity() * 65;
        $metropole = $this->getMetropole() * 120;
        $light = $this->getLightUsine() * 50;
        $heavy = $this->getHeavyUsine() * 150;
        $space = $this->getSpaceShip() * 30;
        $radar = $this->getRadar() * 20;
        $skyr = $this->getSkyRadar() * 60;
        $brouilleur = $this->getSkyBrouilleur() * 100;
        $nuclear = $this->getNuclearBase() * 300;
        $orbital = $this->getOrbital() * 2000;
        $island = $this->getIsland() * 2000;

        $nbr = $farm + $aeroponicFarm + $silos + $extractor + $niobiumStock + $waterStock + $miner + $caserne + $bunker + $center + $city + $metropole + $light + $heavy + $space + $radar + $skyr + $brouilleur + $nuclear + $orbital + $island;
        return $nbr;
    }

    /**
     * @return int
     */
    public function getBuildingCost(): int
    {
        $extractor = $this->getExtractor() * 1;
        $miner = $this->getMiner() * 1;
        $farm = $this->getFarm() * 1;
        $aeroponicFarm = $this->getAeroponicFarm() * 2;
        $niobiumStock = $this->getNiobiumStock() * 30;
        $waterStock = $this->getWaterStock() * 30;
        $silos = $this->getSilos() * 30;
        $caserne = $this->getCaserne() * 66;
        $bunker = $this->getBunker() * 800;
        $center = $this->getCenterSearch() * 53;
        $city = $this->getCity() * 13;
        $metropole = $this->getMetropole() * 26;
        $light = $this->getLightUsine() * 333;
        $heavy = $this->getHeavyUsine() * 666;
        $space = $this->getSpaceShip() * 100;
        $radar = $this->getRadar() * 13;
        $skyr = $this->getSkyRadar() * 133;
        $brouilleur = $this->getSkyBrouilleur() * 400;
        $nuclear = $this->getNuclearBase() * 3333;
        $orbital = $this->getOrbital() * 333;
        $island = $this->getIsland() * 333;

        $nbr = $farm + $aeroponicFarm + $silos + $extractor + $niobiumStock + $waterStock + $miner + $caserne + $bunker + $center + $city + $metropole + $light + $heavy + $space + $radar + $skyr + $brouilleur + $nuclear + $orbital + $island;
        return $nbr;
    }

    /**
     * @return mixed
     */
    public function getFleetNoFriends($user)
    {
        $fullFleet = [];
        $x = 0;
        foreach($this->fleets as $fleet) {
            if($fleet->getUser() == $user || $fleet->getUser()->getAlly() == $user->getAlly()) {
            } else {
                $fullFleet[$x] = $fleet;
            }
        }
        return $fullFleet;
    }

    /**
     * @return int
     */
    public function getConstructionsLike($name): int
    {
        $nbr = 0;
        foreach($this->constructions as $construct) {
            if($construct->getConstruct() == $name) {
                $nbr++;
            }
        }

        return $nbr;
    }

    /**
     * @return int
     */
    public function getFleetWithRec(): int
    {
        $nbr = 0;
        foreach($this->fleets as $fleet) {
            if($fleet->getRecycleur() > 0) {
                $nbr++;
            }
        }

        return $nbr;
    }

    /**
     * @return int
     */
    public function getFleetsAbandon($user): int
    {
        $planete = 0;
        foreach($this->getFleets() as $fleet) {
            if($fleet->getFlightTime()) {
                $planete = 0;
            } else {
                if($fleet->getUser() != $user) {
                    if($fleet->getAttack() == 1) {
                        $planete = 1;
                    }
                    break;
                }
            }
        }
        return $planete;
    }

    /**
     * @return int
     */
    public function getPreviousPlanet(): int
    {
        $id = $this->getId();
        foreach($this->getUser()->getPlanets() as $planet) {
            if($planet->getId() == $this->getId()) {
                return $id;
            }
            if($planet->getEmpty() == false) {
                $id = $planet->getId();
            }
        }
        return $id;
    }
    /**
     * @return int
     */
    public function getNextPlanet(): int
    {
        $id = $this->getId();
        $next = 0;
        foreach($this->getUser()->getPlanets() as $planet) {
            $id = $planet->getId();
            if($next == 1) {
                return $id;
            }
            if($id == $this->getId()) {
                $next = 1;
            }
        }
        return $id;
    }

    /**
     * @return mixed
     */
    public function getPlanetAlliance()
    {
        if ($this->getUser()) {
            return $this->getUser()->getAlly();
        } else {
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function getOurAllyPact($user)
    {
        if ($this->getUser()) {
            if ($this->getUser()->getAlly() && $user->getAlly()) {
                if (count($this->getUser()->getAlly()->getAllieds()) > 0) {
                    foreach($this->getUser()->getAlly()->getAllieds() as $allied) {
                        if($allied->getAllyTag() == $user->getAlly()->getSigle() && $allied->getAccepted() == 1) {
                            return 'pact';
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getOurFleet($user)
    {

        foreach($this->getFleets() as $fleet) {
            if ($fleet->getUser() == $user && $fleet->getFlightTime() == null) {
                return 'hello';
            }
        }
        return null;
    }

    /**
     * @return int
     */
    public function getNbrFleets(): int
    {
        $nbr = 0;
        foreach($this->fleets as $fleet) {
            if ($fleet->getFlightTime() == null ) {
                $nbr++;
            }
        }
        return $nbr;
    }

    /**
     * Add destination
     *
     * @param \App\Entity\Destination $destination
     *
     * @return Planet
     */
    public function addDestination(\App\Entity\Destination $destination)
    {
        $this->destinations[] = $destination;

        return $this;
    }

    /**
     * Remove destination
     *
     * @param \App\Entity\Destination $destination
     */
    public function removeDestination(\App\Entity\Destination $destination)
    {
        $this->destinations->removeElement($destination);
    }

    /**
     * @return mixed
     */
    public function getDestinations()
    {
        return $this->destinations;
    }

    /**
     * @param mixed $destinations
     */
    public function setDestinations($destinations): void
    {
        $this->destinations = $destinations;
    }

    /**
     * Add fleet
     *
     * @param \App\Entity\Fleet $fleet
     *
     * @return Planet
     */
    public function addFleet(\App\Entity\Fleet $fleet)
    {
        $this->fleets[] = $fleet;

        return $this;
    }

    /**
     * Remove fleet
     *
     * @param \App\Entity\Fleet $fleet
     */
    public function removeFleet(\App\Entity\Fleet $fleet)
    {
        $this->fleets->removeElement($fleet);
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
     * @return \App\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \App\Entity\User $user
     * @return Planet
     */
    public function setUser(\App\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
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
     * @return mixed
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    /**
     * @param mixed $lastActivity
     */
    public function setLastActivity($lastActivity): void
    {
        $this->lastActivity = $lastActivity;
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
     * @return int
     */
    public function getFood(): int
    {
        return $this->food;
    }

    /**
     * @param int $food
     */
    public function setFood(int $food): void
    {
        $this->food = $food;
    }

    /**
     * @return int
     */
    public function getFoodMax(): int
    {
        return $this->foodMax;
    }

    /**
     * @param int $foodMax
     */
    public function setFoodMax(int $foodMax): void
    {
        $this->foodMax = $foodMax;
    }

    /**
     * @return mixed
     */
    public function getFleets()
    {
        return $this->fleets;
    }

    /**
     * @param mixed $fleets
     */
    public function setFleets($fleets): void
    {
        $this->fleets = $fleets;
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
    public function getSector()
    {
        return $this->sector;
    }

    /**
     * @param mixed $sector
     */
    public function setSector($sector): void
    {
        $this->sector = $sector;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getGround()
    {
        return $this->ground;
    }

    /**
     * @param mixed $ground
     */
    public function setGround($ground): void
    {
        $this->ground = $ground;
    }

    /**
     * @return mixed
     */
    public function getGroundPlace()
    {
        return $this->groundPlace;
    }

    /**
     * @param mixed $groundPlace
     */
    public function setGroundPlace($groundPlace): void
    {
        $this->groundPlace = $groundPlace;
    }

    /**
     * @return mixed
     */
    public function getSky()
    {
        return $this->sky;
    }

    /**
     * @param mixed $sky
     */
    public function setSky($sky): void
    {
        $this->sky = $sky;
    }

    /**
     * @return mixed
     */
    public function getSkyPlace()
    {
        return $this->skyPlace;
    }

    /**
     * @param mixed $skyPlace
     */
    public function setSkyPlace($skyPlace): void
    {
        $this->skyPlace = $skyPlace;
    }

    /**
     * @return mixed
     */
    public function getEmpty()
    {
        return $this->empty;
    }

    /**
     * @param mixed $empty
     */
    public function setEmpty($empty): void
    {
        $this->empty = $empty;
    }

    /**
     * @return mixed
     */
    public function getCdr()
    {
        return $this->cdr;
    }

    /**
     * @param mixed $cdr
     */
    public function setCdr($cdr): void
    {
        $this->cdr = $cdr;
    }

    /**
     * @return mixed
     */
    public function getMerchant()
    {
        return $this->merchant;
    }

    /**
     * @param mixed $merchant
     */
    public function setMerchant($merchant): void
    {
        $this->merchant = $merchant;
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
    public function getMiner()
    {
        return $this->miner;
    }

    /**
     * @param mixed $miner
     */
    public function setMiner($miner): void
    {
        $this->miner = $miner;
    }

    /**
     * @return mixed
     */
    public function getExtractor()
    {
        return $this->extractor;
    }

    /**
     * @param mixed $extractor
     */
    public function setExtractor($extractor): void
    {
        $this->extractor = $extractor;
    }

    /**
     * @return null
     */
    public function getFarm()
    {
        return $this->farm;
    }

    /**
     * @param null $farm
     */
    public function setFarm($farm): void
    {
        $this->farm = $farm;
    }

    /**
     * @return null
     */
    public function getAeroponicFarm()
    {
        return $this->aeroponicFarm;
    }

    /**
     * @param null $aeroponicFarm
     */
    public function setAeroponicFarm($aeroponicFarm): void
    {
        $this->aeroponicFarm = $aeroponicFarm;
    }

    /**
     * @return null
     */
    public function getSilos()
    {
        return $this->silos;
    }

    /**
     * @param null $silos
     */
    public function setSilos($silos): void
    {
        $this->silos = $silos;
    }

    /**
     * @return mixed
     */
    public function getSpaceShip()
    {
        return $this->spaceShip;
    }

    /**
     * @param mixed $spaceShip
     */
    public function setSpaceShip($spaceShip): void
    {
        $this->spaceShip = $spaceShip;
    }

    /**
     * @return mixed
     */
    public function getCenterSearch()
    {
        return $this->centerSearch;
    }

    /**
     * @param mixed $centerSearch
     */
    public function setCenterSearch($centerSearch): void
    {
        $this->centerSearch = $centerSearch;
    }

    /**
     * @return mixed
     */
    public function getMetropole()
    {
        return $this->metropole;
    }

    /**
     * @param mixed $metropole
     */
    public function setMetropole($metropole): void
    {
        $this->metropole = $metropole;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getCaserne()
    {
        return $this->caserne;
    }

    /**
     * @param mixed $caserne
     */
    public function setCaserne($caserne): void
    {
        $this->caserne = $caserne;
    }

    /**
     * @return mixed
     */
    public function getRadar()
    {
        return $this->radar;
    }

    /**
     * @param mixed $radar
     */
    public function setRadar($radar): void
    {
        $this->radar = $radar;
    }

    /**
     * @return mixed
     */
    public function getSkyRadar()
    {
        return $this->skyRadar;
    }

    /**
     * @param mixed $skyRadar
     */
    public function setSkyRadar($skyRadar): void
    {
        $this->skyRadar = $skyRadar;
    }

    /**
     * @return mixed
     */
    public function getSkyBrouilleur()
    {
        return $this->skyBrouilleur;
    }

    /**
     * @param mixed $skyBrouilleur
     */
    public function setSkyBrouilleur($skyBrouilleur): void
    {
        $this->skyBrouilleur = $skyBrouilleur;
    }

    /**
     * @return mixed
     */
    public function getLightUsine()
    {
        return $this->lightUsine;
    }

    /**
     * @param mixed $lightUsine
     */
    public function setLightUsine($lightUsine): void
    {
        $this->lightUsine = $lightUsine;
    }

    /**
     * @return mixed
     */
    public function getHeavyUsine()
    {
        return $this->heavyUsine;
    }

    /**
     * @param mixed $heavyUsine
     */
    public function setHeavyUsine($heavyUsine): void
    {
        $this->heavyUsine = $heavyUsine;
    }

    /**
     * @return mixed
     */
    public function getShipProduction()
    {
        return $this->shipProduction;
    }

    /**
     * @param mixed $shipProduction
     */
    public function setShipProduction($shipProduction): void
    {
        $this->shipProduction = $shipProduction;
    }

    /**
     * @return mixed
     */
    public function getWorkerProduction()
    {
        return $this->workerProduction;
    }

    /**
     * @param mixed $workerProduction
     */
    public function setWorkerProduction($workerProduction): void
    {
        $this->workerProduction = $workerProduction;
    }

    /**
     * @return mixed
     */
    public function getConstruct()
    {
        return $this->construct;
    }

    /**
     * @param mixed $construct
     */
    public function setConstruct($construct): void
    {
        $this->construct = $construct;
    }

    /**
     * @return mixed
     */
    public function getConstructAt()
    {
        return $this->constructAt;
    }

    /**
     * @param mixed $constructAt
     */
    public function setConstructAt($constructAt): void
    {
        $this->constructAt = $constructAt;
    }

    /**
     * @return mixed
     */
    public function getNbProduction()
    {
        return $this->nbProduction;
    }

    /**
     * @param mixed $nbProduction
     */
    public function setNbProduction($nbProduction): void
    {
        $this->nbProduction = $nbProduction;
    }

    /**
     * @return mixed
     */
    public function getWtProduction()
    {
        return $this->wtProduction;
    }

    /**
     * @param mixed $wtProduction
     */
    public function setWtProduction($wtProduction): void
    {
        $this->wtProduction = $wtProduction;
    }

    /**
     * @return int
     */
    public function getFdProduction(): int
    {
        return $this->fdProduction;
    }

    /**
     * @param int $fdProduction
     */
    public function setFdProduction(int $fdProduction): void
    {
        $this->fdProduction = $fdProduction;
    }

    /**
     * @return mixed
     */
    public function getNbCdr()
    {
        return $this->nbCdr;
    }

    /**
     * @param mixed $nbCdr
     */
    public function setNbCdr($nbCdr): void
    {
        $this->nbCdr = $nbCdr;
    }

    /**
     * @return mixed
     */
    public function getWtCdr()
    {
        return $this->wtCdr;
    }

    /**
     * @param mixed $wtCdr
     */
    public function setWtCdr($wtCdr): void
    {
        $this->wtCdr = $wtCdr;
    }

    /**
     * @return mixed
     */
    public function getSoldierMax()
    {
        return $this->soldierMax;
    }

    /**
     * @param mixed $soldierMax
     */
    public function setSoldierMax($soldierMax): void
    {
        $this->soldierMax = $soldierMax;
    }

    /**
     * @return
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
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
    public function getScientistMax()
    {
        return $this->scientistMax;
    }

    /**
     * @param mixed $scientistMax
     */
    public function setScientistMax($scientistMax): void
    {
        $this->scientistMax = $scientistMax;
    }

    /**
     * @return mixed
     */
    public function getWorkerMax()
    {
        return $this->workerMax;
    }

    /**
     * @param mixed $workerMax
     */
    public function setWorkerMax($workerMax): void
    {
        $this->workerMax = $workerMax;
    }

    /**
     * @return mixed
     */
    public function getScientistAt()
    {
        return $this->scientistAt;
    }

    /**
     * @param mixed $scientistAt
     */
    public function setScientistAt($scientistAt): void
    {
        $this->scientistAt = $scientistAt;
    }

    /**
     * @return mixed
     */
    public function getSoldierAt()
    {
        return $this->soldierAt;
    }

    /**
     * @param mixed $soldierAt
     */
    public function setSoldierAt($soldierAt): void
    {
        $this->soldierAt = $soldierAt;
    }

    /**
     * @return mixed
     */
    public function getScientistAtNbr()
    {
        return $this->scientistAtNbr;
    }

    /**
     * @param mixed $scientistAtNbr
     */
    public function setScientistAtNbr($scientistAtNbr): void
    {
        $this->scientistAtNbr = $scientistAtNbr;
    }

    /**
     * @return mixed
     */
    public function getSoldierAtNbr()
    {
        return $this->soldierAtNbr;
    }

    /**
     * @param mixed $soldierAtNbr
     */
    public function setSoldierAtNbr($soldierAtNbr): void
    {
        $this->soldierAtNbr = $soldierAtNbr;
    }

    /**
     * @return mixed
     */
    public function getNiobiumMax()
    {
        return $this->niobiumMax;
    }

    /**
     * @param mixed $niobiumMax
     */
    public function setNiobiumMax($niobiumMax): void
    {
        $this->niobiumMax = $niobiumMax;
    }

    /**
     * @return mixed
     */
    public function getWaterMax()
    {
        return $this->waterMax;
    }

    /**
     * @param mixed $waterMax
     */
    public function setWaterMax($waterMax): void
    {
        $this->waterMax = $waterMax;
    }

    /**
     * @return mixed
     */
    public function getNiobiumStock()
    {
        return $this->niobiumStock;
    }

    /**
     * @param mixed $niobiumStock
     */
    public function setNiobiumStock($niobiumStock): void
    {
        $this->niobiumStock = $niobiumStock;
    }

    /**
     * @return mixed
     */
    public function getWaterStock()
    {
        return $this->waterStock;
    }

    /**
     * @param mixed $waterStock
     */
    public function setWaterStock($waterStock): void
    {
        $this->waterStock = $waterStock;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product): void
    {
        $this->product = $product;
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
    public function getRadarAt()
    {
        return $this->radarAt;
    }

    /**
     * @param mixed $radarAt
     */
    public function setRadarAt($radarAt): void
    {
        $this->radarAt = $radarAt;
    }

    /**
     * @return mixed
     */
    public function getBrouilleurAt()
    {
        return $this->brouilleurAt;
    }

    /**
     * @param mixed $brouilleurAt
     */
    public function setBrouilleurAt($brouilleurAt): void
    {
        $this->brouilleurAt = $brouilleurAt;
    }

    /**
     * @return mixed
     */
    public function getMoon()
    {
        return $this->moon;
    }

    /**
     * @param mixed $moon
     */
    public function setMoon($moon): void
    {
        $this->moon = $moon;
    }

    /**
     * @return mixed
     */
    public function getBunker()
    {
        return $this->bunker;
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
    public function getNbColo()
    {
        return $this->nbColo;
    }

    /**
     * @param mixed $nbColo
     */
    public function setNbColo($nbColo): void
    {
        $this->nbColo = $nbColo;
    }

    /**
     * @return mixed
     */
    public function getConstructions()
    {
        return $this->constructions;
    }

    /**
     * @param mixed $constructions
     */
    public function setConstructions($constructions): void
    {
        $this->constructions = $constructions;
    }

    /**
     * @return mixed
     */
    public function getIsland()
    {
        return $this->island;
    }

    /**
     * @param mixed $island
     */
    public function setIsland($island): void
    {
        $this->island = $island;
    }

    /**
     * @return mixed
     */
    public function getOrbital()
    {
        return $this->orbital;
    }

    /**
     * @param mixed $orbital
     */
    public function setOrbital($orbital): void
    {
        $this->orbital = $orbital;
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
    public function getAutoSeller()
    {
        return $this->autoSeller;
    }

    /**
     * @param mixed $autoSeller
     */
    public function setAutoSeller($autoSeller): void
    {
        $this->autoSeller = $autoSeller;
    }

    /**
     * @return mixed
     */
    public function getTankAt()
    {
        return $this->tankAt;
    }

    /**
     * @param mixed $tankAt
     */
    public function setTankAt($tankAt): void
    {
        $this->tankAt = $tankAt;
    }

    /**
     * @return mixed
     */
    public function getTankAtNbr()
    {
        return $this->tankAtNbr;
    }

    /**
     * @param mixed $tankAtNbr
     */
    public function setTankAtNbr($tankAtNbr): void
    {
        $this->tankAtNbr = $tankAtNbr;
    }

    /**
     * @return mixed
     */
    public function getNuclearBase()
    {
        return $this->nuclearBase;
    }

    /**
     * @param mixed $nuclearBase
     */
    public function setNuclearBase($nuclearBase): void
    {
        $this->nuclearBase = $nuclearBase;
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
    public function getNuclearAt()
    {
        return $this->nuclearAt;
    }

    /**
     * @param mixed $nuclearAt
     */
    public function setNuclearAt($nuclearAt): void
    {
        $this->nuclearAt = $nuclearAt;
    }

    /**
     * @return mixed
     */
    public function getNuclearAtNbr()
    {
        return $this->nuclearAtNbr;
    }

    /**
     * @param mixed $nuclearAtNbr
     */
    public function setNuclearAtNbr($nuclearAtNbr): void
    {
        $this->nuclearAtNbr = $nuclearAtNbr;
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
     * @param mixed $bunker
     */
    public function setBunker($bunker): void
    {
        $this->bunker = $bunker;
    }
}