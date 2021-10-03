<?php

namespace PN\Bundle\CMSBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\CMSBundle\Entity\Translation\PartnerTranslation;
use PN\Bundle\MediaBundle\Entity\Image;
use PN\ServiceBundle\Model\DateTimeTrait;
use VM5\EntityTranslationsBundle\Model\Translatable;
use PN\LocaleBundle\Model\LocaleTrait;
use PN\ServiceBundle\Model\VirtualDeleteTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Partner
 *
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="partner")
 * @ORM\Entity(repositoryClass="PN\Bundle\CMSBundle\Repository\PartnerRepository")
 */
class Partner implements Translatable {

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
     * @ORM\OneToOne(targetEntity="PN\Bundle\MediaBundle\Entity\Image", inversedBy="partner", cascade={"persist", "remove" })
     */
    protected $image;

    /**
     * @var string
     * @Assert\NotNull()
     * @ORM\Column(name="title", type="string", length=50)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    protected $url;

    /**
     * @var string
     * @ORM\Column(name="tarteb", type="smallint", nullable=true)
     */
    protected $tarteb;

    /**
     * @var integer
     * @ORM\Column(name="rating", type="integer", nullable=true)
     * @Assert\Range(
     * min = 0,
     * max = 5,
     * minMessage = "You must be at least give {{ limit }} as a rating",
     * maxMessage = "You cannot give more than {{ limit }} as a rating"
     * )
     */
    protected $rating;

    /**
     * @var
     * @ORM\Column(name="review", type="text", nullable=true)
     */
    protected $review;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $publish = false;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\CMSBundle\Entity\Translation\PartnerTranslation", mappedBy="translatable", cascade={"ALL"}, orphanRemoval=true)
     */
    protected $translations;

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

    public function __toString() {
        return $this->getTitle();
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     *
     * @return Partner
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return !$this->currentTranslation ? $this->title : $this->currentTranslation->getTitle();
    }

    /**
     * Set publish
     *
     * @param boolean $publish
     *
     * @return Partner
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
     * @return Partner
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
     * @return Partner
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
     * Set url
     *
     * @param string $url
     *
     * @return Partner
     */
    public function setUrl($url) {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return Collection|PartnerTranslation[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(PartnerTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(PartnerTranslation $translation): self
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getTranslatable() === $this) {
                $translation->setTranslatable(null);
            }
        }

        return $this;
    }

    public function getReview(): ?string
    {
        return !$this->currentTranslation ? $this->review : $this->currentTranslation->getReview();
    }

    public function setReview(?string $review): self
    {
        $this->review = $review;

        return $this;
    }

}
