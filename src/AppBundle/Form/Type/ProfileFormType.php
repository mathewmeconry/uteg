<?php

namespace AppBundle\Form\Type;

use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ProfileFormType extends BaseType {
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		// add your custom field
		$builder->add('profilepicturefile', 'file', array('label' => 'profile.edit.profilepicture', "attr" => array("placeholder" => "profile.edit.profilepicture"), 'translation_domain' => 'uteg'))
				->add('username', null, array('label' => 'profile.edit.username', "attr" => array("placeholder" => "profile.edit.username"), 'translation_domain' => 'uteg'))
				->add('firstname', null, array('label' => 'profile.edit.firstname', "attr" => array("placeholder" => "profile.edit.firstname"), 'translation_domain' => 'uteg'))
				->add('lastname', null, array('label' => 'profile.edit.lastname', "attr" => array("placeholder" => "profile.edit.lastname"), 'translation_domain' => 'uteg'))
				->add('email', 'email', array('label' => 'profile.edit.email', "attr" => array("placeholder" => "profile.edit.email"), 'translation_domain' => 'uteg'))
				->add('plainPassword', 'repeated', array(
						'type' => 'password',
						'options' => array('translation_domain' => 'uteg'),
						'first_options' => array('label' => 'profile.edit.password', "attr" => array("placeholder" => "profile.edit.password")),
						'second_options' => array('label' => 'profile.edit.password_repeat', "attr" => array("placeholder" => "profile.edit.password_repeat")),
						'required' => false
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
		return 'uteg_profile_edit';
	}
}