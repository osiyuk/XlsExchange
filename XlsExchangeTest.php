<?php

require_once 'XlsExchange.php';

define('TEST_EAN13', true);

if (TEST_EAN13)
(new XlsExchange())
	->testEAN13('2109970000015')
	->testEAN13('2154231000019')
	->testEAN13('2109991000018')
	->testEAN13('2151212000017')
	->testEAN13('2109980000012')
	->testEAN13('2151246000014')
	->testEAN13('2151246000019')
	;

(new XlsExchange())
	->setFtpHost('localhost')
	->setFtpLogin('root')
	->setFtpPassword('password')
	->setFtpDir('/home')
	;

(new XlsExchange())
	->setInputFile('order.json')
	->setOutputFile('items.xlsx')
	->export()
	;

