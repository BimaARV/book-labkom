<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8fafc; color: #1e293b; line-height: 1.6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background-color: #002B5C; color: #ffffff; text-align: center; padding: 20px; }
        .header h2 { margin: 0; }
        .content { padding: 30px; }
        .footer { background-color: #f1f5f9; text-align: center; padding: 15px; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="font-size: 1.25rem;">Penghapusan Akun</h2>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $userName }}</strong>,</p>
            <p>Ini adalah email konfirmasi bahwa akun Anda di Techub baru saja dihapus secara permanen dari sistem.</p>
            <p>Jika Anda tidak melakukan tindakan ini, harap segera hubungi tim administrator utama.</p>
            <p style="margin-top: 30px;">Salam hangat,<br><strong>Tim IT Infrastruktur</strong></p>
        </div>
        <div class="footer" style="font-size: 11px; line-height: 1.4;">
            This message is intended only for the designated recipient. If you have received this email in error, please notify the sender and delete this email.<br><br>
            &copy; 2026 PT Binawan Inti Teknologi
        </div>
    </div>
</body>
</html>
