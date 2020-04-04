<?php
namespace App\Relase;
use EasySwoole\Component\Process\AbstractProcess;
use Swoole\Coroutine;
class Relase extends AbstractProcess
{

    protected function run($arg)
    {
        go(static function () {
            while (true)
            {
                echo '开始Git pull'.PHP_EOL;
                $exec = 'cd ' .EASYSWOOLE_ROOT. '; git pull';
                $pullResult = exec($exec);
                if (strpos($pullResult, 'Unpacking objects: 100%') !== false) {
                    echo '有新版本发布'.PHP_EOL;

                } else {
                    echo '无新版本发布'.PHP_EOL;
                }
                // 每5秒同步一次代码
                Coroutine::sleep(5);
            }
        });

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