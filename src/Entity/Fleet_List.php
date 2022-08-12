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
     * @ORM\ManyToOne(targetEntity="Commander", inversedBy="fleetLists", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="commander_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $commander;

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
     * @param Commander $commander
     * @param string|null $name
     * @param int $priority
     */
    public function __construct(Commander $commander, ?string $name, int $priority)
    {
        $this->commander = $commander;
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
     * @param Fleet $fleet
     *
     * @return Fleet_list
     */
    public function addFleet(Fleet $fleet)
    {
        $this->fleets[] = $fleet;

        return $this;
    }

    /**
     * Remove fleet
     *
     * @param Fleet $fleet
     */
    public function removeFleet(Fleet $fleet)
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
