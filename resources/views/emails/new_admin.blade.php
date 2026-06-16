<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #002B5C;
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .header h2 {
            margin: 0;
        }
        .content {
            padding: 30px;
        }
        .footer {
            background-color: #f1f5f9;
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #64748b;
        }
        .info-box {
            background: #f1f5f9;
            padding: 15px 20px;
            border-radius: 6px;
            border-left: 4px solid #002B5C;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            background-color: #002B5C;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="font-size: 1.25rem;">Selamat Datang di Techub</h2>
            <p style="margin:0; opacity: 0.8; font-size: 0.9rem;">Sistem Manajemen Booking Labkom</p>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $newAdmin->name }}</strong>,</p>
            <p>Selamat! Akun Anda baru saja didaftarkan sebagai Admin oleh <strong>{{ $creatorAdmin->name }}</strong>.</p>
            
            <div class="info-box">
                <p style="margin: 0;">Anda kini memiliki akses penuh ke Dashboard Manajemen Admin. Gunakan kredensial berikut untuk masuk ke dalam sistem:</p>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li><strong>Email:</strong> {{ $newAdmin->email }}</li>
                    @if($rawPassword)
                    <li><strong>Password:</strong> {{ $rawPassword }}</li>
                    @else
                    <li><strong>Password:</strong> (Password yang telah diatur oleh sistem)</li>
                    @endif
                </ul>
            </div>
            
            <p style="text-align: center;">
                <a href="{{ url('/login') }}" class="btn" style="color: #ffffff !important; text-decoration: none;">Login ke Dashboard</a>
            </p>
            
            <p style="margin-top: 30px;">Salam hangat,<br><strong>Tim IT Infrastruktur</strong></p>
        </div>
        <div class="footer" style="font-size: 11px; line-height: 1.4;">
            This message is intended only for the designated recipient. If you have received this email in error, please notify the sender and delete this email. Thank you for your cooperation.<br><br>
            &copy; {{ date('Y') }} PT Binawan Inti Teknologi
        </div>
    </div>
</body>
</html>
