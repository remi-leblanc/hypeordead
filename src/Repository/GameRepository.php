<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

	public function findAllForDisplay(){
		return $this->createQueryBuilder('g')
			->leftJoin('g.tags', 'tags')
			->where('tags.hidden = 0')
			->addSelect('tags')
			->getQuery()
			->getResult();
	}

	public function findWithFilters($name, $tags){
		$qb = $this->createQueryBuilder('g')
		->leftJoin('g.tags', 'tags')
		->addSelect('tags');

		if($name || $name != ''){
			$qb->andWhere('g.name LIKE :name')
			->setParameter('name', '%'.$name.'%');
		}

		foreach($tags as $i => $tag){
			$qb->andWhere(":tag$i MEMBER OF g.tags")
			->setParameter("tag$i", $tag);
		}

		return $qb->getQuery()->getArrayResult();
	}

	public function findCreatedBeforeDate(\DateTime $compareDate){
		return $this->createQueryBuilder('g')
			->where("g.created_at < DATE_SUB(:compareDate, 2, 'HOUR')")
			->setParameter('compareDate', $compareDate)
			->getQuery()
			->getResult();
	}
}
