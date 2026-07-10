<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Student_session;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class DiagnosticController extends Controller

{
    public function startSession(Request $request){
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:student,parent,teacher',
            'grade' => 'required|integer|between:1,11',
            'examType' => 'required|in:OGE,EGE,EARLY',
            'region' => 'required|string|max:255',
            'targetTrack' => 'required|in:UNIVERSITY,COLLEGE,PROFILE_CLASS',
            'targetYear' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        try {
            $user = Role::create([
                'login' => 'guest_' . Str::random(8) . '_' . time(),
                'password' => Hash::make(Str::random(16)),
                'role' => $request->input('role'),
            ]);
            $session = Student_session::create([
                'user_id' => $user->id,
                'grade' => $request->input('grade'),
                'exam_type' => $request->input('examType'),
                'region' => $request->input('region'),
                'target_track' => $request->input('targetTrack'),
                'target_year' => $request->input('targetYear'),
            ]);

            return response()->json([
                'success' => true,
                'session_id' => $session->id,
                'message' => 'Session created successfully.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
