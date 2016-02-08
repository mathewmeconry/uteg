<?php

namespace uteg\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;

class ClubType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null, array('label' => 'clubs.add.name', "attr" => array("placeholder" => "clubs.add.placeholder"), 'translation_domain' => 'uteg'));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'uteg\Entity\Club'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'uteg_competition';
    }
}
