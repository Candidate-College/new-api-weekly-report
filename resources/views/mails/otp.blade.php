<div
    style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;">
    <h1 style="color: #333; text-align: center;">Hi, {{ $name }}</h1>
    <p style="font-size: 16px; color: #555;">Ini adalah kode verifikasi Anda:</p>
    <div style="text-align: center; margin: 20px 0;">
        <span
            style="display: inline-block; font-size: 24px; font-weight: bold; padding: 10px 20px; border: 2px dashed #007BFF; border-radius: 5px; color: #007BFF;">{{ $code }}</span>
    </div>
    <p style="font-size: 16px; color: #555;">OTP ini akan kedaluwarsa dalam 5 menit.</p>
    <div style="text-align: center; margin-top: 30px;">
        <a href="#"
            style="display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #007BFF; text-decoration: none; border-radius: 5px;">Verifikasi
            OTP</a>
    </div>
</div>
