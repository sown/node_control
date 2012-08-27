<?php

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;

class Model_Repository_NodeDeployment extends EntityRepository {

	public function where_is_finished(QueryBuilder $qb = null)
	{
		if ($qb === NULL)
			$qb = $this->_em->createQueryBuilder();

		return $qb->where('endDate < NOW()');
	}
	
	public function where_is_active(QueryBuilder $qb = null)
	{
		if ($qb === NULL)
			$qb = $this->_em->createQueryBuilder();

		return $qb
			->where('endDate >= NOW()');
			->andWhere('startDate <= NOW()');
	}

}
