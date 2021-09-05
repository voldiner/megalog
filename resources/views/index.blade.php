@extends('layout')

@section('css')
    <!-- daterange picker -->
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <!-- iCheck for checkboxes and radio inputs -->
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Bootstrap Color Picker -->
    <!--<link rel="stylesheet" href="plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">-->
    <!-- Tempusdominus Bbootstrap 4 -->
    {{--<link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">--}}
    <!-- Select2 -->
    <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
    <!--<link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">-->
    <!-- Bootstrap4 Duallistbox -->
    <!--<link rel="stylesheet" href="plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">-->
    <style>
        #hint {
            display: none;
            position: absolute;
            border: 1px solid #ccc;
            padding: 5px;
            background: #fff;
            color: #007bff;
            border-radius: 5px;
            z-index: 200;
            width: 200px;
            text-align: center;
        }

        .no-shadow {
            box-shadow: none !important;
            background: #007bff !important;
            color: #fff;
        }

        .my-danger {
            background: #007bff !important;
        }

        .info-help {
            cursor: pointer;
        }

        .info-box-text {
            white-space: normal !important;
        }
    </style>
@endsection


@section('content')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- ajax -->
        <div id="ajax"></div>
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container">
                <div class="row mb-2">
                    <div class="col-lg-8">
                        <h1 class="m-0 text-dark"> ПрАТ ВОПАС
                            <small>повна інформація по синхронізації</small>
                        </h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <div id="alert-valid" class="alert alert-danger alert-dismissible no-display-alert">
                            {{--<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>--}}
                            <h5><i class="icon fas fa-ban"></i> <span>Помилка!</span></h5>
                            <p></p>
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div id="hint"></div>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Параметри</h5>

                                <p class="card-text">
                                    Задайте параметри для обрахунку графіку, період не більше 31 день
                                </p>
                                <form id="mega-form">
                                    <!-- data range -->
                                    <div class="form-group">
                                        <label>Період:</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                          <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                          </span>
                                            </div>
                                            <input name="data-range" type="text" class="form-control float-right"
                                                   id="reservation">
                                        </div>
                                    </div>
                                    <!-- select ac -->
                                    <div class="form-group">
                                        <label>Автостанція</label>
                                        <select name="ac-name" id="mega-ac" class="form-control select2"
                                                style="width: 100%;">
                                            @if($stations->count())
                                                @foreach($stations as $key => $station)
                                                    @if( isset($old_param->ac_name) && $key == $old_param->ac_name)
                                                        <option selected="selected" value="{{ $key }}" >{{ $station }}</option>
                                                    @else
                                                        <option value="{{ $key }}">{{ $station }}</option>
                                                    @endif
                                                @endforeach
                                            @else
                                                <option disabled>Відсутні автостанції</option>
                                            @endif
                                        </select>
                                    </div>
                                    <!-- multiple -->
                                    <div class="form-group">
                                        <label>Тип помилки</label>
                                        <select id="category" name="category" class="select2" multiple="multiple"
                                                data-placeholder="Select a error"
                                                style="width: 100%;">
                                            @if($categories->count())
                                                @foreach($categories as $key => $category)
                                                   @if(isset($old_param->category) && in_array($key , $old_param->category))
                                                        <option selected="selected" value="{{ $key }}">{{ $category }}</option>
                                                    @else
                                                        <option value="{{ $key }}">{{ $category }}</option>
                                                    @endif
                                                @endforeach
                                            @else
                                                <option disabled>Відсутні типи помилок</option>
                                            @endif
                                        </select>
                                    </div>
                                    <!-- multiple -->
                                    <div class="form-group">
                                        <label>Тип інформації</label>
                                        <select name="folder" id="folder" class="select2" multiple="multiple"
                                                data-placeholder="Select a folder"
                                                style="width: 100%;">
                                            @if($folders->count())
                                                @foreach($folders as $key => $folder)
                                                    @if(isset($old_param->folder) && in_array($key , $old_param->folder))
                                                        <option selected="selected" value="{{ $key }}">{{ $folder }}</option>
                                                    @else
                                                    <option value="{{ $key }}">{{ $folder }}</option>
                                                    @endif
                                                @endforeach
                                            @else
                                                <option disabled>Відсутні папки</option>
                                            @endif
                                            {{--<option value="test1">Довідник населених пунктів</option>--}}

                                        </select>
                                    </div>
                                    <!-- ratio inputs -->
                                    <div class="form-group clearfix">
                                        <div class="icheck-primary d-inline mr-2">
                                            <input type="radio" class="radioPrimary" data-radio="success"
                                                   id="radioPrimary1" name="r1" @if(isset($old_param->r_input) && $old_param->r_input == 'success') checked @endif>
                                            <label for="radioPrimary1">
                                                Успішні сихронізації
                                            </label>
                                        </div>
                                        <div class="icheck-danger d-inline mb-4">
                                            <input type="radio" class="radioPrimary" data-radio="error"
                                                   id="radioPrimary2" name="r1" @if(isset($old_param->r_input) && $old_param->r_input == 'error') checked @endif>
                                            <label for="radioPrimary2">
                                                Помилки передачі
                                            </label>
                                        </div>
                                    </div>
                                    <button id="btn-submit" class="btn btn-block btn-primary mb-3" type="submit">Побудувати
                                        графік
                                    </button>
                                </form>
                               {{-- <a href="#" class="card-link">Card link</a>
                                <a href="#" class="card-link">Another link</a>--}}
                            </div>
                        </div>

                    </div>
                    <div class="col-lg-4">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-thumbs-up"></i></span>
                            <div class="info-box-content">
                                <p>{{$message}}</p>
                                <span class="info-box-text">Вдалих завантажень </span>
                                <span class="info-box-number">{{ $posts_success }}</span>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <a id="get-errors"
                           href="{{ route('getPostsFromDates',['from' => $date_s->toDateTimeString(), 'to' => $date_p->toDateTimeString()]) }}">
                            <div class="info-box mb-3">
                                <span class="info-box-icon bg-danger elevation-1"><i
                                            class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <p>{{$message}}</p>
                                    <span class="info-box-text">Помилок</span>
                                    <span class="info-box-number">{{ $posts_error }}</span>
                                </div>
                                <!-- /.info-box-content -->
                            </div>
                        </a>
                        <div class="card card-primary" id="form-id">
                            <div class="info-box no-shadow">
                                <span class="info-box-icon bg-blue my-danger"><i
                                            rel="Для зміни АС та днів задайте відповідні значення в формі параметрів і натисніть 'Побудувати графік'"
                                            class="fas fa-info-circle info-help"></i></span>
                                <div class="info-box-content">

                                    <span class="info-box-text">Лог за день</span>
                                    {{--<span class="info-box-number">0</span>--}}
                                </div>

                            </div>
                            <!-- /.card-header -->
                            <!-- form start -->
                            <form role="form"
                                  action="{{ route('getPostsFromDay', ['from' => $date_s->toDateTimeString(), 'to' => $date_p->toDateTimeString()]) }}">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Автостанція</label>
                                        <select name="ac-name-day" id="mega-ac1" class="form-control select2"
                                                style="width: 100%;">
                                            @if($stations->count())
                                                @foreach($stations as $key => $station)
                                                    <option value="{{ $key }}">{{ $station }}</option>
                                                @endforeach
                                            @else
                                                <option disabled>Відсутні автостанції</option>
                                            @endif

                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Дата:</label>
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="far fa-calendar-alt"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control datetimepicker-input" name="date-day"
                                                   id="reservationdate-input">
                                        </div>
                                    </div>
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Переглянути</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
                <!-- /.row -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary card-outline">
                            <div class="card-body">
                                <h5 class="card-title">Графік</h5>

                                <p class="card-text">
                                    Щоденна кількість записів в журналі згідно визначених в формі параметрів

                                </p>
                                <!--Bar Chart-->
                                <div class="chart">
                                    <div class="chartjs-size-monitor">
                                        <div class="chartjs-size-monitor-expand">
                                            <div class="">

                                            </div>
                                        </div>
                                        <div class="chartjs-size-monitor-shrink">
                                            <div class="">

                                            </div>
                                        </div>
                                    </div>
                                    <canvas id="barChart"
                                            style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block; width: 649px;"
                                            width="649" height="250" class="chartjs-render-monitor"></canvas>
                                </div>

                                {{--<a href="#" class="card-link">Card link</a>
                                <a href="#" class="card-link">Another link</a>--}}
                            </div>
                        </div><!-- /.card -->
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
        <div class="p-3">
            <h5>Title</h5>
            <p>Sidebar content</p>
        </div>
    </aside>
    <!-- /.control-sidebar -->


    <!-- ./wrapper -->
@endsection

@section('scripts')


    <!-- ChartJS -->
    <script src="plugins/chart.js/Chart.min.js"></script>
    <!-- Select2 -->
    <script src="plugins/select2/js/select2.full.min.js"></script>
    <!-- Bootstrap4 Duallistbox -->
    <!--<script src="plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>-->
    <!-- InputMask -->
    <script src="plugins/moment/moment.min.js"></script>
    <script src="plugins/inputmask/min/jquery.inputmask.bundle.min.js"></script>
    <!-- date-range-picker -->
    <script src="plugins/daterangepicker/daterangepicker.js"></script>
    <!-- bootstrap color picker -->
    <!--<script src="plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>-->
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
    <!-- Bootstrap Switch -->
    {{--<script src="plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>--}}
    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js"></script>
    <script type="text/javascript" src="dist/js/jquery.cookies.js"></script>

    <!-- Page script -->
    <script>
        $('#btn-submit').click(function (e) {
            e.preventDefault();
            // ------ робимо ajax запит на побудову ггафіка,
            // ------ перед цим ставимо прелоадер
            $('#ajax').html('<span>Будуємо діаграму...</span>').fadeIn(500, function () {

                var data = {
                    "category": $('#category').val(),
                    "data-range": $('#reservation').val(),
                    "ac-name": $('#mega-ac').val(),
                    "folder": $('#folder').val(),
                    "r-input": $('.radioPrimary:checked').data("radio")
                };
                //console.log(data);
                var cookie_value = JSON.stringify(data);

                $.ajax({
                    url: "{{ route('showGraf') }}",
                    type: "POST",
                    data: data,
                    dataType: 'json',
                    success: function (data) {
                        $('#alert-valid').addClass('no-display-alert');
                        $.cookie('parameters', cookie_value, {expires: 30});
                        // ----- будуємо графік ---------------------
                        var areaChartData = {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: data.label,
                                    backgroundColor: data.backgroundColor,
                                    borderColor: data.borderColor,
                                    pointRadius: data.pointRadius,
                                    pointColor: data.pointColor,
                                    pointStrokeColor: data.pointStrokeColor,
                                    pointHighlightFill: data.pointHighlightFill,
                                    pointHighlightStroke: data.pointHighlightStroke,
                                    data: data.data
                                },
                            ]
                        };
                        //-------------
                        //- BAR CHART -
                        //-------------
                        var barChartCanvas = $('#barChart').get(0).getContext('2d');
                        var barChartData = jQuery.extend(true, {}, areaChartData);

                        var barChartOptions = {
                            responsive: true,
                            maintainAspectRatio: false,
                            datasetFill: false
                        };

                        var barChart = new Chart(barChartCanvas, {
                            type: 'bar',
                            data: barChartData,
                            options: barChartOptions
                        });

                        $('#ajax').find('span').text('Побудовано!');
                        $('#ajax').delay(300).fadeOut(300);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        var http_kod = jqXHR.status;
                        // ---- розшифровуємо тільки помилки що повертає скрипт --- //
                        var title = 'Невідома помилка -> http kod ' + http_kod;
                        var content = '';
                        if (jqXHR.getResponseHeader('megalog') === 'errorMegalog') {
                            if (typeof $.parseJSON(jqXHR.responseText).error !== 'undefined') {
                                var title = $.parseJSON(jqXHR.responseText).error + ' http kod ' + http_kod;
                            }
                            if (typeof $.parseJSON(jqXHR.responseText).message !== 'undefined') {
                                let massages = $.parseJSON(jqXHR.responseText).message;
                                for (let i = 0; i < massages.length; i++) {
                                    content += massages[i] + '<br>';
                                }
                            }
                        }
                        $('#ajax').find('span').text('Помилка!');
                        $('#ajax').delay(300).fadeOut(300);
                        $('#alert-valid p').html(content);
                        $('#alert-valid h5 span').html(title);
                        $('#alert-valid').removeClass('no-display-alert');
                    }
                })
            });

        });

        function setMessage() {
            // my help messages
            $('.info-help').click(function (e) {
                var hint = $(this).attr('rel');
                $('#hint').css({'left': e.clientX - 200, 'top': e.clientY - 80});
                $('#hint').show().text(hint);
            }).mouseout(function () {
                $('#hint').hide();
            });
        }

        $(function () {
            //Initialize Select2 Elements
            $('.select2').select2({
                tags: true

            });

            //Date range picker
            $('#reservationdate-input').daterangepicker(
                {
                    singleDatePicker: true,
                    showDropdowns: true,
                    "locale": {
                        "format": "DD/MM/YYYY",
                        "separator": " - ",
                        "applyLabel": "Ok",
                        "cancelLabel": "Cancel",
                        "fromLabel": "From",
                        "toLabel": "To",
                        "customRangeLabel": "Custom",
                        "weekLabel": "W",
                        "daysOfWeek": [
                            "Нд",
                            "По",
                            "Вт",
                            "Ср",
                            "Чт",
                            "Пт",
                            "Сб"
                        ],
                        "monthNames": [
                            "Січень",
                            "Лютий",
                            "Березень",
                            "Квітень",
                            "Травень",
                            "Червень",
                            "Липень",
                            "Серпень",
                            "Вересень",
                            "Жовтень",
                            "Листопад",
                            "Грудень"
                        ],
                        "firstDay": 1
                    },
                    //"startDate": "{{ $startDate }}",
                    "endDate": "{{ $endDate }}",
                    "maxDate": "{{ $maxDate }}"

                }, function (start, end, label) {

                });


            /* //Initialize Select2 Elements
             $('.select2bs4').select2({
                 theme: 'bootstrap4'
             })*/

            /*  //Datemask dd/mm/yyyy
              $('#datemask').inputmask('dd/mm/yyyy', { 'placeholder': 'dd/mm/yyyy' })
              //Datemask2 mm/dd/yyyy
              $('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' })
              //Money Euro
              $('[data-mask]').inputmask()
      */
            /* //Date range picker
             $('#reservationdate').datetimepicker({
                 format: 'L'
             });*/
            $('#reservation').daterangepicker(
                {
                    "locale": {
                        "format": "DD/MM/YYYY",
                        "separator": " - ",
                        "applyLabel": "Ok",
                        "cancelLabel": "Cancel",
                        "fromLabel": "From",
                        "toLabel": "To",
                        "customRangeLabel": "Custom",
                        "weekLabel": "W",
                        "daysOfWeek": [
                            "Нд",
                            "По",
                            "Вт",
                            "Ср",
                            "Чт",
                            "Пт",
                            "Сб"
                        ],
                        "monthNames": [
                            "Січень",
                            "Лютий",
                            "Березень",
                            "Квітень",
                            "Травень",
                            "Червень",
                            "Липень",
                            "Серпень",
                            "Вересень",
                            "Жовтень",
                            "Листопад",
                            "Грудень"
                        ],
                        "firstDay": 1
                    },
                    "startDate": "{{ $startDate }}",
                    "endDate": "{{ $endDate }}",
                    "maxDate": "{{ $maxDate }}"
                }, function (start, end, label) {
                    // console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
                    // var u = new Url();
                    // u.query.from = start.format('YYYY-MM-DD');
                    // u.query.to = end.format('YYYY-MM-DD');
                    // history.pushState({}, '', u);
                }
            );

            //Date range picker with time picker
            /* $('#reservationtime').daterangepicker({
                 timePicker: true,
                 timePickerIncrement: 30,
                 locale: {
                     format: 'MM/DD/YYYY hh:mm A'
                 }
             })*/
            //Date range as a button
            /* $('#daterange-btn').daterangepicker(
                 {
                     ranges   : {
                         'Today'       : [moment(), moment()],
                         'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                         'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
                         'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                         'This Month'  : [moment().startOf('month'), moment().endOf('month')],
                         'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                     },
                     startDate: moment().subtract(29, 'days'),
                     endDate  : moment()
                 },
                 function (start, end) {
                     $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
                 }
             )*/

            /* //Timepicker
             $('#timepicker').datetimepicker({
                 format: 'LT'
             })*/

            //Bootstrap Duallistbox
            // $('.duallistbox').bootstrapDualListbox()

            //Colorpicker
            // $('.my-colorpicker1').colorpicker()
            //color picker with addon
            //  $('.my-colorpicker2').colorpicker()

            /* $('.my-colorpicker2').on('colorpickerChange', function(event) {
                 $('.my-colorpicker2 .fa-square').css('color', event.color.toString());
             });

             $("input[data-bootstrap-switch]").each(function(){
                 $(this).bootstrapSwitch('state', $(this).prop('checked'));
             });*/

            /* ChartJS
            * -------
            * Here we will create a few charts using ChartJS
            */
            var areaChartData = {
                labels: [],
                datasets: [
                    {
                        label: '',
                        backgroundColor: 'rgba(60,141,188,0.9)',
                        borderColor: 'rgba(60,141,188,0.8)',
                        pointRadius: false,
                        pointColor: '#3b8bba',
                        pointStrokeColor: 'rgba(60,141,188,1)',
                        pointHighlightFill: '#fff',
                        pointHighlightStroke: 'rgba(60,141,188,1)',
                        data: []
                    },
                    // {
                    //     label               : 'Electronics',
                    //     backgroundColor     : 'rgba(210, 214, 222, 1)',
                    //     borderColor         : 'rgba(210, 214, 222, 1)',
                    //     pointRadius         : false,
                    //     pointColor          : 'rgba(210, 214, 222, 1)',
                    //     pointStrokeColor    : '#c1c7d1',
                    //     pointHighlightFill  : '#fff',
                    //     pointHighlightStroke: 'rgba(220,220,220,1)',
                    //     data                : [65, 59, 80, 81, 56, 55, 40]
                    // },
                ]
            };
            //-------------
            //- BAR CHART -
            //-------------
            var barChartCanvas = $('#barChart').get(0).getContext('2d');
            var barChartData = jQuery.extend(true, {}, areaChartData);
            //var temp0 = areaChartData.datasets[0];
            //var temp1 = areaChartData.datasets[1];

            //barChartData.datasets[0] = temp1;
            //barChartData.datasets[1] = temp0;

            var barChartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                datasetFill: false
            };

            var barChart = new Chart(barChartCanvas, {
                type: 'bar',
                data: barChartData,
                options: barChartOptions
            });
            // ------ блок скриптів для формування параметрів в адресній строці -----------
            //     $('.product_sorting_btn').click(function () {
            //         $('.sorting_text').text($(this).find('span').text());
            //         let orderBy = $(this).data('order');
            //     });
            //     $('.select2-results__option').click(function(){
            //         console.log($(this));
            //     });


            // $('#mega-ac').on('select2:select', function (e) {
            //     var data = e.params.data;
            //     console.log(data.element.value);
            // });
            if ($('#radioPrimary1').prop('checked')) {
                $('#category').prop('disabled', true);
            }
            $('#radioPrimary1').change(function () {
                $('#category').prop('disabled', true);
            })
            $('#radioPrimary2').change(function () {
                $('#category').prop('disabled', false);
            })

        });
    </script>
@endsection