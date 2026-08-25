<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Don't break your streak! Today's mission is waiting</title>
  <style>
    body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
    body { margin: 0; padding: 0; width: 100% !important; background-color: #0b0b14; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #f1f5f9; }
    .wrapper { width: 100%; table-layout: fixed; background-color: #0b0b14; padding-bottom: 40px; }
    .main-table { background-color: #121220; margin: 0 auto; width: 100%; max-width: 600px; border-spacing: 0; font-family: sans-serif; color: #f1f5f9; border-radius: 16px; border: 1px solid #3b2a5a; overflow: hidden; }
    .header-td { padding: 36px 32px 24px; text-align: center; background: linear-gradient(145deg, #2b1536 0%, #121220 100%); }
    .streak-badge { display: inline-block; padding: 6px 14px; border-radius: 999px; background-color: rgba(249, 115, 22, 0.18); border: 1px solid rgba(249, 115, 22, 0.45); color: #fed7aa; font-size: 13px; font-weight: 700; margin-bottom: 12px; }
    .title-h1 { font-size: 24px; font-weight: 800; color: #ffffff; margin: 0 0 8px; line-height: 1.3; }
    .subtitle-p { font-size: 14px; color: #cbd5e1; margin: 0; line-height: 1.5; }
    .content-td { padding: 24px 32px; }
    .mission-box { background-color: #1a1a2e; border: 1px solid #4a3366; border-radius: 14px; padding: 24px; margin-bottom: 24px; }
    .tag-row { margin-bottom: 14px; }
    .tag { display: inline-block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 3px 8px; border-radius: 6px; margin-right: 6px; }
    .tag-pending { background-color: rgba(249, 115, 22, 0.15); color: #fdba74; border: 1px solid rgba(249, 115, 22, 0.3); }
    .mission-title { font-size: 18px; font-weight: 700; color: #ffffff; margin: 0 0 10px; line-height: 1.4; }
    .mission-desc { font-size: 14px; line-height: 1.6; color: #cbd5e1; margin: 0 0 18px; }
    .honest-reminder { background-color: rgba(239, 68, 68, 0.08); border-left: 3px solid #f87171; padding: 12px 16px; border-radius: 0 8px 8px 0; margin-top: 12px; }
    .honest-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #fca5a5; letter-spacing: 0.5px; margin-bottom: 4px; }
    .honest-text { font-size: 13px; font-style: italic; color: #fecdd3; line-height: 1.5; margin: 0; }
    .btn-container { text-align: center; padding: 8px 0 24px; }
    .btn-cta { display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #f97316 0%, #ef4444 100%); background-color: #f97316; color: #ffffff !important; font-size: 15px; font-weight: 700; text-decoration: none; border-radius: 12px; box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4); text-align: center; }
    .footer-td { padding: 24px 32px 32px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #1e1b38; }
    .footer-td a { color: #94a3b8; text-decoration: underline; }
  </style>
</head>
<body>
  <div class="wrapper">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
      <tr>
        <td align="center" style="padding: 24px 12px;">
          <table class="main-table" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
            <!-- Header -->
            <tr>
              <td class="header-td">
                @if($streak > 0)
                  <div class="streak-badge">🔥 {{ $streak }} Day Streak Active!</div>
                @else
                  <div class="streak-badge">⏳ Evening Check-in</div>
                @endif
                <h1 class="title-h1">Don't break your momentum, {{ $user->name }}!</h1>
                <p class="subtitle-p">Your future self is checking in. Just 10-15 minutes of genuine effort today keeps your habit and transformation alive.</p>
              </td>
            </tr>

            <!-- Mission Content Card -->
            <tr>
              <td class="content-td">
                <div class="mission-box">
                  <div class="tag-row">
                    <span class="tag tag-pending">⏳ PENDING MISSION</span>
                    @if($mission->estimated_minutes)
                      <span class="tag" style="background-color: rgba(255,255,255,0.08); color:#e2e8f0;">⏱️ {{ $mission->estimated_minutes }} mins</span>
                    @endif
                  </div>

                  <h2 class="mission-title">{{ $mission->title }}</h2>
                  <p class="mission-desc">{{ $mission->description }}</p>

                  <div class="honest-reminder">
                    <div class="honest-title">Growth Reminder:</div>
                    <p class="honest-text">"Don't cheat on the person you're becoming. Real growth happens in genuine effort, not just checking off a box."</p>
                  </div>
                </div>

                <!-- Call to Action Button -->
                <div class="btn-container">
                  <a href="{{ $missionUrl }}" class="btn-cta" target="_blank">
                    ⚡ Complete & Reflect on FutureSelf →
                  </a>
                </div>
              </td>
            </tr>

            <!-- Footer -->
            <tr>
              <td class="footer-td">
                <p style="margin: 0 0 6px;">Connect with who you want to become &bull; <a href="{{ $missionUrl }}" target="_blank">FutureSelf Missions</a></p>
                <p style="margin: 0;">&copy; {{ date('Y') }} FutureSelf. All rights reserved.</p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
