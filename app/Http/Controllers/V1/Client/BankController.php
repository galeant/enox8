<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Bank;
use App\Http\Response\Client\BankTransformer;

class BankController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $data = Bank::get();
            return BankTransformer::list($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
