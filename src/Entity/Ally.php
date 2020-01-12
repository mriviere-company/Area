<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Table(name="ally")
 * @ORM\Entity
 * @Vich\Uploadable
 */
class Ally
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="ally", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $users;

    /**
     * @ORM\OneToMany(targetEntity="Fleet", mappedBy="ally", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="fleet_id", referencedColumnName="id")
     */
    protected $fleets;

    /**
     * @ORM\Column(name="politic",type="string", length=30)
     * @Assert\NotBlank(message = "required")
     */
    protected $politic;

    /**
     * @ORM\OneToMany(targetEntity="Proposal", mappedBy="ally", fetch="EXTRA_LAZY")
     */
    protected $proposals;

    /**
     * @ORM\Column(name="name",type="string", length=15, unique=true)
     * @Assert\NotBlank(message = "required")
     * @Assert\Regex(
     *     pattern="/[a-zA-Z0-9]/")
     */
    protected $name;

    /**
     * @ORM\Column(name="sigle",type="string", length=5, unique=true)
     * @Assert\NotBlank(message = "required")
     * @Assert\Regex(
     *     pattern="/[a-zA-Z0-9]/")
     */
    protected $sigle;

    /**
     * @ORM\Column(name="slogan",type="string", length=30, unique=true)
     * @Assert\NotBlank(message = "required")
     */
    protected $slogan;

    /**
     * @ORM\Column(name="description",type="string", length=1500, nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="level",type="smallint")
     */
    protected $level;

    /**
     * @ORM\Column(name="max_members",type="smallint")
     */
    protected $maxMembers;

    /**
     * @ORM\OneToMany(targetEntity="Grade", mappedBy="ally", fetch="EXTRA_LAZY", cascade={"persist"})
     */
    protected $grades;

    /**
     * @ORM\Column(name="defcon",type="smallint")
     */
    protected $defcon;

    /**
     * @ORM\ManyToMany(targetEntity="Salon", mappedBy="allys", fetch="EXTRA_LAZY")
     */
    protected $salons;

    /**
     * @ORM\Column(name="rank",type="bigint", nullable=true)
     */
    protected $rank;

    /**
     * @ORM\Column(name="bitcoin",type="bigint")
     */
    protected $bitcoin;

    /**
     * @ORM\Column(name="pdg",type="bigint")
     */
    protected $pdg;

    /**
     * @ORM\OneToMany(targetEntity="Exchange", mappedBy="ally", fetch="EXTRA_LAZY")
     */
    protected $exchanges;

    /**
     * @ORM\Column(name="taxe",type="smallint")
     */
    protected $taxe;

    /**
     * @ORM\OneToMany(targetEntity="Pna", mappedBy="ally", fetch="EXTRA_LAZY", cascade={"persist"})
     */
    protected $pnas;

    /**
     * @ORM\OneToMany(targetEntity="Allied", mappedBy="ally", fetch="EXTRA_LAZY", cascade={"persist"})
     */
    protected $allieds;

    /**
     * @ORM\OneToMany(targetEntity="Peace", mappedBy="ally", fetch="EXTRA_LAZY", cascade={"persist"})
     */
    protected $peaces;

    /**
     * @ORM\OneToMany(targetEntity="War", mappedBy="ally", fetch="EXTRA_LAZY", cascade={"persist"})
     */
    protected $wars;

    /**
     * @Assert\File(
     *     maxSize="600k",
     *     mimeTypes={"image/png", "image/jpeg", "image/bmp"}
     * )
     * @Vich\UploadableField(mapping="ally_img", fileNameProperty="imageName", size="imageSize" )
     *
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $imageName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var integer
     */
    private $imageSize;

    /**
     * @ORM\Column(name="created_at",type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * Ally constructor.
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fleets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->salons = new \Doctrine\Common\Collections\ArrayCollection();
        $this->proposals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pnas = new \Doctrine\Common\Collections\ArrayCollection();
        $this->allieds = new \Doctrine\Common\Collections\ArrayCollection();
        $this->wars = new \Doctrine\Common\Collections\ArrayCollection();
        $this->grades = new \Doctrine\Common\Collections\ArrayCollection();
        $this->defcon = 0;
        $this->bitcoin = 0;
        $this->createdAt = null;
        $this->imageFile = null;
        $this->rank = 1;
        $this->descritpion = '-';
        $this->politic = 'democrat';
        $this->level = 0;
        $this->maxMembers = 3;
    }

    /**
     * Add proposal
     *
     * @param \App\Entity\Proposal $proposal
     *
     * @return Ally
     */
    public function addProposal(\App\Entity\Proposal $proposal)
    {
        $this->proposals[] = $proposal;

        return $this;
    }

    /**
     * Remove proposal
     *
     * @param \App\Entity\Proposal $proposal
     */
    public function removeProposal(\App\Entity\Proposal $proposal)
    {
        $this->proposals->removeElement($proposal);
    }

    /**
     * @return mixed
     */
    public function getPacts()
    {
        $pnas = count($this->getPnas());
        $allieds = count($this->getAllieds());
        $wars = count($this->getWars());

        $pacts = $pnas + $allieds + $wars;
        return $pacts;
    }

    /**
     * @return mixed
     */
    public function getLevelCost()
    {
        $maxMembers = 0;
        $bitcoin = 0;
        $pdg = 0;
        if($this->getLevel() == 0) {
            $maxMembers = $this->maxMembers + 3;
            $bitcoin = 125000;
            $pdg = 0;
        } elseif ($this->getLevel() == 1){
            $maxMembers = $this->maxMembers + 2;
            $bitcoin = 225000;
            $pdg = 2500;
        } elseif ($this->getLevel() == 2){
            $maxMembers = $this->maxMembers + 2;
            $bitcoin = 600000;
            $pdg = 10000;
        } elseif ($this->getLevel() == 3){
            $maxMembers = $this->maxMembers + 2;
            $bitcoin = 1000000;
            $pdg = 25000;
        } elseif ($this->getLevel() == 4){
            $maxMembers = $this->maxMembers + 3;
            $bitcoin = 0;
            $pdg = 50000;
        } elseif ($this->getLevel() == 5){
            $maxMembers = $this->maxMembers + 2;
            $bitcoin = 10000000;
            $pdg = 0;
        } elseif ($this->getLevel() == 6){
            $maxMembers = $this->maxMembers + 3;
            $bitcoin = 15000000;
            $pdg = 125000;
        } elseif ($this->getLevel() == 7){
            $maxMembers = $this->maxMembers + 2;
            $bitcoin = 35000000;
            $pdg = 300000;
        } elseif ($this->getLevel() == 8){
            $maxMembers = $this->maxMembers + 3;
            $bitcoin = 50000000;
            $pdg = 500000;
        } elseif ($this->getLevel() == 9){
            $maxMembers = $this->maxMembers + 5;
            $bitcoin = 100000000;
            $pdg = 1000000;
        }
        $return = [$maxMembers, $bitcoin, $pdg];
        return $return;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        $criteria = Criteria::create()
            ->orderBy(array('username' => 'ASC'));

        return $this->users->matching($criteria);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFleets()
    {
        return $this->fleets;
    }

    /**
     * Add fleet
     *
     * @param \App\Entity\Fleet $fleet
     *
     * @return Ally
     */
    public function addFleet(\App\Entity\Fleet $fleet)
    {
        $this->fleets[] = $fleet;

        return $this;
    }

    /**
     * Remove fleet
     *
     * @param \App\Entity\Fleet $fleet
     */
    public function removeFleet(\App\Entity\Fleet $fleet)
    {
        $this->fleet->removeElement($fleet);
    }

    /**
     * Add user
     *
     * @param \App\Entity\User $user
     *
     * @return Ally
     */
    public function addUser(\App\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \App\Entity\User $user
     */
    public function removeUser(\App\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Add ally pna
     *
     * @param \App\Entity\Pna $pna
     *
     * @return Ally
     */
    public function addAllyPna(\App\Entity\Pna $pna)
    {
        $this->pnas[] = $pna;

        return $this;
    }

    /**
     * Remove ally pna
     *
     * @param \App\Entity\Pna $pna
     */
    public function removeAllyPna(\App\Entity\Pna $pna)
    {
        $this->pnas->removeElement($pna);
    }

    /**
     * Add ally allied
     *
     * @param \App\Entity\Allied $allied
     *
     * @return Ally
     */
    public function addAllyAllied(\App\Entity\Allied $allied)
    {
        $this->allieds[] = $allied;

        return $this;
    }

    /**
     * Remove ally allied
     *
     * @param \App\Entity\Allied $allied
     */
    public function removeAllyAllied(\App\Entity\Allied $allied)
    {
        $this->allieds->removeElement($allied);
    }

    /**
     * Add ally war
     *
     * @param \App\Entity\War $war
     *
     * @return Ally
     */
    public function addAllyWar(\App\Entity\War $war)
    {
        $this->wars[] = $war;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefcon()
    {
        return $this->defcon;
    }

    /**
     * @param mixed $defcon
     */
    public function setDefcon($defcon): void
    {
        $this->defcon = $defcon;
    }

    /**
     * Remove ally war
     *
     * @param \App\Entity\War $war
     */
    public function removeAllyWar(\App\Entity\War $war)
    {
        $this->wars->removeElement($war);
    }

    /**
     * Add grade
     *
     * @param \App\Entity\Grade $grade
     *
     * @return Ally
     */
    public function addGrade(\App\Entity\Grade $grade)
    {
        $this->grades[] = $grade;

        return $this;
    }

    /**
     * Remove grade
     *
     * @param \App\Entity\Grade $grade
     */
    public function removeGrade(\App\Entity\Grade $grade)
    {
        $this->grades->removeElement($grade);
    }

    /**
     * Add salon
     *
     * @param \App\Entity\Salon $salon
     *
     * @return Ally
     */
    public function addSalon(\App\Entity\Salon $salon)
    {
        $this->salons[] = $salon;

        return $this;
    }

    /**
     * Remove salon
     *
     * @param \App\Entity\Salon $salon
     */
    public function removeSalon(\App\Entity\Salon $salon)
    {
        $this->salons->removeElement($salon);
    }

    /**
     * @return mixed
     */
    public function getRadarAlliance($sector, $gal)
    {
        $return = null;

        foreach($this->getUsers() as $user) {
            foreach ($user->getPlanets() as $planet) {
                if ($planet->getSector()->getPosition() == $sector && $planet->getSector()->getGalaxy()->getPosition() == $gal) {
                    $radar = $planet->getRadar() + $planet->getSkyRadar();
                    if ($radar > $return || $return == null) {
                        $return = $radar;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * @return mixed
     */
    public function getAlreadyPact($sigle)
    {
        foreach($this->getPnas() as $pna) {
            if($pna->getAllyTag() == $sigle) {
                return 'pna';
            }
        }
        foreach($this->getWars() as $war) {
            if($war->getAllyTag() == $sigle) {
                return 'pna';
            }
        }
        foreach($this->getAllieds() as $allied) {
            if($allied->getAllyTag() == $sigle) {
                return 'pna';
            }
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getUsersPoint()
    {
        $return = 0;

        foreach($this->getUsers() as $user) {
            if($user->getRank()) {
                $return = $return + $user->getRank()->getPoint();
            }
        }
        return round($return / count($this->getUsers()));
    }

    /**
     * @return mixed
     */
    public function getSigleAllied($sigle)
    {
        foreach($this->getPnas() as $pna) {
            if ($pna->getAllyTag() == $sigle && $pna->getAccepted() == 1) {
                return $sigle;
            }
        }
        foreach($this->getAllieds() as $pact) {
            if ($pact->getAllyTag() == $sigle && $pact->getAccepted() == 1) {
                return $sigle;
            }
        }
        foreach ($this->getPeaces() as $peace) {
            if ($peace->getAllyTag() == $sigle && $peace->getAccepted() == true) {
                return $sigle;
            }
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getSigleAlliedArray($sigles)
    {
        if($sigles) {
            foreach ($sigles as $sigle) {
                foreach ($this->getPnas() as $pna) {
                    if ($pna->getAllyTag() == $sigle && $pna->getAccepted() == true) {
                        return $pna->getAllyTag();
                    }
                }
                foreach ($this->getAllieds() as $pact) {
                    if ($pact->getAllyTag() == $sigle && $pact->getAccepted() == true) {
                        return $pact->getAllyTag();
                    }
                }
                foreach ($this->getPeaces() as $peace) {
                    if ($peace->getAllyTag() == $sigle && $peace->getAccepted() == true) {
                        return $peace->getAllyTag();
                    }
                }
            }
        }
        return null;
    }

    /**
     * @return int
     */
    public function getPlanetsMax(): int
    {
        $return = 0;

        foreach($this->getUsers() as $user) {
            $return = $return + $user->getTerraformation() + 2;
            $return = $return + $user->getPoliticColonisation();
            $return = $return + $user->getPoliticInvade();
        }
        return $return;
    }

    /**
     * @return int
     */
    public function getPlanets(): int
    {
        $return = 0;

        foreach($this->getUsers() as $user) {
            foreach ($user->getPlanets() as $planet) {
                if($planet->getEmpty() == false) {
                    $return++;
                }
            }
        }
        return $return;
    }

    /**
     * @return int
     */
    public function getFlightTime() : int
    {
        foreach ($this->fleets as $fleet) {
            if($fleet->getFlightTime()) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * @return mixed
     */
    public function getNewMember()
    {
        $return = null;

        foreach($this->getGrades() as $grade) {
            if($grade->getPlacement() == 5) {
                $return = $grade;
            }
        }
        return $return;
    }

    /**
     * @return mixed
     */
    public function getBitcoin()
    {
        return $this->bitcoin;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setImageFile(?File $image = null): void
    {
        $this->imageFile = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    /**
     * @return mixed
     */
    public function getTaxe()
    {
        return $this->taxe;
    }

    /**
     * @param mixed $taxe
     */
    public function setTaxe($taxe): void
    {
        $this->taxe = $taxe;
    }

    /**
     * @return
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
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

    /**
     * @return mixed
     */
    public function getGrade()
    {
        return $this->grades;
    }

    /**
     * @param mixed $grade
     */
    public function setGrade($grade): void
    {
        $this->grades = $grade;
    }

    /**
     * @return mixed
     */
    public function getPnas()
    {
        return $this->pnas;
    }

    /**
     * @param mixed $pnas
     */
    public function setPnas($pnas): void
    {
        $this->pnas = $pnas;
    }

    /**
     * @return mixed
     */
    public function getAllieds()
    {
        return $this->allieds;
    }

    /**
     * @param mixed $allieds
     */
    public function setAllieds($allieds): void
    {
        $this->allieds = $allieds;
    }

    /**
     * @return mixed
     */
    public function getWars()
    {
        return $this->wars;
    }

    /**
     * @param mixed $wars
     */
    public function setWars($wars): void
    {
        $this->wars = $wars;
    }

    /**
     * @return mixed
     */
    public function getSigle()
    {
        return $this->sigle;
    }

    /**
     * @param mixed $sigle
     */
    public function setSigle($sigle): void
    {
        $this->sigle = $sigle;
    }

    /**
     * @return mixed
     */
    public function getSlogan()
    {
        return $this->slogan;
    }

    /**
     * @param mixed $slogan
     */
    public function setSlogan($slogan): void
    {
        $this->slogan = $slogan;
    }

    /**
     * @return mixed
     */
    public function getGrades()
    {
        return $this->grades;
    }

    /**
     * @param mixed $grades
     */
    public function setGrades($grades): void
    {
        $this->grades = $grades;
    }

    /**
     * @return mixed
     */
    public function getProposals()
    {
        return $this->proposals;
    }

    /**
     * @param mixed $proposals
     */
    public function setProposals($proposals): void
    {
        $this->proposals = $proposals;
    }

    /**
     * @return mixed
     */
    public function getSalons()
    {
        return $this->salons;
    }

    /**
     * @param mixed $salons
     */
    public function setSalons($salons): void
    {
        $this->salons = $salons;
    }

    /**
     * @return mixed
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param mixed $rank
     */
    public function setRank($rank): void
    {
        $this->rank = $rank;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getExchanges()
    {
        return $this->exchanges;
    }

    /**
     * @param mixed $exchanges
     */
    public function setExchanges($exchanges): void
    {
        $this->exchanges = $exchanges;
    }

    /**
     * @return mixed
     */
    public function getPolitic()
    {
        return $this->politic;
    }

    /**
     * @param mixed $politic
     */
    public function setPolitic($politic): void
    {
        $this->politic = $politic;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getPeaces()
    {
        return $this->peaces;
    }

    /**
     * @param mixed $peaces
     */
    public function setPeaces($peaces): void
    {
        $this->peaces = $peaces;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level): void
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getMaxMembers()
    {
        return $this->maxMembers;
    }

    /**
     * @param mixed $maxMembers
     */
    public function setMaxMembers($maxMembers): void
    {
        $this->maxMembers = $maxMembers;
    }

    /**
     * @return mixed
     */
    public function getPdg()
    {
        return $this->pdg;
    }

    /**
     * @param mixed $pdg
     */
    public function setPdg($pdg): void
    {
        $this->pdg = $pdg;
    }
}
