<?php

namespace uteg\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class C2iType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('club', 'text', array('read_only' => 'true', 'label' => 'invites.edit.form.club', "attr" => array("placeholder" => "invites.edit.form.club"), 'translation_domain' => 'uteg'))
            ->add('firstname', null, array('label' => 'invites.edit.form.firstname', "attr" => array("placeholder" => "invites.edit.form.firstname"), 'translation_domain' => 'uteg'))
            ->add('lastname', null, array('label' => 'invites.edit.form.lastname', "attr" => array("placeholder" => "invites.edit.form.lastname"), 'translation_domain' => 'uteg'))
            ->add('email', 'email', array('label' => 'invites.edit.form.email', "attr" => array("placeholder" => "invites.edit.form.email"), 'translation_domain' => 'uteg'))
            ->add('street', null, array('label' => 'invites.edit.form.street', "attr" => array("placeholder" => "invites.edit.form.street"), 'translation_domain' => 'uteg'))
            ->add('city', null, array('label' => 'invites.edit.form.city', "attr" => array("placeholder" => "invites.edit.form.city"), 'translation_domain' => 'uteg'))
            ->add('zipcode', null, array('label' => 'invites.edit.form.zipcode', "attr" => array("placeholder" => "invites.edit.form.zipcode"), 'translation_domain' => 'uteg'))
            ->add('valid', 'date', array(
                'label' => 'invites.edit.form.valid',
                "attr" => array("placeholder" => "invites.edit.form.valid", "class" => "datepicker"),
                'translation_domain' => 'uteg',
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'html5' => true
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'cascade_validation' => true,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'uteg_invite';
    }
}
