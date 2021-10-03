<?php

namespace PN\Bundle\ProductBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PN\Bundle\CMSBundle\Entity\Project;
use PN\Bundle\CurrencyBundle\Entity\Currency;
use PN\Bundle\CurrencyBundle\Entity\ExchangeRate;
use PN\Bundle\NewShippingBundle\Entity\SiteCountry;
use PN\Bundle\ProductBundle\Entity\Attribute;
use PN\Bundle\ProductBundle\Entity\Category;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Entity\ProductSearch;
use PN\Bundle\ProductBundle\Entity\SubAttribute;
use PN\ServiceBundle\Utils\Date;
use PN\ServiceBundle\Utils\Validate;
use stdClass;
use Symfony\Component\HttpFoundation\Session\Session;

class ProductSearchRepository extends EntityRepository
{

    public function deleteByProduct(Product $product)
    {
        return $this->createQueryBuilder("ps")
            ->delete()
            ->andWhere("ps.product = :productId")
            ->setParameter("productId", $product->getId())
            ->getQuery()->execute();
    }

    public function deleteUnUsedProducts()
    {
        $ids = [];
        $productsIds = $this->createQueryBuilder('ps')
            ->select('p.id')
            ->leftJoin("ps.product", "p")
            ->orWhere("p.publish = 0")
            ->orWhere("p.deleted IS NOT NULL")
            ->getQuery()->getResult();

        foreach ($productsIds as $productsId) {
            $ids[] = $productsId['id'];
        }

        return $this->createQueryBuilder("ps")
            ->delete()
            ->where('ps.product in (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()->execute();
    }

    /**
     * Get all categories has product with filter parameters
     * @param stdClass|null $search
     * @return array
     */
    public function getCategoriesByFilter(stdClass $search = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $statement = $qb->from(Category::class, "cat")
            ->select("cat")
            ->addSelect("count(DISTINCT ps.product) noOfProducts")
            ->leftJoin("cat.products", 'p')
            ->leftJoin("p.search", 'ps')
            ->leftJoin('p.productHasCollections', 'phc')
            ->leftJoin("phc.collection", "c");

        if ($search != null) {
            $this->filterWhereClause($statement, $search);
        }

        $statement->groupBy("cat.id")
            ->andHaving("noOfProducts > 0");

        $results = $statement->getQuery()->execute();
        $return = [];
        foreach ($results as $row) {
            $obj = $row[0];
            $obj->noOfProductss = $row['noOfProducts'];
            $return[] = $obj;
        }

        return $return;
    }

    /**
     * Get all brands has product with category and filter parameters
     * @param stdClass|null $search
     * @return mixed
     */
    public function getSpecsByCategory(Category $category, stdClass $search = null)
    {

        $choicesSpecs = $this->getChoicesSpecsByCategory($category, $search);
        $textSpecs = $this->getTextSpecsByCategory($category);

        $allSpecs = array_merge($choicesSpecs, $textSpecs);
        $sortArray = [];
        foreach ($allSpecs as $choice) {
            $sortArray[] = [
                "attribute" => $choice,
                "sort" => $choice->getTarteb() == null ? 100 : $choice->getTarteb(),
            ];
        }
        uasort($sortArray, function ($a, $b) {
            if ($a['sort'] == $b['sort']) {
                return 0;
            }

            return ($a['sort'] < $b['sort']) ? -1 : 1;
        });

        $return = [];
        foreach ($sortArray as $sortedNod) {
            $return[] = $sortedNod['attribute'];
        }

        return $return;
    }

    private function getTextSpecsByCategory(Category $category)
    {
        $statement2 = $this->_em->createQueryBuilder()
            ->select("a")
            ->from(Attribute::class, "a")
            ->andWhere("a.type IN (:types)")
            ->andWhere("a.search = :search")
            ->andWhere("a.category = :categoryId")
            ->andWhere("a.deleted IS NULL")
            ->setParameter("search", true)
            ->setParameter("categoryId", $category->getId())
            ->setParameter("types", [Attribute::TYPE_NUMBER, Attribute::TYPE_TEXT]);

        return $statement2->getQuery()->execute();
    }

    private function getChoicesSpecsByCategory(Category $category, stdClass $search = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $statement = $qb
            ->select("sa, a")
            ->addSelect("count(DISTINCT ps.product) noOfProducts")
            ->addSelect("-a.tarteb AS HIDDEN inverseTarteb")
            ->from(SubAttribute::class, "sa")
            ->leftJoin("sa.attribute", 'a')
            ->leftJoin("sa.productHasAttributes", 'pha')
            ->leftJoin(ProductSearch::class, 'ps', "WITH", "pha.product=ps.product")
            ->leftJoin("ps.product", 'p')
            ->leftJoin('p.productHasCollections', 'phc')
            ->leftJoin("phc.collection", "c");

        if ($search != null) {
            $currentSearch = clone $search;
            if (isset($currentSearch->categories)) {
                unset($currentSearch->categories);
            }
            if (isset($currentSearch->category)) {
                unset($currentSearch->category);
            }
            if (isset($currentSearch->specs)) {
                unset($currentSearch->specs);
            }

            $this->filterWhereClause($statement, $currentSearch);
        }

        $statement
            ->andWhere("a.search = :search")
            ->andWhere("a.category = :categoryId")
            ->andWhere("a.deleted IS NULL")
            ->andWhere("sa.deleted IS NULL")
            ->setParameter("search", true)
            ->setParameter("categoryId", $category->getId())
            ->orderBy("inverseTarteb", "DESC")
            ->addOrderBy("a.id", "ASC")
            ->addOrderBy("sa.title+0", "ASC")
            ->groupBy("sa.id")
            ->andHaving("noOfProducts > 0");

        $results = $statement->getQuery()->execute();

        $returnChoices = [];
        foreach ($results as $row) {
            $subAttribute = $row[0];
            $subAttribute->noOfProducts = $row['noOfProducts'];
            $attribute = $subAttribute->getAttribute();
            $attribute->subSpecs[] = $subAttribute;
            $returnChoices[$attribute->getId()] = $attribute;
        }

        return $returnChoices;
    }

    private function getStatement(stdClass $search = null)
    {
        $statement = $this->_em->createQueryBuilder()
            ->select("p")
            ->addSelect("ps")
            ->from(Product::class, "p")
            ->innerJoin("p.search", "ps")
            ->leftJoin('p.productHasCollections', 'phc')
            ->leftJoin("phc.collection", "c");


        return $statement;
    }

    private function filterOrder(QueryBuilder $statement, stdClass $search)
    {
        $session = new Session();
        if ($session->has("order-rand-number")) {
            $orderRandNumber = $session->get("order-rand-number");
        } else {
            $orderRandNumber = rand(0, 100);
            $session->set("order-rand-number", $orderRandNumber);
        }
        $sortSQL = [
            0 => 'ps.recommendedSort',
            1 => 'ps.product',
            2 => 'ps.minPrice',
        ];

        if (isset($search->ordr) and Validate::not_null($search->ordr)) {
            $dir = $search->ordr['dir'];
            $columnNumber = $search->ordr['column'];
            if (isset($columnNumber) and array_key_exists($columnNumber, $sortSQL)) {
                $statement->addOrderBy($sortSQL[$columnNumber], $dir);
            }
        } else {
            //            $statement->orderBy($sortSQL[1], "DESC");
            $statement->addOrderBy($sortSQL[1]);
        }
    }

    private function filterWhereClause(QueryBuilder $statement, stdClass $search)
    {
        //        if (isset($search->string) and Validate::not_null($search->string)) {
        //            $statement->addSelect("MATCH_AGAINST (ps.normalizedTxt, :searchTerm ) as score");
        //            $statement->andWhere("MATCH_AGAINST(ps.normalizedTxt, :searchTerm) > 0.5");
        //
        //            $normalizeStr = SearchText::normalizeKeyword($search->string);
        //            if (!isset($search->autocomplete) or $search->autocomplete == false) {
        //                // insert in searched keywords
        //                $this->getEntityManager()->getRepository(SearchKeyword::class)->insert($normalizeStr);
        //            }
        //
        //            $statement->setParameter('searchTerm', trim($normalizeStr));
        //            if ($search->count == false) {
        //                $statement->addOrderBy('score', 'desc');
        //            }
        //        }
        if (isset($search->string) and Validate::not_null($search->string)) {
            $statement->andWhere('ps.normalizedTxt LIKE :searchTerm '
            );
            $statement->setParameter('searchTerm', '%' . trim($search->string) . '%');
        }

        if (isset($search->featured) and $search->featured != "") {
            $statement->andWhere('ps.featured = :featured');
            $statement->setParameter('featured', $search->featured);
        }

        if (isset($search->id) and $search->id != "") {
            $statement->andWhere('ps.product = :id');
            $statement->setParameter('id', $search->id);
        }

        if (isset($search->notId) and $search->notId != "") {
            $statement->andWhere('ps.product <> :id');
            $statement->setParameter('id', $search->notId);
        }

        if (isset($search->minPrice) and $search->minPrice != "") {
            $statement->andWhere('ps.minPrice = :minPrice');
            $statement->setParameter('minPrice', $search->minPrice);
        }

        if (isset($search->maxPrice) and $search->maxPrice != "") {
            $statement->andWhere('ps.maxPrice = :maxPrice');
            $statement->setParameter('maxPrice', $search->maxPrice);
        }

        if ((isset($search->priceFrom) and $search->priceFrom != "") and (isset($search->priceTo) and $search->priceTo != "")) {
            $statement->andWhere('ps.minPrice BETWEEN :priceFrom AND :priceTo');
            $statement->orWhere('ps.maxPrice BETWEEN :priceFrom AND :priceTo');
            $statement->orWhere(':priceFrom > ps.minPrice AND :priceTo < ps.maxPrice');
            $statement->setParameter('priceFrom', $search->priceFrom);
            $statement->setParameter('priceTo', $search->priceTo);
        }

        if (isset($search->notIds) and is_array($search->notIds) and count($search->notIds) > 0) {
            $statement->andWhere('ps.product NOT IN (:notIds)');
            $statement->setParameter('notIds', $search->notIds);
        }

        if (isset($search->premium) and $search->premium != "") {
            $statement->andWhere('ps.premium = :premium');
            $statement->setParameter('premium', $search->premium);
        }

        if (isset($search->category) and is_integer($search->category)) {
            $statement->andWhere('ps.category = :category');
            $statement->setParameter('category', $search->category);
        }

        if (isset($search->collection) and is_integer($search->collection)) {
            $statement->andWhere('c.id = :collection');
            $statement->setParameter('collection', $search->collection);
        }

        if (isset($search->ids) and is_array($search->ids) and count($search->ids) > 0) {
            $statement->andWhere('ps.product in (:ids)');
            $statement->setParameter('ids', $search->ids);
        }


        if (isset($search->categories) and is_array($search->categories) and count($search->categories) > 0) {
            $statement->andWhere('ps.category IN (:categories)');
            $statement->setParameter('categories', $search->categories);
        }
        if (isset($search->offer) and $search->offer == true) {
            $statement->andWhere('ps.hasOffer = :hasOffer');
            $statement->setParameter('hasOffer', true);
        }

        if (isset($search->newArrival) and (is_bool($search->newArrival) or in_array($search->newArrival, [0, 1]))) {
            $statement->andWhere('ps.newArrival = :newArrival');
            $statement->setParameter('newArrival', $search->newArrival);
        }


        if (isset($search->specs) and is_array($search->specs) and count($search->specs) > 0) {

            foreach ($search->specs as $specsId => $subSpecs) {
                $subSpecsWhere = false;
                $subSpecsClause = [];


                if (is_array($subSpecs)) { // Search in choices
                    foreach ($subSpecs as $subSpecsId) {
                        $subSpecsWhere = ($subSpecsWhere === false) ? " OR " : "";
                        $subSpecsClause[] = "JSON_CONTAINS(ps.specs, :subAttrId".$subSpecsId.", '$.attr_".$specsId."') = 1";
                        $statement->setParameter('subAttrId'.$subSpecsId, $subSpecsId);
                    }
                    $statement->andWhere("(".implode("OR ", $subSpecsClause).")");
                } else { // Search in text and Number
                    $statement->andWhere("JSON_CONTAINS(ps.specs, :value".$specsId.", '$.attr_".$specsId."') = 1");
                    $statement->setParameter('value'.$specsId, $subSpecs);
                }
            }
        }


    }

    private function filterPagination(QueryBuilder $statement, $startLimit = null, $endLimit = null)
    {
        if ($startLimit === null or $endLimit === null) {
            return false;
        }
        $statement->setFirstResult($startLimit)
            ->setMaxResults($endLimit);
    }

    private function filterCount(QueryBuilder $statement, stdClass $search)
    {
        $statement->select("COUNT(DISTINCT ps.product)");
        $statement->setMaxResults(1);

        $count = $statement->getQuery()->getOneOrNullResult();
        if (is_array($count) and count($count) > 0) {
            return (int)reset($count);
        }

        return 0;
    }

    public function filter($search, $count = false, $startLimit = null, $endLimit = null)
    {
        $search->count = $count;
        $statement = $this->getStatement($search);
        $this->filterWhereClause($statement, $search);

        if ($count == true) {
            return $this->filterCount($statement, $search);
        }
        $statement->groupBy('ps.product');
        $this->filterPagination($statement, $startLimit, $endLimit);
        $this->filterOrder($statement, $search);
        $statementResult = $statement->getQuery()->execute();

        $products = [];
        if (is_array($statementResult)) {
            foreach ($statementResult as $row) {
                if (is_array($row)) {
                    $product = $row['0'];
                } else {
                    $product = $row;
                }
                $product->isOnSale = $this->isProductOnSale($product);
                $product->minPrice = $this->getMinimumProductPrice($product);
                $product->maxPrice = $this->getMaximumProductPrice($product);
                array_push($products, $product);
            }
        }

        return $products;
    }
    public function getMaxAndMinPricesForFilter($search)
    {
        $statement = $this->getStatement($search);
        $search->count = true;

        $currentSearch = clone $search;

        if (isset($currentSearch->minPrice)) {
            unset($currentSearch->minPrice);
        }
        if (isset($currentSearch->maxPrice)) {
            unset($currentSearch->maxPrice);
        }


        $this->filterWhereClause($statement, $currentSearch);
        $statement->select("LEAST(MIN(ps.minPrice), MIN(ps.minPrice)) AS minPrice, GREATEST(MAX(ps.maxPrice), MAX(ps.maxPrice)) AS maxPrice");
        $statement->setMaxResults(1);

        $results = $statement->getQuery()->getArrayResult();
        $prices = [];
        if (is_array($results) and count($results) > 0) {
            $prices["min_price"] = $results[0]["minPrice"];
            $prices["max_price"] = $results[0]["maxPrice"];
        }

        return $prices;
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

    private function getMaximumProductPrice(Product $product) {
        $productPrices = $product->getProductPrices();
        $maxPrice = 0;
        if (!empty($productPrices)) {
            foreach ($productPrices as $productPrice) {
                if ($productPrice->getSellPrice() > $maxPrice) {
                    $maxPrice = $productPrice->getSellPrice();
                }
            }
        }

        return $maxPrice;
    }

    private function getPriceColumn(stdClass $search = null, $columnName = "ps.price")
    {
        //        $sql = $columnName;
        //        if (isset($search->userSiteCountry) and $search->userSiteCountry instanceof SiteCountry) {
        $sql = $columnName." * IFNULL(exr.ratio, 1)";

        //        }

        return $sql;
    }

    public function getProductIdsUsingProject(Project $project)
    {
        $em = $this->getEntityManager();
        $projectId = $project->getId();

        $sql = "SELECT product_id
                FROM projects_products
                WHERE project_id = $projectId";

        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $results = $statement->fetchAll();

        $ids = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $ids[] = $result["product_id"];
            }
        }

        return $ids;
    }

    private function isProductOnSale(Product $product): bool
    {
        foreach ($product->getProductPrices() as $productPrice) {
            if($productPrice->getPromotionalPrice() && $productPrice->getPromotionalExpiryDate() > new \DateTime()) {
                return true;
            }
        }

        return false;
    }
}
