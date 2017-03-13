<?php

namespace App\Services;

/**
 * Class SqlEscape
 * PSQL escape methods
 * @package App\Services
 */
trait SqlEscape
{
	/**
	 * Escape a LIKE expression
	 * @param string $str
	 * @param bool $toLowerCase
	 * @return string
	 */
	final protected static function escapeLike(string $str, bool $toLowerCase = true): string
	{
		if ( !is_string($str) ) {
			return '';
		}

		if ($toLowerCase) {
			$str = mb_strtolower($str);
		}

		$str = trim($str);
		$str = str_replace(['%', "'", "\""], '', $str);
		$str = self::psqlEscapeString($str);

		return $str;
	}

	/**
	 * An OOP wrapper for pg_escape_string()
	 * @param string $str
	 * @return string
	 */
	final protected static function psqlEscapeString(string $str): string
	{
		return pg_escape_string($str);
	}
}
