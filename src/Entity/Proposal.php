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
     * @ORM\JoinColumn(name="ally_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $ally;

    /**
     * @ORM\ManyToOne(targetEntity="Commander", inversedBy="proposals", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="commander_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $commander;

    /**
     * @ORM\Column(name="proposalAt",type="datetime")
     */
    protected $proposalAt;

    /**
     * Proposal constructor.
     * @param Ally $ally
     * @param Commander $commander
     */
    public function __construct(Ally $ally, Commander $commander)
    {
        $this->ally = $ally;
        $this->commander = $commander;
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
