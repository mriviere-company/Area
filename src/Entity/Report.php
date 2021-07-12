<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Table(name="report")
 * @ORM\Entity
 * @Vich\Uploadable
 */
class Report
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Character", inversedBy="reports", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="character_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $character;

    /**
     * @ORM\Column(name="title",type="string", length=75)
     * @Assert\NotBlank(message = "required")
     */
    protected $title;

    /**
     * @ORM\Column(name="newReport",type="smallint")
     */
    protected $newReport;

    /**
     * @ORM\Column(name="content",type="string", length=16000)
     * @Assert\NotBlank(message = "required")
     */
    protected $content;

    /**
     * @ORM\Column(name="share_key",type="string", length=20, nullable=true)
     */
    protected $shareKey;

    /**
     * @ORM\Column(name="sendAt",type="datetime")
     */
    protected $sendAt;

    /**
     * @ORM\Column(name="type",type="string", length=25)
     */
    protected $type;

    public function __construct()
    {
        $this->newReport = 2;
        $this->shareKey = null;
        $this->type = 'defaut';
    }

    /**
     * @Assert\File(
     *     maxSize="400k",
     *     mimeTypes={"image/png", "image/jpeg", "image/bmp"}
     * )
     * @Vich\UploadableField(mapping="planet_img", fileNameProperty="imageName" )
     *
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(name="imageName",type="string", length=40, nullable=true)
     */
    protected $imageName;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $updatedAt;

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

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
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

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getId()
    {
        return $this->id;
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getShareKey()
    {
        return $this->shareKey;
    }

    /**
     * @param mixed $shareKey
     */
    public function setShareKey($shareKey): void
    {
        $this->shareKey = $shareKey;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }

    /**
     * @param mixed $sendAt
     */
    public function setSendAt($sendAt): void
    {
        $this->sendAt = $sendAt;
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
    public function getNewReport()
    {
        return $this->newReport;
    }

    /**
     * @param mixed $newReport
     */
    public function setNewReport($newReport): void
    {
        $this->newReport = $newReport;
    }
}
