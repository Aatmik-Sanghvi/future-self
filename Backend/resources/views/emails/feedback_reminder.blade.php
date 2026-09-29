<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="x-apple-disable-message-reformatting">
  <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
  <title>Your feedback matters — FutureSelf</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
  {{-- Preheader --}}
  <div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">
    Help shape the future of FutureSelf — takes only 2 minutes &#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;&#847;
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
              <p style="margin:6px 0 0;font-size:11px;color:#8b8b9e;text-transform:uppercase;letter-spacing:1.5px;font-weight:500;">Your Voice Matters</p>
            </td>
          </tr>

          {{-- Divider --}}
          <tr>
            <td style="padding:16px 36px 0;"><div style="border-top:1px solid #eeeef2;"></div></td>
          </tr>

          {{-- Content --}}
          <tr>
            <td style="padding:24px 36px 32px;">
              <p style="margin:0 0 6px;font-size:16px;color:#1a1a2e;font-weight:600;">Hey {{ $user->name }},</p>
              <p style="margin:0 0 20px;font-size:14px;color:#555568;line-height:1.6;">We'd love to hear your honest thoughts about FutureSelf. It only takes about 2 minutes.</p>

              <h2 style="margin:0 0 10px;font-size:16px;font-weight:600;color:#1a1a2e;">Why does this matter?</h2>
              <p style="margin:0 0 12px;font-size:14px;color:#555568;line-height:1.6;">
                We built FutureSelf to help people become who they dream of being. But we can only make it truly meaningful with your honest feedback.
              </p>
              <p style="margin:0 0 20px;font-size:14px;color:#555568;line-height:1.6;">
                When you share your experience, you're not just helping us — you're helping every other user who will use FutureSelf after you. Your voice shapes what we build next.
              </p>

              {{-- Impact Note --}}
              <div style="background-color:#f0faf6;border-left:3px solid #059669;border-radius:0 8px 8px 0;padding:16px 18px;margin-bottom:24px;">
                <p style="margin:0;font-size:13px;color:#065f46;line-height:1.5;">Every piece of feedback helps us understand what's working and what's not. You're co-creating FutureSelf with us.</p>
              </div>

              {{-- Quick Info --}}
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:24px;">
                <tr>
                  <td style="padding:6px 0;font-size:13px;color:#555568;">Takes only <strong style="color:#1a1a2e;">~2 minutes</strong></td>
                </tr>
                <tr>
                  <td style="padding:6px 0;font-size:13px;color:#555568;">Earn <strong style="color:#1a1a2e;">5 bonus chats</strong> as a thank you</td>
                </tr>
                <tr>
                  <td style="padding:6px 0;font-size:13px;color:#555568;">100% used only to improve FutureSelf</td>
                </tr>
              </table>

              {{-- CTA Button --}}
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:20px;">
                <tr>
                  <td align="center">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                      <tr>
                        <td style="background-color:#059669;border-radius:8px;">
                          <a href="{{ $feedbackUrl }}" target="_blank" style="display:inline-block;padding:14px 32px;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;letter-spacing:0.3px;">Share Your Feedback</a>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <p style="margin:0;font-size:12px;color:#8b8b9e;line-height:1.6;text-align:center;">
                We read every single response. Your honesty helps us grow.
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
