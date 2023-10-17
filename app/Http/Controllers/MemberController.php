<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function getAllMembers(Request $request): JsonResponse
    {
        $request->validate([
            'school' => 'nullable|integer|min:1|exists:schools,id'
        ]);

        $hidden = ['created_at', 'updated_at'];

        $school = $request->query('school');
        $members = Member::with(['schools' => function($query) use ($school) {
            $query->when(
                $school !==null,
                function ($query) use ($school) {
                    $query->where('member_school.school_id', $school);
                });
        }]);

        $members = $members->when(
            $school !== null,
            function ($query) use ($school) {
                return $query->whereHas('schools', function($subquery) use ($school) {
                    $subquery->where('schools.id', $school);
                });
            }
        );

        $members = $members->get()->makeHidden($hidden);
        
        return response()->json([
            'data' => $members,
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
