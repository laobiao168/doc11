<?php

namespace app\api\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;
use think\Log;
use app\index\controller\Ajax;


class Kai extends Command
{

    protected function configure()
    {
        $this->setName('kaijiang')->setDescription('私彩开奖');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("开始----");
        $a = new Ajax();
        while (1){
            $a->wy();

            $output->writeln("处理成功----");
            sleep(2);
        }


    }

}