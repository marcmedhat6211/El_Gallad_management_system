<?php

namespace PN\Bundle\ProductBundle\Controller\FrontEnd;

use PN\Bundle\BaseBundle\Controller\AbstractController;
use PN\Bundle\CMSBundle\Lib\Paginator;
use PN\Bundle\ProductBundle\Entity\Category;
use PN\Bundle\ProductBundle\Entity\Collection;
use PN\Bundle\ProductBundle\Entity\Occasion;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Entity\ProductHasAttribute;
use PN\Bundle\ProductBundle\Entity\ProductPrice;
use PN\Bundle\ProductBundle\Entity\ProductPriceRange;
use PN\Bundle\ProductBundle\Entity\ProductSearch;
use PN\Bundle\ProductBundle\Entity\SubAttribute;
use PN\Bundle\ProductBundle\Services\ProductSearchService;
use PN\Bundle\UserBundle\Entity\User;
use PN\SeoBundle\Entity\SeoPage;
use stdClass;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Product controller.
 *
 * @Route("/")
 */
class ProductController extends AbstractController
{

    /**
     * @Route("/filter/{page}/{sort}", requirements={"page" = "\d+","sort" = "\d+"}, name="fe_filter_product", methods={"GET"})
     * @Route("/featured/{page}/{sort}", requirements={"page" = "\d+","sort" = "\d+"}, name="fe_filter_product_featured", methods={"GET"})
     * @Route("/filter/category/{slug}/{page}/{sort}", requirements={"page" = "\d+","sort" = "\d+"}, name="fe_filter_category", methods={"GET"})
     * @Route("/collection/{slug}/{page}/{sort}", requirements={"page" = "\d+","sort" = "\d+"}, name="fe_product_collection", methods={"GET"})
     * @Route("/occasion/{slug}/{page}/{sort}", requirements={"page" = "\d+","sort" = "\d+"}, name="fe_product_occasion", methods={"GET"})
     */
    public function filterAction(Request $request, $page = 1, $slug = null, $sort = 1)
    {
        $categoryParents = $filterCategories = [];
        $categories = $this->getCategoriesList();
        $category = $seoPage = $featured = null;
        $search = $this->collectFilterParams($request);



        switch ($sort) {
            case 1:
                $search->ordr = ['column' => 0, "dir" => 'DESC'];
                break;
            case 2:
                $search->ordr = ['column' => 1, "dir" => 'DESC'];
                break;
            case 3:
                $search->ordr = ['column' => 2, "dir" => 'DESC'];
                break;
            case 4:
                $search->ordr = ['column' => 2, "dir" => 'ASC'];
                break;
        }

        $routeName = $request->get('_route');

        if (in_array($routeName, ['fe_filter_product_featured'])) { // filter featured products
            $featured = true;
        } elseif (in_array($routeName, ['fe_filter_category'])) { // filter product of specific category
            $category = $this->get("fe_seo")->getSlug($request, $slug, new Category());
            if ($category instanceof RedirectResponse) {
                return $category;
            }
            if (!$category) {
                throw $this->createNotFoundException();
            }
            $seoPage = $category;

        }
        if (count($filterCategories) == 0) {
            $filterCategories = $this->getCategories($search, $category);
        }
        if (!$seoPage) {
            $seoPage = $this->em()->getRepository('PNSeoBundle:SeoPage')->find(2);
        }

        $count = $this->em()->getRepository(ProductSearch::class)->filter($search, true);
        $paginator = new Paginator($count, $page, 24);

        $products = $this->em()->getRepository(ProductSearch::class)->filter($search, false,
            $paginator->getLimitStart(), $paginator->getPageLimit());

        $attributes = $this->getSpecs($category, $search);
        $prices = $this->em()->getRepository(ProductSearch::class)->getMaxAndMinPricesForFilter($search);

        return $this->render("product/frontEnd/product/filter.html.twig", [
            'entities' => $products,
            'categories' => $categories,
            'search' => $search,
            'paginator' => $paginator->getPagination(),
            'singleCategory' => $category,
            'featured' => $featured,
            'seoPage' => $seoPage,
            //            'categoryParents' => $categoryParents,
            "filterCategories" => $filterCategories,
            'attributes' => $attributes,
            'request' => $request,
            'minPrice' => $prices["min_price"],
            'maxPrice' => $prices["max_price"],
            'productsCount' => $count,
        ]);
    }

    /**
     * @Route("/show/{slug}", name="fe_product_show", methods={"GET"})
     */
    public function showAction(Request $request, $slug)
    {
        $product = $this->get("fe_seo")->getSlug($request, $slug, new Product());
        if ($product instanceof RedirectResponse) {
            return $product;
        }

        if (!$product) {
            throw $this->createNotFoundException();
        }

        // setting product price
        $product->minPrice = $this->getMinimumProductPrice($product);

        //getting product prices
        $productPrices = $this->getProductPrices($product);

        //setting the saved percentage for the product prices
        $this->calculateSaleOnEachProductPrice($product);

        $response = new Response();
        $specs = $this->getProductSpecs($product);

        return $this->render("product/frontEnd/product/show.html.twig", [
            'request' => $request,
            'product' => $product,
            'specs' => $specs,
            'productPrices' => $productPrices,
            'isOnSale' => $this->isProductOnSale($productPrices),
            'relatedProducts' => $this->getRelatedProducts($product),
        ], $response);
    }

    private function getMinimumProductPrice(Product $product)
    {
        $productPrices = $product->getProductPrices();
        $minPrice = PHP_INT_MAX;
        if (!empty($productPrices)) {
            foreach ($productPrices as $productPrice) {
                if ($productPrice->getSellPrice() < $minPrice) {
                    $minPrice = $productPrice->getSellPrice();
                }
            }
        }

        return $minPrice;
    }

    private function isProductOnSale($productPrices) {
        $isOnSale = false;

        foreach ($productPrices as $productPrice) {
            if ($productPrice->getSellPrice() < $productPrice->getUnitPrice()) {
                $isOnSale = true;
            }
        }

        return $isOnSale;
    }

    private function getProductPrices(Product $product) {
        $search = new \stdClass();
        $search->ordr = ["column" => 1, "dir" => "ASC"];
        $search->deleted = 0;
        $search->product = $product->getId();

        $productPrices = $this->em()->getRepository(ProductPrice::class)->filter($search, false);
        foreach ($productPrices as $productPrice) {
            $productPrice->sellPrice = $productPrice->getSellPrice();
        }

        return $productPrices;
    }

    private function calculateSaleOnEachProductPrice(Product $product) {
        $productPrices = $product->getProductPrices();
        $maxProductPrice = $this->getMaxProductPrice($product);
        foreach($productPrices as $productPrice) {
            $sellingPrice = $productPrice->getSellPrice();
            $savedPercentage = (int) round((1 - ($sellingPrice / $maxProductPrice)) * 100);
            $productPrice->savedPercentage = $savedPercentage;
        }
    }

    private function getMaxProductPrice(Product $product) {
        $productPrices = $product->getProductPrices();
        $max = 0;
        foreach($productPrices as $productPrice) {
            $sellingPrice = $productPrice->getSellPrice();
            if ($sellingPrice > $max) $max = $sellingPrice;
        }

        return $max;
    }

    private function getRelatedProducts(Product $product) {
        $search = new stdClass;
        $search->ordr = ["column" => 0, "dir" => "ASC"];
        $search->deleted = 0;
        $search->publish = 1;
        $search->category = $product->getCategory();
        $search->notId = $product->getId();

        return $this->em()->getRepository(ProductSearch::class)->filter($search, false, 0, 8);
    }

    private function getSpecs(Category $category = null, \stdClass $search = null)
    {
        if ($category == null and (!is_array($search->categories) or count($search->categories) > 1)) {
            return [];
        }

        if (is_array($search->categories) and count($search->categories) == 1 and $category == null) {
            $category = $this->em()->getRepository(Category::class)->find($search->categories[0]);
        }

        return $this->em()->getRepository(ProductSearch::class)->getSpecsByCategory($category, $search);
    }

    private function getCategoriesList()
    {
        $search = new stdClass;
        $search->ordr = ["column" => 1, "dir" => "ASC"];
        $search->deleted = 0;

        return $this->em()->getRepository(Category::class)->filter($search, false);
    }

    private function getCategories(\stdClass $search = null, ?Category $category = null)
    {
        $clonedSearch = clone $search;
        if ($category and $category->getParent() == null) {
            $clonedSearch->categoryLevelOne = $category->getId();
            $clonedSearch->categories = null;
        } elseif ($category and $category->getParent()) {
            return [];
        }

        return $this->em()->getRepository(ProductSearch::class)->getCategoriesByFilter($clonedSearch);
    }

    private function collectFilterParams(Request $request): stdClass
    {
        $requestParams = ($request->isMethod("POST")) ? $request->request : $request->query;

        $search = new \stdClass();
        $search->specs = $requestParams->get('specs'); //
        $search->priceFrom = $request->query->get('minPrice');
        $search->priceTo = $request->query->get('maxPrice');
        $search->string = $requestParams->get('q');
        $search->categories = $requestParams->get('categories');

        $routeName = $request->get('_route');
        $slug = $request->attributes->get('slug');

        if (in_array($routeName, ['fe_filter_product_featured', 'fe_filter_product_featured_ajax'])) {
            $search->featured = true;
        } elseif (in_array($routeName,
            ['fe_filter_category', 'fe_filter_category_ajax'])) { // filter product of specific category
            if ($slug == null) {
                throw $this->createNotFoundException();
            }

            $category = $this->get("fe_seo")->getSlug($request, $slug, new Category());
            if ($category instanceof Category) {
                $search->category = $category->getId();
            }

        }

        return $search;
    }

    private function getProductSpecs(Product $product): array
    {
        $specsAsText = [];
        $specs = $this->em()->getRepository(ProductHasAttribute::class)->findProduct($product);
        foreach ($specs as $spec) {
            $attribute = $spec->getAttribute();
            $subAttribute = $spec->getSubAttribute();
            $value = ($spec->getOtherValue()) ? $spec->getOtherValue() : $subAttribute->getTitle();

            if (array_key_exists($attribute->getId(), $specsAsText)) {
                $specsAsText[$attribute->getId()]['value'] = $specsAsText[$attribute->getId()]['value'] . ", " . $value;
            } else {
                $specsAsText[$attribute->getId()] = [
                    "title" => $attribute->getTitle(),
                    "value" => $value,
                ];
            }
        }

        return $specsAsText;
    }
}
