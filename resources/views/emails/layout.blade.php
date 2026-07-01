<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;">
          <tr>
            <td style="background:#7EA35D;padding:20px 24px;color:#ffffff;font-size:22px;font-weight:700;">NatureOne</td>
          </tr>
          <tr>
            <td style="padding:24px;">
              @yield('content')
            </td>
          </tr>
          <tr>
            <td style="padding:16px 24px;background:#fafafa;color:#777777;font-size:12px;">
              © {{ date('Y') }} NatureOne. All rights reserved.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>

