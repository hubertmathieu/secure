<?php

final class CuNhsVJXkPguGPXBs8eT1
{
	public const authenticate = "Les informations d'authentification sont invalides";
	public const username_already_taken = "Le nom d'utilisateur existe déjà pour un autre client";
	public const email_invalid = "Le courriel est invalide";
	public const username_empty = "Le nom d'utilisateur ne doit pas être vide";
	public const email_empty = "Le courriel ne doit pas être vide";

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
