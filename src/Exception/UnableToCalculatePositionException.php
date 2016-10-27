<?php
/**
 * Created by PhpStorm.
 * User: huyanping
 * Date: 2016/10/27
 * Time: 上午11:17
 */

namespace Jenner\SQL\Parser\Exception;


class UnableToCalculatePositionException extends \Exception
{
    protected $needle;
    protected $haystack;

    public function __construct($needle, $haystack)
    {
        $this->needle = $needle;
        $this->haystack = $haystack;
        parent::__construct("cannot calculate position of " . $needle . " within " . $haystack, 5);
    }

    public function getNeedle()
    {
        return $this->needle;
    }

    public function getHaystack()
    {
        return $this->haystack;
    }
}