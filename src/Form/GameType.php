<?php

namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Form\CallbackTransformer;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\Tag;

class GameType extends AbstractType
{

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', HiddenType::class)
			->add('tags', EntityType::class, [
				'class' => Tag::class,
				'choice_label' => 'name',
				'multiple' => true,
			])
			->add('subredditName', TextType::class, [
				'required' => false,
				'attr' => [
					'placeholder' => 'Subreddits names (separated by commas)'
				]
			])
			->add('discordId', TextType::class, [
				'required' => false,
				'attr' => [
					'placeholder' => 'Discord IDs (separated by commas)'
				]
			])
			->add('image', HiddenType::class)
			->add('twitchId', HiddenType::class);

		$builder
			->get('subredditName')
			->addModelTransformer(new CallbackTransformer(
				function ($tagsAsArray) {
					return implode(', ', $tagsAsArray);
				},
				function ($tagsAsString) {
					$tagsAsString = trim($tagsAsString, " \n\r\t\v\x00,");
					if ($tagsAsString === "") {
						return [];
					}
					return array_map('trim', explode(',', $tagsAsString));
				}
			));
			
		$builder
			->get('discordId')
			->addModelTransformer(new CallbackTransformer(
				function ($tagsAsArray) {
					return implode(', ', $tagsAsArray);
				},
				function ($tagsAsString) {
					$tagsAsString = trim($tagsAsString, " \n\r\t\v\x00,");
					if ($tagsAsString === "") {
						return [];
					}
					return array_map('trim', explode(',', $tagsAsString));
				}
			));
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Game::class,
		]);
	}
}
