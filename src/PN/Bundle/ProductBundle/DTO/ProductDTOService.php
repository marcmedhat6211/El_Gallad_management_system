<?php

namespace PN\Bundle\ProductBundle\DTO;

use PN\Bundle\MediaBundle\Entity\Image;
use PN\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductDTOService
{
    protected $em;
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function get(Product $object)
    {
        if ($object == null) {
            return $object;
        }
        $object->mainImage = $this->getMainImage($object);
        $object->images = $this->getImages($object);

        return $object;
    }

    public function getMainImage(Product $product): ?Image
    {
        if ($product->getPost()) {
            $mainImage = $product->getPost()->getMainImage();

            return ($mainImage instanceof Image) ? $mainImage : null;
        }

        return null;
    }

    private function getImages(Product $product): array
    {
        $images = [];
        if ($product->getPost()) {
            $images = $product->getPost()->getImages()->toArray();
        }

        return $images;
    }
}
