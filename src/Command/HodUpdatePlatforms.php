<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use App\Repository\GameRepository;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Service\ApiService;

#[AsCommand(name: 'hod:update:platforms')]
class HodUpdatePlatforms extends Command
{

	private $em;
	public $api;
	private $managerRegistry;
	private $tagRepository;
	private $gameRepository;

	public function __construct(
		EntityManagerInterface $em,
		ManagerRegistry $managerRegistry,
		GameRepository $gameRepository,
		TagRepository $tagRepository,
		ApiService $api
	) {
		parent::__construct();
		$this->em = $em;

		$this->gameRepository = $gameRepository;
		$this->tagRepository = $tagRepository;

		$this->api = $api;
		$this->managerRegistry = $managerRegistry;
	}

	protected function configure()
	{
		$this
			->setDescription('Add a new object to the database.')
			->setHelp('Helps you to manually add a new object to the database.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		foreach ($this->gameRepository->findAll() as $game) {
			$twitchData = json_decode($this->api->twitch->getTwitchGameInfo($game->getTwitchId())->getContent())->data;

			//On crée si besoin et ajoute les tags de platformes
			foreach($game->getTags() as $tag){
				if($tag->getType() === Tag::TAG_TYPES["PLATFORM"]){
					$game->removeTag($tag);
				}
			}
			foreach($twitchData->platforms as $platform){
				$tag = $this->tagRepository->findOneBy(['name' => $platform]);
				if(!$tag){
					$tag = new Tag();
					$tag->setName($platform);
					$tag->setType(Tag::TAG_TYPES["PLATFORM"]);
					$tag->setHidden(false);

					$this->em->persist($tag);
				}
				$game->addTag($tag);
			}

			//Send to db
			if(!$this->em->isOpen()){
				$this->managerRegistry->resetManager();
			}
			$this->em->flush();
		}
		return 0;
	}
}
