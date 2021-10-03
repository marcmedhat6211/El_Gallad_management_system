<?php

namespace PN\Bundle\CMSBundle\Services;

use Doctrine\ORM\EntityManagerInterface;

class BannerService {

    protected $em;
    protected $container;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function getBanners($placement, $limit = 3) {
        $search = new \stdClass;
        $search->ordr = ['dir' => NULL, 'column' => 5];
        $search->placement = $placement;
        return $this->em->getRepository('CMSBundle:Banner')->filter($search, FALSE, 0, $limit);
    }

    public function getOneBanner($placement) {
        $search = new \stdClass;
        $search->ordr = ['dir' => NULL, 'column' => 5];
        $search->placement = $placement;
        $banners = $this->em->getRepository('CMSBundle:Banner')->filter($search, FALSE, 0, 1);
        if (count($banners) > 0) {
            return $banners[0];
        }

        return null;
    }
}
