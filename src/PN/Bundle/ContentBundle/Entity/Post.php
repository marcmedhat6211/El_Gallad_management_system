<?php

namespace PN\Bundle\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use VM5\EntityTranslationsBundle\Model\Translatable;
use PN\ContentBundle\Entity\Post as BasePost;
use PN\ContentBundle\Model\PostTrait;

/**
 * Post
 * @ORM\Table(name="post")
 * @ORM\Entity(repositoryClass="PN\Bundle\CMSBundle\Repository\PostRepository")
 */
class Post extends BasePost implements Translatable {

    use PostTrait;

    /**
     * @ORM\OneToMany(targetEntity="PN\Bundle\ContentBundle\Entity\Translation\PostTranslation", mappedBy="translatable", cascade={"ALL"}, orphanRemoval=true)
     */
    protected $translations;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\CMSBundle\Entity\DynamicPage", mappedBy="post")
     */
    protected $dynamicPage;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\CMSBundle\Entity\Blogger", mappedBy="post")
     */
    protected $blogger;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\CMSBundle\Entity\Project", mappedBy="post")
     */
    protected $project;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\CMSBundle\Entity\Service", mappedBy="post")
     */
    protected $service;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\ProductBundle\Entity\Product", mappedBy="post")
     */
    protected $product;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\ProductBundle\Entity\Collection", mappedBy="post")
     */
    protected $collection;

    /**
     * @ORM\OneToOne(targetEntity="PN\Bundle\ProductBundle\Entity\Occasion", mappedBy="post")
     */
    protected $occasion;

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
//            $this->images = null;
        }
    }
}
