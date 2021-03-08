<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
use App\Models\V1\Discount;
use App\Jobs\Discount\ChangeDisplayPrice;

class DisplayPriceRecalculate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'displayprice:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kalkulasi ulang display price denga menghitung promo di tiap tipe product, di produknya sendiri dan di category yang terhubung ke product';

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
        $discount = Discount::get();
        foreach ($discount as $dsc) {
            ChangeDisplayPrice::dispatch($dsc->all_product);
        }
    }
}
