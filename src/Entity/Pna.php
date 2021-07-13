<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="ally_pna")
 * @ORM\Entity
 */
class Pna
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Ally", inversedBy="pnas", fetch="EXTRA_LAZY")
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
     * Pna constructor.
     * @param Ally $ally
     * @param string $tag
     * @param bool $accepted
     */
    public function __construct(Ally $ally, string $tag, bool $accepted)
    {
        $this->ally = $ally;
        $this->tag = $tag;
        $this->accepted = $accepted;
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
    public function getId()
    {
        return $this->id;
    }
}
