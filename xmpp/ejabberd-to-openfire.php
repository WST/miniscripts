#!/usr/bin/php
<?php

$dsn = 'mysql:dbname=ejabberd_jabberid_org;host=127.0.0.1';
$user = 'root';
$password = '';
$domain = 'jabberid.org';

try {
	$pdo = new PDO($dsn, $user, $password);
} catch(PDOException $e) {
	die("{$e->getMessage()}\n");
}

function validate_domain($jid) {
	return preg_match('#^([a-z0-9\-.])+$#', $jid) && $jid[0] != '.' && $jid[strlen($jid) - 1] != '.';
}

function validate_item($jid) {
	$jid = explode('@', $jid);
	switch(count($jid)) {
		case 1: // only domain part (and resource?)
			$jid = explode('/', $jid[0]);
			return validate_domain($jid[0]);
		break;
		case 2: // username and domain part
			$jid = explode('/', $jid[1]);
			return validate_domain($jid[0]);
		break;
	}
	
	return false;
}

$writer = new XMLWriter();
$writer->openMemory();
//$writer->setIndent(true);
$writer->startDocument('1.0', 'UTF-8');
$writer->startElement('Openfire');

$user_statement = $pdo->prepare('SELECT * FROM users');
$roster_statement = $pdo->prepare('SELECT * FROM rosterusers WHERE username = :username');
$vcard_statement = $pdo->prepare('SELECT vcard FROM vcard WHERE username = :username');

$user_statement->execute();
while($user = $user_statement->fetch()) {
	if($user['username'] == 'admin') continue;
	
	$vcard_statement->bindParam(':username', $user['username']);
	$roster_statement->bindParam(':username', $user['username']);
	$vcard_statement->execute();
	$roster_statement->execute();
	
	if($vcard = $vcard_statement->fetch()) {
		$user['name'] = '';
		$user['Email'] = '';
	} else {
		$user['name'] = $user['username'];
		$user['Email'] = '';
	}
	$vcard_statement->closeCursor();
	
	$matches = array();
	preg_match('#^([0-9]{4})\-([0-9]{2})\-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$#', $user['created_at'], $matches);
	$creation_date = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
	
	$writer->startElement('User');
	
	$writer->startElement('Username');
	$writer->writeCData($user['username']);
	$writer->endElement();
	
	$writer->startElement('Password');
	$writer->writeCData($user['password']);
	$writer->endElement();
	
	$writer->startElement('Name');
	$writer->writeCData('');
	$writer->endElement();
	
	$writer->startElement('Email');
	$writer->writeCData('');
	$writer->endElement();
	
	$writer->startElement('ModifiedDate');
	$writer->writeCData("{$creation_date}000");
	$writer->endElement();
	
	$writer->startElement('CreationDate');
	$writer->writeCData("{$creation_date}000");
	$writer->endElement();
	
	$writer->startElement('Roster');
	while($item = $roster_statement->fetch()) {
		if(!validate_item($item['jid'])) continue;
		
		$writer->startElement('Item');
		
		$writer->writeAttribute('jid', $item['jid']);
		$writer->writeAttribute('askstatus', '-1');
		$writer->writeAttribute('recvstatus', '-1');
		$writer->writeAttribute('substatus', '3');
		$writer->writeAttribute('name', $item['nick']);
		
		$writer->endElement();
	}
	$writer->endElement();
	
	$roster_statement->closeCursor();
	
	$writer->endElement();
}
$user_statement->closeCursor();

$writer->endElement();
$writer->endDocument();

file_put_contents('results.xml', $writer->outputMemory());
