<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $data['subject'] }}</title>
    <style>
        .container{
            justify-content: center;
            width:100%;
        }
        .row{
            width: 100%;
        }
        .col-md-12{
            width: 100%;
            text-align: center;
            margin-bottom:10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h3 style="color: blueviolet">{{ $data['subject'] }} Information</h3>
                <code>{{ $data['message'] }}</code>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #ffffff;padding: 20px;">
                    <tbody>
                        <tr>
                            <td valign="center" style="padding: 3px 10px; border: 1px solid #e4e4e4;"><b>Full Name</b></td>
                            <td valign="center" style="padding: 3px 10px; border: 1px solid #e4e4e4;">{{ $data['firstname'] }} {{ $data['lastname'] }}</td>
                        </tr>
                        <tr>
                            <td valign="center" style="padding: 3px 10px; border: 1px solid #e4e4e4;"><b>Email</b></td>
                            <td valign="center" style="padding: 3px 10px; border: 1px solid #e4e4e4;">{{ $data['email'] }}</td>
                        </tr>
                        <tr>
                            <td valign="center" style="padding: 3px 10px; border: 1px solid #e4e4e4;"><b>Phone</b></td>
                            <td valign="center" style="padding: 3px 10px; border: 1px solid #e4e4e4;">{{ $data['phone'] }}</td>
                        </tr>
                        <tr>
                            <td valign="center" style="padding: 3px 10px; border: 1px solid #e4e4e4;"><b>Age</b></td>
                            <td valign="center" style="padding: 3px 10px; border: 1px solid #e4e4e4;">{{ $data['age'] }}</td>
                        </tr>
                        <tr>
                            <td valign="center" style="padding: 3px 10px; border: 1px solid #e4e4e4;"><b>Room No.</b></td>
                            <td valign="center" style="padding: 3px 10px; border: 1px solid #e4e4e4;">{{ $data['room_number'] }}</td>
                        </tr>
                    </tbody>
                </table>
             </div>
        </div>
    </div>
</body>
</html>
