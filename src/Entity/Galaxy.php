<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="galaxy")
 * @ORM\Entity
 */
class Galaxy
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Sector", mappedBy="galaxy", fetch="EXTRA_LAZY")
     */
    protected $sectors;

    /**
     * @ORM\Column(name="position",type="smallint")
     * @Assert\NotBlank(message = "required")
     */
    protected $position;

    /**
     * @ORM\ManyToOne(targetEntity="Server", inversedBy="galaxys", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="server_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $server;

    /**
     * Galaxy constructor.
     * @param Server $server
     * @param int $position
     */
    public function __construct(Server $server, int $position)
    {
        $this->server = $server;
        $this->position = $position;
        $this->sectors = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getSectors()
    {
        return $this->sectors;
    }

    /**
     * @param mixed $sectors
     */
    public function setSectors($sectors): void
    {
        $this->sectors = $sectors;
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
     * Add sector
     *
     * @param \App\Entity\Sector $sector
     *
     * @return Galaxy
     */
    public function addSector(\App\Entity\Sector $sector)
    {
        $this->sectors[] = $sector;

        return $this;
    }

    /**
     * Remove sector
     *
     * @param \App\Entity\Sector $sector
     */
    public function removeSector(\App\Entity\Sector $sector)
    {
        $this->sectors->removeElement($sector);
    }

    /**
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param mixed $server
     */
    public function setServer($server): void
    {
        $this->server = $server;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
