<?php

namespace PN\Bundle\ProductBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use PN\Bundle\ProductBundle\Entity\Attribute;
use PN\Bundle\ProductBundle\Entity\Category;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Entity\ProductHasAttribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductHasAttributeType extends AbstractType
{

    private $em;
    private $translator;
    private $product;

    //Constructor
    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->product = $options['product'];
        $product = $options['product'];
        $attributes = [];
        if ($this->product instanceof Product) {
            $attributes = $this->em->getRepository(Attribute::class)->findByProduct($this->product);
        }

        foreach ($attributes as $attribute) {
            $value = null;
            $otherValue = null;

            $attr = ["placeholder" => $attribute->getTitle()];

            $fieldOptions = [
                "label" => $attribute->getTitle(),
                'data' => $value,
                "required" => false,
                "attr" => $attr,
                "data_class" => null,
                'mapped' => false,
            ];
            if ($product != null) {
                //                $productHasAttributes = $this->em->getRepository(ProductHasAttribute::class)->findBy([
                //                    "product" => $product,
                //                    "attribute" => $attribute->getId(),
                //                ]);
                $productHasAttributes = $product->getProductHasAttributesByAttributeId($attribute);
                foreach ($productHasAttributes as $productHasAttribute) {
                    $attributeValue = ($productHasAttribute->getSubAttribute() != null) ? $productHasAttribute->getSubAttribute()->getId() : null;

                    if ($attribute->getType() == Attribute::TYPE_DROPDOWN AND $productHasAttribute->getOtherValue() != null) {
                        $attributeValue = "other";
                        $otherValue = $productHasAttribute->getOtherValue();
                    }
                }
            }
            $fieldOptions['data'] = $value;

            $inputType = null;
            switch ($attribute->getType()) {
                case Attribute::TYPE_DROPDOWN:
                    $fieldOptions['placeholder'] = "Choose an option";
                    $inputType = ChoiceType::class;
                    $fieldOptions['attr']['class'] = "select-search";
                    break;
            }

            if ($attribute->getMandatory() == true) {
                $fieldOptions['constraints'] = new NotBlank();
                $fieldOptions['label_attr']['class'] = 'required';
                $fieldOptions['attr']['required'] = true;
            }

            // add other option
            if ($attribute->getType() == Attribute::TYPE_DROPDOWN) {
                $fieldOptions['choices']["Other"] = "other";
            }
            $builder->add($attribute->getId(), $inputType, $fieldOptions);

            // add other option
            if ($attribute->getType() == Attribute::TYPE_DROPDOWN) {
                $builder->add($attribute->getId().'_other', TextType::class, [
                    "label" => $attribute->getTitle()." other",
                    "data" => $otherValue,
                    "required" => false,
                    "attr" => ['hide' => ($otherValue == null) ? true : false],
                ]);
            }
        }

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSubmit']);

    }

    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $attributes = $this->em->getRepository(Attribute::class)->findByProduct($this->product);

        $product = $form->getRoot()->getData();

        // remove old relations
        if ($product->getId() != null) {
            $this->em->getRepository(ProductHasAttribute::class)->removeByProduct($product);
        }

        foreach ($attributes as $attribute) {

            $subAttributeValue = $form->get($attribute->getId())->getData();
            if ($subAttributeValue == null) {
                continue;
            }
            $otherValue = null;
            if ($subAttributeValue == "other") {
                $otherField = $form->get($attribute->getId()."_other");
                $otherValue = $otherField->getData();
                if ($otherValue == "other") {
                    $options = $otherField->getConfig()->getOptions();

                    $type = $otherField->getConfig()->getType()->getInnerType();
                    $options["attr"]['hide'] = false;
                    $form->add($attribute->getId()."_other", get_class($type),
                        $options);
                    $otherField = $form->get($attribute->getId()."_other");
                    $otherField->addError(new FormError($this->translator->trans("'Other' is not allowed")));
                }
            }
            if (!is_array($subAttributeValue)) {
                $this->createNewProductHasAttribute($product, $attribute, $subAttributeValue, $otherValue);
            } else {
                foreach ($subAttributeValue as $value) {
                    $this->createNewProductHasAttribute($product, $attribute, $value, $otherValue);
                }
            }
        }
    }

    private function createNewProductHasAttribute($product, $attribute, $subAttributeValue, $otherValue = null)
    {
        $subAttribute = $this->em->getRepository('ProductBundle:SubAttribute')->findOneBy([
            "attribute" => $attribute,
            "id" => $subAttributeValue,
        ]);

        $productHasAttribute = new ProductHasAttribute();
        $productHasAttribute->setProduct($product);
        $productHasAttribute->setAttribute($attribute);

        if ($subAttribute and $otherValue == null) {
            $productHasAttribute->setSubAttribute($subAttribute);
        } else {
            $otherValue = ($otherValue == null) ? $subAttributeValue : $otherValue;
            $productHasAttribute->setOtherValue($otherValue);
        }
        $product->addProductHasAttribute($productHasAttribute);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'product' => null,
        ));
//        $resolver->setRequired([
//            "category",
//        ]);
    }

}
