<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\ManyToMany(targetEntity="Commander", mappedBy="quests", fetch="EXTRA_LAZY")
     */
    protected $commanders;

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
        $this->commanders = new ArrayCollection();
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
     * Add commander
     *
     * @param Commander $commander
     *
     * @return Quest
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

    public function getId()
    {
        return $this->id;
    }
}
