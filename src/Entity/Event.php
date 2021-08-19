<?php

namespace App\Entity;

use DateInterval;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="event")
 * @ORM\Entity
 */
class Event
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=20)
     * @Assert\NotBlank(message = "required")
     * @Assert\Regex(
     *     pattern="/[a-zA-Z0-9]/")
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Server", inversedBy="events", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="server_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $server;

    /**
     * @ORM\Column(name="startAt",type="datetime")
     */
    protected $startAt;

    /**
     * @ORM\Column(name="endAt",type="datetime")
     */
    protected $endAt;

    /**
     * Event constructor.
     * @param string $name
     * @param Server $server
     * @param int $startAt
     * @param int $startHour
     * @param int $startMin
     * @param int $endAt
     * @param int $endHour
     * @param int $endMin
     * @throws Exception
     */
    public function __construct(string $name, Server $server, int $startAt, int $startHour, int $startMin, int $endAt, int $endHour, int $endMin)
    {
        $now = new DateTime();
        $nowBis = new DateTime();

        $this->name = $name;
        $this->server = $server;
        $this->startAt = $now->add(new DateInterval('P' . $startAt . 'D'))->setTime($startHour, $startMin, 00);
        $this->endAt = $nowBis->add(new DateInterval('P' . $endAt . 'D'))->setTime($endHour, $endMin, 00);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param mixed $server
     */
    public function setServer($server): void
    {
        $this->server = $server;
    }

    /**
     * @return mixed
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * @param mixed $startAt
     */
    public function setStartAt($startAt): void
    {
        $this->startAt = $startAt;
    }

    /**
     * @return mixed
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * @param mixed $endAt
     */
    public function setEndAt($endAt): void
    {
        $this->endAt = $endAt;
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
}
