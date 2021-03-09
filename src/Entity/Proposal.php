<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="ally_proposal")
 * @ORM\Entity
 */
class Proposal
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Ally", inversedBy="proposals", fetch="EXTRA_LAZY")
     */
    protected $ally;

    /**
     * @ORM\ManyToOne(targetEntity="Character", inversedBy="proposals", fetch="EXTRA_LAZY")
     */
    protected $character;

    /**
     * @ORM\Column(name="proposalAt",type="datetime")
     */
    protected $proposalAt;

    /**
     * Proposal constructor.
     * @param Ally $ally
     * @param Character $character
     */
    public function __construct(Ally $ally, Character $character)
    {
        $this->ally = $ally;
        $this->character = $character;
        $this->proposalAt = new DateTime();
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
    public function getProposalAt()
    {
        return $this->proposalAt;
    }

    /**
     * @param mixed $proposalAt
     */
    public function setProposalAt($proposalAt): void
    {
        $this->proposalAt = $proposalAt;
    }

    public function getId()
    {
        return $this->id;
    }
}
