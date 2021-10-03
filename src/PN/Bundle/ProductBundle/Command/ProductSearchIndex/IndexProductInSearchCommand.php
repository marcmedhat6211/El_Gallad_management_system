<?php

namespace PN\Bundle\ProductBundle\Command\ProductSearchIndex;

use PN\Bundle\ProductBundle\Entity\PrepareProductSearchIndex;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Entity\ProductSearch;
use PN\Bundle\ProductBundle\Services\ProductSearchService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexProductInSearchCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'app:index-product-search';

    protected function configure()
    {
        $this
            ->setDescription('index product in search and remove unused products')
            ->setHelp('Run every  day');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->removeUnusedProducts();
        $this->updateProductHasOfferAndExpired($output);
        $this->addProductInSearch($output);

    }

    private function addProductInSearch(OutputInterface $output = null)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $limit = 500;

        $entities = $em->getRepository(PrepareProductSearchIndex::class)->findBy([], [], $limit);
        $progressBar = new ProgressBar($output, count($entities));
        foreach ($entities as $entity) {
            $progressBar->advance();
            $product = $em->getRepository(Product::class)->find($entity->getProduct());
            $this->getContainer()->get(ProductSearchService::class)->insertOrDeleteProductInSearch($product);
            $em->remove($entity);
            $em->flush();
        }
        $progressBar->finish();

    }

    private function removeUnusedProducts()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->getRepository(ProductSearch::class)->deleteUnUsedProducts();
    }

    private function updateProductHasOfferAndExpired(OutputInterface $output = null)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $limit = 500;

        $sql = "SELECT p.id
                FROM product AS p LEFT JOIN product_prices AS pp
                ON p.id = pp.product_id
                WHERE p.deleted IS NULL
                AND p.publish IS NOT NULL
                AND pp.promotional_expiry_date = CURDATE()
                GROUP BY p.id
                LIMIT $limit";

        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $results = $statement->fetchAll();

        foreach ($results as $value) {
            $productId = $value['id'];
            $product = $em->getRepository(Product::class)->find($productId);
            $this->getContainer()->get(ProductSearchService::class)->insertOrDeleteProductInSearch($product);
        }

    }
}
