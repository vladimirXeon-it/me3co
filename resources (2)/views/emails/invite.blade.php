
<!DOCTYPE html>
<html>
<head>
    {{-- <title>{{ $data['title']}}</title> --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">
    <style type="text/css">
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            width: 100%;
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: #fff;
        }

        .container {
            max-width: 850px;
            margin: auto;
        }

        .templete-wrapper {
            background: #fff;
        }

        header {
            font-size: 2.2em;
            font-weight: 700;
            text-align: center;
            background: #e3e4e5;
            color: #15aabf;
            padding: 20px;
        }

        .body-text {
            padding: 30px 20px;
        }

        .body-text p {
            font-size: 16px;
            color: #566;
        }

        h1 {
            font-size: 1.7em;
            margin-bottom: 20px;
        }

        .btn-box {
            text-align: center;
            margin: 20px 0px;
        }

        .verify-btn {
            outline: none;
            background: #15aabf;
            text-decoration: none;
            color: #fff;
            font-size: 18px;
            height: auto;
            font-weight: bold;
            padding: 14px 22px;
            text-transform: capitalize;
            border: none;
            border-radius: 4px;
            box-shadow: none;
            transition: all ease-in-out 0.3s;
        }

        .verify-btn:hover {
            box-shadow: rgba(0, 0, 0, 0.2) 0px 3px 7px !important;
        }

        hr {
            border: 1px solid #eee;
        }

        small {
            text-align: left;
            display: block;
            color: #566;
            font-size: 14px;
            padding: 10px 0px;
        }

        footer {
            font-size: 14px;
            font-weight: 300;
            text-align: center;
            background: #e3e4e5;
            color: #566;
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="templete-wrapper">
    <header>
        <div class="container">
            ME3CO.COM
        </div>
    </header>
    <div class="body-text">
        <div class="container">
            <h1>Hello!</h1>
            <p>
                {{ $data['body']}}
            </p>
            <br>
            <div class="btn-box">
                <a href={{ $data['url']}} class="verify-btn">Accept Invitation</a>
            </div>
            <br>
            <p>
                If you don't want to accept, don't need to proceed further.
            </p>
            <br>
            <p>
                Regards, <br>
                Me3co.com
            </p>
            <br>
            <hr>

            <small>
                If you are having trouble clicking the 'Verify button', copy and paste the below URL into your web
                browser
                <a href={{ $data['url']}} target="_blank">{{ $data['url']}}<a>
            </small>
        </div>
    </div>
    <footer>
        <div class="container">
            &copy; 2023 Me3co.com. All Rights Reserved.
        </div>
    </footer>
</div>
</body>
</html>
