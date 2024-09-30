<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="grade")
 * @ORM\Entity
 */
class Grade
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Alliance", inversedBy="grades", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="ally_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $ally;

    /**
     * @ORM\OneToMany(targetEntity="Commander", mappedBy="grade", fetch="EXTRA_LAZY", orphanRemoval=true)
     */
    protected $commanders;

    /**
     * @ORM\Column(name="placement",type="smallint", options={"unsigned":true})
     */
    protected $placement;

    /**
     * @ORM\Column(name="name",type="string", length=20)
     * @Assert\NotBlank(message = "required")
     */
    protected $name;

    /**
     * @ORM\Column(name="canRecruit",type="boolean")
     */
    protected $canRecruit;

    /**
     * @ORM\Column(name="canKick",type="boolean")
     */
    protected $canKick;

    /**
     * @ORM\Column(name="canWar",type="boolean")
     */
    protected $canWar;

    /**
     * @ORM\Column(name="canPeace",type="boolean")
     */
    protected $canPeace;

    /**
     * @ORM\Column(name="canEdit",type="boolean")
     */
    protected $canEdit;

    /**
     * @ORM\Column(name="seeMembers",type="boolean")
     */
    protected $seeMembers;

    /**
     * @ORM\Column(name="useFleets",type="boolean")
     */
    protected $useFleets;

    /**
     * Grade constructor.
     * @param Alliance $ally
     * @param string $name
     * @param int $placement
     * @param bool $recruit
     * @param bool $kick
     * @param bool $war
     * @param bool $peace
     * @param bool $edit
     * @param bool $see
     * @param bool $use
     */
    public function __construct(Alliance $ally, string $name, int $placement, bool $recruit, bool $kick, bool $war, bool $peace, bool $edit, bool $see, bool $use)
    {
        $this->ally = $ally;
        $this->name = $name;
        $this->placement = $placement;
        $this->canRecruit = $recruit;
        $this->canKick = $kick;
        $this->canWar = $war;
        $this->canPeace = $peace;
        $this->canEdit = $edit;
        $this->seeMembers = $see;
        $this->useFleets = $use;
        $this->commanders = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getAlliance()
    {
        return $this->ally;
    }

    /**
     * @param mixed $ally
     */
    public function setAlliance($ally): void
    {
        $this->ally = $ally;
    }

    /**
     * @return mixed
     */
    public function getPlacement()
    {
        return $this->placement;
    }

    /**
     * @param mixed $placement
     */
    public function setPlacement($placement): void
    {
        $this->placement = $placement;
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
    public function getCanRecruit()
    {
        return $this->canRecruit;
    }

    /**
     * @param mixed $canRecruit
     */
    public function setCanRecruit($canRecruit): void
    {
        $this->canRecruit = $canRecruit;
    }

    /**
     * @return mixed
     */
    public function getCanKick()
    {
        return $this->canKick;
    }

    /**
     * @param mixed $canKick
     */
    public function setCanKick($canKick): void
    {
        $this->canKick = $canKick;
    }

    /**
     * @return mixed
     */
    public function getCanWar()
    {
        return $this->canWar;
    }

    /**
     * @param mixed $canWar
     */
    public function setCanWar($canWar): void
    {
        $this->canWar = $canWar;
    }

    /**
     * @return mixed
     */
    public function getCanPeace()
    {
        return $this->canPeace;
    }

    /**
     * @param mixed $canPeace
     */
    public function setCanPeace($canPeace): void
    {
        $this->canPeace = $canPeace;
    }

    /**
     * Add commander
     *
     * @param Commander $commander
     *
     * @return Grade
     */
    public function addCommander(Commander $commander)
    {
        $this->commanders[] = $commander;

        return $this;
    }

    /**
     * Remove commander
     *
     * @param Commander $commander
     */
    public function removeCommander(Commander $commander)
    {
        $this->commanders->removeElement($commander);
    }

    /**
     * @return mixed
     */
    public function getCommanders()
    {
        return $this->commanders;
    }

    /**
     * @param mixed $commanders
     */
    public function setcommanders($commanders): void
    {
        $this->commanders = $commanders;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
