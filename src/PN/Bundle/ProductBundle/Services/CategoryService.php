<?php

namespace PN\Bundle\ProductBundle\Services;

use PN\Bundle\ProductBundle\Entity\Category;
use PN\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CategoryService {

    protected $em;
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
    }

    public function parentsByChildId(Category $parent = null) {
        $parents = [];
        if ($parent == null) {
            return $parents;
        }

        $lastParent = NULL;
        if ($parent->getParent() != NULL) {
            do {
                $parents[] = $parent;
                $lastParent = $parent->getParent();
                $parent = $lastParent;
            } while ($parent->getParent() != NULL);
            if ($lastParent != NULL) {
                $parents[] = $lastParent;
            }
        } else {
            $parents[] = $parent;
        }
        return array_reverse($parents);
    }

    public function getCategoryForFancyTree(Product $product = null) {
        $categoriesArr = [];
        $categories = $this->em->getRepository('ProductBundle:Category')->findBy(['parent' => NULL, 'deleted' => NULL]);
        foreach ($categories as $category) {
            $array = $this->fancyTreeCategories($category, $categoriesArr, $product);
            array_push($categoriesArr, $array);
        }
        return $categoriesArr;
    }

    private function fancyTreeCategories(Category $category, array $array, Product $product = null) {
        $productCategoryId = null;
        if ($product != null and $product->getCategory() != null) {
            $productCategoryId = $product->getCategory()->getId();
        }
        if (count($category->getChildren()) > 0) {
            $childrenIds = explode(",", $category->getConcatIds());
            $expanded = false;
            if (in_array($productCategoryId, $childrenIds)) {
                $expanded = true;
            }
            $node = [
                "title" => $category->getTitle(),
                "checkbox" => false,
                "folder" => true,
                "expanded" => $expanded,
                "children" => []
            ];
            foreach ($category->getChildren() as $category) {
                $children = $this->fancyTreeCategories($category, $array, $product);
                $node["children"][] = $children;

            }
        } else {
            $selected = false;

            if ($category->getId() == $productCategoryId) {
                $selected = true;
            }
            $node = [
                "id" => $category->getId(),
                "title" => $category->getTitle(),
                "selected" => $selected,
            ];
        }
        return $node;
    }
}
