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

    public function getId()
    {
        return $this->id;
    }
}
