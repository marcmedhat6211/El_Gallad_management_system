<?php

namespace PN\Bundle\CMSBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\CMSBundle\Entity\Translation\BannerTranslation;
use PN\Bundle\MediaBundle\Entity\Image;
use PN\LocaleBundle\Model\LocaleTrait;
use PN\ServiceBundle\Model\DateTimeTrait;
use VM5\EntityTranslationsBundle\Model\Translatable;

/**
 * Banner
 *
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="banner")
 * @ORM\Entity(repositoryClass="PN\Bundle\CMSBundle\Repository\BannerRepository")
 */
class Banner implements Translatable {

    use DateTimeTrait,
        LocaleTrait;

    public static $placements = array(
        'Home Page slider (1920px * 975px)' => 1,
        'Home Page Body (1720px * 500px)' => 2,
    );
    public static $placementDimensions = array(
        1 => ["width" => 1920, "height" => 975],
        2 => ["width" => 1720, "height" => 500],
    );

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="PN\Bundle\MediaBundle\Entity\Image", inversedBy="banner", cascade={"persist", "remove" })
     */
    protected $image;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=50)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_title", type="string", length=35,nullable=true)
     */
    protected $subTitle;

    /**
     * @var int
     *
     * @ORM\Column(name="placement", type="integer")
     */
    protected $placement;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="string", length=255, nullable=true)
     */
    protected $text;

    /**
     * @var string
     *
     * @ORM\Column(name="actionButton", type="string", length=20, nullable=true)
     */
    protected $actionButton = 'View';

    /**
     * @var string
     * @ORM\Column(name="tarteb", type="smallint", nullable=true)
     */
    protected $tarteb;

    /**
     * @var bool
     *
     * @ORM\Column(name="publish", type="boolean")
     */
    private $publish = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="openType", type="boolean")
     */
    private $openType = false;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\CMSBundle\Entity\Translation\BannerTranslation", mappedBy="translatable", cascade={"ALL"}, orphanRemoval=true)
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

    /**
     * Constructor
     */
    public function __construct() {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Banner
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
     * Set subTitle
     *
     * @param string $subTitle
     *
     * @return Banner
     */
    public function setSubTitle($subTitle) {
        $this->subTitle = $subTitle;

        return $this;
    }

    /**
     * Get subTitle
     *
     * @return string
     */
    public function getSubTitle() {
        return !$this->currentTranslation ? $this->subTitle : $this->currentTranslation->getSubTitle();
    }

    /**
     * Set placement
     *
     * @param integer $placement
     *
     * @return Banner
     */
    public function setPlacement($placement) {
        $this->placement = $placement;

        return $this;
    }

    /**
     * Get placement
     *
     * @return int
     */
    public function getPlacement() {
        return $this->placement;
    }

    public function getPlacementName() {
        $placement = $this->getPlacement();
        return array_search($placement, self::$placements);
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Banner
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
        return !$this->currentTranslation ? $this->url : $this->currentTranslation->getUrl();
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Banner
     */
    public function setText($text) {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText() {
        return !$this->currentTranslation ? $this->text : $this->currentTranslation->getText();
    }

    /**
     * Set openType
     *
     * @param boolean $openType
     *
     * @return Banner
     */
    public function setOpenType($openType) {
        $this->openType = $openType;

        return $this;
    }

    /**
     * Get openType
     *
     * @return bool
     */
    public function getOpenType() {
        return $this->openType;
    }

    /**
     * Set image
     *
     * @param \PN\Bundle\MediaBundle\Entity\Image $image
     *
     * @return Banner
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

    public function getActionButton(): ?string
    {
        return !$this->currentTranslation ? $this->actionButton : $this->currentTranslation->getActionButton();
    }

    public function setActionButton(?string $actionButton): self
    {
        $this->actionButton = $actionButton;

        return $this;
    }

    /**
     * @return Collection|BannerTranslation[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(BannerTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(BannerTranslation $translation): self
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getTranslatable() === $this) {
                $translation->setTranslatable(null);
            }
        }

        return $this;
    }

    public function getTarteb(): ?int
    {
        return $this->tarteb;
    }

    public function setTarteb(?int $tarteb): self
    {
        $this->tarteb = $tarteb;

        return $this;
    }

    public function getPublish(): ?bool
    {
        return $this->publish;
    }

    public function setPublish(bool $publish): self
    {
        $this->publish = $publish;

        return $this;
    }

}
