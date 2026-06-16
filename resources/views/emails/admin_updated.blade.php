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
        .changes-list {
            background: #f1f5f9;
            padding: 15px 20px;
            border-radius: 6px;
            border-left: 4px solid #f59e0b;
            margin-top: 15px;
        }
        .footer {
            background-color: #f1f5f9;
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="font-size: 1.25rem;">Pembaruan Data Admin</h2>
            <p style="margin:0; opacity: 0.8; font-size: 0.9rem;">Techub</p>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $updatedUser->name }}</strong>,</p>
            <p>Sistem mendeteksi adanya perubahan pada data akun Anda yang dilakukan oleh <strong>{{ $changerAdmin->name }}</strong>.</p>
            
            <p>Berikut adalah rincian perubahan yang dilakukan:</p>
            <div class="changes-list">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($changes as $change)
                        <li>{{ $change }}</li>
                    @endforeach
                </ul>
            </div>
            
            <p style="margin-top: 30px;">Salam hangat,<br><strong>Tim IT Infrastruktur</strong></p>
        </div>
        <div class="footer" style="font-size: 11px; line-height: 1.4;">
            This message is intended only for the designated recipient. If you have received this email in error, please notify the sender and delete this email. Thank you for your cooperation.<br><br>
            &copy; {{ date('Y') }} PT Binawan Inti Teknologi
        </div>
    </div>
</body>
</html>
