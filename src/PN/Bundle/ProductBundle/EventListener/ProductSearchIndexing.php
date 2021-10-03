<?php

namespace PN\Bundle\ProductBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Entity\ProductDetails;
use PN\Bundle\ProductBundle\Entity\ProductPrice;
use PN\Bundle\ProductBundle\Services\ProductSearchService;
use PN\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductSearchIndexing implements EventSubscriber
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    // this method can only return the event names; you cannot define a
    // custom method name to execute when each event triggers
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
//            Events::preUpdate,
        ];

    }

    // callback methods must be called exactly like the events they listen to;
    // they receive an argument of type LifecycleEventArgs, which gives you access
    // to both the entity object of the event and the entity manager itself
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->indexingProduct($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();


        if (!$entity instanceof Product and !$entity instanceof ProductDetails and !$entity instanceof ProductPrice) {
            return;
        }
        $this->indexingProduct($args);
    }

    private function indexingProduct(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        // if this subscriber only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if ($entity instanceof ProductDetails) {
            $product = $entity->getProduct();
            $this->container->get(ProductSearchService::class)->prepareProductToIndex($product);
        } elseif ($entity instanceof Product) {
            $product = $entity;
            $this->container->get(ProductSearchService::class)->prepareProductToIndex($product);
        } elseif ($entity instanceof ProductPrice) {
            $product = $entity->getProduct();
            $this->container->get(ProductSearchService::class)->prepareProductToIndex($product);
        }
    }
}
