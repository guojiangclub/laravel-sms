<?php

/*
 * This file is part of ibrand/laravel-sms.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace iBrand\Sms\Jobs;

use Carbon\Carbon;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DbLogger implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $code;
    private $result;
    private $flag;

    /**
     * Create a new job instance.
     */
    public function __construct($code, $result, $flag)
    {
        $this->code = $code;
        $this->result = $result;
        $this->flag = $flag;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (!config('ibrand.sms.dblog')) {
            return;
        }
        DB::table('laravel_sms_log')->insert([
            'mobile' => $this->code->to,
            'data' => json_encode($this->code),
            'is_sent' => $this->flag,
            'result' => json_encode($this->result),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
