<?php

namespace App\Command;

use App\Entity\DailyRecord;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Game;
use App\Repository\GameRepository;
use App\Repository\RecordRepository;
use App\Service\ApiService;

use Psr\Log\LoggerInterface;

#[AsCommand(name: 'hod:cron:day')]
class HodCronDay extends Command
{
	private $commandName = 'hod:cron:day';
	private $em;
	private $gameList;
	public $api;

	public function __construct(
		EntityManagerInterface $em,
		GameRepository $gameRepository,
		RecordRepository $recordRepository,
		LoggerInterface $cronLogger,
		ApiService $api,
	) {
		parent::__construct();
		$this->em = $em;

		$lastRecordDate = $recordRepository->getLastRecordDate();
		$this->gameList = $gameRepository->findCreatedBeforeDate($lastRecordDate);
		
		$this->api = $api;
		$this->cronLogger = $cronLogger;
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
		try{
			foreach ($this->gameList as $game) {
				//On récupère les noms des subreddits liés au jeu
				$subredditNameArr = $game->getSubredditName();

				//On cumule le nombre de posts de chaque subreddit
				$redditPostCount = 0;
				foreach ($subredditNameArr as $r) {
					$redditPostCount += json_decode($this->api->reddit->getSubredditPosts($r)->getContent())->data[0];
				}

				//On envoie le tout dans la db
				$record = new DailyRecord();
				$record->setGame($game);
				$record->setDate($startTime);
				$record->setRedditPosts($redditPostCount);
				$this->em->persist($record);

				$this->em->flush();
			}
		} catch(\Exception $e){
			$this->cronLogger->info($this->commandName . ' ERROR', [
				'message' => $e->getMessage(),
			]);
		}
		$endTime = new \DateTime();
		$processTime = $startTime->diff($endTime);
		$this->cronLogger->info($this->commandName . ' end', [
			'duration' => $processTime->format('%hh %im %ss'),
		]);
		return 0;
	}
}
