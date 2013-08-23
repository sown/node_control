<?php

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections;

class Model_Repository_InventoryItem extends EntityRepository {

	public function all(QueryBuilder $qb = null)
	{
		if ($qb === NULL)
			$qb = $this->createQueryBuilder('i');
                $qb->orderBy('i.id', 'ASC');
		
		return $qb->getQuery()->getResult();	
	}
	
}
