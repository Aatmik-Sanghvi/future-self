<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Your Mission Reminder — FutureSelf</title>
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
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #121220; max-width: 580px; width: 100%; border-radius: 16px; border: 1px solid #3b2a5a; overflow: hidden; border-spacing: 0;">
          <!-- Header -->
          <tr>
            <td align="center" style="padding: 36px 32px 24px; background-color: #1e132c; border-bottom: 1px solid #3b2a5a;">
              <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                <tr>
                  <td align="center" style="background-color: rgba(249, 115, 22, 0.18); border: 1px solid rgba(249, 115, 22, 0.45); border-radius: 999px; padding: 6px 16px; color: #fed7aa; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                    @if($streak > 0)
                      {{ $streak }} Day Streak
                    @else
                      Evening Check-in
                    @endif
                  </td>
                </tr>
              </table>
              <h1 style="font-size: 24px; font-weight: 800; color: #ffffff; margin: 16px 0 8px; line-height: 1.3;">Your mission is ready, {{ $user->name }}</h1>
              <p style="font-size: 14px; color: #cbd5e1; margin: 0; line-height: 1.5;">A few minutes of genuine effort today keeps your momentum alive.</p>
            </td>
          </tr>

          <!-- Mission Content Card -->
          <tr>
            <td style="padding: 28px 32px 24px;">
              <div style="background-color: #1a1a2e; border: 1px solid #4a3366; border-radius: 14px; padding: 24px; margin-bottom: 24px;">
                <div style="margin-bottom: 14px;">
                  <span style="display: inline-block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 6px; margin-right: 6px; background-color: rgba(249, 115, 22, 0.15); color: #fdba74; border: 1px solid rgba(249, 115, 22, 0.3);">Today's Focus</span>
                  @if($mission->estimated_minutes)
                    <span style="display: inline-block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 6px; background-color: rgba(255, 255, 255, 0.08); color: #e2e8f0;">{{ $mission->estimated_minutes }} mins</span>
                  @endif
                </div>

                <h2 style="font-size: 18px; font-weight: 700; color: #ffffff; margin: 0 0 10px; line-height: 1.4;">{{ $mission->title }}</h2>
                <p style="font-size: 14px; line-height: 1.6; color: #cbd5e1; margin: 0 0 16px;">{{ $mission->description }}</p>

                <div style="background-color: rgba(239, 68, 68, 0.08); border-left: 3px solid #f87171; padding: 12px 16px; border-radius: 0 8px 8px 0; margin-top: 12px;">
                  <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #fca5a5; letter-spacing: 0.5px; margin-bottom: 4px;">Reflection:</div>
                  <p style="font-size: 13px; font-style: italic; color: #fecdd3; line-height: 1.5; margin: 0;">"Real growth happens through consistent small steps. Take a moment when you are ready."</p>
                </div>
              </div>

              <!-- Bulletproof Call to Action Button -->
              <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: 28px auto;">
                <tr>
                  <td align="center" bgcolor="#ea580c" style="border-radius: 12px; background-color: #ea580c;">
                    <!--[if mso]>
                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $missionUrl }}" style="height:48px;v-text-anchor:middle;width:240px;" arcsize="25%" stroke="f" fillcolor="#ea580c">
                    <w:anchorlock/>
                    <center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;">Open Today's Mission</center>
                    </v:roundrect>
                    <![endif]-->
                    <!--[if !mso]><!-->
                    <a href="{{ $missionUrl }}" target="_blank" style="display: inline-block; background-color: #ea580c; color: #ffffff !important; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 15px; font-weight: 700; line-height: 48px; text-align: center; text-decoration: none; width: 240px; border-radius: 12px; -webkit-text-size-adjust: none; mso-hide: all;">
                      Open Today's Mission
                    </a>
                    <!--<![endif]-->
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="padding: 24px 32px 32px; font-size: 12px; color: #64748b; border-top: 1px solid #1e1b38; background-color: #0e0e1a;">
              <p style="margin: 0 0 6px;">Connect with who you want to become &bull; FutureSelf Daily Growth</p>
              <p style="margin: 0 0 6px;">&copy; {{ date('Y') }} FutureSelf. All rights reserved.</p>
              <p style="margin: 0 0 6px; color: #64748b;">FutureSelf &middot; India</p>
              <p style="margin: 0;"><a href="{{ $preferencesUrl }}" target="_blank" style="color: #94a3b8; text-decoration: underline;">Manage email preferences</a></p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
