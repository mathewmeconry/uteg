<?php

namespace uteg\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;

class S2cType extends AbstractType
{

    protected $readonly;

    public function __construct($readonly = true)
    {
        $this->readonly = ($readonly) ? 'readonly' : '';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', null, array('read_only' => $this->readonly, 'required' => true, 'label' => 'starters.edit.form.firstname', "attr" => array("placeholder" => "starters.edit.form.firstname"), 'translation_domain' => 'uteg'))
            ->add('lastname', null, array('read_only' => $this->readonly, 'label' => 'starters.edit.form.lastname', "attr" => array("placeholder" => "starters.edit.form.lastname"), 'translation_domain' => 'uteg'))
            ->add('birthyear', 'number', array('read_only' => $this->readonly, 'label' => 'starters.edit.form.birthyear', "attr" => array("placeholder" => "starters.edit.form.birthyear"), 'translation_domain' => 'uteg'))
            ->add('sex', 'choice', array('choices' => array('male' => 'starters.male', 'female' => 'starters.female'), 'read_only' => $this->readonly, 'label' => 'starters.edit.form.sex', "attr" => array("placeholder" => "starters.edit.form.sex", "class" => "select"), 'translation_domain' => 'uteg'))
            ->add('category', 'entity', array('class' => 'uteg:Category', 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')->orderBy('c.number', 'ASC');
            }, 'label' => 'starters.edit.form.category', "attr" => array("placeholder" => "starters.edit.form.category", "class" => "select"), 'translation_domain' => 'uteg'))
            ->add('club', 'entity', array('class' => 'uteg:Club', 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
            }, 'label' => 'starters.edit.form.club', "attr" => array("class" => "selectSearch"), 'translation_domain' => 'uteg'))
            ->add('present', 'checkbox', array('label' => 'starters.edit.form.present', 'required' => false, "attr" => array("placeholder" => "starters.edit.form.present"), 'translation_domain' => 'uteg'))
            ->add('medicalcert', 'checkbox', array('label' => 'starters.edit.form.medicalcert', 'required' => false, "attr" => array("placeholder" => "starters.edit.form.medicalcert"), 'translation_domain' => 'uteg'));
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
        return 'uteg_starter';
    }
}