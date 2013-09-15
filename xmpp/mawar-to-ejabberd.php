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
	private $statements;

	public function __construct($config) {
		try {
			$this->logIntro("\n Mawar to Ejabberd migration script\n © 2013 Ilya I. Averkov <http://github.com/WST>\n");
			$this->logMessage("Connecting databases...");
			$this->mawar = new PDO($config['mawar']['dsn'], $config['mawar']['user'], $config['mawar']['password']);
			$this->ejabberd = new PDO($config['ejabberd']['dsn'], $config['ejabberd']['user'], $config['ejabberd']['password']);
			$this->logWarning("Successfully connected to the databases!");
			$this->mawar->exec('SET NAMES UTF8');
			$this->ejabberd->exec('SET NAMES UTF8');

			// Preparing statements
			$this->statements['USERS'] = $this->mawar->prepare('SELECT * FROM users ORDER BY user_login ASC');
			$this->statements['ROSTER'] = $this->mawar->prepare('SELECT * FROM roster WHERE id_user = :user_id');
			$this->statements['VCARD'] = $this->mawar->prepare('SELECT * FROM vcard WHERE id_user = :user_id');
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

	private function logWarning($message) {
		echo "\033[01;33m{$message}\n\033[0m";
	}

	private function logError($message, $exitcode = -1) {
		echo "\033[22;31m{$message}\n\033[0m";
		die($exitcode);
	}

	public function execute() {
		$this->logWarning("Starting actual migration process");

		$this->statements['USERS']->execute();
		while($user_row = $this->statements['USERS']->fetch()) {
			$username = $this->ejabberd->quote($user_row['user_login']);
			$password = $this->ejabberd->quote($user_row['user_password']);

			$this->ejabberd->beginTransaction();	
			$this->ejabberd->exec("INSERT INTO users (username, password) VALUES ($username, $password)");

			// Retreiving current user’s roster items
			$this->statements['ROSTER']->bindParam(':user_id', $user_row['id_user']);
			$this->statements['ROSTER']->execute();

			while($roster_row = $this->statements['ROSTER']->fetch()) {
				$jid = $this->ejabberd->quote($roster_row['contact_jid']);
				$nick = $this->ejabberd->quote($roster_row['contact_nick']);
				$subscription = $roster_row['contact_subscription'];

				$this->ejabberd->exec("INSERT INTO rosterusers (username, jid, nick, subscription, ask, server, type) VALUES ($username, $jid, $nick, '$subscription', 'N', 'N', 'item')");
			}
			$this->statements['ROSTER']->closeCursor();

			// Retreiving current user’s vCard
			$this->statements['VCARD']->bindParam(':user_id', $user_row['id_user']);
			$this->statements['VCARD']->execute();
			if($row = $this->statements['VCARD']->fetch()) {
				$vcard = $this->ejabberd->quote($row['vcard_data']);
				$this->ejabberd->exec("INSERT INTO vcard (username, vcard) VALUES ($username, $vcard)");
			}
			$this->statements['VCARD']->closeCursor();

			$this->ejabberd->commit();

			$this->logMessage("Done for user: {$user_row['user_login']}");
		}
		$this->statements['USERS']->closeCursor();

		$this->logWarning("Finishing migration process");
		$this->logWarning("Job done");
	}
}

$migration = new Migration($config);
$migration->execute();
