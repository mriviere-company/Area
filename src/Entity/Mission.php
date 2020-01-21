<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="mission")
 * @ORM\Entity
 */
class Mission
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Planet", inversedBy="missions", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="planet_id", referencedColumnName="id")
     */
    protected $planet;

    /**
     * @ORM\Column(name="mission_at", type="datetime")
     */
    protected $missionAt;

    /**
     * @ORM\Column(name="soldier", type="integer", nullable=true, options={"unsigned":true})
     */
    protected $soldier;

    /**
     * @ORM\Column(name="tank", type="integer", nullable=true, options={"unsigned":true})
     */
    protected $tank;

    /**
     * @ORM\Column(name="gain", type="smallint", options={"unsigned":true})
     */
    protected $gain;

    /**
     * @ORM\Column(name="percent", type="smallint", options={"unsigned":true})
     */
    protected $percent;

    /**
     * @ORM\Column(name="type", type="boolean")
     * @Assert\NotBlank(message = "required")
     */
    protected $type;

    public function __construct()
    {
        $this->soldier = null;
        $this->tank = null;
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
    public function getMissionAt()
    {
        return $this->missionAt;
    }

    /**
     * @param mixed $missionAt
     */
    public function setMissionAt($missionAt): void
    {
        $this->missionAt = $missionAt;
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getGain()
    {
        return $this->gain;
    }

    /**
     * @param mixed $gain
     */
    public function setGain($gain): void
    {
        $this->gain = $gain;
    }

    /**
     * @return mixed
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @param mixed $percent
     */
    public function setPercent($percent): void
    {
        $this->percent = $percent;
    }

    public function getId()
    {
        return $this->id;
    }
}
