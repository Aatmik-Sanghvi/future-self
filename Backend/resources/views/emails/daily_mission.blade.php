<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Daily Mission from Future You</title>
  <style>
    body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
    body { margin: 0; padding: 0; width: 100% !important; background-color: #0b0b14; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #f1f5f9; }
    .wrapper { width: 100%; table-layout: fixed; background-color: #0b0b14; padding-bottom: 40px; }
    .main-table { background-color: #121220; margin: 0 auto; width: 100%; max-width: 600px; border-spacing: 0; font-family: sans-serif; color: #f1f5f9; border-radius: 16px; border: 1px solid #2d2b4a; overflow: hidden; }
    .header-td { padding: 36px 32px 24px; text-align: center; background: linear-gradient(145deg, #1c1538 0%, #121220 100%); }
    .logo-badge { display: inline-block; padding: 6px 14px; border-radius: 999px; background-color: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.4); color: #d8b4fe; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 12px; }
    .title-h1 { font-size: 24px; font-weight: 800; color: #ffffff; margin: 0 0 8px; line-height: 1.3; }
    .subtitle-p { font-size: 14px; color: #94a3b8; margin: 0; line-height: 1.5; }
    .content-td { padding: 24px 32px; }
    .mission-box { background-color: #1a1a2e; border: 1px solid #3b3663; border-radius: 14px; padding: 24px; margin-bottom: 24px; }
    .tag-row { margin-bottom: 14px; }
    .tag { display: inline-block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 3px 8px; border-radius: 6px; margin-right: 6px; }
    .tag-cat { background-color: rgba(255, 255, 255, 0.08); color: #e2e8f0; }
    .tag-time { background-color: rgba(249, 115, 22, 0.15); color: #fdba74; border: 1px solid rgba(249, 115, 22, 0.3); }
    .tag-diff { background-color: rgba(168, 85, 247, 0.15); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.3); }
    .mission-title { font-size: 18px; font-weight: 700; color: #ffffff; margin: 0 0 10px; line-height: 1.4; }
    .mission-desc { font-size: 14px; line-height: 1.6; color: #cbd5e1; margin: 0 0 18px; }
    .note-box { background-color: rgba(168, 85, 247, 0.08); border-left: 3px solid #a855f7; padding: 12px 16px; border-radius: 0 8px 8px 0; margin-top: 12px; }
    .note-author { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #c084fc; letter-spacing: 0.5px; margin-bottom: 4px; }
    .note-text { font-size: 13px; font-style: italic; color: #f1f5f9; line-height: 1.5; margin: 0; }
    .btn-container { text-align: center; padding: 8px 0 24px; }
    .btn-cta { display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #f97316 0%, #a855f7 100%); background-color: #a855f7; color: #ffffff !important; font-size: 15px; font-weight: 700; text-decoration: none; border-radius: 12px; box-shadow: 0 6px 20px rgba(168, 85, 247, 0.4); text-align: center; }
    .reminder-notice { background-color: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 14px 18px; font-size: 12px; color: #94a3b8; line-height: 1.5; text-align: center; margin-bottom: 12px; }
    .reminder-notice a { color: #c084fc; text-decoration: underline; }
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
                <div class="logo-badge">✨ FutureSelf Daily Blueprint</div>
                <h1 class="title-h1">Good morning, {{ $user->name }}!</h1>
                <p class="subtitle-p">Your future self has prepared today's micro-mission to keep you on track towards your goals.</p>
              </td>
            </tr>

            <!-- Mission Content Card -->
            <tr>
              <td class="content-td">
                <div class="mission-box">
                  <div class="tag-row">
                    @if($mission->category)
                      <span class="tag tag-cat">{{ $mission->category }}</span>
                    @endif
                    @if($mission->estimated_minutes)
                      <span class="tag tag-time">⏱️ {{ $mission->estimated_minutes }} mins</span>
                    @endif
                    @if($mission->difficulty)
                      <span class="tag tag-diff">{{ strtoupper($mission->difficulty) }}</span>
                    @endif
                  </div>

                  <h2 class="mission-title">{{ $mission->title }}</h2>
                  <p class="mission-desc">{{ $mission->description }}</p>

                  @if($mission->future_self_note)
                    <div class="note-box">
                      <div class="note-author">Note from Future You:</div>
                      <p class="note-text">"{{ $mission->future_self_note }}"</p>
                    </div>
                  @endif
                </div>

                <!-- Call to Action Button -->
                <div class="btn-container">
                  <a href="{{ $missionUrl }}" class="btn-cta" target="_blank">
                    🎯 Complete Mission on FutureSelf →
                  </a>
                </div>

                <!-- Reminder Info -->
                <div class="reminder-notice">
                  ⏳ <strong>Complete before End of Day:</strong> If this task remains pending, a reminder will be sent to you at <strong>{{ $reminderTime }}</strong>. You can customize your reminder time anytime on your <a href="{{ $missionUrl }}" target="_blank">Missions Page</a>.
                </div>
              </td>
            </tr>

            <!-- Footer -->
            <tr>
              <td class="footer-td">
                <p style="margin: 0 0 6px;">Connect with who you want to become &bull; <a href="{{ $missionUrl }}" target="_blank">View Daily Missions</a></p>
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
