<?php

namespace App\Http\Controllers;

use App\Models\TodoUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    public function store(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => TodoUser::create()->id
            ]
        ]);
    }
}
