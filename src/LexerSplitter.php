<?php
/**
 * Created by PhpStorm.
 * User: huyanping
 * Date: 2016/10/27
 * Time: 上午11:35
 */

namespace Jenner\SQL\Parser;


class LexerSplitter
{
    private static $splitters = array("\r\n", "!=", ">=", "<=", "<>", ":=", "\\", "&&", ">", "<", "|", "=", "^", "(",
        ")", "\t", "\n", "'", "\"", "`", ",", "@", " ", "+", "-", "*", "/", ";");
    private $tokenSize;
    private $hashSet;

    public function __construct()
    {
        $this->tokenSize = strlen(self::$splitters[0]); # should be the largest one
        $this->hashSet = array_flip(self::$splitters);
    }

    public function getMaxLengthOfSplitter()
    {
        return $this->tokenSize;
    }

    public function isSplitter($token)
    {
        return isset($this->hashSet[$token]);
    }
}