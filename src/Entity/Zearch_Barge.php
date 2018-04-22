<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="z_barge")
 * @ORM\Entity
 */
class Zearch_Barge
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Research", mappedBy="barge", fetch="EXTRA_LAZY")
     */
    protected $research;

    /**
     * @ORM\Column(name="bitcoin",type="integer")
     */
    protected $bitcoin = 6500;

    /**
     * @ORM\Column(name="level",type="boolean")
     */
    protected $level = false;

    /**
     * @ORM\Column(name="finishAt",type="datetime", nullable=true)
     */
    protected $finishAt;

    /**
     * @ORM\Column(name="constructTime",type="integer")
     */
    protected $constructTime = 9000;

    /**
     * @return mixed
     */
    public function getResearch()
    {
        return $this->research;
    }

    /**
     * @param mixed $research
     */
    public function setResearch($research): void
    {
        $this->research = $research;
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
    public function getFinishAt()
    {
        return $this->finishAt;
    }

    /**
     * @param mixed $finishAt
     */
    public function setFinishAt($finishAt): void
    {
        $this->finishAt = $finishAt;
    }

    /**
     * @return mixed
     */
    public function getConstructTime()
    {
        return $this->constructTime;
    }

    /**
     * @param mixed $constructTime
     */
    public function setConstructTime($constructTime): void
    {
        $this->constructTime = $constructTime;
    }

    public function getId()
    {
        return $this->id;
    }
}
