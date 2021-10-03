<?php

namespace PN\Bundle\CMSBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\CMSBundle\Entity\Translation\TeamTranslation;
use PN\Bundle\MediaBundle\Entity\Image;
use PN\LocaleBundle\Model\LocaleTrait;
use PN\ServiceBundle\Model\DateTimeTrait;
use PN\ServiceBundle\Model\VirtualDeleteTrait;
use VM5\EntityTranslationsBundle\Model\Translatable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Testimonial
 *
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="team")
 * @ORM\Entity(repositoryClass="PN\Bundle\CMSBundle\Repository\TeamRepository")
 */
class Team implements Translatable {

    use LocaleTrait,
        DateTimeTrait,
        VirtualDeleteTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=100)
     */
    protected $position;

    /**
     * @var string
     * @ORM\Column(name="tarteb", type="smallint", nullable=true)
     */
    protected $tarteb;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $publish = true;

    /**
     * @ORM\OneToOne(targetEntity="PN\Bundle\MediaBundle\Entity\Image", inversedBy="team", cascade={"persist", "remove" })
     */
    protected $image;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\CMSBundle\Entity\Translation\TeamTranslation", mappedBy="translatable", cascade={"ALL"}, orphanRemoval=true)
     */
    protected $translations;

    /**
     * Constructor
     */
    public function __construct() {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps() {
        $this->setModified(new \DateTime(date('Y-m-d H:i:s')));
        if ($this->getCreated() == null) {
            $this->setCreated(new \DateTime(date('Y-m-d H:i:s')));
        }
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Team
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return !$this->currentTranslation ? $this->name : $this->currentTranslation->getName();
    }

    /**
     * Set publish
     *
     * @param boolean $publish
     *
     * @return Team
     */
    public function setPublish($publish) {
        $this->publish = $publish;

        return $this;
    }

    /**
     * Get publish
     *
     * @return boolean
     */
    public function getPublish() {
        return $this->publish;
    }

    /**
     * Set image
     *
     * @param \PN\Bundle\MediaBundle\Entity\Image $image
     *
     * @return Team
     */
    public function setImage(\PN\Bundle\MediaBundle\Entity\Image $image = null) {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \PN\Bundle\MediaBundle\Entity\Image
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Set tarteb
     *
     * @param integer $tarteb
     *
     * @return Service
     */
    public function setTarteb($tarteb) {
        $this->tarteb = $tarteb;

        return $this;
    }

    /**
     * Get tarteb
     *
     * @return integer
     */
    public function getTarteb() {
        return $this->tarteb;
    }

    /**
     * @return Collection|TeamTranslation[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(TeamTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(TeamTranslation $translation): self
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getTranslatable() === $this) {
                $translation->setTranslatable(null);
            }
        }

        return $this;
    }

    public function getPosition(): ?string
    {
        return !$this->currentTranslation ? $this->position : $this->currentTranslation->getPosition();

    }

    public function setPosition(string $position): self
    {
        $this->position = $position;

        return $this;
    }

}
