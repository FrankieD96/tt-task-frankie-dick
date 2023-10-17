<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function getAllMembers(): JsonResponse
    {
        return response()->json([
            'data' => Member::with('schools:id,name')->get()->makeHidden(['created_at', 'updated_at']),
            'message' => 'Members succesfully retrieved'
        ]);
    }
}
