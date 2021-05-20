@extends('layout')
@section('css')
    <!-- daterange picker -->
    {{--<link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">--}}
    <!-- iCheck for checkboxes and radio inputs -->
    {{--<link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">--}}
    <!-- Bootstrap Color Picker -->
    <!--<link rel="stylesheet" href="plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">-->
    <!-- Tempusdominus Bbootstrap 4 -->
    <!--<link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">-->
    <!-- Select2 -->
    {{--<link rel="stylesheet" href="plugins/select2/css/select2.min.css">--}}
    <!--<link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">-->
    <!-- Bootstrap4 Duallistbox -->
    <!--<link rel="stylesheet" href="plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">-->
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <style>
        .accordion-button{
            padding: 0;
        }
        .order-sort{
            cursor: pointer;
        }
        .order-sort:hover{
            background-color: #EEEEEE;
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
                    <div class="col-lg-12">
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
                    <div class="col-lg-12">
                        <div id="alert-valid" class="alert alert-danger alert-dismissible no-display-alert">
                            {{--<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>--}}
                            <h5><i class="icon fas fa-ban"></i> <span>Помилка!</span></h5>
                            <p></p>
                        </div>

                        {{--<div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Параметри</h5>

                                <p class="card-text">
                                    Задайте параметри для обрахунку графіку, період не більше 31 день
                                </p>

                                <a href="#" class="card-link">Card link</a>
                                <a href="#" class="card-link">Another link</a>
                            </div>
                        </div>--}}

                        <div class="card" id="table-posts">

                                @include('table_posts')

                        </div>

                    </div>
                </div>
                <!-- /.row -->

            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

@endsection

@section('scripts')
    <!-- Вариант 2: Bootstrap JS отдельно от Popper -->
    {{--<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js" integrity="sha384-KsvD1yqQ1/1+IA7gi3P0tyJcT3vR+NdBTt13hSJ2lnve8agRGXTTyNaBYmCR/Nwi" crossorigin="anonymous"></script>--}}
    <script src="dist/js/url.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.min.js" integrity="sha384-nsg8ua9HAw1y0W1btsyWgBklPnCUAFLuTMS2G72MMONqmOymq585AcH49TLBQObG" crossorigin="anonymous"></script>
    <script>
        $(function () {
            sort_table();

            //$('[data-sort={{$orderBy}}] i').css("display", "inline");

            function sort_table() {

                $('.order-sort').click(function (e) {
                    //e.preventDefault();
                    // ------ робимо ajax запит
                    // ------ перед цим ставимо прелоадер
                    $('#ajax').html('<span>Сортування...</span>').fadeIn(500, function () {
                        my_url = Url.parseQuery();
                        my_url.orderby = $(e.target).data('sort');
                        //var data = JSON.stringify(my_url) ;
                        var data = my_url;
                        console.log(data);

                        // return;
                        $.ajax({
                            url: "{{ $ajaxRoute }}",
                            type: "GET",
                            data: data,
                            success: function (data) {
                                $('#alert-valid').addClass('no-display-alert');
                                //console.log(data);
                                //return;

                                $('#table-posts').html(data);
                                sort_table();
                                Url.updateSearchParam("orderby", my_url.orderby)
                                $('#ajax').find('span').text('Побудовано!');
                                $('#ajax').delay(300).fadeOut(300);
                                //$('.order-sort i').css("display", "none");

                                //$(e.target).find("i").css("display", "inline-block");
                                //console.log($(e.target).find("i"));
                                //$('[data-sort=category_id] i').css("display", "inline");
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                var http_kod = jqXHR.status;
                                //console.log(jqXHR.getResponseHeader('megalog'));
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
            }
        })
    </script>


@endsection