<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use App\Entity\Game;
use App\Entity\Record;
use App\Entity\Tag;
use App\Repository\RecordRepository;
use App\Repository\GameRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MainController extends AbstractController
{

	public $recordRepository;
	public $gameRepository;
	public $tagRepository;

	public function __construct(GameRepository $gameRepository, RecordRepository $recordRepository, TagRepository $tagRepository)
	{
		$this->recordRepository = $recordRepository;
		$this->gameRepository = $gameRepository;
		$this->tagRepository = $tagRepository;
	}

	#[Route("/", name: "home", methods: ["GET","POST"])]
	public function home(Request $request)
	{
		$date = $this->recordRepository->getLastRecordDate();
		$ranking = $this->getGlobalRanking($date);

		$filtersForm = $this->createFormBuilder()
			->add('name', TextType::class)
			->add('tags', EntityType::class, [
				'class' => Tag::class,
				'choice_label' => 'name',
				'multiple' => true
			])
			->add('order', ChoiceType::class, [
				'choices'  => [
					'Score' => 'score',
					'Progression' => 'progression',
				],
			])
			->getForm();
		$filtersForm->handleRequest($request);
		if ($filtersForm->isSubmitted() && $filtersForm->isValid()) {
			$filters = $filtersForm->getData();
			$filteredList = $this->gameRepository->findWithFilters($filters['name'], $filters['tags']);
			$filteredIds = array_column($filteredList, 'id');
			foreach ($ranking as $id => $game) {
				if (!in_array($id, $filteredIds)) {
					unset($ranking[$id]);
				}
			}

			if($filters['order'] == 'progression'){
				uasort($ranking, function ($a, $b) {
					return $b['scoreDiff'] <=> $a['scoreDiff'];
				});
			}
			

			return $this->render('ranking.html.twig', [
				'ranking' => $ranking
			]);
		}

		return $this->render('home.html.twig', [
			'ranking' => $ranking,
			'weekDate' => $date,
			'tags' => $this->tagRepository->findBy(['type' => Tag::TAG_TYPES['GENERAL'], 'hidden' => false]),
			'platforms' => $this->tagRepository->findBy(['type' => Tag::TAG_TYPES['PLATFORM'], 'hidden' => false]),
			'filtersForm' => $filtersForm->createView(),
			'modalFiltersForm' => $filtersForm->createView()
		]);
	}

	#[Route("/game/{gameName}", name: "game_stats", requirements: ['gameName' => '.+'], methods: ["GET","POST"])]
	public function gameStats(string $gameName, ChartBuilderInterface $chartBuilder)
	{
		$date = $this->recordRepository->getLastRecordDate();
		$game = $this->gameRepository->findOneBy(['name' => $gameName]);
		if (!$game) {
			throw $this->createNotFoundException('This game does not exist');
		}
		$gameId = $game->getId();
		$records = $this->recordRepository->findBy(
			['game' => $gameId, 'hidden' => false],
			['date' => 'DESC']
		);
		if (count($records) < 1) {
			throw $this->createNotFoundException('This game does not exist');
		}
		$ranking = $this->getGlobalRanking($date);

		$recordDates = [];
		$recordScores = [];
		foreach ($records as $record) {
			array_push($recordDates, $record->getDate()->format('d/m/Y'));
			array_push($recordScores, $this->getScore($record));
		}
		$gameStats = $ranking[$gameId];

		$chart = $chartBuilder->createChart(Chart::TYPE_LINE);

		$chart->setData([
			'labels' => $recordDates,
			'datasets' => [[
				'label' => 'Score',
				'data' => $recordScores,
				'order' => 1,
			]],
		]);

		$chart->setOptions([
			'aspectRatio' => 3,
			'plugins' => [
				'legend' => [
					'display' => false
				],
				'tooltip' => [
					'displayColors' => false,
					'cornerRadius' => 3,
					'caretPadding' => 10,
					'caretSize' => 6
				],
			],
			'scales' => [
				'x' => [
					'ticks' => [
						'align'=> 'inner'
					],
					'reverse' => true,
				],
				'y' => [
					'grace' => '50%',
				]
			],
			'cubicInterpolationMode' => 'monotone',
			'backgroundColor' => 'rgba(255, 211, 66, 0.05)',
			'borderColor' => 'rgba(255, 211, 66, 1)',
			'borderWidth' => 1,
			'fill' => true
		]);

		return $this->render('game.html.twig', [
			'game' => $gameStats,
			'lastRecord' => $records[0],
			'chart' => $chart,
			'mainLanguageCode' => $records[0]->getTwitchMainLangWeek(),
			'mainLanguageName' => locale_get_display_language($records[0]->getTwitchMainLangWeek(), "en"),
		]);
	}

	#[Route("/admin", name: "admin_dashboard")]
	public function admin()
	{
		return $this->render('admin.html.twig', []);
	}

	#[Route("/privacy-policy", name: "privacypolicy")]
	public function privacypolicy()
	{
		return $this->render('privacypolicy.html.twig', []);
	}

	public function getScore(Record $record)
	{
		$scores = [];

		//Services score multiplier factor
		$coefs = [
			'reddit' => 80,
			'discord' => 10,
			'twitch' => 100,
		];

		//Reddit Score Algorithm
		$redditOnline = $record->getRedditOnlineWeek();
		$redditTotal = $record->getRedditTotalMembers();
		$redditPosts = $record->getRedditPostCountWeek();
		if ($redditTotal !== 0) {
			$scores['reddit'] = $redditOnline * $coefs['reddit'];
		} else {
			$scores['reddit'] = 0;
			unset($coefs['reddit']);
		}

		//Discord Score Algorithm
		$discordOnline = $record->getDiscordOnlineWeek();
		$discordTotal = $record->getDiscordTotalMembers();
		if ($discordTotal !== 0) {
			$scores['discord'] = $discordOnline * $coefs['discord'];
		} else {
			$scores['discord'] = 0;
			unset($coefs['discord']);
		}

		//Twitch Score Algorithm
		$twitchViewers = $record->getTwitchViewersWeek();
		$scores['twitch'] = $twitchViewers * $coefs['twitch'];

		//Global Score Algorithm
		return round(array_sum($scores) / array_sum($coefs));
	}

	public function getGlobalRanking(\DateTime $date)
	{
		$ranking = []; //Used to order the games by score

		$allRecords = $this->recordRepository->getLast2Records($date);

		$games = $this->gameRepository->findAllForDisplay();
		foreach ($games as $game) {
			$gameId = $game->getId();

			//Reset scores stored
			$scores = [];

			//Get the 2 last records for the current game, [0] is current, [1] is previous
			$gameRecords = [];
			foreach ($allRecords as $record) {
				if ($record->getGame() == $game) {
					array_unshift($gameRecords, $record);
				}
			}

			foreach ($gameRecords as $recK => $record) {
				$scores[$recK] = $this->getScore($record);
			}

			if (isset($scores[0])) { //Only if an curr record exist

				//Add the current game to the ranking calculation array
				$ranking[$gameId] = [
					'infos' => $game,
					'currScore' => $scores[0],
					'mainLanguageCode' => $gameRecords[0]->getTwitchMainLangWeek(),
					'mainLanguageName' => locale_get_display_language($gameRecords[0]->getTwitchMainLangWeek(), "en"),
				];

				//Only if a prev record exist
				if (isset($scores[1])) { 
					$scoreDiff = (($scores[0] - $scores[1]) / $scores[1]) * 100;
					$ranking[$gameId]['scoreDiff'] = round($scoreDiff, 1);
					$ranking[$gameId]['prevScore'] = $scores[1];
				}
			}
		}

		//Sort games by curr score
		uasort($ranking, function ($a, $b) {
			return $b['currScore'] <=> $a['currScore'];
		});

		//Set rank value because the keys of $ranking is actually the ids of the games
		$rank = 1;
		foreach($ranking as $id => $game) {
			$ranking[$id]['currRank'] = $rank;
			$rank++;
		}

		return $ranking;
	}
}
