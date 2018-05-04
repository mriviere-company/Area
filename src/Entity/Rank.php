<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="rank")
 * @ORM\Entity
 */
class Rank
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="User", mappedBy="rank", fetch="EXTRA_LAZY")
     */
    protected $user;

    /**
     * @ORM\Column(name="point",type="bigint")
     */
    protected $point = 100;

    /**
     * @ORM\Column(name="oldPoint",type="bigint")
     */
    protected $oldPoint = 0;

    /**
     * @ORM\Column(name="position",type="integer")
     */
    protected $position = 0;

    /**
     * @ORM\Column(name="oldPosition",type="integer")
     */
    protected $oldPosition = 0;

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @param mixed $point
     */
    public function setPoint($point): void
    {
        $this->point = $point;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getOldPoint()
    {
        return $this->oldPoint;
    }

    /**
     * @param mixed $oldPoint
     */
    public function setOldPoint($oldPoint): void
    {
        $this->oldPoint = $oldPoint;
    }

    /**
     * @return mixed
     */
    public function getOldPosition()
    {
        return $this->oldPosition;
    }

    /**
     * @param mixed $oldPosition
     */
    public function setOldPosition($oldPosition): void
    {
        $this->oldPosition = $oldPosition;
    }

    public function getId()
    {
        return $this->id;
    }
}
