<?php

namespace PN\Bundle\BaseBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @author Peter Nassef <peter.nassef@gmail.com>
 * @version 1.0
 */
class VarsExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('youtubeIdFromUrl', [VarsRuntime::class, 'getYoutubeIdFromUrl']),
        ];
    }


    public function getName()
    {
        return 'base.extension';
    }

}
