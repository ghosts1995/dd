<?php

namespace app\process;

use Data;
use Config;
use Log;
use Swoole\Process as swoole_process;

class BaseProcess
{
    private $process_list = [];         // 进程池对象数组
    private $process_use = [];          // 进程占用标记数组
    private $min_worker_num = 2;        // 进程池最小值
    private $max_worker_num = 6;        // 进程池最大值

    private $current_num;               // 当前进程数

    /**
     * @var array
     */
    private $proxyFunction = ['dowProxy', 'checkProxy'];


    /**
     * 对外创建
     * @param $name
     * @param $address
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @param int $status
     */
    private function createSub($status = 0, $fun)
    {
        $process = new swoole_process(array($this, $fun), false, 2);
        $pid = $process->start();
        //设置进程名称
        $process->name("{$fun} worker pid:{$pid} ");
        Log::cmd(" {$fun} worker pid:{$pid} ");
        $this->process_list[$pid] = $process;
        //进程闲置
        $this->process_use[$pid] = ['use' => $status, 'fun' => $fun];
        return ['process' => $process, 'pid' => $pid];
    }


    /**
     * 子进程任务结束
     */
    public function endSub()
    {
        // 绑定子进程管道的读事件，接收子进程任务结束的通知
        foreach ($this->process_list as $process) {
            swoole_event_add($process->pipe, function ($pipe) use ($process) {
                $subPid = $process->read();
                Log::cmd("绑定子进程通知:{$subPid}");
                $this->process_use[$subPid]['use'] = 0;
            });
        }
    }


    /**
     * 下载
     */
    public function runDowProxy()
    {
        $this->current_num = $this->min_worker_num;
        // 初始化进程池
        for ($i = 0; $i < $this->current_num; $i++) {
            //创建进程
            $this->createSub(0, 'dowProxy');
        }
        $this->endSub();

        //71000
        swoole_timer_tick(71000, function ($timer_id) {
            $flag = true;
            // 查找是否有可用的进程派发任务
            foreach ($this->process_use as $pid => $val) {
                // 找到了闲置的进程
                if ($val['use'] == 0) {
                    $flag = false;
                    $this->process_use[$pid]['use'] = 1;
                    // 派发任务
                    $this->process_list[$pid]->write("进程:{$this->process_use[$pid]['fun']}");
                    break;
                }
            }

            // 没有找到进程，并且进程池并没有满
            if ($flag && $this->current_num < $this->max_worker_num) {
                $sub = $this->createSub(1, 'dowProxy');
                $pid = $sub['pid'];
                $process = $sub['process'];
                // 派发任务
                $this->process_list[$pid]->write("进程:{$this->process_use[$pid]['fun']}");
                $this->current_num++;
                // 绑定子进程管道的读事件
                swoole_event_add($process->pipe, function ($pipe) use ($process) {
                    $subPid = $process->read();
                    $this->process_use[$subPid]['use'] = 0;
                    Log::cmd("add sub $subPid");
                });
            }

//            if ($index == 10) {
//                // 任务结束，退出所有子进程
//                foreach ($this->process_list as $process) {
//                    $process->write("exit");
//                }
//                Log::cmd("任务完成");
//                swoole_timer_clear($timer_id);
//            }


        });


    }

    /**
     *
     */
    public function runSetProxy()
    {
        $this->current_num = 3;
        // 初始化进程池
        for ($i = 0; $i < $this->current_num; $i++) {
            //创建进程
            $this->createSub(0, 'checkProxy');
        }
        $this->endSub();

        //71000
        swoole_timer_tick(500, function ($timer_id) {
            $flag = true;
            // 查找是否有可用的进程派发任务
            foreach ($this->process_use as $pid => $val) {
                // 找到了闲置的进程
                if ($val['use'] == 0) {
                    $flag = false;
                    $this->process_use[$pid]['use'] = 1;
                    // 派发任务
                    $this->process_list[$pid]->write("进程:{$this->process_use[$pid]['fun']}");
                    break;
                }
            }

            // 没有找到进程，并且进程池并没有满
            if ($flag && $this->current_num < $this->max_worker_num) {
                $sub = $this->createSub(1, 'checkProxy');
                $pid = $sub['pid'];
                $process = $sub['process'];
                // 派发任务
                $this->process_list[$pid]->write("进程:{$this->process_use[$pid]['fun']}");
                $this->current_num++;
                // 绑定子进程管道的读事件
                swoole_event_add($process->pipe, function ($pipe) use ($process) {
                    $subPid = $process->read();
                    $this->process_use[$subPid]['use'] = 0;
                    Log::cmd("add sub $subPid");
                });
            }
        });


    }

}
