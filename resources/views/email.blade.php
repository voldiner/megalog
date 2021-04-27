<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Megalog</title>
</head>
<body>
    <h1> Це лист повідомлення {{ date("d-m-Y H:i:s") }}</h1>
    @foreach($parameters as $parameter)

        <p>Автостанція {{ $parameter['ac'] }}</p>
        <p>вид синхронізації {{ $parameter['alias'] }}</p>
        <p>не було синхронізації {{ $parameter['time'] }} годин</p>

    @endforeach
</body>
</html>