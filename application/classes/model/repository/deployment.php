<?php

use Doctrine\ORM\Query;

class Model_Repository_Deployment extends EntityRepository {

	public function where_is_finished(QueryBuilder $qb = null)
	{die('this is wrong.');
		if ($qb === NULL)
			$qb = $this->_em->createQueryBuilder();

		return $qb->where('endDate', '!=', NULL);
	}
	
	public function where_is_active(QueryBuilder $qb = null)
	{die('this is wrong.');
		if ($qb === NULL)
			$qb = $this->_em->createQueryBuilder();

		return $qb
			->where('endDate', '=', NULL)
			->andWhere('startDate', '<=', 'NOW()');
	}

}
