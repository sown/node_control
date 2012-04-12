<?php

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class Model_Type_IPv4Address extends Type
{
	public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return "VARCHAR(15) COMMENT '(DC2Type:".$this->getName.")'";
	}

	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		return $value;
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		$ipv4address = IPv4_Address::factory($value);
		return $value;
	}

	public function getName()
	{
		return "ipv4address";
	}
}
