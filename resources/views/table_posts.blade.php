<div class="card-header border-transparent">
    @isset($date,$ac)
        <h3 class="card-title">Журнал за {{ $date }} по {{ $ac->title }}@if(!$posts->count()) &rarr; Інформація відсутня @endif</h3>
    @endisset
    @isset($dateTime_from,$dateTime_to )
        <h3 class="card-title">Помилки за період з {{ $dateTime_from }} по {{$dateTime_to}} @if(!$posts->count()) &rarr; Інформація відсутня @endif </h3>
    @endisset

    <div class="card-tools">
        <a href="{{ route('home') }}" class="text-right">На головну</a>
    </div>
</div>
<!-- /.card-header -->
<div class="card-body p-0">

    <div class="table-responsive">
        <table class="table m-0">
            <thead>
            <tr>
                <th>#</th>
                <th class="order-sort" data-sort="station_id">Автостанція @if($orderBy === 'station_id')<i class="fas fa-angle-double-up ml-2"></i>@endif</th>
                <th class="order-sort" data-sort="category_id">Тип помилки @if($orderBy === 'category_id')<i class="fas fa-angle-double-up ml-2"></i>@endif</th>
                <th class="order-sort" data-sort="alias">Тип синхронізації @if($orderBy === 'alias')<i class="fas fa-angle-double-up ml-2"></i>@endif</th>
                <th class="order-sort" data-sort="created_at">Дата @if($orderBy === 'created_at')<i class="fas fa-angle-double-up ml-2"></i>@endif</th>
            </tr>
            </thead>
            <tbody>
            @foreach($posts as $post)
                <tr>
                    <td> {{ $loop->iteration }}</td>
                    <td>{{ $post->station->title }}</td>
                    <td width="30%">
                        @isset($post->category->title)
                        <div class="accordion accordion-flush" id="accordionFlushExample">
                            <div class="accordion-item">
                                <div class="accordion-header" id="flush-headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#flush-collapse{{ $loop->iteration }}" aria-expanded="false"
                                            aria-controls="flush-collapseOne">
                                        {{ $post->category->title }}
                                    </button>
                                </div>
                                <div id="flush-collapse{{ $loop->iteration }}" class="accordion-collapse collapse"
                                     aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">
                                        {{ $post->error }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                            <span class="badge badge-success">Успішно</span>
                        @endisset
                    </td>
                    <td><span class="badge badge-danger">{{ $post->folder->title }}</span></td>
                    <td>
                        {{ $post->created_at }}
                    </td>
                </tr>
            @endforeach

            </tbody>
        </table>
    </div>
    <!-- /.table-responsive -->
</div>
<!-- /.card-body -->
<div class="card-footer clearfix">
    @if($posts->count())
        @isset($dateTime_from,$dateTime_to )
            {{ $posts->appends(['from' => $dateTime_from, 'to' => $dateTime_to, 'orderby' => $orderBy])->links() }}
        @endisset
        @isset($date,$ac)
            {{ $posts->appends(['ac-name-day' => $ac_id, 'date-day' => $date, 'orderby' => $orderBy])->links() }}
        @endisset
    @endif
</div>
<!-- /.card-footer -->