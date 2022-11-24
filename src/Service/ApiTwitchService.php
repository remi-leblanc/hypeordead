<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiTwitchService extends Api
{

	public function getBody($response)
	{
		$body = json_decode($response->getContent());
		if (isset($body->error)) {
			throw new \Exception("Twitch API Error: " . $body->error);
		}
		return $body;
	}

	public function getPaginationCursor($objects)
	{
		if(empty($objects) || !$objects){
			return false;
		}
		if(!$objects->pageInfo->hasNextPage){
			return false;
		}
		$pagination = $objects->edges[count($objects->edges)-1]->cursor;
		return $pagination;
	}

	public function getResponse($query)
	{
		$response = $this->CallAPI(
			'POST',
			'https://gql.twitch.tv/gql',
			[],
			$this->getServiceHeaders('twitch'),
			['json' => ['query' => $query]],
		);
		return $response;
	}

	public function getTwitchGameInfo($nameOrId)
	{
		$result = new \stdClass();
		$parameter = is_numeric($nameOrId) ? "id: \"$nameOrId\"" : "name: \"$nameOrId\"";

		$query = <<<"GRAPHQL"
			query {
				game($parameter) {
					id
					name
					boxArtURL
					platforms
					followersCount
				}
			}
		GRAPHQL;

		$response = $this->getResponse($query);
		$body = $this->getBody($response);
		if (!$body->data->game) {
			$result->status = 404;
			$result->data = "The game {$parameter} does not exist.";
		} else {
			$result->status = 200;
			$result->data = $body->data->game;
			$result->data->platforms = $body->data->game->platforms ?: [];
		}
		return new JsonResponse($result);
	}

	public function getTwitchGameViewers($gameId)
	{
		$result = new \stdClass();
		$result->data = new \stdClass();

		$languages = [];

		$pagination = "";
		while ($pagination !== false) {
			$query = <<<"GRAPHQL"
				query {
					game(id: "$gameId") {
						broadcastersCount
						viewersCount
						streams(after: "$pagination", first: 100) {
							pageInfo {
								hasNextPage
							}
							edges {
								cursor
								node {
									language
								}
							}
						}
					}
				}
			GRAPHQL;

			$response = $this->getResponse($query);
			$body = $this->getBody($response);

			$pagination = $this->getPaginationCursor($body->data->game->streams);

			$result->data->streams = $body->data->game->broadcastersCount;
			$result->data->viewers = $body->data->game->viewersCount;

			foreach ($body->data->game->streams->edges as $stream) {
				array_push($languages, $stream->node->language);
			}
		}

		if (!empty($languages) && $result->data->streams) {
			$sortedLanguages = array_count_values(array_filter($languages));
			$mainLanguage = array_keys($sortedLanguages, max($sortedLanguages))[0];
			if (max($sortedLanguages) / $result->data->streams >= 0.75 &&  $result->data->streams >= 10) {
				$result->data->mainLanguage = $mainLanguage;
			} else {
				$result->data->mainLanguage = "false";
			}
		} else {
			$result->data->mainLanguage = "false";
		}


		$result->status = 200;
		return new JsonResponse($result);
	}

	public function getTwitchGames()
	{
		$result = new \stdClass();
		$result->data = new \stdClass();
		$result->data->games = [];
		
		$pagination = "";
		while ($pagination !== false) {
			$query = <<<"GRAPHQL"
				query {
					games(after: "$pagination", first: 100, options: {sort: VIEWER_COUNT}) {
						pageInfo {
							hasNextPage
						}
						edges {
							cursor
							node {
								id
								name
								platforms
								boxArtURL
							}
						}
					}
				}
			GRAPHQL;

			$response = $this->getResponse($query);
			$body = $this->getBody($response);

			$pagination = $this->getPaginationCursor($body->data->games);

			foreach ($body->data->games->edges as $game) {
				$result->data->games[$game->node->id] = $game->node;
			}
		}

		$result->status = 200;
		return new JsonResponse($result);
	}
}