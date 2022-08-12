<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="heroe")
 * @ORM\Entity
 */
class Heroe
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Commander", mappedBy="heroe", fetch="EXTRA_LAZY")
     */
    protected $commander;

    /**
     * @ORM\OneToOne(targetEntity="Fleet", mappedBy="heroe", fetch="EXTRA_LAZY")
     */
    protected $fleet;

    /**
     * @ORM\OneToOne(targetEntity="Planet", mappedBy="heroe", fetch="EXTRA_LAZY")
     */
    protected $planet;

    /**
     * @ORM\Column(name="capture",type="boolean")
     * @Assert\NotBlank(message = "required")
     */
    protected $capture;

    /**
     * @ORM\Column(name="name",type="string", length=25)
     * @Assert\NotBlank(message = "required")
     */
    protected $name;

    /**
     * @ORM\Column(name="cost",type="integer", options={"unsigned":true})
     */
    protected $cost;

    /**
     * @ORM\Column(name="level",type="smallint", options={"unsigned":true})
     */
    protected $level;

    /**
     * @ORM\Column(name="speed",type="smallint", options={"unsigned":true})
     */
    protected $speed;

    /**
     * @ORM\Column(name="shield",type="smallint", options={"unsigned":true})
     */
    protected $shield;

    /**
     * @ORM\Column(name="armor",type="smallint", options={"unsigned":true})
     */
    protected $armor;

    /**
     * @ORM\Column(name="laser",type="smallint", options={"unsigned":true})
     */
    protected $laser;

    /**
     * @ORM\Column(name="missile",type="smallint", options={"unsigned":true})
     */
    protected $missile;

    /**
     * @ORM\Column(name="plasma",type="smallint", options={"unsigned":true})
     */
    protected $plasma;

    /**
     * @ORM\Column(name="precision",type="smallint", options={"unsigned":true})
     */
    protected $precision;

    /**
     * @ORM\Column(name="niobium",type="smallint", options={"unsigned":true})
     */
    protected $niobium;

    /**
     * @ORM\Column(name="water",type="smallint", options={"unsigned":true})
     */
    protected $water;

    /**
     * @ORM\Column(name="food",type="smallint", options={"unsigned":true})
     */
    protected $food;

    /**
     * @ORM\Column(name="uranium",type="smallint", options={"unsigned":true})
     */
    protected $uranium;

    /**
     * @ORM\Column(name="bitcoin",type="smallint", options={"unsigned":true})
     */
    protected $bitcoin;

    /**
     * @ORM\Column(name="warPoint",type="smallint", options={"unsigned":true})
     */
    protected $warPoint;

    /**
     * @ORM\Column(name="worker",type="smallint", options={"unsigned":true})
     */
    protected $worker;

    /**
     * @ORM\Column(name="soldier",type="smallint", options={"unsigned":true})
     */
    protected $soldier;

    /**
     * @ORM\Column(name="tank",type="smallint", options={"unsigned":true})
     */
    protected $tank;

    /**
     * @ORM\Column(name="scientist",type="smallint", options={"unsigned":true})
     */
    protected $scientist;

    public function __construct()
    {
        $this->capture = false;
        $this->soldier = $this->getSoldierPoints();
        $this->tank = $this->getTankPoints();
        $this->scientist = $this->getSoldierPoints();
        $this->warPoint = $this->getWarPointPoints();
        $this->uranium = $this->getUraniumPoints();
        $this->precision = $this->getPrecisionPoints();
        $this->bitcoin = $this->getBitcoinPoints();
        $this->water = $this->getWaterPoints();
        $this->niobium = $this->getNiobiumPoints();
        $this->food = $this->getFoodPoints();
        $this->plasma = $this->getPlasmaPoints();
        $this->missile = $this->getMissilePoints();
        $this->laser = $this->getLaserPoints();
        $this->armor = $this->getArmorPoints();
        $this->shield = $this->getShieldPoints();
        $this->speed = $this->getSpeedPoints();
        $this->name = 'Give me a name';
        $this->cost = $this->getTotalSkills() * 500;
        $this->level = $this->getTotalSkills();
    }

    /**
     * @return int
     */
    public function getTotalSkills()
    {
        $return = $this->soldier + $this->bitcoin + $this->water + $this->niobium + $this->plasma + $this->missile + $this->laser + $this->armor + $this->shield + $this->speed;
        return $return;
    }

    /**
     * @return int
     */
    public function getSoldierPoints()
    {
        $points = $this->commander->getMilitaryField(); // militaries building/soldiers/tanks
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getBitcoinPoints()
    {
        $points = $this->commander->getEconomicField(); // citizens building/bitcoins
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getWaterPoints()
    {
        $points = $this->commander->getWaterField(); // waters building/ressources
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getNiobiumPoints()
    {
        $points = $this->commander->getNiobiumField(); // niobiums building/ressources
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getFoodPoints()
    {
        $points = $this->commander->getFoodField(); // foods building/ressources
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getPlasmaPoints()
    {
        $points = $this->commander->getPlasmaField(); // plasmas search/ships/points
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getMissilePoints()
    {
        $points = $this->commander->getMissileField(); // missiles search/ships/points
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getLaserPoints()
    {
        $points = $this->commander->getLaserField(); // lasers search/ships/points
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getArmorPoints()
    {
        $points = $this->commander->getArmorField(); // armors search/ships/points
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getShieldPoints()
    {
        $points = $this->commander->getShieldField(); // shields search/ships/points
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getSpeedPoints()
    {
        $points = $this->commander->getSpeedField(); // speed rapidité
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getTankPoints()
    {
        $points = $this->commander->getTankField(); // sgains tank
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getUraniumPoints()
    {
        $points = $this->commander->getUraniumField(); // gain uranium
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getScientistPoints()
    {
        $points = $this->commander->getScientistField(); // scientifique gains
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getWarPointPoints()
    {
        $points = $this->commander->getWarPointField(); // gain points de guerre
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return int
     */
    public function getPrecisionPoints()
    {
        $points = $this->commander->getPrecisionField();// precision ciblage
        if ($points > 500000 or rand(1, 75) == 1)
            return rand(75, 100);
        if ($points > 250000 or rand(1, 75) == 1)
            return rand(50, 75);
        if ($points > 100000 or rand(1, 75) == 1)
            return rand(25, 50);

        return rand(1, 25);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
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
    public function getFleet()
    {
        return $this->fleet;
    }

    /**
     * @param mixed $fleet
     */
    public function setFleet($fleet): void
    {
        $this->fleet = $fleet;
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
    public function getCapture()
    {
        return $this->capture;
    }

    /**
     * @param mixed $capture
     */
    public function setCapture($capture): void
    {
        $this->capture = $capture;
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
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level): void
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getSpeed()
    {
        return $this->speed;
    }

    /**
     * @param mixed $speed
     */
    public function setSpeed($speed): void
    {
        $this->speed = $speed;
    }

    /**
     * @return mixed
     */
    public function getShield()
    {
        return $this->shield;
    }

    /**
     * @param mixed $shield
     */
    public function setShield($shield): void
    {
        $this->shield = $shield;
    }

    /**
     * @return mixed
     */
    public function getArmor()
    {
        return $this->armor;
    }

    /**
     * @param mixed $armor
     */
    public function setArmor($armor): void
    {
        $this->armor = $armor;
    }

    /**
     * @return mixed
     */
    public function getLaser()
    {
        return $this->laser;
    }

    /**
     * @param mixed $laser
     */
    public function setLaser($laser): void
    {
        $this->laser = $laser;
    }

    /**
     * @return mixed
     */
    public function getMissile()
    {
        return $this->missile;
    }

    /**
     * @param mixed $missile
     */
    public function setMissile($missile): void
    {
        $this->missile = $missile;
    }

    /**
     * @return mixed
     */
    public function getPlasma()
    {
        return $this->plasma;
    }

    /**
     * @param mixed $plasma
     */
    public function setPlasma($plasma): void
    {
        $this->plasma = $plasma;
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
     * @return mixed
     */
    public function getFood()
    {
        return $this->food;
    }

    /**
     * @param mixed $food
     */
    public function setFood($food): void
    {
        $this->food = $food;
    }

    /**
     * @return mixed
     */
    public function getBitcoin()
    {
        return $this->bitcoin;
    }

    /**
     * @param mixed $bitcoin
     */
    public function setBitcoin($bitcoin): void
    {
        $this->bitcoin = $bitcoin;
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
     * @return float|int
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param float|int $cost
     */
    public function setCost($cost): void
    {
        $this->cost = $cost;
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @param int $precision
     */
    public function setPrecision(int $precision): void
    {
        $this->precision = $precision;
    }

    /**
     * @return int
     */
    public function getUranium(): int
    {
        return $this->uranium;
    }

    /**
     * @param int $uranium
     */
    public function setUranium(int $uranium): void
    {
        $this->uranium = $uranium;
    }

    /**
     * @return int
     */
    public function getWarPoint(): int
    {
        return $this->warPoint;
    }

    /**
     * @param int $warPoint
     */
    public function setWarPoint(int $warPoint): void
    {
        $this->warPoint = $warPoint;
    }

    /**
     * @return int
     */
    public function getTank(): int
    {
        return $this->tank;
    }

    /**
     * @param int $tank
     */
    public function setTank(int $tank): void
    {
        $this->tank = $tank;
    }

    /**
     * @return int
     */
    public function getScientist(): int
    {
        return $this->scientist;
    }

    /**
     * @param int $scientist
     */
    public function setScientist(int $scientist): void
    {
        $this->scientist = $scientist;
    }

}
