<?php

namespace PN\Bundle\ProductBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use PN\Bundle\ProductBundle\Entity\Attribute;
use PN\Bundle\ProductBundle\Entity\PrepareProductSearchIndex;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Entity\ProductSearch;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductSearchService
{

    protected $em;
    protected $container;


    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container)
    {
        $this->em = $entityManager;
        $this->container = $container;
    }

    public function prepareProductToIndex(Product $product)
    {
        $isValid = $this->isValidForSearch($product);

        if ($isValid == false) {
            return false;
        }
        $ifExist = $this->em->getRepository(PrepareProductSearchIndex::class)->find($product);
        if (!$ifExist) {
            $this->em->getRepository(PrepareProductSearchIndex::class)->insert($product);
        }

        return true;
    }

    public function insertOrDeleteProductInSearch(Product $product)
    {
        $isValid = $this->isValidForSearch($product);

        if ($isValid == true) {
            $this->indexProduct($product);
        } else {
            $this->em->getRepository(ProductSearch::class)->deleteByProduct($product);
        }
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function isValidForSearch(Product $product)
    {
        if ($product->getPublish() == false
            or $product->getDeleted() != null) {
            return false;
        }

        return true;
    }

    private function indexProduct(Product $product)
    {
        $productSearch = $this->em->getRepository(ProductSearch::class)->find($product);
        if (!$productSearch) {
            $productSearch = new ProductSearch();
            $productSearch->setProduct($product);
            $productSearch->setCurrency($product->getDetails()->getCurrency());
        }

        $productSearch->setNormalizedTxt($product->getNormalizedTxt());

        $categoryId = ($product->getCategory()) ? $product->getCategory()->getId() : null;
        $productSearch->setCategory($categoryId);

        $minAndMaxPrice = $this->getMinAndMaxPrices($product);

        $productSearch->setFeatured($product->getFeatured());
        $productSearch->setNewArrival($product->getNewArrival());
        $productSearch->setMinPrice($minAndMaxPrice->minPrice);
        $productSearch->setMaxPrice($minAndMaxPrice->maxPrice);
        $this->assignSpecToProductSearch($product, $productSearch);
        $this->em->persist($productSearch);
        $this->em->flush();
    }

    private function assignSpecToProductSearch(Product $product, ProductSearch $productSearch)
    {
        $specs = [];
        $productSpecs = $product->getProductHasAttributes();
        foreach ($productSpecs as $productSpec) {
            $attribute = $productSpec->getAttribute();
            $subAttribute = $productSpec->getSubAttribute();
            if ($attribute->getSearch() == false) {
                continue;
            }
            if ($subAttribute == null) {
                if ($attribute->getType() == Attribute::TYPE_NUMBER) {
                    $otherValue = (float)$productSpec->getOtherValue();
                } else {
                    $otherValue = (string)$productSpec->getOtherValue();
                }
                $specs["attr_" . $attribute->getId()] = $otherValue;
            } else {
                $specs["attr_" . $attribute->getId()][] = $subAttribute->getId();
            }
        }
        $productSearch->setSpecs($specs);
    }

    private function getMinAndMaxPrices(Product $product):\stdClass
    {
        $prices = [];
        foreach ($product->getProductPrices() as $productPrice) {
            $prices[] = $productPrice->getSellPrice();
        }
        $return = new \stdClass();
        $return->minPrice = min($prices);
        $return->maxPrice = max($prices);

        return $return;
    }
}
