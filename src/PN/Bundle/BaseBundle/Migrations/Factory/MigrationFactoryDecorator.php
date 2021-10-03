<?php

declare(strict_types=1);

namespace PN\Bundle\BaseBundle\Migrations\Factory;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MigrationFactoryDecorator implements MigrationFactory
{
    /** @var ContainerInterface */
    private $container;

    /** @var MigrationFactory */
    private $migrationFactory;

    public function __construct(MigrationFactory $migrationFactory, ContainerInterface $container)
    {
        $this->container = $container;
        $this->migrationFactory = $migrationFactory;

    }

    public function createVersion(string $migrationClassName): AbstractMigration
    {
        $instance = $this->migrationFactory->createVersion($migrationClassName);

        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($this->container);
        }

        return $instance;
    }
}