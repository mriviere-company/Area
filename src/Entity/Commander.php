<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="commander")
 * @ORM\Entity
 */
class Commander
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="User", mappedBy="commander", fetch="EXTRA_LAZY")
     */
    protected $user;

    /**
     * @ORM\OneToOne(targetEntity="Fleet", mappedBy="commander", fetch="EXTRA_LAZY")
     */
    protected $fleet;

    /**
     * @ORM\OneToOne(targetEntity="Planet", mappedBy="commander", fetch="EXTRA_LAZY")
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
     * @ORM\Column(name="niobium",type="smallint", options={"unsigned":true})
     */
    protected $niobium;

    /**
     * @ORM\Column(name="water",type="smallint", options={"unsigned":true})
     */
    protected $water;

    /**
     * @ORM\Column(name="bitcoin",type="smallint", options={"unsigned":true})
     */
    protected $bitcoin;

    /**
     * @ORM\Column(name="worker",type="smallint", options={"unsigned":true})
     */
    protected $worker;

    /**
     * @ORM\Column(name="soldier",type="smallint", options={"unsigned":true})
     */
    protected $soldier;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->capture = false;
        $this->soldier = rand(0,5);
        $this->bitcoin = rand(0,5);
        $this->water = rand(0,5);
        $this->niobium = rand(0,5);
        $this->plasma = rand(0,5);
        $this->missile = rand(0,5);
        $this->laser = rand(0,5);
        $this->armor = rand(0,5);
        $this->shield = rand(0,5);
        $this->speed = rand(0,5);
        $this->name = 'Temporary';
        $this->cost = $this->getTotalSkills() * 10000;
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

}
