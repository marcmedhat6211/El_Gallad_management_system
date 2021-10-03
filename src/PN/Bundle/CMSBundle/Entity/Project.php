<?php

namespace PN\Bundle\CMSBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\CMSBundle\Entity\Translation\ProjectTranslation;
use PN\Bundle\ContentBundle\Entity\Post;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\SeoBundle\Entity\Seo;
use PN\LocaleBundle\Model\LocaleTrait;
use PN\ServiceBundle\Interfaces\DateTimeInterface;
use PN\ServiceBundle\Model\DateTimeTrait;
use PN\ServiceBundle\Model\VirtualDeleteTrait;
use VM5\EntityTranslationsBundle\Model\Translatable;

/**
 * Project
 *
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="PN\Bundle\CMSBundle\Repository\ProjectRepository")
 */
class Project implements Translatable {

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
     * @ORM\ManyToMany(targetEntity="PN\Bundle\CMSBundle\Entity\Service", inversedBy="projects")
     * @ORM\JoinTable(name="projects_services")
     */
    private $services;

    /**
     * @ORM\ManyToMany(targetEntity="PN\Bundle\ProductBundle\Entity\Product", inversedBy="projects")
     * @ORM\JoinTable(name="projects_products")
     */
    private $products;

    /**
     * Description
     * Brief
     * Gallery
     *
     * @ORM\OneToOne(targetEntity="PN\Bundle\ContentBundle\Entity\Post", inversedBy="project", cascade={"persist", "remove" })
     */
    protected $post;

    /**
     * @ORM\OneToOne(targetEntity="PN\Bundle\SeoBundle\Entity\Seo", inversedBy="project", cascade={"persist", "remove" })
     */
    protected $seo;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="interior_designer_name", type="string", length=255, nullable=true)
     */
    protected $interiorDesignerName;

    /**
     * @var string
     *
     * @ORM\Column(name="client", type="string", length=120, nullable=true)
     */
    protected $client;

    /**
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="project_scope", type="string", length=255, nullable=true)
     */
    protected $projectScope;

    /**
     * @var string
     * @ORM\Column(name="tarteb", type="smallint", nullable=true)
     */
    protected $tarteb;

    /**
     * @var bool
     *
     * @ORM\Column(name="featured", type="boolean")
     */
    private $featured = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="publish", type="boolean")
     */
    private $publish = true;

//    /**
//     * @ORM\ManyToMany(targetEntity="PN\Bundle\ProductBundle\Entity\Product", inversedBy="projects")
//     */
//    protected $relatedProducts;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\CMSBundle\Entity\Translation\ProjectTranslation", mappedBy="translatable", cascade={"ALL"}, orphanRemoval=true)
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
        $this->services = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set subTitle
     *
     * @param string $subTitle
     *
     * @return Project
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
     * Set featured
     *
     * @param boolean $featured
     *
     * @return Blogger
     */
    public function setFeatured($featured) {
        $this->featured = $featured;

        return $this;
    }

    /**
     * Get featured
     *
     * @return bool
     */
    public function getFeatured() {
        return $this->featured;
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
     * Set seo
     *
     * @param \PN\Bundle\SeoBundle\Entity\Seo $seo
     *
     * @return Project
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
     * Set tarteb
     *
     * @param integer $tarteb
     *
     * @return Project
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

//    /**
//     * Add relatedProduct
//     *
//     * @param \PN\Bundle\ProductBundle\Entity\Product $relatedProduct
//     *
//     * @return Project
//     */
//    public function addRelatedProduct(\PN\Bundle\ProductBundle\Entity\Product $relatedProduct) {
//        $this->relatedProducts[] = $relatedProduct;
//
//        return $this;
//    }
//
//    /**
//     * Remove relatedProduct
//     *
//     * @param \PN\Bundle\ProductBundle\Entity\Product $relatedProduct
//     */
//    public function removeRelatedProduct(\PN\Bundle\ProductBundle\Entity\Product $relatedProduct) {
//        $this->relatedProducts->removeElement($relatedProduct);
//    }
//
//    /**
//     * Get relatedProducts
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getRelatedProducts() {
//        return $this->relatedProducts;
//    }


    /**
     * Set interiorDesignerName
     *
     * @param string $interiorDesignerName
     *
     * @return Project
     */
    public function setInteriorDesignerName($interiorDesignerName) {
        $this->interiorDesignerName = $interiorDesignerName;

        return $this;
    }

    /**
     * Get interiorDesignerName
     *
     * @return string
     */
    public function getInteriorDesignerName() {
        return $this->interiorDesignerName;
    }

    /**
     * Set projectScope
     *
     * @param string $projectScope
     *
     * @return Project
     */
    public function setProjectScope($projectScope) {
        $this->projectScope = $projectScope;

        return $this;
    }

    /**
     * Get projectScope
     *
     * @return string
     */
    public function getProjectScope() {
        return $this->projectScope;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(?string $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection|ProjectTranslation[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(ProjectTranslation $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setTranslatable($this);
        }

        return $this;
    }

    public function removeTranslation(ProjectTranslation $translation): self
    {
        if ($this->translations->removeElement($translation)) {
            // set the owning side to null (unless already changed)
            if ($translation->getTranslatable() === $this) {
                $translation->setTranslatable(null);
            }
        }

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        $this->services->removeElement($service);

        return $this;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        dump($this->products);
//        if (!$this->products->contains($product)) {
            $this->products[] = $product;
//        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->products->removeElement($product);

        return $this;
    }
}
