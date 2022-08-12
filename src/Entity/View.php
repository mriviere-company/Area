<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="view")
 * @ORM\Entity
 */
class View
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Commander", inversedBy="views", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="commander_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $commander;

    /**
     * @ORM\ManyToOne(targetEntity="Salon", inversedBy="views", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="salon_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $salon;

    /**
     * View constructor.
     * @param Commander $commander
     * @param Salon $salon
     */
    public function __construct(Commander $commander, Salon $salon)
    {
        $this->commander = $commander;
        $this->salon = $salon;
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
}
