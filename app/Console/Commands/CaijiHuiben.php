<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use QL\QueryList;
use App\Jobs\CaijiHuiben as CaijiHuibenJob;

class CaijiHuiben extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CaijiHuiben';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '采集绘本中国数据';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("开始采集");
        //
        for($i=6297; $i<= 1000000; $i++){
            $this->info("开始采集第".$i."页");
            $url = 'https://huiben.cn/daquan/list-'.$i.'.shtml';
            // 定义采集规则
            $rules = [
                // 采集文章标题
                'title' => ["h4>a","text"],
                // 采集文章url
                'url' => ["h4>a","href"]
            ];
            $rt = QueryList::get($url)->rules($rules)->query()->getData();
            if(!$rt->all()){
                break;
            }
            foreach ($rt->all() as $job) {
                CaijiHuibenJob::dispatch($job);
            }
            $this->info("第".$i."页采集结束");
        }
        $this->info("采集结束");

    }
}
