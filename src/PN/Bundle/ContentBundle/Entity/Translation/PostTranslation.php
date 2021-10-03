<?php

namespace PN\Bundle\ContentBundle\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use PN\ContentBundle\Entity\Translation\PostTranslation as BasePostTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="post_translations")
 */
class PostTranslation extends BasePostTranslation {

    /**
     * @var
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PN\Bundle\ContentBundle\Entity\Post", inversedBy="translations")
     */
    protected $translatable;

}
