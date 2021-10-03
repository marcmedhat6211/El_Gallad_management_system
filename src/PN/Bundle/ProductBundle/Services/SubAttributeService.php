<?php

namespace PN\Bundle\ProductBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use PN\Bundle\CMSBundle\Lib\Paginator;
use PN\Bundle\ProductBundle\Entity\Attribute;
use PN\Bundle\ProductBundle\Entity\ProductHasAttribute;
use PN\Bundle\ProductBundle\Entity\SubAttribute;
use PN\Bundle\ProductBundle\Form\SubAttributeType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SubAttributeService
{

    protected $em;
    protected $container;


    public function __construct(ContainerInterface $container, EntityManagerInterface $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public function getEditSubAttributesForm(Attribute $attribute, $page = 1)
    {
        $em = $this->em;

        $search = new \stdClass();
        $search->deleted = 0;
        $search->attribute = $attribute->getId();
        $search->ordr = ["column" => 0, "dir" => "DESC"];

        $count = $em->getRepository(SubAttribute::class)->filter($search, true);
        $paginator = new Paginator($count, $page, 20);
        $subAttributes = $em->getRepository(SubAttribute::class)->filter($search, false, $paginator->getLimitStart(),
            $paginator->getPageLimit());
        foreach ($subAttributes as $subAttribute) {
            $noOfUsage = $em->getRepository(ProductHasAttribute::class)->countBySubAttribute($subAttribute);
            $subAttribute->isUsed = ($noOfUsage > 0) ? true : false;
        }
        $form = $this->createFormBuilder($attribute)
            ->setAction($this->generateUrl("sub_attribute_edit", ["id" => $attribute->getId(), "page" => $page]))
            ->add("subAttributes", CollectionType::class, [
                'entry_type' => SubAttributeType::class,
                "data" => $subAttributes,
                "allow_delete" => false,
                "mapped" => false,
                "label"=>false,
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                if (array_key_exists('subAttributes', $data)) {
                    $subAttributes = $data['subAttributes'];
                    $sortedSubAttributes = [];

                    foreach ($subAttributes as $subAttribute) {
                        $sortedSubAttributes[] = $subAttribute;
                    }
                    $data['subAttributes'] = $sortedSubAttributes;
                    $event->setData($data);
                }
            })
            ->getForm();

        $return = new \stdClass();
        $return->paginator = $paginator;
        $return->form = $form;

        return $return;
    }

    private function createFormBuilder($data = null, array $options = [])
    {
        return $this->container->get('form.factory')->createBuilder(FormType::class, $data, $options);
    }

    private function generateUrl($route, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }
}
