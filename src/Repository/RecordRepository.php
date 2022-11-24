<?php

namespace App\Repository;

use App\Entity\Record;
use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Record|null find($id, $lockMode = null, $lockVersion = null)
 * @method Record|null findOneBy(array $criteria, array $orderBy = null)
 * @method Record[]    findAll()
 * @method Record[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecordRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Record::class);
	}

	/**
	 * @return Record[]
	 */
	public function getLast2RecordsForGame(Game $game)
	{
		return $this->createQueryBuilder('r')
			->andWhere('r.game = ' . $game->getId())
			->addOrderBy('r.date', 'DESC')
			->setMaxResults(2)
			->getQuery()
			->getResult();
	}

	/**
	 * @return \DateTime
	 */
	public function getLastRecordDate()
	{
		return $this->createQueryBuilder('r')
			->addOrderBy('r.date', 'DESC')
			->setMaxResults(1)
			->getQuery()
			->getSingleResult()
			->getDate();
	}

	/**
	 * @return Record[]
	 */
	public function getLast2Records($currentDate = null)
	{
		if(!$currentDate){
			$currentDate = $this->getLastRecordDate();
		}
		return $this->createQueryBuilder('r')
			->where("DATE(r.date) >= DATE_SUB(DATE_SUB(DATE(:currentDate), 1, 'WEEK'), 2, 'DAY')")
			->andWhere("r.hidden = 0")
			->setParameter('currentDate', $currentDate)
			->getQuery()
			->getResult();
	}

	/**
	 * @return Record[]
	 */
	public function getRecordsGroupedByDate()
	{
		return $this->createQueryBuilder('r')
		->select('r.date, r.hidden, DATE(r.date) AS groupedDate, count(r.id) as count')
		->groupBy('groupedDate')
		->orderBy('groupedDate', 'desc')
		->getQuery()
		->getResult();
	}

	/**
	 * @return Record[]
	 */
	public function getByDay(\DateTime $date)
	{
		return $this->createQueryBuilder('r')
		->where('DATE(r.date) = :date')
		->setParameter('date', $date->format('Y-m-d'))
		->getQuery()
		->getResult();
	}
}
