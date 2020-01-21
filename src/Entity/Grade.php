<?php

namespace App\Entity;

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
     * @ORM\ManyToOne(targetEntity="Ally", inversedBy="grades", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="ally_id", referencedColumnName="id")
     */
    protected $ally;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="grade", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $users;

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

    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->canRecruit = false;
        $this->canKick = false;
        $this->canWar = false;
        $this->canPeace = false;
    }

    /**
     * @return mixed
     */
    public function getAlly()
    {
        return $this->ally;
    }

    /**
     * @param mixed $ally
     */
    public function setAlly($ally): void
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
     * Add user
     *
     * @param \App\Entity\User $user
     *
     * @return Grade
     */
    public function addUser(\App\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \App\Entity\User $user
     */
    public function removeUser(\App\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $users
     */
    public function setUsers($users): void
    {
        $this->users = $users;
    }

    public function getId()
    {
        return $this->id;
    }
}
