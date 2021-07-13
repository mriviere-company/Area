<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="ally_peace")
 * @ORM\Entity
 */
class Peace
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Ally", inversedBy="peaces", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="ally_id", referencedColumnName="id")
     */
    protected $ally;

    /**
     * @ORM\Column(name="allyTag",type="string", length=5)
     * @Assert\NotBlank(message = "required")
     */
    protected $allyTag;

    /**
     * @ORM\Column(name="signedAt",type="datetime")
     */
    protected $signedAt;

    /**
     * @ORM\Column(name="accepted",type="boolean")
     * @Assert\NotBlank(message = "required")
     */
    protected $accepted;

    /**
     * @ORM\Column(name="type",type="boolean")
     * @Assert\NotBlank(message = "required")
     */
    protected $type;

    /**
     * @ORM\Column(name="planet",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $planet;

    /**
     * @ORM\Column(name="taxe",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $taxe;

    /**
     * @ORM\Column(name="pdg",type="smallint", nullable=true, options={"unsigned":true})
     */
    protected $pdg;

    /**
     * Peace constructor.
     * @param Ally $ally
     * @param string $tag
     * @param bool $type
     * @param int $planet
     * @param int $taxe
     * @param int $pdg
     * @param bool $accepted
     */
    public function __construct(Ally $ally, string $tag, bool $type, int $planet, int $taxe, int $pdg, bool $accepted)
    {
        $this->ally = $ally;
        $this->tag = $tag;
        $this->type = $type;
        $this->accepted = $accepted;
        $this->planet = $planet;
        $this->taxe = $taxe;
        $this->pdg = $pdg;
        $this->signedAt = new DateTime();
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
    public function getAllyTag()
    {
        return $this->allyTag;
    }

    /**
     * @param mixed $allyTag
     */
    public function setAllyTag($allyTag): void
    {
        $this->allyTag = $allyTag;
    }

    /**
     * @return mixed
     */
    public function getSignedAt()
    {
        return $this->signedAt;
    }

    /**
     * @param mixed $signedAt
     */
    public function setSignedAt($signedAt): void
    {
        $this->signedAt = $signedAt;
    }

    /**
     * @return mixed
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * @param mixed $accepted
     */
    public function setAccepted($accepted): void
    {
        $this->accepted = $accepted;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

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
    public function getTaxe()
    {
        return $this->taxe;
    }

    /**
     * @param mixed $taxe
     */
    public function setTaxe($taxe): void
    {
        $this->taxe = $taxe;
    }

    /**
     * @return mixed
     */
    public function getPdg()
    {
        return $this->pdg;
    }

    /**
     * @param mixed $pdg
     */
    public function setPdg($pdg): void
    {
        $this->pdg = $pdg;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
