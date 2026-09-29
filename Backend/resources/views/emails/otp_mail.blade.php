<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="x-apple-disable-message-reformatting">
  <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
  <title>Your Verification Code</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
  {{-- Preheader: visible in inbox list but hidden in email body --}}
  <div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">
    Your FutureSelf verification code is ready &#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;
  </div>

  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f4f4f7;">
    <tr>
      <td align="center" style="padding:32px 16px;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="max-width:520px;width:100%;background-color:#ffffff;border-radius:12px;border:1px solid #e1e1e8;">

          {{-- Brand Accent Bar --}}
          <tr>
            <td style="height:4px;background-color:#6c3ce0;border-radius:12px 12px 0 0;font-size:0;line-height:0;">&nbsp;</td>
          </tr>

          {{-- Header --}}
          <tr>
            <td style="padding:28px 36px 0;text-align:center;">
              <p style="margin:0;font-size:22px;font-weight:700;color:#1a1a2e;letter-spacing:-0.5px;">Future<span style="color:#6c3ce0;">Self</span></p>
              <p style="margin:6px 0 0;font-size:11px;color:#8b8b9e;text-transform:uppercase;letter-spacing:1.5px;font-weight:500;">Account Security</p>
            </td>
          </tr>

          {{-- Divider --}}
          <tr>
            <td style="padding:16px 36px 0;"><div style="border-top:1px solid #eeeef2;"></div></td>
          </tr>

          {{-- Content --}}
          <tr>
            <td style="padding:24px 36px 32px;">
              <p style="margin:0 0 6px;font-size:16px;color:#1a1a2e;font-weight:600;">Hello{{ isset($name) ? ', ' . e($name) : '' }}!</p>
              <p style="margin:0 0 24px;font-size:14px;color:#555568;line-height:1.6;">
                Use the code below to verify your identity. This code expires in <strong style="color:#1a1a2e;">5 minutes</strong>.
              </p>

              {{-- OTP Code (table-based for Outlook compatibility) --}}
              <table role="presentation" align="center" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 24px;">
                <tr>
                  <td style="background-color:#f8f8fc;border:2px solid #e1e1e8;border-radius:10px;padding:18px 40px;">
                    <p style="margin:0;font-size:34px;font-weight:700;letter-spacing:10px;color:#1a1a2e;font-family:'Courier New',Courier,monospace;text-align:center;">{{ $otp }}</p>
                  </td>
                </tr>
              </table>

              {{-- Security Notice --}}
              <div style="background-color:#fef9f0;border:1px solid #fde5c0;border-radius:8px;padding:14px 16px;">
                <p style="margin:0;font-size:13px;color:#92600a;line-height:1.5;">
                  <strong>Security tip:</strong> If you didn't request this code, you can safely ignore this email. Never share this code with anyone.
                </p>
              </div>
            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="padding:24px 36px;background-color:#fafafc;border-top:1px solid #eeeef2;border-radius:0 0 12px 12px;text-align:center;">
              <p style="margin:0 0 6px;font-size:12px;color:#8b8b9e;">&copy; {{ date('Y') }} FutureSelf &middot; India</p>
              <p style="margin:0;font-size:10px;color:#c0c0ce;line-height:1.5;">
                This is an automated security message from your FutureSelf account.<br>
                Please do not reply to this email.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
