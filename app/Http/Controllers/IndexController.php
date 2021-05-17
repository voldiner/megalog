<?php

namespace App\Http\Controllers;

use App\Category;
use App\Folder;
use App\Mail\ControlMail;
use App\Message;
use App\Post;
use App\Station;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Uapixart\LaravelTurbosms\Turbosms;

class IndexController extends Controller
{
    public $errorMessages;

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
        if (!isset($startDate, $endDate)) {
            $startDate = Carbon::createFromTimestamp(time())->subDay(30)->format('d/m/Y');
            $endDate = Carbon::createFromTimestamp(time())->format('d/m/Y');
        }
        // помилки та успішні передачі за поточну добу
        $date_s = Carbon::createFromTime(0, 0, 0);
        $date_p = Carbon::createFromTimestamp(time());

        $posts_error = DB::table('posts')
            ->whereDate('created_at', '=', $date_p->toDateString())
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

    /**
     *  перевірка умов відсутності повідомлень про синхронізацію
     *  та при необхідності відправка листа з повідомленням на email
     * викликається скриптом або через крон регулярно,
     *  вертає json з інформацією про виконання
     * https://github.com/mkuzmych/laravel-turbosms  відправка SMS
     */
    public function control()
    {
        $messagesToMail = [];
        $parameters = config('megalog');
        $stations = Station::whereIn('id', $parameters['ac'])->get();
        $folders = Folder::whereIn('name', $parameters['category'])->get();
        // --- визначимо, чи є не закриті повідомлення про помилки
        // --- на які треба повідомити про виправлення старіші за $parameters['day_success_email'] днів ігноруємо
        $dateIgnore = Carbon::now()->subDay($parameters['day_success_email'])->toDateString();
        $this->errorMessages = Message::where('count_for_send', 1)
            ->whereDate('created_at', '>=', $dateIgnore)
            ->get();
        dump($this->errorMessages);

        foreach ($stations as $station) {
            $checkStation = $this->checkStation($station, $folders, $parameters, $dateIgnore);

            if ($checkStation) {
                $messagesToMail = array_merge($messagesToMail, $checkStation);
            }
        }

        if ($messagesToMail) {
            dump($messagesToMail);
            Mail::to($parameters['email_alert'])->send(new ControlMail($messagesToMail));

            if ($parameters['sendSMS']) {
                $turboSMS = new Turbosms();
                $turboSMS->send($parameters['phones'], $this->createMessageSMS($messagesToMail));
            }

            dump('висилаю пошту -> !');
            foreach ($messagesToMail as $messageToMail) {
                $this->saveMessage($messageToMail);
            }
            return response()->json(['success' => true, 'message_send' => true], 200);
        }
        dump('все нормально !');
        return response()->json(['success' => true, 'message_send' => false], 200);


    }

    public function checkStation($station, $folders, $parameters, $dateIgnore)
    {

        $messagesToMail = false;
        foreach ($folders as $folder) {
            dump($folder->name);
            dump($parameters[$folder->name]['hour_start'], $parameters[$folder->name]['hour_finish']);
            if (Carbon::now()->hour > $parameters[$folder->name]['hour_start'] && Carbon::now()->hour < $parameters[$folder->name]['hour_finish']) {

                $check_timestamp = Carbon::now()->subHour($parameters[$folder->name]['alert_hours'])->timestamp;

                $not_exist_posts = DB::table('posts')
                    ->where('timestamp', '>=', $check_timestamp)
                    ->where('station_id', '=', $station->id)
                    ->where('alias', '=', $folder->name)
                    ->where('result', '=', 1)
                    ->doesntExist();
                if ($not_exist_posts) {
                    dump('немає постів');
                    if ($this->checkMessage($station->id, $folder->name)) {
                        $messagesToMail[] = [
                            'ac' => $station->title,
                            'ac_id' => $station->id,
                            'alias' => $folder->title,
                            'alias_name' => $folder->name,
                            'time' => $parameters[$folder->name]['alert_hours'],
                        ];
                    }
                } else {
                    // -- перевірка якщо є пости після повідомлень про помилки, треба повідомити
                    // -- про появу завантаження
                    if ($this->needEmailToSuccess($station->id, $folder->name)) {
                        $messagesToMail[] = [
                            'ac' => $station->title,
                            'ac_id' => $station->id,
                            'alias' => $folder->title,
                            'alias_name' => $folder->name,
                            'time' => $parameters[$folder->name]['alert_hours'],
                            'success' => true,
                        ];
                        // --- оновити лічильник
                        Message::where('count_for_send', 1)
                            ->whereDate('created_at', '>=', $dateIgnore)
                            ->where('alias', $folder->name)
                            ->where('station_id', $station->id)
                            ->update(['count_for_send' => 2]);
                    }

                    dump('є пости');
                }
            } else {
                dump('не час моніторингу');
            }

        }

        return $messagesToMail;
    }

    /*
     *перевірка - якщо по вказаній АС та по вказаному типу завантаження
     * сьогодні вже повідомлення
     * було відправлене двічі, то більше не відправляти.
     *
     */
    public function checkMessage($stationId, $folderName)
    {
        return Message::where('station_id', $stationId)
            ->where('alias', $folderName)
            ->where('count_for_send', 1)
            ->whereDay('created_at', '=', Carbon::now()->day)
            ->doesntExist();

    }

    public function saveMessage($messageToMail)
    {
        if (isset($messageToMail['success'])) {
            return;
        }
        $message = Message::where('station_id', $messageToMail['ac_id'])
            ->where('alias', $messageToMail['alias_name'])
            ->where('count_for_send', 0)
            ->whereDay('created_at', Carbon::now()->day)
            ->first();

        if ($message) {
            $message->update(['count_for_send' => 1]);
        } else {
            Message::create([
                'station_id' => $messageToMail['ac_id'],
                'alias' => $messageToMail['alias_name'],
            ]);
        }
    }

    public function needEmailToSuccess($station_id, $folder_name)
    {
        if ($this->errorMessages) {
            if ($this->errorMessages
                    ->where('station_id', $station_id)
                    ->where('alias', $folder_name)
                    ->count() > 0) {
                return true;
            }
        }
        return false;
    }

    public function createMessageSMS($messagesToMail)
    {
        $message = 'Це лист повідомлення ' . date("d-m-Y H:i:s");

        foreach ($messagesToMail as $messageToMail) {
            if (isset($messageToMail['success'])) {
                $message .= 'Автостанція ' . $messageToMail['ac'] . ' вид синхронізації ' . $messageToMail['alias'] . ' синхронізація відновлена';
            } else {
                $message .= 'Автостанція ' . $messageToMail['ac'] . ' вид синхронізації ' . $messageToMail['alias'] . ' не було синхронізації ' . $messageToMail['time'] . 'годин';
            }
        }

        return $message;
    }
}
