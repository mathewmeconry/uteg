<?php

namespace AppBundle\Form\Type;

use \FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // add your custom field
        $builder->add('username', null, array("attr" => array("placeholder" => "register.username"), 'translation_domain' => 'uteg'))
                ->add('firstname', null, array("attr" => array("placeholder" => "register.firstname"), 'translation_domain' => 'uteg'))
                ->add('lastname', null, array("attr" => array("placeholder" => "register.lastname"), 'translation_domain' => 'uteg'))
                ->add('email', 'email', array("attr" => array("placeholder" => "register.email"), 'translation_domain' => 'uteg'))
                ->add('plainPassword', 'repeated', array(
                    'type' => 'password',
                    'options' => array('translation_domain' => 'uteg'),
                    'first_options' => array('label' => 'form.password', "attr" => array("placeholder" => "register.password")),
                    'second_options' => array('label' => 'form.password_confirmation', "attr" => array("placeholder" => "register.password_repeat"))
                ))
                ->add('recaptcha', 'ewz_recaptcha', array(
                    'attr' => array(
                        'options' => array(
                            'theme' => 'light',
                            'type'  => 'image'
                        ),
                    ),
                    'mapped' => false,
                    'constraints'   => array(
                        new True()
                    ),
                    'error_bubbling' => true
                ));
    }

    public function getName()
    {
        return 'uteg_user_registration';
    }
}