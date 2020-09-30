<?php

require_once 'XlsExchange.php';

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
	->setInputFile('order.json')
	->setOutputFile('items.xlsx')
	->export()
	;

