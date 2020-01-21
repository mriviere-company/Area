<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="quest")
 * @ORM\Entity
 */
class Quest
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="quests", fetch="EXTRA_LAZY")
     */
    protected $users;

    /**
     * @ORM\Column(name="gain",type="integer", options={"unsigned":true})
     */
    protected $gain;

    /**
     * @ORM\Column(name="name",type="string", length=40)
     * @Assert\NotBlank(message = "required")
     */
    protected $name;

    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->gain = 500;
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
     * Add user
     *
     * @param \App\Entity\User $user
     *
     * @return User
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
