<?php

require_once(dirname(__FILE__) . '/../../../php-sql-parser.php');
require_once(dirname(__FILE__) . '/../../../php-sql-creator.php');
require_once(dirname(__FILE__) . '/../../test-more.php');


$sql = "SELECT a.* FROM tabla_a a WHERE (a.client_id in (1,2,3))";
$parser = new Jenner\SQL\Parser\Parser($sql);
$creator = new Jenner\SQL\Parser\Creator($parser->parsed);
$created = $creator->created;
$expected = getExpectedValue(dirname(__FILE__), 'issue58.sql', false);
ok($created === $expected, 'in-list within WHERE expression');