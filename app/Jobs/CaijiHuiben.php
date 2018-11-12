<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use QL\QueryList;

class CaijiHuiben implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $url = 'https://huiben.cn'
    private $title = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($book)
    {
        //
        $this->url .= $book['url'];
        $this->title = $book['title'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->info("开始采集".$this->title);
        //
        $url = $this;

        $ql = QueryList::get($this->url);
        // 定义采集规则
        $rules = [
            'title' => ["div.ny_hb_title>div>h3", "text"],
            'keyword' => ["div.ny_hb_title>div>div>a", "text"],
            'image' => ["div.ny_hb_title>img", "src"],
            'data' => ["div.ny_hb_title>div>p", "text"],
            'series' => ["ul.ny_cs_ul>li", "text"],
            'images' => ["ul.carousel img", "src"],
        ];

        $rt = $ql->rules(['title'=>$rules['title']])->query()->getData();
        $this->info("开始解析title");
        print_r($rt->all());
        $this->info("结束解析title");
        //
        $rt = $ql->rules(['keyword'=>$rules['keyword']])->query()->getData();
        $this->info("开始解析keyword");
        print_r($rt->all());
        $this->info("结束解析keyword");
        //
        $rt = $ql->rules(['image'=>$rules['image']])->query()->getData();
        $this->info("开始解析image");
        print_r($rt->all());
        $this->info("结束解析image");
        //
        $rt = $ql->rules(['data'=>$rules['data']])->query()->getData();
        $this->info("开始解析data");
        print_r($rt->all());
        $this->info("结束解析data");
        //
        $rt = $ql->rules(['series'=>$rules['series']])->query()->getData();
        $this->info("开始解析series");
        print_r($rt->all());
        $this->info("结束解析series");
        //
        $rt = $ql->rules(['images'=>$rules['images']])->query()->getData();
        $this->info("开始解析images");
        print_r($rt->all());
        $this->info("结束解析images");

        $this->info($this->title."采集结束");

        $this->info("开始入库".$this->title."入库");

        //插入系列series
        

        //插入tag
        DB::table('tags')->insertGetId([
            'name' => str_random(10),
            'email' => str_random(10).'@gmail.com',
            'password' => bcrypt('secret'),
        ]);
        //插入book
        DB::table('books')->insertGetId([
            'name' => str_random(10),
            'email' => str_random(10).'@gmail.com',
            'password' => bcrypt('secret'),
        ]);
        //插入关联表

        //插入系列
        $this->info($this->title."结束入库");
    }
}
