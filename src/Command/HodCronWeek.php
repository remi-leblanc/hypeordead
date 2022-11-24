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

use App\Entity\Record;
use App\Entity\Tag;
use App\Repository\RecordRepository;
use App\Repository\TagRepository;
use App\Service\ApiService;

use Psr\Log\LoggerInterface;

#[AsCommand(name: 'hod:cron:week')]
class HodCronWeek extends Command
{

	private $commandName = 'hod:cron:week';
	private $em;
	private $gameList;
	public $api;
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

		$lastRecordDate = $recordRepository->getLastRecordDate();
		$this->gameList = $gameRepository->findCreatedBeforeDate($lastRecordDate);

		$this->api = $api;
		$this->cronLogger = $cronLogger;
		$this->managerRegistry = $managerRegistry;
		
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
				//Create new Record object
				$record = new Record();

				//Setting up base information about the new Record
				$record->setDate(new \DateTime());
				$record->setGame($game);
				$record->setHidden(false);

				//Getting the game's services IDs
				$subredditNameArr = $game->getSubredditName();
				$discordIdArr = $game->getDiscordId();

				//Sum up the total members of a all the plateform's servers
				$redditTotalMembers = 0;
				foreach ($subredditNameArr as $r) {
					$redditTotalMembers += json_decode($this->api->reddit->getSubredditInfo($r)->getContent())->data[0];
				}
				$discordTotalMembers = 0;
				foreach ($discordIdArr as $d) {
					$discordTotalMembers += json_decode($this->api->discord->getDiscordServer($d)->getContent())->data[0];
				}
				$twitchData = json_decode($this->api->twitch->getTwitchGameInfo($game->getTwitchId())->getContent())->data;

				//Get the hourly values
				$redditOnlineArr = [];
				$discordOnlineArr = [];
				$twitchViewersArr = [];
				$twitchStreamsArr = [];
				$twitchLangArr = [];
				foreach($game->getHourlyRecords() as $hourlyRecord){
					$redditOnlineArr[] = $hourlyRecord->getRedditOnline();
					$discordOnlineArr[] = $hourlyRecord->getDiscordOnline();
					$twitchViewersArr[] = $hourlyRecord->getTwitchViewers();
					$twitchStreamsArr[] = $hourlyRecord->getTwitchStreams();
					$twitchLangArr[] = $hourlyRecord->getTwitchMainLang();
					$this->em->remove($hourlyRecord);
				}

				//Calculate the hourly values
				$redditOnlineWeek = array_sum($redditOnlineArr) / count($redditOnlineArr);
				$discordOnlineWeek = array_sum($discordOnlineArr) / count($discordOnlineArr);
				$twitchViewersWeek = array_sum($twitchViewersArr) / count($twitchViewersArr);

				$sortedLanguages = array_count_values($twitchLangArr);
				$mainLanguage = array_keys($sortedLanguages, max($sortedLanguages))[0];
				$twitchLangWeek = $mainLanguage;

				//Get the daily values
				$redditPostsArr = [];
				foreach($game->getDailyRecords() as $dailyRecord){
					$redditPostsArr[] = $dailyRecord->getRedditPosts();
					$this->em->remove($dailyRecord);
				}
				//Calculate the daily values
				$redditPostCount = array_sum($redditPostsArr);

				//Set the values to the Record object
				$record->setRedditOnlineWeek($redditOnlineWeek);
				$record->setRedditPostCountWeek($redditPostCount);
				$record->setDiscordOnlineWeek($discordOnlineWeek);
				$record->setTwitchViewersWeek($twitchViewersWeek);
				$record->setTwitchMainLangWeek($twitchLangWeek);

				$record->setRedditTotalMembers($redditTotalMembers);
				$record->setDiscordTotalMembers($discordTotalMembers);
				$record->setTwitchFollowers($twitchData->followersCount);

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
				$this->em->persist($record);
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
