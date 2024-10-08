<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="track")
 * @ORM\Entity
 */
class Track
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="username",type="string", length=20, nullable=true)
     */
    protected $username;

    /**
     * @ORM\Column(name="page",type="string", length=30, nullable=true)
     */
    protected $page;

    /**
     * @ORM\Column(name="ip",type="string", length=40, nullable=true)
     */
    protected $ip;

    /**
     * @ORM\Column(name="browser",type="string", length=40, nullable=true)
     */
    protected $browser;

    /**
     * @ORM\Column(name="computer",type="string", length=20, nullable=true)
     */
    protected $computer;

    /**
     * @ORM\Column(name="previous_page",type="string", length=50, nullable=true)
     */
    protected $previousPage;

    /**
     * @ORM\Column(name="date",type="datetime", nullable=true)
     */
    protected $date;

    public function __construct()
    {
        $this->date = new DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page): void
    {
        $this->page = $page;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return mixed
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * @param mixed $browser
     */
    public function setBrowser($browser): void
    {
        $this->browser = $browser;
    }

    /**
     * @return mixed
     */
    public function getPreviousPage()
    {
        return $this->previousPage;
    }

    /**
     * @param mixed $previousPage
     */
    public function setPreviousPage($previousPage): void
    {
        $this->previousPage = $previousPage;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getComputer()
    {
        return $this->computer;
    }

    /**
     * @param mixed $computer
     */
    public function setComputer($computer): void
    {
        $this->computer = $computer;
    }
}
