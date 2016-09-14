<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class UpdatePostCountJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $category;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($category_id_relation)
    {
        //
        $this->category = $category_id_relation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $categorys = $this->category;
        if(count($categorys))
        {
            foreach ($categorys as $category_id) {
                $count = 0;
                $count = DB::connection('ucenter')->table('forum_post')
                    ->where('fid', $category_id)->count();

                $update_data = [
                    'threads'   =>  $count,
                    'posts' =>  $count,
                    'todayposts' =>  $count,
                ];
                DB::connection('ucenter')->table('forum_forum')
                    ->where('fid', $category_id)->update($update_data);
            }
        }
    }
}
