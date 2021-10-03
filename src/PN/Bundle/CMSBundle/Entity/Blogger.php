<?php

namespace PN\Bundle\CMSBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\CMSBundle\Entity\Translation\BloggerTranslation;
use PN\Bundle\ContentBundle\Entity\Post;
use PN\Bundle\SeoBundle\Entity\Seo;
use PN\ServiceBundle\Model\VirtualDeleteTrait;
use PN\ServiceBundle\Model\DateTimeTrait;
use VM5\EntityTranslationsBundle\Model\Translatable;
use PN\LocaleBundle\Model\LocaleTrait;

/**
 * Blogger
 *
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="blogger")
 * @ORM\Entity(repositoryClass="PN\Bundle\CMSBundle\Repository\BloggerRepository")
 */
class Blogger implements Translatable {

    use VirtualDeleteTrait,
        LocaleTrait,
        DateTimeTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="PN\Bundle\SeoBundle\Entity\Seo", inversedBy="blogger", cascade={"persist", "remove" })
     */
    protected $seo;

    /**
     * Description
     * Brief
     * Gallery
     *
     * @ORM\OneToOne(targetEntity="PN\Bundle\ContentBundle\Entity\Post", inversedBy="blogger", cascade={"persist", "remove" })
     */
    protected $post;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var bool
     *
     * @ORM\Column(name="publish", type="boolean")
     */
    private $publish = TRUE;

    /**
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\CMSBundle\Entity\Translation\BloggerTranslation", mappedBy="translatable", cascade={"ALL"}, orphanRemoval=true)
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
        $this->bloggerTags = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Blogger
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
     * @return Blogger
     */
    public function setPublish($publish) {
        $this->publish = $publish;

        return $this;
    }

    /**
     * Get publish
     *
     * @return bool
     */
    public function getPublish() {
        return $this->publish;
    }

    /**
     * Set seo
     *
     * @param \PN\Bundle\SeoBundle\Entity\Seo $seo
     *
     * @return Blogger
     */
    public function setSeo(\PN\Bundle\SeoBundle\Entity\Seo $seo = null) {
        $this->seo = $seo;

        return $this;
    }

    /**
     * Get seo
     *
     * @return \PN\Bundle\SeoBundle\Entity\Seo
     */
    public function getSeo() {
        return $this->seo;
    }

    /**
     * Set post
     *
     * @param \PN\Bundle\ContentBundle\Entity\Post $post
     *
     * @return Blogger
     */
    public function setPost(\PN\Bundle\ContentBundle\Entity\Post $post = null) {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return \PN\Bundle\ContentBundle\Entity\Post
     */
    public function getPost() {
        return $this->post;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

}
