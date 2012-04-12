<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;

/**
 * Model_Certificate
 *
 * @Table(name="certificates")
 * @Entity
 */
class Model_Certificate extends Model_Entity
{
	/**
	 * @var blob $publicKey
	 *
	 * @Column(name="public_key", type="blob", nullable=false)
	 */
	private $publicKey;

	/**
	 * @var blob $privateKey
	 *
	 * @Column(name="private_key", type="blob", nullable=false)
	 */
	private $privateKey;

	/**
	 * @var boolean $current
	 *
	 * @Column(name="current", type="boolean", nullable=false)
	 */
	private $current;
}
