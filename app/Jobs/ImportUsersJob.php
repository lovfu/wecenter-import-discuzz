<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ImportUsersJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $page;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($page = 0)
    {
        //
        $this->page = $page ?: 0;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $wecenter_users = DB::connection('wecenter')->table('users')
            ->paginate(15, ['*'], 'page', $this->page);

        if(count($wecenter_users))
        {
            foreach ($wecenter_users as $wecenter_user) {
                //
                $users[] = [
                    'uid'           => $wecenter_user->uid,
                    'username'      => $wecenter_user->user_name,
                    'email'         => $wecenter_user->email,
                    'password'      => $wecenter_user->password,
                    'regdate'       => $wecenter_user->reg_time,
                ];
            }

            if (count($users)) {
                DB::connection('ucenter')->table('common_member')->insert($users);
            }
        }
    }

}
