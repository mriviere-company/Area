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
     * @ORM\OneToOne(targetEntity="Character", mappedBy="rank", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="character_id", referencedColumnName="id")
     */
    protected $character;

    /**
     * @ORM\Column(name="warPoint",type="bigint", options={"unsigned":true})
     */
    protected $warPoint;

    /**
     * @ORM\Column(name="point",type="bigint", options={"unsigned":true})
     */
    protected $point;

    /**
     * @ORM\Column(name="oldPoint",type="bigint", options={"unsigned":true})
     */
    protected $oldPoint;

    /**
     * @ORM\Column(name="position",type="smallint", options={"unsigned":true})
     */
    protected $position;

    /**
     * @ORM\Column(name="oldPosition",type="smallint", options={"unsigned":true})
     */
    protected $oldPosition;

    /**
     * Rank constructor.
     * @param Character $character
     */
    public function __construct(Character $character)
    {
        $this->character = $character;
        $this->warPoint = 0;
        $this->point = 100;
        $this->oldPoint = 0;
        $this->position = 0;
        $this->oldPosition = 0;
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

    /**
     * @return mixed
     */
    public function getWarPoint()
    {
        return $this->warPoint;
    }

    /**
     * @param mixed $warPoint
     */
    public function setWarPoint($warPoint): void
    {
        $this->warPoint = $warPoint;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
