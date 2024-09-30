<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="ally_war")
 * @ORM\Entity
 */
class War
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Alliance", inversedBy="wars", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="ally_id", referencedColumnName="id", onDelete="SET NULL")
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
     * War constructor.
     * @param Alliance $ally
     * @param string $tag
     * @param bool $accepted
     */
    public function __construct(Alliance $ally, string $tag, bool $accepted)
    {
        $this->ally = $ally;
        $this->allyTag = $tag;
        $this->accepted = $accepted;
        $this->signedAt = new DateTime();
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
    public function getAllianceTag()
    {
        return $this->allyTag;
    }

    /**
     * @param mixed $allyTag
     */
    public function setAllianceTag($allyTag): void
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

    public function getId()
    {
        return $this->id;
    }
}
