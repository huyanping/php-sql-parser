<?php

require_once(dirname(__FILE__) . '/../../../php-sql-parser.php');
require_once(dirname(__FILE__) . '/../../../php-sql-creator.php');
require_once(dirname(__FILE__) . '/../../test-more.php');


$sql = "SELECT * FROM `table` `t` WHERE ( ( UNIX_TIMESTAMP() + 3600 ) > `t`.`expires` ) ";
$parser = new Jenner\SQL\Parser\Parser($sql);
$creator = new Jenner\SQL\Parser\Creator($parser->parsed);
$created = $creator->created;
$expected = getExpectedValue(dirname(__FILE__), 'where.sql', false);
ok($created === $expected, 'expressions with function within WHERE clause');