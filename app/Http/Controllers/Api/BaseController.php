<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function responseApi($status,$message, $data,  $code)
    {
        return response()->json([
            'status' => $status,
            'data' => $data,
            'message' => $message
        ], $code);
    }
}
