<?php

namespace App\Jobs\Discount;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\V1\Product;
use Carbon\Carbon;

class ChangeDisplayPrice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $discount, $product_id, $action;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($product_id)
    {
        $this->product_id = $product_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $product = Product::with('defaultType', 'discount')->whereIn('id', $this->product_id)->get();
        foreach ($product as $prd) {
            $discount = 0;
            foreach ($prd->all_discount as $padsc) {
                $now = Carbon::now();
                $discount_start_date = Carbon::parse($padsc['effective_start_date']);
                $discount_end_date = Carbon::parse($padsc['effective_end_date']);
                if ($now->between($discount_start_date, $discount_end_date) && $padsc['status'] === 'Publish') {
                    switch ($padsc['unit']) {
                        case 'decimal':
                            $discount = $discount + $padsc['value'];
                            break;
                        case 'percentage':
                            $discount = $discount + ($prd->defaultType->price * $padsc['value']) / 100;
                            break;
                    }
                }
            }

            $display_price = $prd->defaultType->price;
            if ($prd->defaultType->discount_price !== NULL) {
                $display_price = $prd->defaultType->discount_price;
            }
            $display_price = $display_price - $discount;
            $prd->update([
                'display_price' => ($display_price < 0) ? 0 : $display_price
            ]);
        }
    }

    // private function calculateTotalPromo($loop, $total_discount, $prd = NULL)
    // {
    //     // logger($total_discount);
    //     foreach ($loop->discount as $dsc) {
    //         $discount = 0;
    //         $now = Carbon::now();
    //         $discount_start_date = Carbon::parse($dsc->effective_start_date);
    //         $discount_end_date = Carbon::parse($dsc->effective_end_date);

    //         if ($now->between($discount_start_date, $discount_end_date)) {
    //             switch ($dsc->unit) {
    //                 case 'decimal':
    //                     $discount_promo = $dsc->value;
    //                     break;
    //                 case 'percentage':
    //                     if (isset($prd)) {
    //                         $discount_promo = ($prd->defaultType->price * $dsc->value) / 100;
    //                     } else {
    //                         $discount_promo = ($loop->defaultType->price * $dsc->value) / 100;
    //                     }

    //                     break;
    //             }
    //         }

    //         if (!in_array($dsc->id, array_column($total_discount, 'id'))) {
    //             switch ($this->action) {
    //                 case 'apply':
    //                     $total_discount[] = [
    //                         'id' => $dsc->id,
    //                         'value' => $discount_promo
    //                     ];
    //                     break;
    //                 case 'delete':
    //                     if ($this->discount->id !== $dsc->id) {
    //                         $total_discount[] = [
    //                             'id' => $dsc->id,
    //                             'value' => $discount_promo
    //                         ];
    //                     }
    //                     break;
    //             }
    //         }
    //     }
    //     // dd($total_discount);
    //     return $total_discount;
    // }
}
