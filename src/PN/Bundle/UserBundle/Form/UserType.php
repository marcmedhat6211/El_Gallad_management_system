<?php

namespace PN\Bundle\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use PN\Bundle\UserBundle\Entity\User;

class UserType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('fullName')
                ->add('email', EmailType::class)
                ->add('phone')
                ->add('plainPassword', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'first_options' => array('label' => 'Password'),
                    'second_options' => array('label' => 'Password confirmation'),
                    'invalid_message' => "The entered passwords don't match",
                ))
//                ->add('role', ChoiceType::class, [
//                    'choices' => User::$rolesList,
//                    'choices_as_values' => TRUE,
//                    "attr" => ["class" => "select-search "]
//                ])
                ->add('enabled', NULL, array(
                    'label' => 'Active',
                ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'PN\Bundle\UserBundle\Entity\User',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'pn_bundle_userbundle_user';
    }

}
