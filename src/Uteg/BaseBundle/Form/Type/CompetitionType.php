<?php

namespace Uteg\BaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;
use Doctrine\ORM\EntityRepository;

class CompetitionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'competitionlist.addcomp.name', "attr" => array("placeholder" => "competitionlist.addcomp.name"), 'translation_domain' => 'UtegBase'))
            ->add('module', 'entity', array('class' => 'UtegBaseBundle:Module', 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')->orderBy('c.id', 'ASC');
            }, 'label' => 'competitionlist.addcomp.module', "attr" => array("placeholder" => "competitionlist.addcomp.module", "class" => "select"), 'translation_domain' => 'UtegBase'))
            ->add('gym', null, array('label' => 'competitionlist.addcomp.gym', "attr" => array("placeholder" => "competitionlist.addcomp.gym"), 'translation_domain' => 'UtegBase'))
            ->add('location', null, array('label' => 'competitionlist.addcomp.location', "attr" => array("placeholder" => "competitionlist.addcomp.location"), 'translation_domain' => 'UtegBase'))
            ->add('zipcode', 'number', array('label' => 'competitionlist.addcomp.zipcode', "attr" => array("placeholder" => "competitionlist.addcomp.zipcode"), 'translation_domain' => 'UtegBase'))
            ->add('startdate', 'date', array(
                'label' => 'competitionlist.addcomp.startdate',
                "attr" => array("placeholder" => "competitionlist.addcomp.startdate"),
                'translation_domain' => 'UtegBase',
                'input' => 'string',
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'html5' => true
            ))
            ->add('enddate', 'date', array(
                'label' => 'competitionlist.addcomp.enddate',
                "attr" => array("placeholder" => "competitionlist.addcomp.enddate"),
                'translation_domain' => 'UtegBase',
                'input' => 'string',
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'html5' => true
            ))
            ->add('recaptcha', 'ewz_recaptcha', array(
                'attr' => array(
                    'options' => array(
                        'theme' => 'light',
                        'type' => 'image'
                    ),
                ),
                'mapped' => false,
                'constraints' => array(
                    new True()
                ),
                'error_bubbling' => true
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Uteg\BaseBundle\Entity\Competition'
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
