<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="mission")
 * @ORM\Entity
 */
class Mission
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Character", inversedBy="missions", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="character_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $character;

    /**
     * @ORM\Column(name="mission_at", type="datetime")
     */
    protected $missionAt;
    /**
     * @ORM\Column(name="type", type="smallint", options={"unsigned":true})
     */
    protected $type;

    /**
     * Mission constructor.
     * @param Character $character
     * @param int $type
     */
    public function __construct(Character $character, int $type)
    {
        $this->character = $character;
        $this->type = $type;
        $this->missionAt = new DateTime();
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
    public function getMissionAt()
    {
        return $this->missionAt;
    }

    /**
     * @param mixed $missionAt
     */
    public function setMissionAt($missionAt): void
    {
        $this->missionAt = $missionAt;
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
    public function getGain()
    {
        if ($this->type < 3)
            return $this->type;
        if ($this->type < 5)
            return $this->type * 2;
        if ($this->type < 8)
            return $this->type * 3;
        if ($this->type < 10)
            return $this->type * 4;
        if ($this->type < 12)
            return $this->type * 5;
        if ($this->type < 15)
            return $this->type * 7;
        if ($this->type < 15)
            return $this->type * 8;
        if ($this->type < 17)
            return $this->type * 9;
        if ($this->type < 18)
            return $this->type * 10;
        if ($this->type < 20)
            return $this->type * 11;
        if ($this->type < 22)
            return $this->type * 12;
        if ($this->type < 24)
            return $this->type * 13;
        if ($this->type < 25)
            return $this->type * 14;
        if ($this->type < 27)
            return $this->type * 15;
        if ($this->type < 28)
            return $this->type * 16;
        if ($this->type < 29)
            return $this->type * 18;
        if ($this->type < 30)
            return $this->type * 20;
        if ($this->type < 31)
            return $this->type * 21;
        if ($this->type < 32)
            return $this->type * 22;
        if ($this->type < 34)
            return $this->type * 24;
        if ($this->type < 35)
            return $this->type * 25;
        if ($this->type < 36)
            return $this->type * 26;
        if ($this->type < 37)
            return $this->type * 28;
        if ($this->type < 38)
            return $this->type * 30;
        if ($this->type < 39)
            return $this->type * 35;
        if ($this->type <= 40) ;
            return $this->type * 40;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        if ($this->type < 3)
            return $this->getGain() * 60;
        if ($this->type < 5)
            return $this->getGain() * 70;
        if ($this->type < 8)
            return $this->getGain() * 80;
        if ($this->type < 10)
            return $this->getGain() * 90;
        if ($this->type < 12)
            return $this->getGain() * 120;
        if ($this->type < 15)
            return $this->getGain() * 150;
        if ($this->type < 18)
            return $this->getGain() * 180;
        if ($this->type < 22)
            return $this->getGain() * 210;
        if ($this->type < 25)
            return $this->getGain() * 230;
        if ($this->type < 28)
            return $this->getGain() * 260;
        if ($this->type < 30)
            return $this->getGain() * 290;
        if ($this->type < 32)
            return $this->getGain() * 310;
        if ($this->type < 35)
            return $this->getGain() * 350;
        if ($this->type < 38)
            return $this->getGain() * 380;
        if ($this->type <= 40)
            return $this->getGain() * 450;
    }

    /**
     * @return mixed
     */
    public function getLevelMission()
    {
        $level = $this->getCharacter()->getLevel();
        if ($level >= $this->type) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
