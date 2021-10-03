<?php

namespace PN\Bundle\SeoBundle\Entity;

use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping as ORM;
use PN\SeoBundle\Entity\Seo as BaseSeo;
use PN\SeoBundle\Model\SeoTrait;

/**
 * Seo
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("seo", uniqueConstraints={@UniqueConstraint(name="slug_unique", columns={"slug", "seo_base_route_id"})})
 * @ORM\Entity(repositoryClass="PN\Bundle\SeoBundle\Repository\SeoRepository")
 */
class Seo extends BaseSeo {

    use SeoTrait;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\SeoBundle\Entity\Translation\SeoTranslation", mappedBy="translatable", cascade={"ALL"}, orphanRemoval=true)
     */
    protected $translations;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\CMSBundle\Entity\DynamicPage", mappedBy="seo")
     */
    protected $dynamicPage;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\CMSBundle\Entity\Blogger", mappedBy="seo")
     */
    protected $blogger;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\CMSBundle\Entity\Service", mappedBy="seo")
     */
    protected $service;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\CMSBundle\Entity\Project", mappedBy="seo")
     */
    protected $project;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\ProductBundle\Entity\Product", mappedBy="seo")
     */
    protected $product;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\ProductBundle\Entity\Category", mappedBy="seo")
     */
    protected $category;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\ProductBundle\Entity\Collection", mappedBy="seo")
     */
    protected $collection;

    /**
     * @ORM\OneToOne(targetEntity="PN\Bundle\ProductBundle\Entity\Occasion", mappedBy="seo")
     */
    protected $occasion;


    /**
     * Set dynamicPage
     *
     * @param \PN\Bundle\CMSBundle\Entity\DynamicPage $dynamicPage
     *
     * @return Seo
     */
    public function setDynamicPage(\PN\Bundle\CMSBundle\Entity\DynamicPage $dynamicPage = null) {
        $this->dynamicPage = $dynamicPage;

        return $this;
    }

    /**
     * Get dynamicPage
     *
     * @return \PN\Bundle\CMSBundle\Entity\DynamicPage
     */
    public function getDynamicPage() {
        return $this->dynamicPage;
    }

    /**
     * Set blogger
     *
     * @param \PN\Bundle\CMSBundle\Entity\Blogger $blogger
     *
     * @return Seo
     */
    public function setBlogger(\PN\Bundle\CMSBundle\Entity\Blogger $blogger = null) {
        $this->blogger = $blogger;

        return $this;
    }

    /**
     * Get blogger
     *
     * @return \PN\Bundle\CMSBundle\Entity\Blogger
     */
    public function getBlogger() {
        return $this->blogger;
    }

}
