<?php

namespace App\Http\Controllers;

use App\Models\MyCourse;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class MyCourseController extends Controller
{
    //[MyCourse]-API Get
    public function index(Request $request)
    {
        // $myCourses = MyCourse::all();

        // return response()->json([
        //     'status' => 'success',
        //     'data' => $myCourses
        // ]);

        //Filter By User Id and Course Id
        //Menampilkan Data Course  "->with(course)"
        $myCourses = MyCourse::query()->with('course');

        $courseId = $request->query('course_id');
        $myCourses->when($courseId, function ($query) use ($courseId) {
            return $query->where('course_id', '=', $courseId);
        });
        $userId = $request->query('user_id');
        $myCourses->when($userId, function ($query) use ($userId) {
            return $query->where('user_id', '=', $userId);
        });

        return response()->json([
            'status' => 'success',
            'data' => $myCourses->get()
        ]);
    }

    //[MyCourse]-API Create
    public function create(Request $request)
    {
        $rules = [
            'course_id' => 'required|integer',
            'user_id' => 'required|integer'
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $courseId = $request->input('course_id');
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'Course Not Found'
            ], 404);
        }

        $userId = $request->input('user_id');
        $user = getUser($userId);
        if ($user['status'] === 'error') {
            return response()->json([
                'status' => $user['status'],
                'message' => $user['message']
            ], $user['http_code']);
        }

        $isExistMyCourse = MyCourse::where('course_id', '=', $courseId)
            ->where('user_id', '=', $userId)
            ->exists();

        if ($isExistMyCourse) {
            return response()->json([
                'status' => 'error',
                'message' => 'User Already Taken This Course'
            ], 409);
        }

        if ($course->type === 'premium') {
            if ($course->price === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Price can't be 0"
                ], 405);
            }

            $order = postOrder([
                'user' => $user['data'],
                'course' => $course->toArray()
            ]);

            // //debug
            // echo "<pre>" . print_r($order, 1) . "</pre>";

            if ($order['status'] === 'error') {
                return response()->json([
                    'status' => $order['status'],
                    'message' => $order['message']
                ], $order['http_code']);
            }

            return response()->json([
                'status' => $order['status'],
                'data' => $order['data']
            ]);
        } else {
            $myCourse = MyCourse::create($data);
            return response()->json([
                'status' => 'success',
                'data' => $myCourse
            ]);
        }
    }

    //API ACCESS PREMIUM COURSE
    public function createPremiumAccess(Request $request)
    {
        $data = $request->all();
        $myCourse = MyCourse::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $myCourse
        ]);
    }
}