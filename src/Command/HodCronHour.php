<?php

namespace App\Command;

use App\Entity\HourlyRecord;
use App\Entity\Tag;
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
use App\Repository\RecordRepository;
use App\Repository\TagRepository;
use App\Service\ApiService;
use Psr\Log\LoggerInterface;

#[AsCommand(name: 'hod:cron:hour')]
class HodCronHour extends Command
{

	private $commandName = 'hod:cron:hour';
	private $em;
	private $gameList;
	private $api;
	private $cronLogger;
	private $managerRegistry;
	private $tagRepository;

	public function __construct(
		EntityManagerInterface $em,
		ManagerRegistry $managerRegistry,
		GameRepository $gameRepository,
		RecordRepository $recordRepository,
		TagRepository $tagRepository,
		LoggerInterface $cronLogger,
		ApiService $api
	) {
		parent::__construct();
		$this->em = $em;
		$this->managerRegistry = $managerRegistry;
		
		$lastRecordDate = $recordRepository->getLastRecordDate();
		$this->gameList = $gameRepository->findCreatedBeforeDate($lastRecordDate);

		$this->api = $api;
		$this->cronLogger = $cronLogger;

		$this->tagRepository = $tagRepository;
	}

	protected function configure()
	{
		$this
			->setDescription('Add a new object to the database.')
			->setHelp('Helps you to manually add a new object to the database.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->cronLogger->info($this->commandName . ' start');
		$startTime = new \DateTime();
		
		foreach ($this->gameList as $game) {
			try{
				//On récupère les noms/ids des services liés au jeu
				$subredditNameArr = $game->getSubredditName();
				$discordIdArr = $game->getDiscordId();
				$twitchId = $game->getTwitchId();
	
				//On cumule le nombre de user online pour chaque nom/id des services
				$redditOnlineMembers = 0;
				foreach ($subredditNameArr as $r) {
					$redditOnlineMembers += json_decode($this->api->reddit->getSubredditInfo($r)->getContent())->data[1];
				}
				$discordOnlineMembers = 0;
				foreach ($discordIdArr as $d) {
					$discordOnlineMembers += json_decode($this->api->discord->getDiscordServer($d)->getContent())->data[1];
				}

				$twitchData = json_decode($this->api->twitch->getTwitchGameViewers($twitchId)->getContent());

				//On envoie le tout dans la db
				$record = new HourlyRecord();
				$record->setGame($game);
				$record->setDate($startTime);
				$record->setRedditOnline($redditOnlineMembers);
				$record->setDiscordOnline($discordOnlineMembers);
				$record->setTwitchViewers($twitchData->data->viewers);
				$record->setTwitchStreams($twitchData->data->streams);
				$record->setTwitchMainLang($twitchData->data->mainLanguage);
				$this->em->persist($record);				

				//We need to reset the Manager since the command takes too long
				if(!$this->em->isOpen()){
					$this->managerRegistry->resetManager();
				}
				$this->em->flush();

			} catch(\Exception $e){
				$this->cronLogger->info($this->commandName . ' ERROR', [
					'message' => $e->getMessage(),
				]);
			}
		}

		$endTime = new \DateTime();
		$processTime = $startTime->diff($endTime);
		$this->cronLogger->info($this->commandName . ' end', [
			'duration' => $processTime->format('%hh %im %ss'),
		]);
		return 0;
	}
}
