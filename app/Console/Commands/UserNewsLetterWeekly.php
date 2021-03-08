<?php

namespace App\Console\Commands;

use App\Jobs\SendNewsLetter;
use App\Models\V1\NewsLetter;
use App\Models\V1\Subscribe;
use App\Models\V1\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserNewsLetterWeekly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletterweekly:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send news letter to user weekly';

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
        DB::beginTransaction();
        try {
            $newsletter = DB::table('newsletter')
                ->where('delivery_time', 'weekly')
                ->where('status', false)
                ->first();

            $subscriber = DB::table('subscribe')->get();
            if ($subscriber !== null &&  $newsletter !== null) {
                foreach ($subscriber as $d) {
                    $details['email'] = $d->email;
                    $details['content'] = $newsletter;
                    dispatch(new SendNewsLetter($details));
                }
            }

            $customer = DB::table('users')->where('subscribe', true)->get();
            if ($customer !== null &&  $newsletter !== null) {
                foreach ($customer as $d) {
                    $details['email'] = $d->email;
                    $details['content'] = $newsletter;
                    dispatch(new SendNewsLetter($details));
                }
            }
            
            //Update newsletter status to 1
            $newsletter = DB::table('newsletter')
                ->where('delivery_time', 'daily')
                ->where('status', false)
                ->update(['status' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return transformer(null, 500, 'Something Error, Please Contact Admin', false);
        }
    }
}
