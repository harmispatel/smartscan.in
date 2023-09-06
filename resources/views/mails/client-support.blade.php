@php
    $shop_name = isset($data['client_details']->hasOneShop->shop['name']) ? $data['client_details']->hasOneShop->shop['name'] : '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $shop_name }}</title>
</head>
<body>
    <div style="width: 100%; padding:5px; text-align:center;background: lightgray">
        <h3>{{ $shop_name }}</h3>
        <hr>
        <p>{{ $data['message'] }}</p>
    </div>
</body>
</html>
