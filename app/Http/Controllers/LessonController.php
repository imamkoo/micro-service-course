<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\Chapter;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
{
    //[Lesson]-API Get
    //List Lesson
    public function index(Request $request)
    {
        $lessons = Lesson::query();
        $chapterId = $request->query('chapter_id');

        //Filter by Chapter Id
        $lessons->when($chapterId, function ($query) use ($chapterId) {
            return $query->where('chapter_id', '=', $chapterId);
        });


        return response()->json([
            'status' => 'success',
            'data' => $lessons->get()
        ]);
    }

    //Detail Lesson
    public function show($id)
    {
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lesson Not Found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }


    //[Lesson]-API Create
    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'video' => 'required|string',
            'chapter_id' => 'required|integer'
        ];

        $data = $request->all();
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $chapterId = $request->input('chapter_id');
        $chapter = Chapter::find($chapterId);
        if (!$chapter) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chapter Not Found'
            ]);
        }

        $lesson = Lesson::create($data);
        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }

    //[Lesson]-API Update
    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'string',
            'video' => 'string',
            'chapter_id' => 'integer'
        ];

        $data = $request->all();
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json([
                'status' => 'error',
                'message' => "Lesson Not Found"
            ], 404);
        }

        $chapterId = $request->input('chapter_id');
        if ($chapterId) {
            $chapter = Chapter::find($chapterId);
            if (!$chapter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Chapter Not Found'
                ]);
            }
        }

        $lesson->fill($data);
        $lesson->save();
        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }

    //[Lesson]-API Delete
    public function destroy($id)
    {
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lesson Not Found'
            ], 404);
        }

        $lesson->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Lesson Deleted'
        ]);
    }
}