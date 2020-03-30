<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="server")
 * @ORM\Entity
 */
class Server
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="open",type="boolean")
     * @Assert\NotBlank(message = "required")
     */
    protected $open;

    /**
     * @ORM\Column(name="pvp",type="smallint")
     * @Assert\NotBlank(message = "required")
     */
    protected $pvp;

    /**
     * @ORM\Column(name="dailyReport",type="datetime", nullable=true)
     */
    protected $dailyReport;

    /**
     * @ORM\OneToMany(targetEntity="Galaxy", mappedBy="server", fetch="EXTRA_LAZY", cascade={"persist"})
     */
    protected $galaxys;

    /**
     * @ORM\Column(name="speed",type="decimal", precision=28, scale=3)
     * @Assert\NotBlank(message = "required")
     */
    protected $speed;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->open = false;
        $this->speed = 0.1;
        $this->pvp = false;
        $this->dailyReport = null;
        $this->galaxys = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * @return mixed
     */
    public function getGalaxys()
    {
        return $this->galaxys;
    }

    /**
     * @param mixed $galaxys
     */
    public function setGalaxys($galaxys): void
    {
        $this->galaxys = $galaxys;
    }

    /**
     * Add galaxy
     *
     * @param \App\Entity\Galaxy $galaxys
     *
     * @return Galaxy
     */
    public function addGalaxy(\App\Entity\Galaxy $galaxys)
    {
        $this->galaxys[] = $galaxys;

        return $this;
    }

    /**
     * Remove galaxy
     *
     * @param \App\Entity\Galaxy $galaxys
     */
    public function removeGalaxy(\App\Entity\Galaxy $galaxys)
    {
        $this->galaxys->removeElement($galaxys);
    }

    /**
     * @return mixed
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * @param mixed $open
     */
    public function setOpen($open): void
    {
        $this->open = $open;
    }

    /**
     * @return mixed
     */
    public function getPvp()
    {
        return $this->pvp;
    }

    /**
     * @param mixed $pvp
     */
    public function setPvp($pvp): void
    {
        $this->pvp = $pvp;
    }

    /**
     * @return mixed
     */
    public function getDailyReport()
    {
        return $this->dailyReport;
    }

    /**
     * @param mixed $dailyReport
     */
    public function setDailyReport($dailyReport): void
    {
        $this->dailyReport = $dailyReport;
    }

    /**
     * @return float
     */
    public function getSpeed(): float
    {
        return $this->speed;
    }

    /**
     * @param float $speed
     */
    public function setSpeed(float $speed): void
    {
        $this->speed = $speed;
    }

    public function getId()
    {
        return $this->id;
    }
}
