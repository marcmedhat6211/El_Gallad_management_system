<?php

namespace PN\Bundle\SeoBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use PN\SeoBundle\Entity\Translation\SeoTranslation as BaseSeoTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="seo_translations")
 */
class SeoTranslation extends BaseSeoTranslation {

    /**
     * @var
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\Bundle\SeoBundle\Entity\Seo", inversedBy="translations")
     * @ORM\JoinColumn(name="translatable_id", referencedColumnName="id")
     */
    protected $translatable;

}
