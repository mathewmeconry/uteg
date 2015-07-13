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
            ->add('club', 'text', array('read_only' => 'true', 'label' => 'invites.edit.form.club', "attr" => array("placeholder" => "invites.edit.form.club", "class" => "form-control"), "label_attr" => array("class" => "control-label"), 'translation_domain' => 'uteg'))
            ->add('valid', 'date', array(
                'label' => 'invites.edit.form.valid',
                "attr" => array("placeholder" => "invites.edit.form.valid", "class" => "form-control"),
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
