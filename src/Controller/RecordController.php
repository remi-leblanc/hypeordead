<?php

namespace App\Controller;

use App\Entity\Record;
use App\Repository\RecordRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\HttpFoundation\JsonResponse;

use App\Service\ApiService;


#[Route("/admin/record")]
class RecordController extends AbstractController
{

	private $api;

	public function __construct(ApiService $api)
	{
		$this->api = $api;
	}

	#[Route("/", name: "record_group_index", methods: ["GET"])]
	public function index(RecordRepository $recordRepository): Response
	{
		return $this->render('record/group/index.html.twig', [
			'records' => $recordRepository->getRecordsGroupedByDate(),
		]);
	}

	#[Route("/{date}", name: "record_group_hide", methods: ["POST"])]
	public function hide(string $date, ManagerRegistry $doctrine, RecordRepository $recordRepository): Response
	{
		$entityManager = $doctrine->getManager();
		$records = $recordRepository->getByDay(new \DateTime($date));
		foreach($records as $record){
			$record->setHidden(!$record->isHidden());
		}
		$entityManager->flush();

		return $this->redirectToRoute('record_group_index');
	}

	#[Route("/{id}", name: "record_group_delete", methods: ["DELETE"])]
	public function delete(Request $request, Record $record, ManagerRegistry $doctrine): Response
	{
		if ($this->isCsrfTokenValid('delete' . $record->getId(), $request->request->get('_token'))) {
			$entityManager = $doctrine->getManager();
			$entityManager->remove($record);
			$entityManager->flush();
		}

		return $this->redirectToRoute('record_group_index');
	}
}
