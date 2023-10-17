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

    public function addNewMember(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'school_ids' => 'required|array|min:1',
            'school_ids.*' => 'integer|min:1|exists:schools,id'
        ]);

        $newMember = new Member();

        $newMember->name = $request->name;
        $newMember->email = $request->email;

        if ($newMember->save()) {
            $newMember->schools()->attach($request->school_ids);

            return response()->json([
                'message' => 'Member added'
            ], 201);
        }

        return response()->json([
            'message' => 'Unexpected error occured'
        ], 500);
    }
}
