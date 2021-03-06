<?php

namespace uteg\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;

class DepartmentType extends AbstractType
{
    protected $datelist;
    protected $competitionPlaces;
    protected $format;
    protected $disabled;

    public function __construct($competitionPlaceList, $datelist, $format = 'Y-m-d', $disabled = false)
    {
        $this->competitionPlaces = $competitionPlaceList;
        $this->datelist = $datelist;
        $this->format = $format;
        $this->disabled = $disabled;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('date', 'choice', array('choices' => $this->datelist, 'label' => 'department.add.date', "attr" => array("class" => "select"), 'translation_domain' => 'uteg'))
            ->add('competitionPlace', 'choice', array('choices' => $this->competitionPlaces, 'label' => 'department.add.competitionPlace', 'attr' => array('class'=>'select'), 'translation_domain' => 'uteg'))
            ->add('gender', 'choice', array('disabled' => $this->disabled, 'choices' => array('male' => 'department.add.male', 'female' => 'department.add.female'), 'label' => 'department.add.gender', "attr" => array("class" => "select"), 'translation_domain' => 'uteg'))
            ->add('category', 'entity', array('disabled' => $this->disabled, 'class' => 'uteg:Category', 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')->orderBy('c.number', 'ASC');
            }, 'label' => 'department.add.category', "attr" => array("class" => "select"), 'translation_domain' => 'uteg'));
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
