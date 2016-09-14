<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class ImportQuestionJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $page;

    protected $category_relation;


    /**
     * Create a new job instance.
     *
     * @param int $page
     * @param     $category_relation
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
        $questions = DB::connection('wecenter')->table('question')
            ->paginate(15, ['*'], 'page', $this->page);

        $last_post = DB::connection('ucenter')->table('forum_thread')
            ->select('tid')
            ->orderBy('tid','desc')
            ->limit(0,1)
            ->first();
        $tid = count($last_post) ? $last_post->tid + 1 : 1;

        if(count($questions))
        {
            foreach ($questions as $question) {

                $user = DB::connection('wecenter')->table('users')
                    ->where('uid', $question->published_uid)
                    ->first();

                $fid = isset($this->category_relation[$question->category_id]) ? intval($this->category_relation[$question->category_id]) : array_first($this->category_relation);

                $new_question[] = [
                    'tid'        =>  $tid,
                    'pid'        =>  $tid,
                    'fid'        => $fid,
                    'first'      => 1,
                    'author'     => $user->user_name,
                    'authorid'   => 1,
                    'subject'    => $question->question_content,
                    'dateline'   => $question->add_time,
                    'message'    => $question->question_detail,
                    'attachment' => $question->has_attach,
                    'usesig'     => 1,
                ];

                $new_forum_thread[] = [
                    'tid'   => $tid,
                    'fid'   =>  $fid,
                    'author'     => $user->user_name,
                    'authorid'   => 1,
                    'subject'    => $question->question_content,
                    'dateline'   => $question->add_time,
                    'lastpost'  =>$question->add_time,
                    'lastposter'    =>  $user->user_name,
                    'views' =>  $question->view_count,
                    'status'    =>  32,
                    'stamp' =>  -1,
                    'icon'  =>  -1,
                ];
                $new_forum_sofa[] = [
                    'tid'   => $tid,
                    'fid'   =>  $fid,
                ];
                $new_forum_newthread[] = [
                    'tid'   => $tid,
                    'fid'   =>  $fid,
                    'dateline'   => $question->add_time,
                ];
                $new_forum_post_tableid[] = [
                    'pid'        =>  $tid,
                ];

                if($question->has_attach)
                {
                    $this->import_post_attach($question->question_id, $tid, $question->published_uid);
                }

                $tid++;
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
            ->where('item_id',$post_id)->where('item_type','question')
            ->get();

        $tid_temp = (string)$tid;
        $tableid = $tid_temp{strlen($tid_temp)-1};
        if(count($wecenter_attachs))
        {
            foreach ($wecenter_attachs as $wecenter_attach) {
                //
                $ucenter_attachment[] = [
                    'aid'   =>  $wecenter_attach->id,
                    'tid'   =>  $tid,
                    'pid'   =>  $tid,
                    'uid'   =>  $uid,
                    'tableid'   =>  $tableid,
                ];

                $attachment = 'questions/'.date('Ymd', $wecenter_attach->add_time).'/'.$wecenter_attach->file_location;
                $ucenter_attachment_file[] = [
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
            }

            DB::connection('ucenter')->table('forum_attachment')
                ->insert($ucenter_attachment);
            DB::connection('ucenter')->table('forum_attachment_'.$tableid)
                ->insert($ucenter_attachment_file);
        }
    }
}
