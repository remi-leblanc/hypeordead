<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Repository\TokenRepository;

class Api
{
	protected $tokens = [];
	private $cronLogger;
	private $httpClient;

	public $twitch;

	public function __construct(TokenRepository $tokenRepository, LoggerInterface $cronLogger, HttpClientInterface $httpClient) {
		$tokens = $tokenRepository->findAll();
		foreach ($tokens as $token) {
			$this->tokens[$token->getServiceName()][$token->getHeaderKey()] = $token->getHeaderValue();
		}
		$this->cronLogger = $cronLogger;
		$this->httpClient = $httpClient;
	}

	protected function getServiceHeaders($service)
	{
		$headers = [];
		foreach ($this->tokens[$service] as $key => $token) {
			$headers[] = $key . ':' . $token;
		}
		return $headers;
	}

	protected function CallAPI($method, $url, $parameters = [], $httpheader = [], $options = [])
	{
		ini_set('max_execution_time', 0);
		$options = array_merge([
			'headers' => $httpheader,
			'query' => $parameters,
			'timeout' => 5
		], $options);
		try {
			$response = $this->httpClient->request($method, $url, $options);
		} catch (\Exception $e) {
			$this->cronLogger->info('HTTP Client Exception', [
				'message' => $e->getMessage()
			]);
		}

		return $response;
	}

	protected function getRequestData(Request $request, string $parameter)
	{
		if ($request->isMethod('get')) {
			$gameName = $request->query->get($parameter);
		} elseif ($request->isMethod('post')) {
			$gameName = $request->request->get($parameter);
		}
		return $gameName;
	}
}
