<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use QL\QueryList;
use Illuminate\Support\Facades\Log;

class CaijiHuiben implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $url = 'https://huiben.cn';
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
        print_r($this->title);
        echo "\r\n";

        Log::info("开始采集".$this->title);
        //

        $ql = QueryList::get($this->url);
        // 定义采集规则
        $rules = [
            'title' => ["div.ny_hb_title>div>h3", "text"],
            'keyword' => ["div.ny_hb_title>div>div>a", "text"],
            'image' => ["div.ny_hb_title>img", "src"],
            'data' => ["div.ny_hb_title>div>p", "text"],
            'series' => ["ul.ny_cs_ul>li", "text"],
            'images' => ["ul.carousel img", "src"],
            'neirong' => ["div.neirong", "html"],

        ];

        $rt = $ql->rules(['title'=>$rules['title']])->query()->getData();
        Log::info("开始解析title");
        $title = $rt->all();
        Log::info("结束解析title");
        //
        $rt = $ql->rules(['keyword'=>$rules['keyword']])->query()->getData();
        Log::info("开始解析keyword");
        $keyword = $rt->all();
        Log::info("结束解析keyword");
        //
        $rt = $ql->rules(['image'=>$rules['image']])->query()->getData();
        Log::info("开始解析image");
        $image = $rt->all();
        Log::info("结束解析image");
        //
        $rt = $ql->rules(['data'=>$rules['data']])->query()->getData();
        Log::info("开始解析data");
        $data = $rt->all();
        Log::info("结束解析data");
        //
        $rt = $ql->rules(['series'=>$rules['series']])->query()->getData();
        Log::info("开始解析series");
        $series = $rt->all();
        Log::info("结束解析series");
        //
        $rt = $ql->rules(['images'=>$rules['images']])->query()->getData();
        Log::info("开始解析images");
        $images = $rt->all();
        Log::info("结束解析images");

        //
        $rt = $ql->rules(['neirong'=>$rules['neirong']])->query()->getData();
        Log::info("开始解析neirong");
        $neirong = $rt->all();
        Log::info("结束解析neirong");

        Log::info($this->title."采集结束");
        Log::info("开始入库".$this->title."入库");

        //插入系列series
/*        DB::table('series')->insertGetId([
            'name' => str_random(10),
            'email' => str_random(10).'@gmail.com',
            'password' => bcrypt('secret'),
        ]);*/      
        //
            $ages = [
                '青少年',
                '0-2岁',
                '3-4岁',
                '5-7岁',
                '8-10岁',
                '11-14岁',
                '家长用书',
                '小学用书',
            ];
         $age = '';
         $subject = [];  
        foreach ($keyword as $key) {
            if(in_array($key['keyword'], $ages)){
                $group = 'age';
                $age = $key['keyword'];
            }else{
                $group = 'subject';
                $subject[] = $key['keyword'];
            }
            $tags[] = [
                'name'=> $key['keyword'],
                'group'=> $group,
                'source'=> 'huiben.cn',
                'source_hash'=> md5('huiben.cn')
            ];
        } 
        print_r($data);
        //data
        $preg = preg_match_all("/作 者：(.*)出版社：(.*)    语种：(.*)被/", $data[0]['data'], $matches);
        $preg = preg_match_all("/\d+/", $data[0]['data'], $matches2);

       $book_id = \DB::table('books')->insertGetId([
            'subtitle'=>'',
            'origin_title'=>'',
            'translator'=>'',
            'pubdate'=>'2020-10-10',
            'tags'=>'',
            'rating'=>'',
            'pages'=>0,
            'isbn10'=>'',
            'isbn13'=>'',
            'binding'=>'',
            'author_intro'=>'',
            'series_id'=>0,
            'price'=>0.00,
            'title' => isset($title[0]['title']) ? $title[0]['title'] : '',
            'author' => isset($matches[1][0])? $matches[1][0] :'',
            'image' => isset($image[0]['image'])?$image[0]['image']:'',
            'images' => json_encode(array_column($images, 'images')),
            'publisher' => isset($matches[2][0])?$matches[2][0]:'',
            'data' => json_encode($matches2[0]),
            'keyword' => json_encode(array_column($keyword, 'keyword')),
            'series' => json_encode(array_column($keyword, 'keyword')),
            'language' => isset($matches[3][0])?$matches[3][0]:'',
            'subject' => json_encode($subject),
            'age' => $age,
            'summary' => isset($neirong[0]['neirong'])?$neirong[0]['neirong']:'',
            'source'=> $this->url,
            'source_hash' => md5($this->url),
            'catalog'=>'',
        ]);

       foreach ($tags as $tag) {
        $tag_id = \DB::table('tags')->insertGetId($tag);
        //插入关联表
        \DB::table('taggables')->insertGetId([
            'tag_id' => $tag_id,
            'taggable_id' => $book_id,
        ]);

       }
        //插入系列
        Log::info($this->title."结束入库");
    }
}
