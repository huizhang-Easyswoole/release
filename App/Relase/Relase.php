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
                Coroutine::sleep(5);
                error_log('开始检测代码是否更新3'.PHP_EOL, 3, '/Users/yuzhao3/sites/es-log.log');

                $diffExec = 'cd ' .EASYSWOOLE_ROOT. '; git fetch; git diff --stat master origin/master;';
                $pullResult = exec($diffExec);
                error_log(json_encode($pullResult), 3, '/Users/yuzhao3/sites/es-log.log');

                if ($pullResult !== '') {
                    error_log('有新版本发布'.PHP_EOL, 3, '/Users/yuzhao3/sites/es-log.log');
                    $newVersionPath = '../relase-'.time();

                    $cloneExec = "git clone https://github.com/huizhang-Easyswoole/release.git {$newVersionPath} >> shell.log;cd {$newVersionPath} >> shell.log;composer update >> shell.log; >> shell.log";
                    $res = exec($cloneExec, $a, $b);
                    error_log(json_encode([$cloneExec, $res, $a, $b]), 3, '/Users/yuzhao3/sites/es-log.log');
                    error_log('新版本代码clone'.PHP_EOL, 3, '/Users/yuzhao3/sites/es-log.log');

                    $lsofExec = 'lsof -i:9501';
                    $lsofResult = exec($lsofExec);
                    $newPort = 9501;
                    $oldPort = 9502;
                    if ($lsofResult !== '') {
                        $newPort = 9502;
                        $oldPort = 9501;
                    }

                    error_log('开始替换端口'.$newPort.PHP_EOL, 3, '/Users/yuzhao3/sites/es-log.log');

                    $devConfig = file_get_contents($newVersionPath.'/dev.php');
                    $devConfig = str_replace($oldPort, $newPort, $devConfig);
                    file_put_contents($newVersionPath.'/dev.php', $devConfig);

                    error_log('新服务启动'.PHP_EOL, 3, '/Users/yuzhao3/sites/es-log.log');
                    $startExec = "cd {$newVersionPath}; php easyswoole start d";
                    exec($startExec);

                    // 替换nginx配置
                    error_log('开始替换ng端口'.PHP_EOL, 3, '/Users/yuzhao3/sites/es-log.log');
                    $ngConfigPath = '/usr/local/etc/nginx/nginx.conf';
                    $ngConfig  = file_get_contents($ngConfigPath);
                    $ngConfig = str_replace($oldPort, $newPort, $ngConfig);
                    file_put_contents($ngConfigPath, $ngConfig);

                    error_log('重启ng'.PHP_EOL, 3, '/Users/yuzhao3/sites/es-log.log');
                    $reloadNgExec = 'nginx -s reload';
                    exec($reloadNgExec);

                    error_log('旧服务停掉'.PHP_EOL, 3, '/Users/yuzhao3/sites/es-log.log');
                    $stopExec = 'php easyswoole stop';
                    exec($stopExec);

                    // 每5秒同步一次代码1
                    Coroutine::sleep(5);
                } else {
                    error_log('无新版本'.PHP_EOL, 3, '/Users/yuzhao3/sites/es-log.log');

                }

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