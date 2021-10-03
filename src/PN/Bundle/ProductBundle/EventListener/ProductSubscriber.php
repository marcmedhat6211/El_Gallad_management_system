<?php

namespace PN\Bundle\ProductBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use PN\Bundle\ProductBundle\DTO\ProductDTOService;
use PN\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductSubscriber implements EventSubscriber
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
            Events::postLoad,
        ];

    }

    // callback methods must be called exactly like the events they listen to;
    // they receive an argument of type LifecycleEventArgs, which gives you access
    // to both the entity object of the event and the entity manager itself
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof Product) {
            return;
        }

        $this->container->get(ProductDTOService::class)->get($entity);
    }

}
