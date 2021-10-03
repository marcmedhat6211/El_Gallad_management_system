<?php

namespace PN\Bundle\CMSBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\CMSBundle\Entity\Banner;
use PN\LocaleBundle\Entity\Language;
use VM5\EntityTranslationsBundle\Model\EditableTranslation;
use PN\LocaleBundle\Model\TranslationEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="banner_translations")
 */
class BannerTranslation extends TranslationEntity implements EditableTranslation {

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
    protected $actionButton = 'عرض';

    /**
     * @var 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\Bundle\CMSBundle\Entity\Banner", inversedBy="translations")
     */
    protected $translatable;

    /**
     * @var Language
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\LocaleBundle\Entity\Language")
     */
    protected $language;


    /**
     * Set title
     *
     * @param string $title
     *
     * @return BannerTranslation
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set subTitle
     *
     * @param string $subTitle
     *
     * @return BannerTranslation
     */
    public function setSubTitle($subTitle)
    {
        $this->subTitle = $subTitle;

        return $this;
    }

    /**
     * Get subTitle
     *
     * @return string
     */
    public function getSubTitle()
    {
        return $this->subTitle;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return BannerTranslation
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return BannerTranslation
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    public function getActionButton(): ?string
    {
        return $this->actionButton;
    }

    public function setActionButton(?string $actionButton): self
    {
        $this->actionButton = $actionButton;

        return $this;
    }
}
