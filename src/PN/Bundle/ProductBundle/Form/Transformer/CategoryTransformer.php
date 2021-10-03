<?php

namespace PN\Bundle\ProductBundle\Form\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use PN\Bundle\ProductBundle\Entity\Category;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CategoryTransformer implements DataTransformerInterface
{
    private $em;

    //Constructor
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Transforms an object (course) to a string (id).
     */
    public function transform($category)
    {
        if (null === $category) {
            return '';
        }

        return $category->getId();
    }

    /**
     * Transforms a string (id) to an object (category).
     */
    public function reverseTransform($categoryId)
    {
        // no course id? It's optional, so that's ok
        if (!$categoryId) {
            return;
        }

        $category = $this->em->getRepository(Category::class)->find($categoryId);
        if (null === $category) {
            // causes a validation error
            // this message is not shown to the user
            throw new TransformationFailedException(sprintf(
                'An course with id "%s" does not exist!',
                $categoryId
            ));
        }

        return $category;
    }
}