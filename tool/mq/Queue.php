<?php
namespace tool\mq;

use splQueue;

class Queue
{

    private $spl;

    public static function init()
    {
        return new static();
    }


    public function __construct()
    {
        $this->spl = new splQueue();
    }

    public function set($data)
    {
        if ($this->spl->push($data)) {
            return true;
        } else {
            return false;
        }

    }

    public function get()
    {
        $data = $this->spl->shift();
        if ($data) {
            return $data;
        } else {
            return null;
        }
    }


}