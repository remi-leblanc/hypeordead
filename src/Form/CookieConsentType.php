<?php

namespace App\Form;

use App\Entity\CookieConsent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CookieConsentType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('analytics', CheckboxType::class, [
				'required' => false,
			])
			->add('ads', CheckboxType::class, [
				'required' => false,
			])
			->add('cookie_key', HiddenType::class)
			->add('deny', SubmitType::class)
			->add('save', SubmitType::class);
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => CookieConsent::class,
		]);
	}
}
