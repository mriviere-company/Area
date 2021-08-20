<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="exchange")
 * @ORM\Entity
 */
class Exchange
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Ally", inversedBy="exchanges", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="ally_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $ally;

    /**
     * @ORM\Column(name="name",type="string")
     */
    protected $name;

    /**
     * @ORM\Column(name="amount",type="bigint")
     */
    protected $amount;

    /**
     * @ORM\Column(name="content",type="string", length=200, nullable=true)
     */
    protected $content;

    /**
     * @ORM\Column(name="type",type="boolean")
     * @Assert\NotBlank(message = "required")
     */
    protected $type;

    /**
     * @ORM\Column(name="accepted",type="boolean")
     */
    protected $accepted;

    /**
     * Exchange constructor.
     * @param Ally $ally
     * @param string $username
     * @param bool $type
     * @param bool $accepted
     * @param int $amount
     * @param string|null $content
     */
    public function __construct(Ally $ally, string $username, bool $type, bool $accepted, int $amount, ?string $content)
    {
        $this->ally = $ally;
        $this->name = $username;
        $this->createdAt = new DateTime();
        $this->type = $type;
        $this->accepted = $accepted;
        $this->amount = $amount;
        $this->content = $content ? $content : "-";
    }

    /**
     * @ORM\Column(name="createdAt",type="datetime")
     */
    protected $createdAt;

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
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
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
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
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
}
