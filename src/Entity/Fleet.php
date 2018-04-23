<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="fleet")
 * @ORM\Entity
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
     * @ORM\Column(name="name",type="string", length=15)
     * @Assert\NotBlank(message = "required")
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="fleets", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Planet", inversedBy="fleets", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="planet_id", referencedColumnName="id")
     */
    protected $planet;

    /**
     * @ORM\OneToOne(targetEntity="Ship", mappedBy="fleet", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="ship_id", referencedColumnName="id")
     */
    protected $ship;

    /**
     * @ORM\OneToOne(targetEntity="Soldier", inversedBy="fleet", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="soldier_id", referencedColumnName="id")
     */
    protected $soldier;

    /**
     * @ORM\OneToOne(targetEntity="Worker", inversedBy="fleet", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="worker_id", referencedColumnName="id")
     */
    protected $worker;

    /**
     * @ORM\OneToOne(targetEntity="Scientist", inversedBy="fleet", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="scientist_id", referencedColumnName="id")
     */
    protected $scientist;

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
    public function getSoldier()
    {
        return $this->soldier;
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
    public function getScientist()
    {
        return $this->scientist;
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
    public function getShip()
    {
        return $this->ship;
    }

    /**
     * @param mixed $ship
     */
    public function setShip($ship): void
    {
        $this->ship = $ship;
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
    public function getNbrShips()
    {
        $fregate = $this->getShip()->getFregate()->getAmount();
        $colonizer = $this->getShip()->getColonizer()->getAmount();
        $barge = $this->getShip()->getBarge()->getAmount();
        $hunter = $this->getShip()->getHunter()->getAmount();
        $recycleur = $this->getShip()->getRecycleur()->getAmount();
        $sonde = $this->getShip()->getSonde()->getAmount();

        $nbr = $fregate + $colonizer + $barge + $hunter + $recycleur + $sonde;
        return $nbr;
    }

    /**
     * @return mixed
     */
    public function getNbrSignatures()
    {
        $fregate = $this->getShip()->getFregate()->getAmount() * $this->getShip()->getFregate()->getSignature();
        $colonizer = $this->getShip()->getColonizer()->getAmount() * $this->getShip()->getColonizer()->getSignature();
        $barge = $this->getShip()->getBarge()->getAmount() * $this->getShip()->getBarge()->getSignature();
        $hunter = $this->getShip()->getHunter()->getAmount() * $this->getShip()->getHunter()->getSignature();
        $recycleur = $this->getShip()->getRecycleur()->getAmount() * $this->getShip()->getRecycleur()->getSignature();
        $sonde = $this->getShip()->getSonde()->getAmount() * $this->getShip()->getSonde()->getSignature();

        $nbr = $fregate + $colonizer + $barge + $hunter + $recycleur + $sonde;
        return $nbr;
    }

    public function getId()
    {
        return $this->id;
    }
}
