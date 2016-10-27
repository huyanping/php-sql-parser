<?php

require_once(dirname(__FILE__) . '/../../../php-sql-parser.php');
require_once(dirname(__FILE__) . '/../../../php-sql-creator.php');
require_once(dirname(__FILE__) . '/../../test-more.php');


$sql = "SELECT SUM(value)/(ABS(2)) as x FROM table";
$parser = new Jenner\SQL\Parser\Parser($sql);
$creator = new Jenner\SQL\Parser\Creator($parser->parsed);
$created = $creator->created;
$expected = getExpectedValue(dirname(__FILE__), 'issue66.sql', false);
ok($created === $expected, 'Expression subtree should not fail.');