<?php defined('SYSPATH') or die('No direct script access.');

class PKI {

	public static function PEM_decode($text)
	{
		$text = explode("\n",$text);
		// Chop off the leading and trailing lines
		$text = array_slice($text, 1, -2);
		return base64_decode(join($text));
	}

	/**
	 * Encode a binary certificate (DER) in base64 encoded form (PEM)
	 * 
	 */
	public static function PEM_encode_certificate($data)
	{
		return "-----BEGIN CERTIFICATE-----\n".static::_PEM_encode($data)."-----END CERTIFICATE-----\n";
	}
	
	/**
	 * Encode a binary key (DER) in base64 encoded form (PEM)
	 * 
	 */
	public static function PEM_encode_key($data)
	{
		return "-----BEGIN RSA PRIVATE KEY-----\n".static::_PEM_encode($data)."-----END RSA PRIVATE KEY-----\n";
	}
	
	protected static function _PEM_encode($data)
	{
		return chunk_split(base64_encode($data), 64, "\n");
	}
}
