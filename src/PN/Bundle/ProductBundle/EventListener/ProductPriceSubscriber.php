<?php

namespace PN\Bundle\ProductBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use PN\Bundle\ProductBundle\DTO\ProductDTOService;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Entity\ProductPrice;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductPriceSubscriber implements EventSubscriber
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
            Events::preUpdate,
        ];

    }

    // callback methods must be called exactly like the events they listen to;
    // they receive an argument of type LifecycleEventArgs, which gives you access
    // to both the entity object of the event and the entity manager itself
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof ProductPrice) {
            return;
        }
        if ($args->hasChangedField('deleted') and $args->getOldValue('deleted')==null and $args->getNewValue('deleted') instanceof \DateTime) {
            $userName = $this->container->get('user')->getUserName();
            $entity->setDeletedBy($userName);
        }
    }

}
