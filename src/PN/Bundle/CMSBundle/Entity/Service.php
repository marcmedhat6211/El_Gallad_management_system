<?php

namespace PN\Bundle\CMSBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\CMSBundle\Entity\Translation\ServiceTranslation;
use PN\Bundle\ContentBundle\Entity\Post;
use PN\Bundle\SeoBundle\Entity\Seo;
use PN\LocaleBundle\Model\LocaleTrait;
use PN\ServiceBundle\Model\DateTimeTrait;
use PN\ServiceBundle\Model\VirtualDeleteTrait;
use VM5\EntityTranslationsBundle\Model\Translatable;

/**
 * Service
 *
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="service")
 * @ORM\Entity(repositoryClass="PN\Bundle\CMSBundle\Repository\ServiceRepository")
 */
class Service implements Translatable {

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
     * Description
     * Brief
     * Gallery
     *
     * @ORM\OneToOne(targetEntity="PN\Bundle\ContentBundle\Entity\Post", inversedBy="service", cascade={"persist", "remove" })
     */
    protected $post;

    /**
     * @ORM\ManyToMany(targetEntity="PN\Bundle\CMSBundle\Entity\Project", mappedBy="services")
     */
    private $projects;

    /**
     * @ORM\OneToOne(targetEntity="PN\Bundle\SeoBundle\Entity\Seo", inversedBy="service", cascade={"persist", "remove" })
     */
    protected $seo;

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
     *
     * @ORM\Column(name="contact_text", type="text", length=300, nullable=true)
     */
    private $contactText;

    /**
     * @var int
     *
     * @ORM\Column(name="tarteb", type="integer")
     */
    private $tarteb;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\CMSBundle\Entity\Translation\ServiceTranslation", mappedBy="translatable", cascade={"ALL"}, orphanRemoval=true)
     */
    protected $translations;

    public function __toString() {
        return $this->getTitle();
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
     * Constructor
     */
    public function __construct() {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->projects = new ArrayCollection();
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
     * Set seo
     *
     * @param \PN\Bundle\SeoBundle\Entity\Seo $seo
     *
     * @return Service
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
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->addService($this);
        }

        return $this;
    }

//    public function addProject(Project $project) {
//        $this->projects[] = $project;
//    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            $project->removeService($this);
        }

        return $this;
    }

    /**
     * @return Collection|ServiceTranslation[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ServiceTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(ServiceTranslation $translation): self
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getTranslatable() === $this) {
                $translation->setTranslatable(null);
            }
        }

        return $this;
    }

    public function getContactText(): ?string
    {
        return !$this->currentTranslation ? $this->contactText : $this->currentTranslation->getContactText();
    }

    public function setContactText(?string $contactText): self
    {
        $this->contactText = $contactText;

        return $this;
    }
}
