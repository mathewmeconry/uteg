<?php

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;

class S2cType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', null, array('read_only' => 'readonly', 'label' => 'starters.edit.form.firstname', "attr" => array("placeholder" => "starters.edit.form.firstname", "class" => "form-control"), "label_attr" => array("class" => "control-label"), 'translation_domain' => 'uteg'))
            ->add('lastname', null, array('read_only' => 'readonly', 'label' => 'starters.edit.form.lastname', "attr" => array("placeholder" => "starters.edit.form.lastname", "class" => "form-control"), "label_attr" => array("class" => "control-label"), 'translation_domain' => 'uteg'))
            ->add('birthyear', 'number', array('read_only' => 'readonly', 'label' => 'starters.edit.form.birthyear', "attr" => array("placeholder" => "starters.edit.form.birthyear", "class" => "form-control"), "label_attr" => array("class" => "control-label"), 'translation_domain' => 'uteg'))
            ->add('club', 'entity', array('class' => 'AppBundle:Club', 'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                }, 'label' => 'starters.edit.form.club', "attr" => array("placeholder" => "starters.edit.form.club", "class" => "form-control"), "label_attr" => array("class" => "control-label"), 'translation_domain' => 'uteg'))
            ->add('present', 'checkbox', array('label' => 'starters.edit.form.present', 'required' => false, "attr" => array("placeholder" => "starters.edit.form.present"), "label_attr" => array("class" => "control-label"), 'translation_domain' => 'uteg'))
            ->add('medicalcert', 'checkbox', array('label' => 'starters.edit.form.medicalcert', 'required' => false, "attr" => array("placeholder" => "starters.edit.form.medicalcert"), "label_attr" => array("class" => "control-label"), 'translation_domain' => 'uteg'));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Starters2Competitions'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_starter';
    }
}
