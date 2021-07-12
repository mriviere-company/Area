<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="server")
 * @ORM\Entity
 */
class Server
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
     * @ORM\Column(name="open",type="boolean")
     * @Assert\NotBlank(message = "required")
     */
    protected $open;

    /**
     * @ORM\Column(name="pvp",type="smallint")
     * @Assert\NotBlank(message = "required")
     */
    protected $pvp;

    /**
     * @ORM\Column(name="dailyReport",type="datetime", nullable=true)
     */
    protected $dailyReport;

    /**
     * @ORM\Column(name="embargo",type="datetime", nullable=true)
     */
    protected $embargo;

    /**
     * @ORM\Column(name="speed",type="decimal", precision=28, scale=3)
     * @Assert\NotBlank(message = "required")
     */
    protected $speed;

    /**
     * @ORM\Column(name="production",type="decimal", precision=28, scale=3)
     * @Assert\NotBlank(message = "required")
     */
    protected $production;

    /**
     * @ORM\OneToMany(targetEntity="Character", mappedBy="server", fetch="EXTRA_LAZY")
     */
    protected $characters;

    /**
     * @ORM\Column(name="attackStartAt",type="datetime", nullable=true)
     */
    protected $attackStartAt;

    /**
     * @ORM\Column(name="attackEndAt",type="datetime", nullable=true)
     */
    protected $attackEndAt;

    /**
     * @ORM\OneToMany(targetEntity="Galaxy", mappedBy="server", fetch="EXTRA_LAZY")
     */
    protected $galaxys;

    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="server", fetch="EXTRA_LAZY")
     */
    protected $events;

    /**
     * @ORM\OneToMany(targetEntity="Salon", mappedBy="server", fetch="EXTRA_LAZY")
     */
    protected $salons;

    /**
     * Server constructor.
     * @param string $name
     * @param bool $pvp
     * @param int $speed
     * @param int $startHour
     * @param int $startMin
     * @param int $endHour
     * @param int $endMin
     * @param int $prod
     */
    public function __construct(string $name, bool $pvp, int $speed, int $startHour, int $startMin, int $endHour, int $endMin, int $prod)
    {
        $now = new DateTime();
        $nowBis = new DateTime();
        $nowTer = new DateTime();

        $this->characters = new ArrayCollection();
        $this->galaxys = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->salons = new ArrayCollection();
        $this->dailyReport = $now;
        $this->production = $prod;
        $this->embargo = $now;
        $this->attackStartAt = $nowBis->setTime($startHour, $startMin, 00);
        $this->attackEndAt = $nowTer->setTime($endHour, $endMin, 00);
        $this->open = false;
        $this->speed = $speed;
        $this->pvp = $pvp;
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getGalaxys()
    {
        return $this->galaxys;
    }

    /**
     * @param mixed $galaxys
     */
    public function setGalaxys($galaxys): void
    {
        $this->galaxys = $galaxys;
    }

    /**
     * Add galaxy
     *
     * @param Galaxy $galaxys
     *
     * @return Server
     */
    public function addGalaxy(Galaxy $galaxys): Server
    {
        $this->galaxys[] = $galaxys;

        return $this;
    }

    /**
     * Remove galaxy
     *
     * @param Galaxy $galaxys
     */
    public function removeGalaxy(Galaxy $galaxys)
    {
        $this->galaxys->removeElement($galaxys);
    }

    /**
     * @return mixed
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param ArrayCollection $events
     */
    public function setEvents(ArrayCollection $events): void
    {
        $this->events = $events;
    }

    /**
     * Add event
     *
     * @param Event $events
     *
     * @return Server
     */
    public function addEvent(Event $events): Server
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove event
     *
     * @param Event $events
     */
    public function removeEvent(Event $events)
    {
        $this->events->removeElement($events);
    }

    /**
     * @return mixed
     */
    public function getCharacters()
    {
        return $this->characters;
    }

    /**
     * @param mixed $characters
     */
    public function setCharacters($characters): void
    {
        $this->characters = $characters;
    }

    /**
     * Add character
     *
     * @param Character $characters
     *
     * @return Server
     */
    public function addCharacter(Character $characters): Server
    {
        $this->characters[] = $characters;

        return $this;
    }

    /**
     * Remove character
     *
     * @param Character $characters
     */
    public function removeCharacter(Character $characters)
    {
        $this->characters->removeElement($characters);
    }

    /**
     * @return mixed
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * @param mixed $open
     */
    public function setOpen($open): void
    {
        $this->open = $open;
    }

    /**
     * @return mixed
     */
    public function getPvp()
    {
        return $this->pvp;
    }

    /**
     * @param mixed $pvp
     */
    public function setPvp($pvp): void
    {
        $this->pvp = $pvp;
    }

    /**
     * @return mixed
     */
    public function getDailyReport()
    {
        return $this->dailyReport;
    }

    /**
     * @param mixed $dailyReport
     */
    public function setDailyReport($dailyReport): void
    {
        $this->dailyReport = $dailyReport;
    }

    /**
     * @return float
     */
    public function getSpeed(): float
    {
        return $this->speed;
    }

    /**
     * @param float $speed
     */
    public function setSpeed(float $speed): void
    {
        $this->speed = $speed;
    }

    /**
     * @return int
     */
    public function getProduction(): int
    {
        return $this->production;
    }

    /**
     * @param int $production
     */
    public function setProduction(int $production): void
    {
        $this->production = $production;
    }

    /**
     * @return null
     */
    public function getEmbargo(): ?DateTime
    {
        return $this->embargo;
    }

    /**
     * @param null $embargo
     */
    public function setEmbargo($embargo): void
    {
        $this->embargo = $embargo;
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
    public function getSalons()
    {
        return $this->salons;
    }

    /**
     * @param ArrayCollection $salons
     */
    public function setSalons(ArrayCollection $salons): void
    {
        $this->salons = $salons;
    }

    /**
     * Add salon
     *
     * @param Salon $salons
     *
     * @return Server
     */
    public function addSalon(Salon $salons): Server
    {
        $this->salons[] = $salons;

        return $this;
    }

    /**
     * Remove salon
     *
     * @param Salon $salons
     */
    public function removeSalon(Salon $salons)
    {
        $this->salons->removeElement($salons);
    }

    /**
     * @return string
     */
    public function getName(): string
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
     * @return null
     */
    public function getAttackStartAt()
    {
        return $this->attackStartAt;
    }

    /**
     * @param null $attackStartAt
     */
    public function setAttackStartAt($attackStartAt): void
    {
        $this->attackStartAt = $attackStartAt;
    }

    /**
     * @return null
     */
    public function getAttackEndAt()
    {
        return $this->attackEndAt;
    }

    /**
     * @param null $attackEndAt
     */
    public function setAttackEndAt($attackEndAt): void
    {
        $this->attackEndAt = $attackEndAt;
    }
}
