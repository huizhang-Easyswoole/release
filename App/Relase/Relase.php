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

                $diffExec = 'cd ' .EASYSWOOLE_ROOT. '; git fetch; git diff --stat master origin/master;';
                $pullResult = exec($diffExec);

                if ($pullResult !== '') {
                    $newVersionPath = '../relase-'.time();

                    $cloneExec = "git clone https://github.com/huizhang-Easyswoole/release.git {$newVersionPath};cd {$newVersionPath};composer update;";
                    exec($cloneExec);

                    $lsofExec = 'lsof -i:9501';
                    $lsofResult = exec($lsofExec);
                    $newPort = 9501;
                    $oldPort = 9502;
                    if ($lsofResult !== '') {
                        $newPort = 9502;
                        $oldPort = 9501;
                    }

                    echo '开始替换启动端口'.PHP_EOL;
                    $devConfig = file_get_contents($newVersionPath.'/dev.php');
                    $devConfig = str_replace($oldPort, $newPort, $devConfig);
                    file_put_contents($newVersionPath.'/dev.php', $devConfig);

                    $startExec = "cd {$newVersionPath}; php easyswoole start d";
                    exec($startExec);

                    // 替换nginx配置
                    $ngConfigPath = '/usr/local/etc/nginx/nginx.conf';
                    $ngConfig  = file_get_contents($ngConfigPath);
                    $ngConfig = str_replace($oldPort, $newPort, $ngConfig);
                    file_put_contents($ngConfigPath, $ngConfig);

                    // 重启nginx
                    $reloadNgExec = 'nginx -s reload';
                    exec($reloadNgExec);

                    $stopExec = 'php easyswoole stop';
                    exec($stopExec);

                    // 每5秒同步一次代码1
                    Coroutine::sleep(100);
                } else {
                    echo '无新版本发布'.PHP_EOL;

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