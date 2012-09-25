<?php

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections;

class Model_Repository_Node extends EntityRepository {

	public function all(QueryBuilder $qb = null)
	{
		if ($qb === NULL)
			$qb = $this->createQueryBuilder('n');
//		$qb->leftJoin('n.Deployment', 'd', 'ON', 'n.id = d.node_id');
                $qb->orderBy('n.box_number', 'ASC');
		
		return $qb->getQuery()->getResult();	
	}
	
}
