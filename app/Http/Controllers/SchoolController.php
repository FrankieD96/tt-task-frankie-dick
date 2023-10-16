<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function getAllSchools()
    {
        return response()->json([
            'data' => School::all()->makeHidden(['created_at', 'updated_at']),
            'message' => 'Schools succesfully retrieved'
        ]);
    }
}
