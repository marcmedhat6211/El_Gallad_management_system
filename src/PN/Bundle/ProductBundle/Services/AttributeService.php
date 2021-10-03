<?php

namespace PN\Bundle\ProductBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use PN\Bundle\ProductBundle\Entity\Category;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Form\ProductHasAttributeType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;

class AttributeService
{

    protected $em;
    protected $container;


    public function __construct(ContainerInterface $container, EntityManagerInterface $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public function getSpecsForm(?Category $category, ?Product $product): ?FormInterface
    {
        if ($category == null) {
            return null;
        }

        return $this->createFormBuilderNamed("product", $product)
            ->add('subAttributes', ProductHasAttributeType::class, array(
                "mapped" => false,
                "label" => false,
                'category' => $category,
                'product' => $product,
            ));

    }

    private function createFormBuilderNamed($name, $data = null, array $options = [])
    {
        return $this->container->get('form.factory')->createNamed($name, FormType::class, $data, $options);
    }

}
