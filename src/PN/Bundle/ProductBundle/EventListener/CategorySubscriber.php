<?php

namespace PN\Bundle\ProductBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use PN\Bundle\ProductBundle\Entity\Category;
use PN\Bundle\ProductBundle\Entity\Product;

class CategorySubscriber implements EventSubscriber
{

    // this method can only return the event names; you cannot define a
    // custom method name to execute when each event triggers
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postRemove,
            Events::postUpdate,
        ];
    }

    // callback methods must be called exactly like the events they listen to;
    // they receive an argument of type LifecycleEventArgs, which gives you access
    // to both the entity object of the event and the entity manager itself
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->updateNoOfProducts($args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->updateNoOfProducts($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->updateNoOfProducts($args);
    }

    private function updateNoOfProducts(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Product) {
            return;
        }
        $em = $args->getObjectManager();

        $category = $entity->getCategory();
        while ($category) {
            $noOfAllProducts = $this->updateNoOfAllProducts($category, $em);
            $category->setNoOfProducts($noOfAllProducts);

            $noOfPublishedProducts = $this->updateNoOfPublishedProducts($category, $em);
            $category->setNoOfPublishProducts($noOfPublishedProducts);
            $em->persist($category);

            $category = $category->getParent();

        }
        $em->flush();

    }

    private function updateNoOfAllProducts(Category $category, ObjectManager $em): int
    {
        $search = new \stdClass();
        $search->deleted = 0;
        $search->category = $category->getId();

        return $em->getRepository(Product::class)->filter($search, true);
    }

    private function updateNoOfPublishedProducts(Category $category, ObjectManager $em): int
    {
        $search = new \stdClass();
        $search->deleted = 0;
        $search->publish = true;
        $search->category = $category->getId();

        return $em->getRepository(Product::class)->filter($search, true);
    }
}
