<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="app_users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="username",type="string", length=20, unique=true)
     * @Assert\NotBlank(message = "required")
     * @Assert\Regex(
     *     pattern="/[a-zA-Z0-9]/")
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     * @Assert\Email(message = "email.mail_format")
     * @Assert\NotBlank(message = "required")
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=40, unique=true, nullable=true)
     * @Assert\Ip
     * @Assert\NotBlank(message = "required")
     */
    protected $ipAddress;

    /**
     * @ORM\Column(name="cheat",type="smallint", options={"unsigned":true})
     */
    protected $cheat;

    /**
     *
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank(message = "required")
     */
    protected $password;

    /**
     * @ORM\Column(name="tutorial",type="smallint", options={"unsigned":true})
     */
    protected $tutorial;

    /**
     * @ORM\Column(name="newletter",type="boolean")
     * @Assert\NotBlank(message = "required")
     */
    protected $newletter;

    /**
     * @ORM\Column(name="confirmed",type="boolean")
     */
    protected $confirmed;

    /**
     * @ORM\Column(name="connect_last",type="boolean")
     */
    protected $connectLast;

    /**
     * @ORM\OneToMany(targetEntity="Character", mappedBy="user", fetch="EXTRA_LAZY")
     */
    protected $characters;

    /**
     * User constructor.
     * @param string $username
     * @param string $email
     * @param string $password
     * @param string|null $ip
     * @param bool $confirmed
     */
    public function __construct(string $username, string $email, string $password, ?string $ip, bool $confirmed)
    {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->ipAddress = $ip;
        $this->confirmed = $confirmed;
        $this->characters = new ArrayCollection();
        $this->connectLast = 0;
        $this->newletter = 1;
        $this->tutorial = 1;
        $this->cheat = 0;
    }

    /**
     * @return mixed
     */
    public function getSpecUsername()
    {
        $return = null;
        $name = ['Admin', 'Dev', 'Zombies'];

        if(in_array($this->username, $name)) {
            $return = $this->username;
        }
        return $return;
    }

    /**
     * @param Server $server
     * @return mixed
     */
    public function getCharacter(Server $server)
    {
        foreach($this->characters as $character) {
            if ($character->getServer() ==$server)
                return $character;
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getMainCharacter()
    {
        $mainCharacter =  null;
        foreach($this->characters as $character) {
            if (!$mainCharacter || ($mainCharacter && $character->getLastActivity() > $mainCharacter->getLastActivity()))
                $mainCharacter = $character;
        }
        return $mainCharacter;
    }

    /**
     * @return mixed
     */
    public function getServers()
    {
        $servers = [];
        $x = 0;
        foreach ($this->characters as $character) {
            $servers[$x] = $character->getServer()->getId();
            $x++;
        }
        return $servers;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        if ($this->getUsername() == 'Admin') {
            return ['ROLE_ADMIN'];
        }
        return ['ROLE_USER'];
    }

    /**
     *
     */
    public function eraseCredentials()
    {
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
        ));
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            ) = unserialize($serialized);
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCheat()
    {
        return $this->cheat;
    }

    /**
     * @param mixed $cheat
     */
    public function setCheat($cheat): void
    {
        $this->cheat = $cheat;
    }

    /**
     * @return mixed
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * @param mixed $confirmed
     */
    public function setConfirmed($confirmed): void
    {
        $this->confirmed = $confirmed;
    }

    /**
     * @return mixed
     */
    public function getNewletter()
    {
        return $this->newletter;
    }

    /**
     * @param mixed $newletter
     */
    public function setNewletter($newletter): void
    {
        $this->newletter = $newletter;
    }

    /**
     * @return int
     */
    public function getTutorial(): int
    {
        return $this->tutorial;
    }

    /**
     * @param int $tutorial
     */
    public function setTutorial(int $tutorial): void
    {
        $this->tutorial = $tutorial;
    }

    /**
     * @return int
     */
    public function getConnectLast(): int
    {
        return $this->connectLast;
    }

    /**
     * @param int $connectLast
     */
    public function setConnectLast(int $connectLast): void
    {
        $this->connectLast = $connectLast;
    }

    /**
     * @return ArrayCollection
     */
    public function getCharacters(): ArrayCollection
    {
        return $this->characters;
    }

    /**
     * @param ArrayCollection $characters
     */
    public function setCharacters(ArrayCollection $characters): void
    {
        $this->characters = $characters;
    }

    /**
     * Add character
     *
     * @param \App\Entity\Character $characters
     *
     * @return User
     */
    public function addCharacter(\App\Entity\Character $characters)
    {
        $this->characters[] = $characters;

        return $this;
    }

    /**
     * Remove character
     *
     * @param \App\Entity\Character $characters
     */
    public function removeCharacter(\App\Entity\Character $characters)
    {
        $this->characters->removeElement($characters);
    }

    /**
     * @return mixed
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @param mixed $ipAddress
     */
    public function setIpAddress($ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }
}
