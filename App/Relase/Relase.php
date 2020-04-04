<?php
namespace App\Relase;
use EasySwoole\Component\Process\AbstractProcess;
use Swoole\Coroutine;
class Relase extends AbstractProcess
{

    protected function run($arg)
    {
        while (true)
        {

            $exec = 'cd ' .EASYSWOOLE_ROOT. '; git pull';
            $pullResult = exec($exec);
            var_dump($pullResult);
            // 每5秒同步一次代码
            Coroutine::sleep(5);
        }
    }

    protected function onPipeReadable(\Swoole\Process $process)
    {

    }

    protected function onShutDown()
    {

    }

    protected function onException(\Throwable $throwable, ...$args)
    {

    }
}