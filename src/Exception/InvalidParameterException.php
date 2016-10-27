<?php
/**
 * Created by PhpStorm.
 * User: huyanping
 * Date: 2016/10/27
 * Time: 上午11:16
 */

namespace Jenner\SQL\Parser\Exception;


class InvalidParameterException extends \Exception
{
    protected $argument;

    public function __construct($argument)
    {
        $this->argument = $argument;
        parent::__construct("no SQL string to parse: \n" . $argument, 10);
    }

    public function getArgument()
    {
        return $this->argument;
    }
}