<?php

require "CuNhsVJXkPguGPXBs8eT1.php";

final class Localize
{
	public static function errors(): CuNhsVJXkPguGPXBs8eT1
	{
		return CuNhsVJXkPguGPXBs8eT1::getInstance();
	}

	private static $instance = null;

	public static function getInstance(): self
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}
}
