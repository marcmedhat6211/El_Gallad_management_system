<?php

namespace PN\Bundle\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PN\MediaBundle\Entity\Image as BaseImage;
use PN\MediaBundle\Model\ImageInterface;
use PN\MediaBundle\Model\ImageTrait;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("image")
 * @ORM\Entity(repositoryClass="PN\MediaBundle\Repository\ImageRepository")
 */
class Image extends BaseImage implements ImageInterface {

    use ImageTrait;

    const TYPE_COVER_PHOTO = 3;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\CMSBundle\Entity\Banner", mappedBy="image")
     */
    protected $banner;

    /**
     * @ORM\ManyToMany(targetEntity="\PN\Bundle\ContentBundle\Entity\Post", mappedBy="images")
     */
    protected $posts;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\CMSBundle\Entity\Partner", mappedBy="image")
     */
    protected $partner;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\ProductBundle\Entity\Category", mappedBy="image")
     */
    protected $category;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\CMSBundle\Entity\Team", mappedBy="image")
     */
    protected $team;

    /**
     * @ORM\PreRemove
     */
    public function preRemove() {
        $this->removeUpload();
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
     * Constructor
     */
    public function __construct() {
        $this->posts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set banner
     *
     * @param \PN\Bundle\CMSBundle\Entity\Banner $banner
     *
     * @return Image
     */
    public function setBanner(\PN\Bundle\CMSBundle\Entity\Banner $banner = null) {
        $this->banner = $banner;

        return $this;
    }

    /**
     * Get banner
     *
     * @return \PN\Bundle\CMSBundle\Entity\Banner
     */
    public function getBanner() {
        return $this->banner;
    }

    /**
     * Add post
     *
     * @param \PN\Bundle\ContentBundle\Entity\Post $post
     *
     * @return Image
     */
    public function addPost(\PN\Bundle\ContentBundle\Entity\Post $post) {
        $this->posts[] = $post;

        return $this;
    }

    /**
     * Remove post
     *
     * @param \PN\Bundle\ContentBundle\Entity\Post $post
     */
    public function removePost(\PN\Bundle\ContentBundle\Entity\Post $post) {
        $this->posts->removeElement($post);
    }

    /**
     * Get posts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPosts() {
        return $this->posts;
    }

}
