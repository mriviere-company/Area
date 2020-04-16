<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RemembermeToken
 *
 * @ORM\Table(name="rememberme_token", uniqueConstraints={@ORM\UniqueConstraint(name="series", columns={"series"})})
 * @ORM\Entity
 */
class RemembermeToken
{
    /**
     * @var string
     *
     * @ORM\Column(name="series", type="string", length=88, nullable=false, options={"fixed"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $series;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=88, nullable=false, options={"fixed"=true})
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastUsed", type="datetime", nullable=false)
     */
    private $lastused;

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=100, nullable=false)
     */
    private $class;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=200, nullable=false)
     */
    private $username;

    /**
     * @return string
     */
    public function getSeries(): string
    {
        return $this->series;
    }

    /**
     * @param string $series
     */
    public function setSeries(string $series): void
    {
        $this->series = $series;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return \DateTime
     */
    public function getLastused(): \DateTime
    {
        return $this->lastused;
    }

    /**
     * @param \DateTime $lastused
     */
    public function setLastused(\DateTime $lastused): void
    {
        $this->lastused = $lastused;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }
}
