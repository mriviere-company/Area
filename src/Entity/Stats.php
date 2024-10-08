<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="stats")
 * @ORM\Entity
 */
class Stats
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Commander", inversedBy="stats", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="commander_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $commander;

    /**
     * @ORM\Column(name="bitcoin",type="bigint")
     */
    protected $bitcoin;

    /**
     * @ORM\Column(name="points",type="bigint", options={"unsigned":true})
     */
    protected $points;

    /**
     * @ORM\Column(name="pdg",type="bigint", options={"unsigned":true})
     */
    protected $pdg;

    /**
     * @ORM\Column(name="zombie",type="bigint")
     */
    protected $zombie;

    /**
     * @ORM\Column(name="date",type="datetime")
     */
    protected $date;

    /**
     * Stats constructor.
     * @param Commander $commander
     * @param int $bitcoin
     * @param int $points
     * @param int $pdg
     * @param int $zombie
     */
    public function __construct(Commander $commander, int $bitcoin, int $points, int $pdg, int $zombie)
    {
        $this->commander = $commander;
        $this->bitcoin = $bitcoin;
        $this->points = $points;
        $this->pdg = $pdg;
        $this->zombie = $zombie;
        $this->date = new DateTime();
    }

    public function getId()
    {
        return $this->id;
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
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param mixed $points
     */
    public function setPoints($points): void
    {
        $this->points = $points;
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
    public function getPdg()
    {
        return $this->pdg;
    }

    /**
     * @param mixed $pdg
     */
    public function setPdg($pdg): void
    {
        $this->pdg = $pdg;
    }

    /**
     * @return mixed
     */
    public function getZombie()
    {
        return $this->zombie;
    }

    /**
     * @param mixed $zombie
     */
    public function setZombie($zombie): void
    {
        $this->zombie = $zombie;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }
}
