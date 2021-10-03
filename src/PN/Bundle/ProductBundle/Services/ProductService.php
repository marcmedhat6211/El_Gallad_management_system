<?php

namespace PN\Bundle\ProductBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use PN\Bundle\UserBundle\Services\UserService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;

class ProductService
{

    protected $em;
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function collectSearchData(FormInterface $form)
    {
        $search = new \stdClass;
        $search->deleted = 0;
        $search->string = $form->get("str")->getData();
        $search->selectedCategory = ($form->get("category")->getData()) ? $form->get("category")->getData() : null;
        $search->publish = $form->get("publish")->getData();
        $search->featured = $form->get("featured")->getData();
        $search->newArrival = $form->get("newArrival")->getData();
//        $search->collection = ($form->get("collection")->getData()) ? $form->get("collection")->getData() : null;
//        $search->occasion = ($form->get("occasion")->getData()) ? $form->get("occasion")->getData() : null;

        $search->categories = [];
        $categories = $form->get("category")->getData();
        if ($categories instanceof ArrayCollection and count($categories) > 0) {
            foreach ($categories as $category) {
                $search->categories[] = $category->getId();
            }
        }

        return $search;
    }
}
