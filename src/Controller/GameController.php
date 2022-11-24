<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Tag;
use App\Form\GameType;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Service\ApiService;
use App\Repository\TagRepository;


#[Route("/admin/game")]
class GameController extends AbstractController
{

	private $api;
	private $tagRepository;

	public function __construct(ApiService $api, TagRepository $tagRepository)
	{
		$this->api = $api;
		$this->tagRepository = $tagRepository;
	}

	#[Route("/", name: "game_index", methods: ["GET"])]
	public function index(GameRepository $gameRepository): Response
	{
		return $this->render('game/index.html.twig', [
			'games' => $gameRepository->findAll(),
		]);
	}

	#[Route("/new", name: "game_new", methods: ["GET","POST"])]
	public function new(Request $request, ManagerRegistry $doctrine): Response
	{
		$gameData = [
			'id' => $request->query->get('id'),
			'name' => $request->query->get('name'),
			'boxArtURL' => $request->query->get('boxArtURL'),
			'platforms' => $request->query->get('platforms'),
		];
		if(!$gameData['id']){
			$searchForm = $this->createFormBuilder()
				->add('name', TextType::class)
				->add('submit', SubmitType::class, ['label' => 'Search game on Twitch'])
				->getForm();
			$searchForm->handleRequest($request);
			if ($searchForm->isSubmitted() && $searchForm->isValid()) {
				$gameName = $searchForm['name']->getData();
				$twitchData = json_decode($this->api->twitch->getTwitchGameInfo($gameName)->getContent(), true);
				if($twitchData['status'] != 200){
					return $this->redirectToRoute('game_new');
				}
				$existingGame = $doctrine->getRepository(Game::class)->findOneBy(['twitchId' => $twitchData['data']['id']]);
				if($existingGame){
					return $this->redirectToRoute('game_new');
				}
				return $this->redirectToRoute('game_new', $twitchData['data']);
			}
			return $this->render('game/twitchSearch.html.twig', [
				'form' => $searchForm->createView(),
			]);
		}

		$game = new Game();
		$game->setName($gameData['name']);
		$game->setTwitchId($gameData['id']);
		$game->setImage($gameData['boxArtURL']);

		$tags = $this->tagRepository->findBy(['name' => $gameData['platforms']]);
		foreach($tags as $tag){
			$game->addTag($tag);
		}

		$addForm = $this->createForm(GameType::class, $game);
		$addForm->handleRequest($request);
		if ($addForm->isSubmitted() && $addForm->isValid()) {
			$entityManager = $doctrine->getManager();

			$game->setCreatedAt(new \DateTime());

			$entityManager->persist($game);
			$entityManager->flush();

			$redirectRoute = $request->query->get('redirect') ?: 'game_index';
			return $this->redirectToRoute($redirectRoute);
		}

		return $this->render('game/new.html.twig', [
			'game' => $game,
			'form' => $addForm->createView(),
			'gameData' => $gameData,
			'tags' => $this->tagRepository->findBy(['type' => Tag::TAG_TYPES['GENERAL'], 'hidden' => false]),
		]);
	}

	#[Route("/twitchlist", name: "game_twitch_list", methods: ["GET"])]
	public function twitchlist(GameRepository $gameRepository): Response
	{
		$games = $gameRepository->findAll();
		$twitchGames = json_decode($this->api->twitch->getTwitchGames()->getContent(), true)['data']['games'];
		foreach($games as $game){
			if(isset($twitchGames[$game->getTwitchId()])){
				unset($twitchGames[$game->getTwitchId()]);
			}
		}

		return $this->render('game/twitchlist.html.twig', [
			'twitchGames' => $twitchGames,
		]);
	}

	#[Route("/{id}", name: "game_show", methods: ["GET"])]
	public function show(Game $game): Response
	{
		return $this->render('game/show.html.twig', [
			'game' => $game,
		]);
	}

	#[Route("/{id}/edit", name: "game_edit", methods: ["GET","POST"])]
	public function edit(Request $request, Game $game, ManagerRegistry $doctrine): Response
	{
		$form = $this->createForm(GameType::class, $game);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$doctrine->getManager()->flush();

			return $this->redirectToRoute('game_index');
		}

		return $this->render('game/edit.html.twig', [
			'game' => $game,
			'form' => $form->createView(),
			'tags' => $this->tagRepository->findAll()
		]);
	}

	#[Route("/{id}", name: "game_delete", methods: ["DELETE"])]
	public function delete(Request $request, Game $game, ManagerRegistry $doctrine): Response
	{
		if ($this->isCsrfTokenValid('delete' . $game->getId(), $request->request->get('_token'))) {
			$entityManager = $doctrine->getManager();
			$entityManager->remove($game);
			$entityManager->flush();
		}

		return $this->redirectToRoute('game_index');
	}
}
