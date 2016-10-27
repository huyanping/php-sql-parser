<?php
/**
 * Created by PhpStorm.
 * User: huyanping
 * Date: 2016/10/27
 * Time: 上午11:15
 */

namespace Jenner\SQL\Parser\Exception;


class UnsupportedFeatureException extends \Exception
{
    protected $key;

    public function __construct($key)
    {
        $this->key = $key;
        parent::__construct($key . " not implemented.", 20);
    }

    public function getKey()
    {
        return $this->key;
    }
}