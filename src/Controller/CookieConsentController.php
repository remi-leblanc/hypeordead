<?php

namespace App\Controller;

use App\Entity\CookieConsent;
use App\Form\CookieConsentType;
use App\Repository\CookieConsentRepository;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Uid\Uuid;

#[Route('/cookieconsent')]
class CookieConsentController extends AbstractController
{
    #[Route('/record', name: 'cookieconsent_record', methods: ['GET', 'POST'])]
    public function record(Request $request, CookieConsentRepository $cookieConsentRepository, ManagerRegistry $doctrine): Response
    {
		$cookieKey = $request->cookies->get('hodCookieConsentKey');
		if($cookieConsent = $cookieConsentRepository->findOneBy(['cookieKey' => $cookieKey])){
			$updating = true;
		} else {
			$updating = false;
			$cookieConsent = new CookieConsent();
			$cookieConsent->setAnalytics(true);
			$cookieConsent->setAds(true);
		}

		

		$form = $this->createForm(CookieConsentType::class, $cookieConsent, [
			'action' => $this->generateUrl('cookieconsent_record'),
		]);
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {

			$now = new DateTimeImmutable();
			$response = new Response();
			$cookies = [];

			if($form->get('save')->isClicked()){
				$cookies[] = "hodCookieConsentAnalytics=".(int)$cookieConsent->getAnalytics();
				$cookies[] = "hodCookieConsentAds=".(int)$cookieConsent->getAds();
			} else {
				$cookieConsent->setAnalytics(false);
				$cookies[] = "hodCookieConsentAnalytics=0";
				$cookieConsent->setAds(false);
				$cookies[] = "hodCookieConsentAds=0";
			}

			if($updating){
				$cookieConsent->setUpdatedAt($now);
	
				$doctrine->getManager()->flush();
			} else {
				$uuid = Uuid::v4();
				$uuid = bin2hex($uuid->toBinary());
	
				$cookieConsent->setUpdatedAt($now);
				$cookieConsent->setCreatedAt($now);
				$cookieConsent->setCookieKey($uuid);
				$cookieConsentRepository->add($cookieConsent, true);

				$cookies[] = "hodCookieConsentKey=$uuid";
			}

			$response->headers->set("Set-Cookie", $cookies);
			return $response;
		}

		return $this->renderForm('cookieconsent.html.twig', [
			'cookieconsent' => $cookieConsent,
			'form' => $form,
		]);
    }
}
