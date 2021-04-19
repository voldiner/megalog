<?php

namespace App\Http\Controllers;

use App\Category;
use App\Folder;
use App\Post;
use App\Station;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        // cookies поставили JS тому читаємо на PHP
        //dd(str_replace('-', '_',$_COOKIE['parameters']));
        if (isset($_COOKIE['parameters'])) {
            $old_param = json_decode(str_replace('-', '_', $_COOKIE['parameters']));
        } else {
            $old_param = json_decode('{}');
        }

        $maxDate = Carbon::createFromTimestamp(time())->format('d/m/Y');

        if (isset($old_param->data_range)) {
            $dates_array = explode('_', $old_param->data_range);
            if (count($dates_array) === 2) {
                $startDate = trim($dates_array[0]);
                $endDate = trim($dates_array[1]);
            }
        }
        if (!isset($startDate,$endDate)){
            $startDate = Carbon::createFromTimestamp(time())->subDay(30)->format('d/m/Y');
            $endDate = Carbon::createFromTimestamp(time())->format('d/m/Y');
        }
        // помилки та успішні передачі за поточну добу
        $date_s = Carbon::createFromTime(0, 0, 0);
        $date_p = Carbon::createFromTimestamp(time());

        $posts_error = DB::table('posts')
            ->whereDay('created_at', '=', $date_p->day)
            ->where('result', '=', 0)
            ->count();

        $posts_success = DB::table('posts')
            ->whereDay('created_at', '=', $date_p->day)
            ->where('result', '=', 1)
            ->count();


        $message = 'за ' . ($date_s->diffForHumans($date_p)) . ' ' . $date_p->toDateTimeString();

        $folders = Folder::all()->pluck('title', 'name');
        $categories = Category::all()->pluck('title', 'id');
        $stations = Station::all()->pluck('title', 'id');
        return view('index', compact('maxDate',
            'startDate',
            'endDate',
            'posts_success',
            'posts_error',
            'message',
            'folders',
            'categories',
            'stations',
            'date_s',
            'date_p',
            'old_param'
        ));

    }

    public function getPostsFromDates(Request $request)
    {
        $rules = [
            'from' => 'required|date_format:Y-m-d H:i:s|required_with:to',
            'to' => 'required|date_format:Y-m-d H:i:s|required_with:from',
            'orderby' => 'in:station_id,category_id,alias,created_at'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'validation error', 'message' => $validator->errors()->all()], 400)->withHeaders(['megalog' => 'errorMegalog']);
            }
            return redirect(route('home'))
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->has('orderby')) {
            $orderBy = $request->orderby;
        } else {
            $orderBy = 'station_id';
        }

        $posts = Post::whereDay('created_at', '=', substr($request->to, 8, 2))
            ->where('result', '=', 0)
            ->with(['station', 'category', 'folder'])
            ->orderBy($orderBy)
            //->get();
            ->paginate(15);
        $ajaxRoute = route('getPostsFromDates');
        $dateTime_from = $request->from;
        $dateTime_to = $request->to;
        if ($request->ajax()) {
            return view('table_posts', compact('posts', 'dateTime_from', 'dateTime_to', 'orderBy'))->render();
        }
        return view('posts', compact('posts', 'dateTime_from', 'dateTime_to', 'orderBy', 'ajaxRoute'));

    }

    public function getPostsFromDay(Request $request)
    {
        $rules = [
            'ac-name-day' => 'required|integer',
            'date-day' => 'required|date_format:d/m/Y',
            'orderby' => 'in:station_id,category_id,alias,created_at'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'validation error', 'message' => $validator->errors()->all()], 400)->withHeaders(['megalog' => 'errorMegalog']);
            }
            return redirect(route('home'))
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->has('orderby')) {
            $orderBy = $request->orderby;
        } else {
            $orderBy = 'station_id';
        }

        $dateToFind = Carbon::createFromFormat('d/m/Y', $request->input('date-day'))->toDateString();
        $ac = Station::find($request->input('ac-name-day'));
        $ajaxRoute = route('getPostsFromDay');
        $ac_id = $request->input('ac-name-day');
        $date = $request->input('date-day');

        $posts = Post::whereDate('created_at', '=', $dateToFind)
            ->where('station_id', '=', $request->input('ac-name-day'))
            ->with(['station', 'category', 'folder'])
            ->orderBy($orderBy)
            ->paginate(15);

        if ($request->ajax()) {
            return view('table_posts', compact('posts', 'date', 'ac', 'orderBy', 'ac_id', 'ajaxRoute'))->render();
        }
        return view('posts', compact('posts', 'date', 'ac', 'orderBy', 'ac_id', 'ajaxRoute'));


    }
}
