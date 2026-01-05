<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Error - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .error-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        .error-icon {
            font-size: 80px;
            color: #e74c3c;
            margin-bottom: 20px;
        }

        h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 15px;
        }

        p {
            color: #7f8c8d;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn-home {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease;
        }

        .btn-home:hover {
            transform: translateY(-2px);
        }

        .error-code {
            color: #bdc3c7;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>Database Error</h1>
        <p>We're experiencing technical difficulties with the database. Our team has been notified and is working to
            resolve the issue.</p>
        <p>Please try again later or contact support if the problem persists.</p>
        <a href="{{ url('/') }}" class="btn-home">Return to Home</a>
        <div class="error-code">Error Code: DB-500</div>
    </div>
</body>

</html>
