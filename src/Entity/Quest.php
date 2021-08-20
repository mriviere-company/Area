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
     * @ORM\ManyToMany(targetEntity="Character", mappedBy="quests", fetch="EXTRA_LAZY")
     */
    protected $characters;

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
        $this->characters = new ArrayCollection();
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
     * Add character
     *
     * @param Character $character
     *
     * @return Quest
     */
    public function addCharacter(Character $character)
    {
        $this->characters[] = $character;

        return $this;
    }

    /**
     * Remove character
     *
     * @param Character $character
     */
    public function removeCharacter(Character $character)
    {
        $this->characters->removeElement($character);
    }

    /**
     * @return mixed
     */
    public function getCharacters()
    {
        return $this->characters;
    }

    /**
     * @param mixed $characters
     */
    public function setcharacters($characters): void
    {
        $this->characters = $characters;
    }

    public function getId()
    {
        return $this->id;
    }
}
