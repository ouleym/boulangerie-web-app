<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Boulangerie</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .header {
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
            color: white;
            text-align: center;
            padding: 30px 20px;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header h2 {
            font-size: 18px;
            font-weight: normal;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .content p {
            margin-bottom: 15px;
            font-size: 16px;
        }

        .status-update {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }

        .status-change {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            font-size: 16px;
            font-weight: bold;
        }

        .old-status {
            color: #6c757d;
            text-decoration: line-through;
        }

        .arrow {
            color: #28a745;
            font-size: 20px;
        }

        .new-status {
            padding: 8px 16px;
            border-radius: 20px;
            color: white;
        }

        .status-en_attente { background-color: #6c757d; }
        .status-en_preparation { background-color: #ffc107; }
        .status-prete { background-color: #17a2b8; }
        .status-en_livraison { background-color: #fd7e14; }
        .status-livree { background-color: #28a745; }
        .status-annulee { background-color: #dc3545; }

        .order-details {
            margin: 25px 0;
        }

        .order-details h3 {
            color: #8B4513;
            margin-bottom: 15px;
            border-bottom: 2px solid #8B4513;
            padding-bottom: 5px;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table td {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .order-table td:first-child {
            width: 40%;
            color: #666;
        }

        .message-box {
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid;
        }

        .message-box.info {
            background-color: #e3f2fd;
            border-left-color: #2196f3;
            color: #1976d2;
        }

        .message-box.success {
            background-color: #e8f5e8;
            border-left-color: #4caf50;
            color: #2e7d32;
        }

        .message-box.warning {
            background-color: #fff3e0;
            border-left-color: #ff9800;
            color: #f57c00;
        }

        .message-box.error {
            background-color: #ffebee;
            border-left-color: #f44336;
            color: #c62828;
        }

        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 5px;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #8B4513;
            color: white;
        }

        .btn-primary:hover {
            background-color: #6d2c0a;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 25px;
            border-top: 1px solid #dee2e6;
        }

        .footer p {
            margin-bottom: 5px;
        }

        @media (max-width: 600px) {
            .container {
                margin: 0;
                box-shadow: none;
            }

            .content {
                padding: 20px;
            }

            .status-change {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
@yield('content')
</body>
</html>
