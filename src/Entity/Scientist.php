<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="scientist")
 * @ORM\Entity
 */
class Scientist
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Planet", mappedBy="scientist", fetch="EXTRA_LAZY", cascade={"persist"})
     */
    protected $planet;

    /**
     * @ORM\OneToOne(targetEntity="Fleet", mappedBy="scientist", fetch="EXTRA_LAZY", cascade={"persist"})
     */
    protected $fleet;

    /**
     * @ORM\Column(name="amount",type="bigint")
     */
    protected $amount;

    /**
     * @ORM\Column(name="life",type="integer")
     * @Assert\NotBlank(message = "required")
     */
    protected $life = 1;

    /**
     * @return mixed
     */
    public function getPlanet()
    {
        return $this->planet;
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
    public function getFleet()
    {
        return $this->fleet;
    }

    /**
     * @param mixed $fleet
     */
    public function setFleet($fleet): void
    {
        $this->fleet = $fleet;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getLife()
    {
        return $this->life;
    }

    /**
     * @param mixed $life
     */
    public function setLife($life): void
    {
        $this->life = $life;
    }

    public function getId()
    {
        return $this->id;
    }
}
