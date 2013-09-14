#!/usr/bin/php
<?php

$config = [];

// Mawar’s database
$config['mawar']['dsn'] = 'mysql:dbname=mawar_jabberid_org;host=127.0.0.1';
$config['mawar']['user'] = 'mawar';
$config['mawar']['password'] = '123456';

// Ejabberd’s database
$config['ejabberd']['dsn'] = 'mysql:dbname=ejabberd_jabberid_org;host=127.0.0.1';
$config['ejabberd']['user'] = 'ejabberd';
$config['ejabberd']['password'] = '123456';

//////////// Do not edit the code below this line ////////////

class Migration
{
	private $mawar;
	private $ejabberd;

	public function __construct($config) {
		try {
			$this->logIntro("\n Mawar to Ejabberd migration script\n © 2013 Ilya I. Averkov <http://github.com/WST>\n");
			$this->logMessage("Connecting databases...");
			$this->mawar = new PDO($config['mawar']['dsn'], $config['mawar']['user'], $config['mawar']['password']);
			$this->ejabberd = new PDO($config['ejabberd']['dsn'], $config['ejabberd']['user'], $config['ejabberd']['password']);
			$this->logImportantMessage("Successfully connected to the databases!");
			$this->mawar->exec('SET NAMES UTF8');
			$this->ejabberd->exec('SET NAMES UTF8');
		} catch(PDOException $e) {
			$this->logError($e->getMessage());
		}
	}

	private function logMessage($message) {
		echo "\033[22;37m{$message}\n\033[0m";
	}

	private function logIntro($message) {
		echo "\033[22;35m{$message}\n\033[0m";
	}

	private function logWarning() {
		echo "\033[01;33m{$message}\n\033[0m";
	}

	private function logError($message, $exitcode = -1) {
		echo "\033[22;31m{$message}\n\033[0m";
		die($exitcode);
	}

	public function execute() {

	}
}

$migration = new Migration($config);
$migration->execute();
