<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Atur Ulang Kata Sandi — KosFly</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; -webkit-font-smoothing: antialiased; line-height: 1.6;">

  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc; padding: 40px 16px;">
    <tr>
      <td align="center">

        <!-- Outer Card Wrapper (Max-width 560px) -->
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 560px; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">
          
          <!-- Header Branding -->
          <tr>
            <td style="background-color: #0f172a; padding: 28px 32px; text-align: center;">
              <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                <tr>
                  <td style="background-color: #e11d48; color: #ffffff; width: 34px; height: 34px; border-radius: 8px; text-align: center; vertical-align: middle; font-weight: 800; font-size: 18px; line-height: 34px;">
                    K
                  </td>
                  <td style="padding-left: 10px; font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">
                    Kos<span style="color: #fb7185;">Fly</span>
                  </td>
                </tr>
              </table>
              <div style="color: #94a3b8; font-size: 12px; margin-top: 6px; letter-spacing: 0.5px; text-transform: uppercase;">
                Manajemen Hunian Kos Modern
              </div>
            </td>
          </tr>

          <!-- Main Content -->
          <tr>
            <td style="padding: 36px 32px 28px;">
              <h1 style="margin: 0 0 16px; font-size: 20px; font-weight: 700; color: #0f172a;">
                Atur Ulang Kata Sandi Akun Anda
              </h1>

              <p style="margin: 0 0 16px; font-size: 15px; color: #334155;">
                Halo <strong>{{ $userName ?? 'Pengguna KosFly' }}</strong>,
              </p>

              <p style="margin: 0 0 24px; font-size: 15px; color: #475569; line-height: 1.6;">
                Kami menerima permintaan untuk mengatur ulang kata sandi akun KosFly yang terhubung dengan alamat email ini. Klik tombol di bawah untuk membuat kata sandi baru:
              </p>

              <!-- Reset Button -->
              <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: 28px auto;">
                <tr>
                  <td align="center" style="border-radius: 8px; background-color: #e11d48;">
                    <a href="{{ $resetUrl }}" target="_blank" style="display: inline-block; padding: 14px 32px; font-size: 15px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 8px; background-color: #e11d48; box-shadow: 0 2px 6px rgba(225, 29, 72, 0.35);">
                      Atur Ulang Kata Sandi
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Security Warning Box -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; margin-top: 24px; margin-bottom: 24px;">
                <tr>
                  <td style="padding: 14px 16px; font-size: 13px; color: #92400e; line-height: 1.5;">
                    <div style="font-weight: 600; margin-bottom: 4px;">⚠️ Informasi Keamanan Penting:</div>
                    <ul style="margin: 0; padding-left: 18px;">
                      <li>Tautan ini hanya berlaku selama <strong>60 menit</strong> sejak email dikirim.</li>
                      <li>Jika Anda tidak meminta pengaturan ulang kata sandi, abaikan email ini. Akun Anda tetap aman dan kata sandi lama Anda tidak akan berubah.</li>
                    </ul>
                  </td>
                </tr>
              </table>

              <!-- Fallback Direct URL -->
              <p style="margin: 24px 0 6px; font-size: 12px; color: #64748b; line-height: 1.5;">
                Jika tombol di atas tidak dapat diklik, salin dan tempel tautan berikut ke peramban (browser) web Anda:
              </p>
              <p style="margin: 0 0 16px; font-size: 12px; color: #e11d48; word-break: break-all; line-height: 1.4;">
                <a href="{{ $resetUrl }}" style="color: #e11d48; text-decoration: underline;">{{ $resetUrl }}</a>
              </p>

              <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 28px 0 20px;">

              <p style="margin: 0; font-size: 13px; color: #64748b;">
                Salam hangat,<br>
                <strong>Tim Pengelola KosFly</strong>
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color: #f1f5f9; padding: 20px 32px; text-align: center; border-top: 1px solid #e2e8f0;">
              <p style="margin: 0 0 6px; font-size: 12px; color: #64748b;">
                Email ini dikirim secara otomatis oleh sistem KosFly. Mohon untuk tidak membalas email ini.
              </p>
              <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                &copy; {{ date('Y') }} KosFly Management System. Seluruh hak cipta dilindungi.
              </p>
            </td>
          </tr>

        </table>
        <!-- End Outer Card Wrapper -->

      </td>
    </tr>
  </table>

</body>
</html>
