<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Your Daily Mission — FutureSelf</title>
  <!--[if mso]>
  <noscript>
    <xml>
      <o:OfficeDocumentSettings>
        <o:PixelsPerInch>96</o:PixelsPerInch>
      </o:OfficeDocumentSettings>
    </xml>
  </noscript>
  <![endif]-->
  <style>
    body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
    body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
  </style>
</head>
<body style="margin: 0; padding: 0; width: 100% !important; background-color: #0b0b14; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #f1f5f9;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="center" style="background-color: #0b0b14; table-layout: fixed;">
    <tr>
      <td align="center" style="padding: 32px 16px 40px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #121220; max-width: 580px; width: 100%; border-radius: 16px; border: 1px solid #2d2b4a; overflow: hidden; border-spacing: 0;">
          <!-- Header -->
          <tr>
            <td align="center" style="padding: 36px 32px 24px; background-color: #1a1532; border-bottom: 1px solid #2d2b4a;">
              <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                <tr>
                  <td align="center" style="background-color: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.4); border-radius: 999px; padding: 6px 16px; color: #d8b4fe; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                    ✨ FutureSelf Daily Blueprint
                  </td>
                </tr>
              </table>
              <h1 style="font-size: 24px; font-weight: 800; color: #ffffff; margin: 16px 0 8px; line-height: 1.3;">Good morning, {{ $user->name }}!</h1>
              <p style="font-size: 14px; color: #94a3b8; margin: 0; line-height: 1.5;">Your future self has prepared today's micro-mission to keep you on track towards your goals.</p>
            </td>
          </tr>

          <!-- Mission Content Card -->
          <tr>
            <td style="padding: 28px 32px 24px;">
              <div style="background-color: #1a1a2e; border: 1px solid #3b3663; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
                <div style="margin-bottom: 14px;">
                  @if($mission->category)
                    <span style="display: inline-block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 6px; margin-right: 6px; background-color: rgba(255, 255, 255, 0.08); color: #e2e8f0;">{{ $mission->category }}</span>
                  @endif
                  @if($mission->estimated_minutes)
                    <span style="display: inline-block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 6px; margin-right: 6px; background-color: rgba(249, 115, 22, 0.15); color: #fdba74; border: 1px solid rgba(249, 115, 22, 0.3);">⏱️ {{ $mission->estimated_minutes }} mins</span>
                  @endif
                  @if($mission->difficulty)
                    <span style="display: inline-block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 6px; background-color: rgba(168, 85, 247, 0.15); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.3);">{{ strtoupper($mission->difficulty) }}</span>
                  @endif
                </div>

                <h2 style="font-size: 18px; font-weight: 700; color: #ffffff; margin: 0 0 10px; line-height: 1.4;">{{ $mission->title }}</h2>
                <p style="font-size: 14px; line-height: 1.6; color: #cbd5e1; margin: 0 0 16px;">{{ $mission->description }}</p>

                @if($mission->future_self_note)
                  <div style="background-color: rgba(168, 85, 247, 0.08); border-left: 3px solid #a855f7; padding: 12px 16px; border-radius: 0 8px 8px 0; margin-top: 12px;">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #c084fc; letter-spacing: 0.5px; margin-bottom: 4px;">Note from Future You:</div>
                    <p style="font-size: 13px; font-style: italic; color: #f1f5f9; line-height: 1.5; margin: 0;">"{{ $mission->future_self_note }}"</p>
                  </div>
                @endif
              </div>

              <!-- Call to Action Button -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 24px;">
                <tr>
                  <td align="center">
                    <a href="{{ $missionUrl }}" target="_blank" style="display: inline-block; padding: 14px 32px; background-color: #9333ea; color: #ffffff !important; font-size: 15px; font-weight: 700; text-decoration: none; border-radius: 12px; text-align: center;">
                      Complete Mission on FutureSelf &rarr;
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Reminder Info -->
              <div style="background-color: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.12); border-radius: 10px; padding: 14px 18px; font-size: 12px; color: #94a3b8; line-height: 1.5; text-align: center;">
                <strong>Complete before End of Day:</strong> If this task remains pending, a reminder will be sent to you at <strong>{{ $reminderTime }}</strong>. You can customize your reminder time anytime on your <a href="{{ $missionUrl }}" target="_blank" style="color: #c084fc; text-decoration: underline;">Missions Page</a>.
              </div>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="padding: 24px 32px 32px; font-size: 12px; color: #64748b; border-top: 1px solid #1e1b38; background-color: #0e0e1a;">
              <p style="margin: 0 0 6px;">Connect with who you want to become &bull; <a href="{{ $missionUrl }}" target="_blank" style="color: #94a3b8; text-decoration: underline;">View Daily Missions</a></p>
              <p style="margin: 0 0 6px;">&copy; {{ date('Y') }} FutureSelf. All rights reserved.</p>
              <p style="margin: 0 0 6px; color: #64748b;">FutureSelf &middot; India</p>
              <p style="margin: 0;"><a href="{{ rtrim(env('FRONTEND_URL', 'https://futureself.in'), '/') }}/missions?settings=1" target="_blank" style="color: #94a3b8; text-decoration: underline;">Manage email preferences</a></p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
