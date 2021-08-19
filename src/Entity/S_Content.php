<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="s_content")
 * @ORM\Entity
 */
class S_Content
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Character", inversedBy="sContents", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="character_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $character;

    /**
     * @ORM\Column(name="message",type="string", length=200)
     * @Assert\NotBlank(message = "required")
     */
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity="Salon", inversedBy="contents", fetch="EXTRA_LAZY")
     */
    protected $salon;

    /**
     * @ORM\Column(name="sendAt",type="datetime")
     */
    protected $sendAt;

    /**
     * S_Content constructor.
     * @param $character
     * @param $message
     * @param $salon
     */
    public function __construct(Character $character, string $message, Salon $salon)
    {
        $this->character = $character;
        $this->message = $message;
        $this->salon = $salon;
        $this->sendAt = new DateTime();
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
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
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

    /**
     * @return mixed
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }

    /**
     * @param mixed $sendAt
     */
    public function setSendAt($sendAt): void
    {
        $this->sendAt = $sendAt;
    }

    public function getId()
    {
        return $this->id;
    }
}
