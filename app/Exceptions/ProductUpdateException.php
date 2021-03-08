<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Storage;
use Exception;

class ProductUpdateException extends Exception
{

    protected $data,$message;
    public function __construct($message,$data){
        $this->message = $message;
        $this->data = $data;
    }
    public function report()
    {
        foreach($this->data as $dt){
            Storage::delete($dt);
        }
        return $this->message;
    }
}
