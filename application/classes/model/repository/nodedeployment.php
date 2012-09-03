<?php

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;

class Model_Repository_NodeDeployment extends EntityRepository {

	public function where_is_finished(QueryBuilder $qb = null)
	{
		if ($qb === NULL)
                	$qb = $this->createQueryBuilder('nd');

                $qb->where("nd.endDate < :now");
                $qb->orderBy('nd.name', 'ASC');
                $qb->setParameter('now', new \DateTime());

                return $qb->getQuery()->getResult();

	}
	
	public function where_is_active(QueryBuilder $qb = null)
	{
		if ($qb === NULL)
                        $qb = $this->createQueryBuilder('nd');

                $qb->where("nd.endDate >= :now");
                $qb->andWhere("nd.startDate <= :now");
                $qb->orderBy('nd.name', 'ASC');
                $qb->setParameter('now', new \DateTime());

                return $qb->getQuery()->getResult();
	}

}
