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
        .password-box {
            display: inline-block;
            background-color: #f1f5f9;
            padding: 15px 25px;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px dashed #cbd5e1;
            color: #0f172a;
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
            <h2 style="font-size: 1.25rem;">Reset Password Akun Admin</h2>
            <p style="margin:0; opacity: 0.8; font-size: 0.9rem;">Techub</p>
        </div>
        <div class="content">
            <p>Halo,</p>
            <p>Sistem telah membuatkan password baru secara otomatis untuk akun Anda. Berikut adalah password baru yang dapat Anda gunakan untuk masuk:</p>
            
            <div style="text-align: center;">
                <div class="password-box">{{ $newPassword }}</div>
            </div>

            <p style="color: #ef4444; font-size: 0.9em; margin-top: 10px;">
                <strong>Penting:</strong> Harap segera ubah password ini melalui menu profil Anda setelah Anda berhasil login demi keamanan akun Anda.
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
