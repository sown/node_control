<?php defined('SYSPATH') or die('No direct script access.');

class SownValid extends Valid {

        public static function mac($value)
        {
                return (bool) preg_match(
                        '/^([0-9a-fA-F]{2}:){5}[0-9a-fA-F]{2}$/',
                        $value
                );
        }
}

