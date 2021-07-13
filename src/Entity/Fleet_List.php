<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="fleet_list")
 * @ORM\Entity
 */
class Fleet_List
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name",type="string", length=20)
     * @Assert\NotBlank(message = "required")
     * @Assert\Regex(
     *     pattern="/[a-zA-Z0-9]/")
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Character", inversedBy="fleetLists", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="character_id", referencedColumnName="id")
     */
    protected $character;

    /**
     * @ORM\OneToMany(targetEntity="Fleet", mappedBy="fleetList", fetch="EXTRA_LAZY")
     */
    protected $fleets;

    /**
     * @ORM\Column(name="priority",type="smallint", options={"unsigned":true})
     */
    protected $priority;

    /**
     * Fleet_List constructor.
     * @param Character $character
     * @param string|null $name
     * @param int $priority
     */
    public function __construct(Character $character, ?string $name, int $priority)
    {
        $this->character = $character;
        $this->name = $name ? $name : 'Cohorte';
        $this->priority = $priority;
        $this->fleets = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getFlightTime() : int
    {
        foreach ($this->fleets as $fleet) {
            if($fleet->getFlightTime()) {
                return 1;
            }
        }
        return 0;
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
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * @param mixed $character
     */
    public function setCharacter($character): void
    {
        $this->character = $character;
    }

    /**
     * @return mixed
     */
    public function getFleets()
    {
        return $this->fleets;
    }

    /**
     * @param mixed $fleets
     */
    public function setFleets($fleets): void
    {
        $this->fleets = $fleets;
    }

    /**
     * Add fleet
     *
     * @param \App\Entity\Fleet $fleet
     *
     * @return Fleet_list
     */
    public function addFleet(\App\Entity\Fleet $fleet)
    {
        $this->fleets[] = $fleet;

        return $this;
    }

    /**
     * Remove fleet
     *
     * @param \App\Entity\Fleet $fleet
     */
    public function removeFleet(\App\Entity\Fleet $fleet)
    {
        $this->fleets->removeElement($fleet);
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
