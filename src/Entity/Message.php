<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="message")
 * @ORM\Entity
 */
class Message
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Commander", inversedBy="messages", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="commander_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $commander;

    /**
     * @ORM\Column(name="sender",type="string", nullable=true)
     */
    protected $sender;

    /**
     * @ORM\Column(name="idSender",type="integer")
     * @Assert\NotBlank(message = "required")
     */
    protected $idSender;

    /**
     * @ORM\Column(name="title",type="string", length=20)
     */
    protected $title;

    /**
     * @ORM\Column(name="share_key",type="string", length=20, nullable=true)
     */
    protected $shareKey;

    /**
     * @ORM\Column(name="newMessage",type="boolean")
     */
    protected $newMessage;

    /**
     * @ORM\Column(name="content",type="string", length=1000)
     * @Assert\NotBlank(message = "required")
     */
    protected $content;

    /**
     * @ORM\Column(name="bitcoin",type="bigint", options={"unsigned":true})
     */
    protected $bitcoin;

    /**
     * @ORM\Column(name="sendAt",type="datetime")
     */
    protected $sendAt;

    /**
     * Message constructor.
     * @param Commander $commander
     * @param string|null $title
     * @param string $content
     * @param int $bitcoin
     * @param int $id
     * @param string|null $username
     */
    public function __construct(Commander $commander, ?string $title, string $content, int $bitcoin, int $id, ?string $username)
    {
        $this->commander = $commander;
        $this->title = $title ? $title : 'Bonjour';
        $this->content = $content;
        $this->bitcoin = $bitcoin;
        $this->idSender = $id;
        $this->sender = $username ? $username : null;
        $this->newMessage = 1;
        $this->shareKey = null;
        $this->sendAt = new DateTime();
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
    public function getShareKey()
    {
        return $this->shareKey;
    }

    /**
     * @param mixed $shareKey
     */
    public function setShareKey($shareKey): void
    {
        $this->shareKey = $shareKey;
    }

    /**
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param mixed $sender
     */
    public function setSender($sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
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
    public function getBitcoin()
    {
        return $this->bitcoin;
    }

    /**
     * @param mixed $bitcoin
     */
    public function setBitcoin($bitcoin): void
    {
        $this->bitcoin = $bitcoin;
    }

    /**
     * @return mixed
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }

    /**
     * @return mixed
     */
    public function getIdSender()
    {
        return $this->idSender;
    }

    /**
     * @param mixed $idSender
     */
    public function setIdSender($idSender): void
    {
        $this->idSender = $idSender;
    }

    /**
     * @param mixed $sendAt
     */
    public function setSendAt($sendAt): void
    {
        $this->sendAt = $sendAt;
    }

    /**
     * @return mixed
     */
    public function getNewMessage()
    {
        return $this->newMessage;
    }

    /**
     * @param mixed $newMessage
     */
    public function setNewMessage($newMessage): void
    {
        $this->newMessage = $newMessage;
    }
}
