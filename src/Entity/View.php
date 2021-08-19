<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="view")
 * @ORM\Entity
 */
class View
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Character", inversedBy="views", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="character_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $character;

    /**
     * @ORM\ManyToOne(targetEntity="Salon", inversedBy="views", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="salon_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $salon;

    /**
     * View constructor.
     * @param Character $character
     * @param Salon $salon
     */
    public function __construct(Character $character, Salon $salon)
    {
        $this->character = $character;
        $this->salon = $salon;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getSalon()
    {
        return $this->salon;
    }

    /**
     * @param mixed $salon
     */
    public function setSalon($salon): void
    {
        $this->salon = $salon;
    }
}
