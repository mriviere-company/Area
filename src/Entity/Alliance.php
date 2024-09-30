<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
class Alliance
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Commander", mappedBy="ally", fetch="EXTRA_LAZY")
     */
    protected $commanders;

    /**
     * @ORM\OneToMany(targetEntity="Fleet", mappedBy="ally", fetch="EXTRA_LAZY")
     */
    protected $fleets;

    /**
     * @ORM\Column(name="politic",type="string", length=30)
     * @Assert\NotBlank(message = "required")
     */
    protected $politic;

    /**
     * @ORM\OneToMany(targetEntity="Offer", mappedBy="ally", fetch="EXTRA_LAZY")
     */
    protected $offers;

    /**
     * @ORM\Column(name="name",type="string", length=15, unique=true)
     * @Assert\NotBlank(message = "required")
     * @Assert\Regex(
     *     pattern="/[a-zA-Z0-9]/")
     */
    protected $name;

    /**
     * @ORM\Column(name="tag",type="string", length=5, unique=true)
     * @Assert\NotBlank(message = "required")
     * @Assert\Regex(
     *     pattern="/[a-zA-Z0-9]/")
     */
    protected $tag;

    /**
     * @ORM\Column(name="slogan",type="string", length=30)
     * @Assert\NotBlank(message = "required")
     */
    protected $slogan;

    /**
     * @ORM\Column(name="description",type="string", length=1500, nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="level",type="smallint", options={"unsigned":true})
     */
    protected $level;

    /**
     * @ORM\Column(name="max_members",type="smallint", options={"unsigned":true})
     */
    protected $maxMembers;

    /**
     * @ORM\OneToMany(targetEntity="Grade", mappedBy="ally", fetch="EXTRA_LAZY")
     */
    protected $grades;

    /**
     * @ORM\Column(name="defcon",type="smallint", options={"unsigned":true})
     */
    protected $defcon;

    /**
     * @ORM\ManyToMany(targetEntity="Salon", mappedBy="allys", fetch="EXTRA_LAZY")
     */
    protected $salons;

    /**
     * @ORM\Column(name="rank",type="bigint", nullable=true, options={"unsigned":true})
     */
    protected $rank;

    /**
     * @ORM\Column(name="bitcoin",type="bigint", options={"unsigned":true})
     */
    protected $bitcoin;

    /**
     * @ORM\Column(name="pdg",type="bigint", options={"unsigned":true})
     */
    protected $pdg;

    /**
     * @ORM\OneToMany(targetEntity="Exchange", mappedBy="ally", fetch="EXTRA_LAZY")
     */
    protected $exchanges;

    /**
     * @ORM\Column(name="taxe",type="smallint", options={"unsigned":true})
     */
    protected $taxe;

    /**
     * @ORM\OneToMany(targetEntity="Pna", mappedBy="ally", fetch="EXTRA_LAZY")
     */
    protected $pnas;

    /**
     * @ORM\OneToMany(targetEntity="Allied", mappedBy="ally", fetch="EXTRA_LAZY")
     */
    protected $allieds;

    /**
     * @ORM\OneToMany(targetEntity="Peace", mappedBy="ally", fetch="EXTRA_LAZY")
     */
    protected $peaces;

    /**
     * @ORM\OneToMany(targetEntity="War", mappedBy="ally", fetch="EXTRA_LAZY")
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
     * @var DateTime
     */
    private $updatedAt;

    /**
     * Alliance constructor.
     */
    public function __construct()
    {
        $this->commanders = new ArrayCollection();
        $this->fleets = new ArrayCollection();
        $this->salons = new ArrayCollection();
        $this->offers = new ArrayCollection();
        $this->pnas = new ArrayCollection();
        $this->allieds = new ArrayCollection();
        $this->wars = new ArrayCollection();
        $this->grades = new ArrayCollection();
        $this->defcon = 0;
        $this->bitcoin = 0;
        $this->createdAt = new DateTime();
        $this->imageFile = null;
        $this->rank = 1;
        $this->description = '-';
        $this->politic = 'democrat';
        $this->level = 0;
        $this->maxMembers = 3;
    }

    /**
     * Add offer
     *
     * @param Offer $offer
     *
     * @return Alliance
     */
    public function addOffer(Offer $offer)
    {
        $this->offers[] = $offer;

        return $this;
    }

    /**
     * Remove offer
     *
     * @param Offer $offer
     */
    public function removeOffer(Offer $offer)
    {
        $this->offers->removeElement($offer);
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
     * @return Collection
     */
    public function getCommanders()
    {
        $criteria = Criteria::create()
            ->orderBy(array('username' => 'ASC'));

        return $this->commanders->matching($criteria);
    }

    /**
     * @return Collection
     */
    public function getFleets()
    {
        return $this->fleets;
    }

    /**
     * Add fleet
     *
     * @param Fleet $fleet
     *
     * @return Alliance
     */
    public function addFleet(Fleet $fleet)
    {
        $this->fleets[] = $fleet;

        return $this;
    }

    /**
     * Remove fleet
     *
     * @param Fleet $fleet
     */
    public function removeFleet(Fleet $fleet)
    {
        $this->fleet->removeElement($fleet);
    }

    /**
     * Add commander
     *
     * @param Commander $commander
     *
     * @return Alliance
     */
    public function addCommander(Commander $commander)
    {
        $this->commanders[] = $commander;

        return $this;
    }

    /**
     * Remove commander
     *
     * @param Commander $commander
     */
    public function removeCommander(Commander $commander)
    {
        $this->commanders->removeElement($commander);
    }

    /**
     * Add ally pna
     *
     * @param Pna $pna
     *
     * @return Alliance
     */
    public function addAlliancePna(Pna $pna)
    {
        $this->pnas[] = $pna;

        return $this;
    }

    /**
     * Remove ally pna
     *
     * @param Pna $pna
     */
    public function removeAlliancePna(Pna $pna)
    {
        $this->pnas->removeElement($pna);
    }

    /**
     * Add ally allied
     *
     * @param Allied $allied
     *
     * @return Alliance
     */
    public function addAllianceAllied(Allied $allied)
    {
        $this->allieds[] = $allied;

        return $this;
    }

    /**
     * Remove ally allied
     *
     * @param Allied $allied
     */
    public function removeAllianceAllied(Allied $allied)
    {
        $this->allieds->removeElement($allied);
    }

    /**
     * Add ally war
     *
     * @param War $war
     *
     * @return Alliance
     */
    public function addAllianceWar(War $war)
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
     * @param War $war
     */
    public function removeAllianceWar(War $war)
    {
        $this->wars->removeElement($war);
    }

    /**
     * Add grade
     *
     * @param Grade $grade
     *
     * @return Alliance
     */
    public function addGrade(Grade $grade)
    {
        $this->grades[] = $grade;

        return $this;
    }

    /**
     * Remove grade
     *
     * @param Grade $grade
     */
    public function removeGrade(Grade $grade)
    {
        $this->grades->removeElement($grade);
    }

    /**
     * Add salon
     *
     * @param Salon $salon
     *
     * @return Alliance
     */
    public function addSalon(Salon $salon)
    {
        $this->salons[] = $salon;

        return $this;
    }

    /**
     * Remove salon
     *
     * @param Salon $salon
     */
    public function removeSalon(Salon $salon)
    {
        $this->salons->removeElement($salon);
    }

    /**
     * @return mixed
     */
    public function getAlreadyPact($tag)
    {
        foreach($this->getPnas() as $pna) {
            if($pna->getAllianceTag() == $tag) {
                return 'pna';
            }
        }
        foreach($this->getWars() as $war) {
            if($war->getAllianceTag() == $tag) {
                return 'pna';
            }
        }
        foreach($this->getAllieds() as $allied) {
            if($allied->getAllianceTag() == $tag) {
                return 'pna';
            }
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getCommandersPoint()
    {
        $return = 0;

        foreach($this->getCommanders() as $commander) {
            if($commander->getRank()) {
                $return += $commander->getRank()->getPoint();
            }
        }
        return round($return / (count($this->getCommanders()) > 0 ? count($this->getCommanders()) : 1));
    }

    /**
     * @return mixed
     */
    public function getTagAllied($tag)
    {
        foreach($this->getPnas() as $pna) {
            if ($pna->getAllianceTag() == $tag && $pna->getAccepted() == 1) {
                return $tag;
            }
        }
        foreach($this->getAllieds() as $pact) {
            if ($pact->getAllianceTag() == $tag && $pact->getAccepted() == 1) {
                return $tag;
            }
        }
        foreach ($this->getPeaces() as $peace) {
            if ($peace->getAllianceTag() == $tag && $peace->getAccepted()) {
                return $tag;
            }
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getTagAlliedArray($tags)
    {
        if($tags) {
            foreach ($tags as $tag) {
                foreach ($this->getPnas() as $pna) {
                    if ($pna->getAllianceTag() == $tag && $pna->getAccepted()) {
                        return $pna->getAllianceTag();
                    }
                }
                foreach ($this->getAllieds() as $pact) {
                    if ($pact->getAllianceTag() == $tag && $pact->getAccepted()) {
                        return $pact->getAllianceTag();
                    }
                }
                foreach ($this->getPeaces() as $peace) {
                    if ($peace->getAllianceTag() == $tag && $peace->getAccepted()) {
                        return $peace->getAllianceTag();
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

        foreach($this->getCommanders() as $commander) {
            $return += $commander->getTerraformation() + 2;
            $return += $commander->getPoliticColonisation();
            $return += $commander->getPoliticInvade();
        }
        return $return;
    }

    /**
     * @return int
     */
    public function getPlanets(): int
    {
        $return = 0;

        foreach($this->getCommanders() as $commander) {
            foreach ($commander->getPlanets() as $planet) {
                if(!$planet->getEmpty()) {
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
            } elseif ($grade->getName() == 'Camarade' && $this->politic() == 'communism') {
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
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt(DateTime $updatedAt): void
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
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param mixed $tag
     */
    public function setTag($tag): void
    {
        $this->tag = $tag;
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
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * @param mixed $offers
     */
    public function setOffers($offers): void
    {
        $this->offers = $offers;
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
