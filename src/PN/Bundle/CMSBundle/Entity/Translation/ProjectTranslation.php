<?php

namespace PN\Bundle\CMSBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use PN\Bundle\CMSBundle\Entity\Project;
use PN\LocaleBundle\Entity\Language;
use VM5\EntityTranslationsBundle\Model\EditableTranslation;
use PN\LocaleBundle\Model\TranslationEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="project_translations")
 */
class ProjectTranslation extends TranslationEntity implements EditableTranslation {

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
     * @var string
     *
     * @ORM\Column(name="project_scope", type="string", length=255, nullable=true)
     */
    protected $projectScope;

    /**
     * @var
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\Bundle\CMSBundle\Entity\Project", inversedBy="translations")
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
     * @return Project
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
        return $this->title;
    }

    /**
     * Set interiorDesignerName
     *
     * @param string $interiorDesignerName
     *
     * @return ProjectTranslation
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
     * @return ProjectTranslation
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

    public function getLanguage(): ?Language
    {
        return $this->language;
    }
}
