<?php

namespace App\Http\Controllers;

use App\Post;
use App\Station;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function addPost(Request $request)
    {

        $rules = [
            'alias' => 'required',
            'result' => 'required|integer',
            'files' => 'required|array',
            'category_id' => 'required_with:error|integer',
            'error' => 'required_with:type_error',

        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            return response()->json(['error' => true, 'message' => $validator->errors()], 400);
        }

        // ------- create post ------- //

        $post = [
            'result' => $request->input('result'),
            'error' => $request->input('error'),
            'category_id' => $request->input('category_id'),
            'alias' => $request->input('alias'),
            'files' => json_encode($request->input('files')),
            'station_id' => $request->input('station_id'),
        ];

        try {
            $result = Post::create($post);
        } catch (Exception $exception) {
            return response()->json(['error' => true, 'message' => $exception->getMessage()], 401);
        }

        if ($result) {
            return response()->json($result, 200);
        }

    }

    public function showGraf(Request $request)
    {

        // return response()->json($request->all(), 200);
        // ------- validation -------- //
        $rules = [
            'ac-name' => 'required|integer',
            'folder' => 'required|array',
            'category' => 'array|required_if:r-input,error',
            'data-range' => 'required|string',
            'r-input' => 'required|in:success,error',

        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => 'validation error', 'message' => $validator->errors()->all()], 400)->withHeaders(['megalog' => 'errorMegalog']);
        }

        $dates = explode('-', $request->input('data-range'));
        foreach ($dates as &$date) {
            $date = Carbon::createFromFormat('d/m/Y', trim($date));
        }
        if ($dates[0]->diffInDays($dates[1]) > 31) {
            return response()->json(['error' => 'validation error', 'message' => ['Період не більше 31 дня']], 400)->withHeaders(['megalog' => 'errorMegalog']);
        }
        
        $query = DB::table('posts')
            ->whereDate('created_at', '>=', $dates[0]->toDateString())
            ->whereDate('created_at', '<=', $dates[1]->toDateString());
        // ---- підготовка масиву умов відбору --------
        $conditionsAnd = [
            ['station_id', '=', $request->input('ac-name')],
        ];
        if ($request->input('r-input') === 'success') {
            $conditionsAnd[] = ['result', '=', 1];
        } else if ($request->input('r-input') === 'error') {
            $conditionsAnd[] = ['result', '=', 0];
        }
        $query->where($conditionsAnd);
        // ----------- OR statement ------------------------- //
        $query->whereIn('alias', $request->input('folder'));

        if ($request->input('r-input') === 'error' && $request->has('category')) {
            $query->whereIn('category_id', $request->input('category'));
        }

        $posts = $query->orderBy('id')->get();

        // ----------- обрахунок статистичних даних для графіку --------- //
        $labels = [];
        $data = [];
        $label = $request->input('r-input') === 'error' ? 'Помилки ' : 'Успішні завантаження ';
        $label .= ' за період з ' . $dates[0]->format('d.m.Y') . ' по ' . $dates[1]->format('d.m.Y');
        $counted = $posts->countBy(function ($post) {
            return Str::substr($post->created_at, 8, 2);
        });

        foreach ($counted as $key => $value) {
            $labels[] = $key;
            $data[] = $value;
        }

        $result = [
            'labels' => $labels,
            'label' => $label,
            'backgroundColor' => 'rgba(60,141,188,0.9)',
            'borderColor' => 'rgba(60,141,188,0.8)',
            'pointRadius' => false,
            'pointColor' => '#3b8bba',
            'pointStrokeColor' => 'rgba(60,141,188,1)',
            'pointHighlightFill' => '#fff',
            'pointHighlightStroke' => 'rgba(60,141,188,1)',
            'data' => $data,
        ];

        return response()->json($result, 200);

    }

}