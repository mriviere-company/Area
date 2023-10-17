<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="ally_offer")
 * @ORM\Entity
 */
class Offer
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Alliance", inversedBy="offers", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="ally_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $ally;

    /**
     * @ORM\ManyToOne(targetEntity="Commander", inversedBy="offers", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="commander_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $commander;

    /**
     * @ORM\Column(name="offerAt",type="datetime")
     */
    protected $offerAt;

    /**
     * Offer constructor.
     * @param Alliance $ally
     * @param Commander $commander
     */
    public function __construct(Alliance $ally, Commander $commander)
    {
        $this->ally = $ally;
        $this->commander = $commander;
        $this->offerAt = new DateTime();
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
    public function getOfferAt()
    {
        return $this->offerAt;
    }

    /**
     * @param mixed $offerAt
     */
    public function setOfferAt($offerAt): void
    {
        $this->offerAt = $offerAt;
    }

    public function getId()
    {
        return $this->id;
    }
}
