<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImageCourse;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;

use function PHPSTORM_META\map;

class ImageCourseController extends Controller
{
    //[Image Course]-API Create
    public function create(Request $request)
    {
        $rules = [
            'image' => 'required|url',
            'course_id' => 'required|integer'
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

        $imageCourse = ImageCourse::create($data);
        return response()->json([
            'status' => 'success',
            'data' => $imageCourse
        ]);
    }

    //[Image Course]-API Delete
    public function destroy($id)
    {
        $imageCourse = ImageCourse::find($id);
        if (!$imageCourse) {
            return response()->json([
                'status' => 'error',
                'message' => 'Image Course Not Found'
            ], 404);
        }

        $imageCourse->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Image Course Deleted'
        ]);
    }
}