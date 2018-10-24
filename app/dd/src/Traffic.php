<?php

namespace app\dd\src;

trait Traffic
{
    public $clinet;

    public $clinetData;

    public $clientInfo;

    /**
     * 访问是否成功
     * @var bool
     */
    public $is_visit = true;

    //计费数据
    private $charging;

    private $writeData;

    public $state = false;

    public function main()
    {
        if ($this->state) {
            $this->charging = $this->clientInfo;
            $this->bindStrlen();
            $this->bindVisit();
            $this->write();
        }
    }

    private function bindVisit()
    {
        if ($this->is_visit) {
            $this->charging['is_visit'] = 1;
        } else {
            $this->charging['is_visit'] = 2;
        }
    }

    private function bindStrlen()
    {
        $this->charging['strlen'] = mb_strlen($this->clinetData, 'UTF-8');
    }

    private function bindWriteData()
    {
        if ($this->charging) {
            $info = '';
            foreach ($this->charging as $k => $v) {
                $info .= $k . '=' . $v . " ####  ";
            }
            $this->writeData = $info;
        }
    }

    private function write()
    {
        $this->bindWriteData();
        $info = $this->writeData;
        $now = date('Y-m-d H:i:s', time());
        $data = '[' . $now . '] ' . $info . PHP_EOL;
        file_put_contents('Traffic.logs', $data . "\n", FILE_APPEND);
        unset($data);
    }


}