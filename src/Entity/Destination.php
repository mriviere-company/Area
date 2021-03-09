<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="destination")
 * @ORM\Entity
 */
class Destination
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Fleet", inversedBy="destination", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="fleet_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $fleet;

    /**
     * @ORM\ManyToOne(targetEntity="Planet", inversedBy="destinations", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="planet_id", referencedColumnName="id")
     */
    protected $planet;

    /**
     * Destination constructor.
     * @param Fleet $fleet
     * @param Planet $planet
     */
    public function __construct(Fleet $fleet, Planet $planet)
    {
        $this->fleet = $fleet;
        $this->planet = $planet;
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
    public function getId()
    {
        return $this->id;
    }
}
