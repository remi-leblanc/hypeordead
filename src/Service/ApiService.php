<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;

use App\Repository\TokenRepository;
use App\Entity\Token;

use App\Service\ApiTwitchService;
use App\Service\ApiRedditService;
use App\Service\ApiDiscordService;

class ApiService
{
	public $twitch;
	public $reddit;
	public $discord;

	public function __construct(ApiTwitchService $twitch, ApiRedditService $reddit, ApiDiscordService $discord) {
		$this->twitch = $twitch;
		$this->reddit = $reddit;
		$this->discord = $discord;
	}

}
