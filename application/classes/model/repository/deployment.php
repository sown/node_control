<?php

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections;

class Model_Repository_Deployment extends EntityRepository {

	public function where_is_finished(QueryBuilder $qb = null)
	{
		if ($qb === NULL)
			$qb = $this->createQueryBuilder('d');
		
		$qb->where("d.endDate < :now");
                $qb->orderBy('d.name', 'ASC');
                $qb->setParameter('now', new \DateTime());
		
		return $qb->getQuery()->getResult();	
	}
	
	public function where_is_active(QueryBuilder $qb = null)
	{
		if ($qb === NULL)
			$qb = $this->createQueryBuilder('d');

		$qb->where("d.endDate >= :now");
		$qb->andWhere("d.startDate <= :now");
		$qb->orderBy('d.name', 'ASC');
		$qb->setParameter('now', new \DateTime());
		
		return $qb->getQuery()->getResult();
	}

}
