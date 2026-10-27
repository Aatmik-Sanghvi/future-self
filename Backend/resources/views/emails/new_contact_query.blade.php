<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Contact Query — FutureSelf</title>
</head>
<body style="margin:0;padding:0;background-color:#0f0c1a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#ffffff;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#0f0c1a;">
    <tr>
      <td align="center" style="padding:40px 16px;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;background-color:#1a1030;border-radius:16px;border:1px solid rgba(167,139,250,0.25);overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,0.6);">
          
          {{-- Accent Top Bar --}}
          <tr>
            <td style="height:4px;background:linear-gradient(90deg, #7c3aed, #a78bfa, #4f46e5);font-size:0;line-height:0;">&nbsp;</td>
          </tr>

          {{-- Header --}}
          <tr>
            <td style="padding:28px 36px 20px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.08);">
              <div style="display:inline-block;padding:6px 14px;background:rgba(124,58,237,0.2);border:1px solid rgba(167,139,250,0.3);border-radius:20px;font-size:12px;font-weight:600;color:#c4b5fd;letter-spacing:0.5px;text-transform:uppercase;margin-bottom:12px;">
                📩 New Contact Query
              </div>
              <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">
                {{ $contact->subject }}
              </h1>
              <p style="margin:6px 0 0;font-size:13px;color:rgba(255,255,255,0.6);">
                Category: <strong style="color:#a78bfa;">{{ ucfirst($contact->category ?? 'General') }}</strong>
              </p>
            </td>
          </tr>

          {{-- Sender Details Card --}}
          <tr>
            <td style="padding:24px 36px 16px;">
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:16px 20px;">
                <tr>
                  <td style="padding:4px 0;font-size:13px;color:rgba(255,255,255,0.6);width:90px;">From:</td>
                  <td style="padding:4px 0;font-size:14px;color:#ffffff;font-weight:600;">{{ $contact->name }}</td>
                </tr>
                <tr>
                  <td style="padding:4px 0;font-size:13px;color:rgba(255,255,255,0.6);">Email:</td>
                  <td style="padding:4px 0;font-size:14px;color:#a78bfa;">
                    <a href="mailto:{{ $contact->email }}?subject=Re:%20{{ urlencode($contact->subject) }}" style="color:#a78bfa;text-decoration:none;font-weight:500;">
                      {{ $contact->email }}
                    </a>
                  </td>
                </tr>
                <tr>
                  <td style="padding:4px 0;font-size:13px;color:rgba(255,255,255,0.6);">Received:</td>
                  <td style="padding:4px 0;font-size:13px;color:rgba(255,255,255,0.8);">
                    {{ $contact->created_at ? $contact->created_at->format('M d, Y — h:i A') : 'Just now' }}
                  </td>
                </tr>
                @if($contact->user_id)
                <tr>
                  <td style="padding:4px 0;font-size:13px;color:rgba(255,255,255,0.6);">Account:</td>
                  <td style="padding:4px 0;font-size:13px;color:#34d399;font-weight:500;">
                    Registered User (ID: #{{ $contact->user_id }})
                  </td>
                </tr>
                @else
                <tr>
                  <td style="padding:4px 0;font-size:13px;color:rgba(255,255,255,0.6);">Account:</td>
                  <td style="padding:4px 0;font-size:13px;color:rgba(255,255,255,0.5);">Guest User</td>
                </tr>
                @endif
              </table>
            </td>
          </tr>

          {{-- Message Body --}}
          <tr>
            <td style="padding:8px 36px 28px;">
              <h3 style="margin:0 0 10px;font-size:14px;font-weight:600;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.5px;">Message Content:</h3>
              <div style="background:rgba(15,12,26,0.6);border:1px solid rgba(167,139,250,0.2);border-radius:12px;padding:20px;font-size:15px;line-height:1.7;color:#f1f5f9;white-space:pre-wrap;word-break:break-word;">
{{ $contact->message }}
              </div>
            </td>
          </tr>

          {{-- Action Buttons --}}
          <tr>
            <td style="padding:0 36px 36px;text-align:center;">
              <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto;">
                <tr>
                  <td style="padding-right:12px;">
                    <a href="mailto:{{ $contact->email }}?subject=Re:%20{{ urlencode($contact->subject) }}" style="display:inline-block;padding:12px 24px;background:linear-gradient(135deg, #7c3aed, #4f46e5);color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;border-radius:10px;box-shadow:0 4px 14px rgba(124,58,237,0.4);">
                      ✉️ Reply Directly
                    </a>
                  </td>
                  <td>
                    <a href="{{ $adminContactsUrl }}" style="display:inline-block;padding:12px 24px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;border-radius:10px;">
                      View in Dashboard ↗
                    </a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="padding:16px 36px;background:rgba(15,12,26,0.5);border-top:1px solid rgba(255,255,255,0.06);text-align:center;font-size:12px;color:rgba(255,255,255,0.4);">
              FutureSelf Automated Contact Notification • <a href="{{ $adminContactsUrl }}" style="color:#a78bfa;text-decoration:none;">Admin Panel</a>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
