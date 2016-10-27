<?php
/**
 * Created by PhpStorm.
 * User: huyanping
 * Date: 2016/10/27
 * Time: 上午11:15
 */

namespace Jenner\SQL\Parser\Exception;


class UnableToCreateSQLException extends \Exception
{
    protected $part;
    protected $partkey;
    protected $entry;
    protected $entrykey;

    public function __construct($part, $partkey, $entry, $entrykey)
    {
        $this->part = $part;
        $this->partkey = $partkey;
        $this->entry = $entry;
        $this->entrykey = $entrykey;
        parent::__construct("unknown " . $entrykey . " in " . $part . "[" . $partkey . "] " . $entry[$entrykey], 15);
    }

    public function getEntry()
    {
        return $this->entry;
    }

    public function getEntryKey()
    {
        return $this->entrykey;
    }

    public function getSQLPart()
    {
        return $this->part;
    }

    public function getSQLPartKey()
    {
        return $this->partkey;
    }
}