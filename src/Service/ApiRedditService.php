<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiRedditService extends Api
{

	public function getBody($response)
	{
		$body = json_decode($response->getContent());
		$headers = $response->getHeaders();
		if (!$body) {
			throw new \Exception('Reddit API Error: Invalid data');
		}
		if ($headers['x-ratelimit-remaining'][0] === 0) {
			throw new \Exception('Reddit API rate limit exeeded');
		}
		return $body;
	}

	public function getSubredditInfo($subName)
	{
		$result = new \stdClass();
		$response = $this->CallAPI(
			'GET',
			'https://www.reddit.com/r/' . $subName . '/about.json'
		);
		$body = $this->getBody($response);
		$result->status = 200;
		$result->data = [
			$body->data->subscribers,
			$body->data->accounts_active,
		];
		return new JsonResponse($result);
	}

	public function getSubredditPosts($subName)
	{
		$result = new \stdClass();
		
		$url = 'https://www.reddit.com/r/' . $subName . '/new.json?limit=50';
		$postCount = 0;
		$dateNow = time();

		$pagination = "";
		while ($pagination !== false && $pagination !== null) {
			$response = $this->CallAPI(
				'GET',
				$pagination ? "$url&after=$pagination" : $url,
			);
			$body = $this->getBody($response);
			foreach ($body->data->children as $post) {
				if ($post->data->created_utc >= $dateNow - 86400) {
					$postCount++;
					$pagination = $body->data->after;
				} else {
					$pagination = false;
				}
			}
		}
		$result->status = 200;
		$result->data = [
			$postCount,
		];
		return new JsonResponse($result);
	}
}
