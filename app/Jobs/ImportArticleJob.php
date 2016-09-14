<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class ImportArticleJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $page;

    protected $category_relation;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($page = 0, $category_relation)
    {
        //
        $this->page = $page ?: 0;
        $this->category_relation = $category_relation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $articles = DB::connection('wecenter')->table('article')
            ->paginate(15, ['*'], 'page', $this->page);

        $last_post = DB::connection('ucenter')->table('forum_thread')
            ->select('tid')
            ->orderBy('tid','desc')
            ->limit(0,1)
            ->first();
        $tid = count($last_post) ? $last_post->tid + 1 : 1;

        if(count($articles))
        {
            foreach ($articles as $article) {

                $user = DB::connection('wecenter')->table('users')
                    ->where('uid', $article->uid)
                    ->first();

                $fid = isset($this->category_relation[$article->category_id]) ? intval($this->category_relation[$article->category_id]) : array_first($this->category_relation);

                $new_question[] = [
                    'tid'        => $tid,
                    'pid'        => $tid,
                    'fid'        => $fid,
                    'first'      => 1,
                    'author'     => $user->user_name,
                    'authorid'   => 1,
                    'subject'    => $article->title,
                    'dateline'   => $article->add_time,
                    'message'    => $article->message,
                    'attachment' => $article->has_attach,
                    'usesig'     => 1,
                ];

                $new_forum_thread[] = [
                    'tid'        => $tid,
                    'fid'        => $fid,
                    'author'     => $user->user_name,
                    'authorid'   => 1,
                    'subject'    => $article->title,
                    'dateline'   => $article->add_time,
                    'lastpost'   => $article->add_time,
                    'lastposter' => $user->user_name,
                    'views'      => $article->views,
                    'status'     => 32,
                    'stamp'      => -1,
                    'icon'       => -1,
                ];
                $new_forum_sofa[] = [
                    'tid'   => $tid,
                    'fid'   =>  $fid,
                ];
                $new_forum_newthread[] = [
                    'tid'   => $tid,
                    'fid'   =>  $fid,
                    'dateline'   => $article->add_time,
                ];
                $new_forum_post_tableid[] = [
                    'pid'        =>  $tid,
                ];

                if($article->has_attach)
                {
                    $this->import_post_attach($article->id, $tid, $article->uid);
                }

                $tid = $tid + 1;
            }
        }
        DB::connection('ucenter')->table('forum_post')
            ->insert($new_question);
        DB::connection('ucenter')->table('forum_thread')
            ->insert($new_forum_thread);
        DB::connection('ucenter')->table('forum_sofa')
            ->insert($new_forum_sofa);
        DB::connection('ucenter')->table('forum_post_tableid')
            ->insert($new_forum_post_tableid);
        DB::connection('ucenter')->table('forum_newthread')
            ->insert($new_forum_newthread);
    }

    private function import_post_attach($post_id, $tid, $uid)
    {
        $wecenter_attachs = DB::connection('wecenter')->table('attach')
            ->where('item_id',$post_id)->where('item_type','article')
            ->get();

        $tid_temp = (string)$tid;
        $tableid = $tid_temp{strlen($tid_temp)-1};
        if(count($wecenter_attachs))
        {
            foreach ($wecenter_attachs as $wecenter_attach) {
                //
                $ucenter_attachment = [
                    'aid'   =>  $wecenter_attach->id,
                    'tid'   =>  $tid,
                    'pid'   =>  $tid,
                    'uid'   =>  $uid,
                    'tableid'   =>  $tableid,
                ];

                $attachment = 'article/'.date('Ymd', $wecenter_attach->add_time).'/'.$wecenter_attach->file_location;
                $ucenter_attachment_file = [
                    'aid'   =>  $wecenter_attach->id,
                    'tid'   =>  $tid,
                    'pid'   =>  $tid,
                    'uid'   =>  $uid,
                    'dateline'  =>  $wecenter_attach->add_time,
                    'filename'  =>  $wecenter_attach->file_name,
                    'filesize'  =>  103102,
                    'attachment'    =>  $attachment,
                    'description'   =>  $wecenter_attach->file_name,
                    'width' =>  429,
                    'isimage'   => 1,
                ];

                $aid = DB::connection('ucenter')->table('forum_attachment')
                    ->insertGetId($ucenter_attachment);
                DB::connection('ucenter')->table('forum_attachment_'.$tableid)
                    ->insert($ucenter_attachment_file);
            }

        }
    }
}
