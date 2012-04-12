<?php

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class Model_Type_IPv6NetworkAddress extends Type
{
	public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return "VARCHAR(43) COMMENT '(DC2Type:".$this->getName.")'";
	}

	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		return $value;
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		$ipv6networkaddress = IPv6_Network_Address::factory($value);
		return $value;
	}

	public function getName()
	{
		return "ipv6networkaddress";
	}
}
