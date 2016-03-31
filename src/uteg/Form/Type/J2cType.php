<?php

namespace uteg\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;

class J2cType extends AbstractType
{

    protected $disabled;

    public function __construct($disabled = true)
    {
        $this->disabled = $disabled;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array('disabled' => $this->disabled, "attr" => array('label' => 'egt.judges.edit.email', "placeholder" => "egt.judges.edit.email"), 'translation_domain' => 'uteg'))
            ->add('device', 'entity', array('class' => 'uteg:Device', 'label' => 'egt.judges.edit.device', 'translation_domain' => 'uteg', 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')->orderBy('c.id', 'ASC');
            }));
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
        return 'uteg_judges';
    }
}