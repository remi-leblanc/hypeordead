<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiDiscordService extends Api
{

	public function getBody($response)
	{
		$body = json_decode($response->getContent());
		$headers = $response->getHeaders();
		if (isset($body->message)) {
			if (isset($body->code)) {
				switch ($body->code) {
					case 0:
						throw new \Exception('Discord API Error 401: ' . $body->message);
					case 10004:
						throw new \Exception('Discord API Error 404: ' . $body->message);
				}
			} else {
				throw new \Exception('Discord API Error: ' . $body->message);
			}
		}
		if (isset($body->guild_id)) {
			throw new \Exception('Discord API Error 404: Invalid ID');
		}
		if ($headers['x-ratelimit-remaining'][0] === '0') {
			sleep($headers['x-ratelimit-reset-after'][0] + 1);
		}
		return $body;
	}

	public function getDiscordServer($serverId)
	{
		$result = new \stdClass();
		$response = $this->CallAPI(
			'GET',
			'https://discord.com/api/guilds/' . $serverId . '/preview',
			[],
			$this->getServiceHeaders('discord')
		);
		$body = $this->getBody($response);
		$result->status = 200;
		$result->data = [
			$body->approximate_member_count,
			$body->approximate_presence_count,
		];
		return new JsonResponse($result);
	}
}
