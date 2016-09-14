<?php

namespace App\Http\Controllers\Forum;

use App\Jobs\ImportArticleJob;
use App\Jobs\ImportAttachJob;
use App\Jobs\ImportForumJob;
use App\Jobs\ImportQuestionJob;
use App\Jobs\ImportUsersJob;
use App\Jobs\UpdatePostCountJob;
use Illuminate\Http\Request;
use App\Jobs\Job;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImportDBController extends Controller
{
    private $category_id_relation;

    //
    public function handle()
    {
        //clear up
        $this->clear_up();

        //import_users
        //$this->import_users();

        $this->import_category();

        //import question
        $this->import_forum_question();

        //import artcle
        $this->import_forum_article();

        //update section post count
        $this->update_section_postcounts();

    }

    public static function clear_up()
    {
        DB::table('jobs')->truncate();
        DB::table('failed_jobs')->truncate();
        DB::connection('ucenter')->table('forum_post')->truncate();
        DB::connection('ucenter')->table('forum_thread')->truncate();
        DB::connection('ucenter')->table('forum_sofa')->truncate();
        DB::connection('ucenter')->table('forum_post_tableid')->truncate();
        DB::connection('ucenter')->table('forum_newthread')->truncate();
        DB::connection('ucenter')->table('forum_attachment')->truncate();
        for($i=0;$i<10;$i++)
        {
            DB::connection('ucenter')->table('forum_attachment_'.$i)->truncate();
        }
    }

    public static function import_users()
    {
        $page = 1;
        Cache::put('user_page', $page, 1440);

        echo "开始导入用户数据...<br />";
        while($page= Cache::get('user_page') > 0)
        {
            $page= Cache::get('user_page');
            echo "开始导入第".$page."数据.<br />";
            //队列导入数据
            dispatch(new ImportUsersJob($page));

            $next_page = $page + 1;
            $wecenter_users = DB::connection('wecenter')->table('users')
                ->paginate(15, ['*'], 'page', $next_page);

            if (count($wecenter_users)) {
                Cache::put('user_page', $next_page, 1440);
            } else {
                Cache::put('user_page', 0, 1440);
            }
            ob_flush();
        }
        echo "结束导入用户数据.<br />";

        return response('start import user task successful');
    }

    public function import_category()
    {
        echo "开始导入版块数据...<br />";
        $categorys = DB::connection('wecenter')->table('category')
            ->get();
        if(count($categorys))
        {
            foreach ($categorys as $category) {
                $new_category = [
                    'fup'    => 1,
                    'name'   => $category->title,
                    'status' => 1,
                    'allowbbcode'   => 1,
                    'allowsmilies'  =>  1,
                    'allowhtml' =>  1,
                ];
                $exist_category = DB::connection('ucenter')->table('forum_forum')
                ->where('name',$category->title)->first();
                if(count($exist_category))
                {
                    $new_category_id = $exist_category->fid;
                }
                else
                {
                    $new_category_id = DB::connection('ucenter')->table('forum_forum')
                        ->insertGetId($new_category);
                }

                $this->category_id_relation[$category->id] = $new_category_id;
            }
        }

    }

    public function import_forum_question()
    {
        $page = 1;
        Cache::put('forum_question_page', $page, 1440);

        echo "开始导入问题数据...<br />";
        while($page = Cache::get('forum_question_page') > 0)
        {
            $page = Cache::get('forum_question_page');
            echo "开始导入第".$page."数据.<br />";
            //队列导入数据
            Log::info($this->category_id_relation);
            dispatch(new ImportQuestionJob($page,$this->category_id_relation));

            $next_page = $page + 1;
            $questions = DB::connection('wecenter')->table('question')
                ->paginate(15, ['*'], 'page', $next_page);

            if (count($questions)) {
                Cache::put('forum_question_page', $next_page, 1440);
            } else {
                Cache::put('forum_question_page', 0, 1440);
            }
            ob_flush();
        }
        echo "结束导入问题数据.<br />";

        return response('start import question task successful');
    }

    public function import_forum_article()
    {
        $page = 1;
        Cache::put('forum_article_page', $page, 1440);

        echo "开始导入文章数据...<br />";
        while($page= Cache::get('forum_article_page') > 0)
        {
            $page= Cache::get('forum_article_page');
            echo "开始导入第".$page."数据.<br />";
            //队列导入数据
            dispatch(new ImportArticleJob($page,$this->category_id_relation));

            $next_page = $page + 1;
            $articles = DB::connection('wecenter')->table('article')
                ->paginate(15, ['*'], 'page', $next_page);

            if (count($articles)) {
                Cache::put('forum_article_page', $next_page, 1440);
            } else {
                Cache::put('forum_article_page', 0, 1440);
            }
            ob_flush();
        }
        echo "结束导入文章数据.<br />";
        echo "完成!<br />";

        return response('start import article task successful');
    }

    public function update_section_postcounts()
    {
        dispatch(new UpdatePostCountJob($this->category_id_relation));
    }

    public static function import_attach()
    {

    }

    public static function import_tag()
    {

    }

    public static function import_tag_item()
    {

    }
}
