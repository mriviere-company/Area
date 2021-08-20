<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="sector")
 * @ORM\Entity
 */
class Sector
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Planet", mappedBy="sector", fetch="EXTRA_LAZY")
     */
    protected $planets;

    /**
     * @ORM\ManyToOne(targetEntity="Galaxy", inversedBy="sectors", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="galaxy_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $galaxy;

    /**
     * @ORM\Column(name="position",type="smallint", options={"unsigned":true})
     * @Assert\NotBlank(message = "required")
     */
    protected $position;

    /**
     * @ORM\Column(name="destroy",type="boolean")
     * @Assert\NotBlank(message = "required")
     */
    protected $destroy;

    public function __construct(Galaxy $galaxy, int $position)
    {
        $this->galaxy = $galaxy;
        $this->position = $position;
        $this->planets = new ArrayCollection();
        $this->destroy = false;
    }

    /**
     * Add planet
     *
     * @param Planet $planet
     *
     * @return Sector
     */
    public function addPlanet(Planet $planet)
    {
        $this->planets[] = $planet;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPlanets()
    {
        $criteria = Criteria::create()
            ->orderBy(array('position' => 'ASC'));

        return $this->planets->matching($criteria);
    }

    /**
     * Remove planet
     *
     * @param Planet $planet
     */
    public function removePlanet(Planet $planet)
    {
        $this->planets->removeElement($planet);
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
     * @param mixed $planet
     */
    public function setPlanet($planet): void
    {
        $this->planet = $planet;
    }

    /**
     * @return mixed
     */
    public function getGalaxy()
    {
        return $this->galaxy;
    }

    /**
     * @param mixed $galaxy
     */
    public function setGalaxy($galaxy): void
    {
        $this->galaxy = $galaxy;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDestroy()
    {
        return $this->destroy;
    }

    /**
     * @param mixed $destroy
     */
    public function setDestroy($destroy): void
    {
        $this->destroy = $destroy;
    }
}
