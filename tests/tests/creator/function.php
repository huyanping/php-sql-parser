<?php

require_once(dirname(__FILE__) . '/../../../php-sql-parser.php');
require_once(dirname(__FILE__) . '/../../../php-sql-creator.php');
require_once(dirname(__FILE__) . '/../../test-more.php');


$sql = 'SELECT 	SUM( 10 ) as test FROM account';
$parser = new Jenner\SQL\Parser\Parser($sql);
$creator = new Jenner\SQL\Parser\Creator($parser->parsed);
$created = $creator->created;
$expected = getExpectedValue(dirname(__FILE__), 'function.sql', false);
ok($created === $expected, 'a function');

