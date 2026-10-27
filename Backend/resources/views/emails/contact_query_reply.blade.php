<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $replySubject }}</title>
</head>
<body style="margin:0;padding:0;background-color:#0a0914;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#f1f5f9;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#0a0914;">
    <tr>
      <td align="center" style="padding:40px 16px;">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;background-color:#141224;border-radius:18px;border:1px solid rgba(167,139,250,0.22);overflow:hidden;box-shadow:0 24px 60px rgba(0,0,0,0.65);">
          
          {{-- Accent Top Gradient Bar --}}
          <tr>
            <td style="height:4px;background:linear-gradient(90deg, #7c3aed, #06b6d4, #10b981);font-size:0;line-height:0;">&nbsp;</td>
          </tr>

          {{-- Header --}}
          <tr>
            <td style="padding:32px 36px 20px;border-bottom:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.015);">
              <div style="display:inline-block;padding:5px 14px;background:rgba(124,58,237,0.18);border:1px solid rgba(167,139,250,0.3);border-radius:20px;font-size:11px;font-weight:700;color:#c4b5fd;letter-spacing:0.8px;text-transform:uppercase;margin-bottom:12px;">
                FutureSelf Support
              </div>
              <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;line-height:1.35;letter-spacing:-0.4px;">
                {{ $replySubject }}
              </h1>
            </td>
          </tr>

          {{-- Main Reply Body --}}
          <tr>
            <td style="padding:28px 36px 20px;">
              <p style="margin:0 0 16px;font-size:16px;font-weight:600;color:#ffffff;">
                Hello {{ $contact->name }},
              </p>
              
              <div style="font-size:15px;line-height:1.75;color:#e2e8f0;white-space:pre-wrap;word-break:break-word;margin-bottom:28px;">
{{ $replyMessage }}
              </div>

              <div style="margin-top:24px;padding-top:18px;border-top:1px solid rgba(255,255,255,0.08);font-size:14px;color:rgba(255,255,255,0.7);line-height:1.6;">
                Warm regards,<br>
                <strong style="color:#ffffff;">{{ $adminName }}</strong><br>
                <span style="color:#a78bfa;font-size:13px;">FutureSelf Team</span>
              </div>
            </td>
          </tr>

          {{-- Action Button --}}
          <tr>
            <td style="padding:0 36px 28px;">
              <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                <tr>
                  <td align="left">
                    <a href="{{ $frontendUrl }}" style="display:inline-block;padding:12px 26px;background:linear-gradient(135deg, #7c3aed, #4f46e5);color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;border-radius:10px;box-shadow:0 4px 16px rgba(124,58,237,0.35);">
                      Visit FutureSelf &rarr;
                    </a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Quoted Original Query Box --}}
          <tr>
            <td style="padding:0 36px 32px;">
              <div style="background:rgba(10,9,20,0.6);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:20px 22px;">
                <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:10px;display:flex;align-items:center;">
                  Your Original Inquiry ({{ $contact->created_at ? $contact->created_at->format('M d, Y') : 'Recent' }}):
                </div>
                <div style="font-size:13px;font-weight:600;color:#c4b5fd;margin-bottom:6px;">
                  Subject: {{ $contact->subject }}
                </div>
                <div style="font-size:13px;line-height:1.65;color:#94a3b8;white-space:pre-wrap;word-break:break-word;font-style:italic;">
"{{ $contact->message }}"
                </div>
              </div>
            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="padding:20px 36px;background:rgba(10,9,20,0.85);border-top:1px solid rgba(255,255,255,0.06);text-align:center;font-size:12px;color:rgba(255,255,255,0.45);line-height:1.6;">
              You received this message in response to your inquiry on <a href="{{ $frontendUrl }}" style="color:#a78bfa;text-decoration:none;">FutureSelf</a>.<br>
              Have follow-up questions? Simply reply directly to this email.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
