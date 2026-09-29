<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="x-apple-disable-message-reformatting">
  <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
  <title>A message from your Future Self</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
  {{-- Preheader --}}
  <div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">
    Your future self has a message for you &#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;
  </div>

  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f4f4f7;">
    <tr>
      <td align="center" style="padding:32px 16px;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;width:100%;background-color:#ffffff;border-radius:12px;border:1px solid #e1e1e8;">

          {{-- Brand Accent Bar --}}
          <tr>
            <td style="height:4px;background-color:#6c3ce0;border-radius:12px 12px 0 0;font-size:0;line-height:0;">&nbsp;</td>
          </tr>

          {{-- Header --}}
          <tr>
            <td style="padding:28px 36px 0;text-align:center;">
              <p style="margin:0;font-size:22px;font-weight:700;color:#1a1a2e;letter-spacing:-0.5px;">Future<span style="color:#6c3ce0;">Self</span></p>
              <p style="margin:6px 0 0;font-size:11px;color:#8b8b9e;text-transform:uppercase;letter-spacing:1.5px;font-weight:500;">A Note from Future You</p>
            </td>
          </tr>

          {{-- Divider --}}
          <tr>
            <td style="padding:16px 36px 0;"><div style="border-top:1px solid #eeeef2;"></div></td>
          </tr>

          {{-- Content --}}
          <tr>
            <td style="padding:24px 36px 32px;">
              <p style="margin:0 0 16px;font-size:16px;color:#1a1a2e;font-weight:500;line-height:1.5;">{{ $aiGreeting }}</p>
              <p style="margin:0 0 24px;font-size:14px;color:#555568;line-height:1.7;white-space:pre-line;">{{ $aiBody }}</p>

              {{-- Actionable Step --}}
              <div style="background-color:#f0f4ff;border-left:3px solid #3b82f6;border-radius:0 8px 8px 0;padding:16px 18px;margin-bottom:24px;">
                <p style="margin:0 0 6px;font-size:11px;font-weight:600;color:#3b82f6;text-transform:uppercase;letter-spacing:0.5px;">One small step you can take today</p>
                <p style="margin:0;font-size:14px;color:#333346;line-height:1.6;">{{ $aiActionableStep }}</p>
              </div>

              <p style="margin:0 0 28px;font-size:14px;font-style:italic;color:#8b8b9e;text-align:right;">&mdash; {{ $aiClosing }}</p>

              {{-- CTA Button (bulletproof: table-based for Outlook) --}}
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:20px;">
                <tr>
                  <td align="center">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                      <tr>
                        <td style="background-color:#3b82f6;border-radius:8px;">
                          <a href="{{ $missionUrl }}" target="_blank" style="display:inline-block;padding:14px 32px;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;letter-spacing:0.3px;">Resume Your Journey</a>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <p style="margin:0;font-size:12px;color:#8b8b9e;line-height:1.6;text-align:center;">
                Progress isn't about perfection. Even opening this email means you haven't given up.
              </p>
            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="padding:24px 36px;background-color:#fafafc;border-top:1px solid #eeeef2;border-radius:0 0 12px 12px;text-align:center;">
              <p style="margin:0 0 6px;font-size:12px;color:#8b8b9e;">&copy; {{ date('Y') }} FutureSelf &middot; India</p>
              <p style="margin:0 0 8px;font-size:11px;">
                <a href="{{ rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://futureself.in')), '/') }}/missions?settings=1" target="_blank" style="color:#6c3ce0;text-decoration:underline;">Manage email preferences</a>
              </p>
              <p style="margin:0;font-size:10px;color:#c0c0ce;line-height:1.5;">
                You received this because you have a FutureSelf account.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
