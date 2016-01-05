<?php

namespace uteg\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;

class DepartmentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('date', 'date', array(
            'label' => 'egt.department.add.date',
            'translation_domain' => 'uteg',
            'input' => 'string',
            'format' => 'yyyy-MM-dd',
            'widget' => 'single_text',
            'html5' => true
        ))
            ->add('sex', 'choice', array('choices' => array('male' => 'egt.department.add.male', 'female' => 'egt.department.add.female'), 'label' => 'egt.department.add.sex', "attr" => array("class" => "select"), 'translation_domain' => 'uteg'))
            ->add('category', 'entity', array('class' => 'uteg:Category', 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')->orderBy('c.number', 'ASC');
            }, 'label' => 'egt.department.add.category', "attr" => array("class" => "select"), 'translation_domain' => 'uteg'));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'uteg\Entity\Department'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'uteg_department';
    }
}
