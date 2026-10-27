Hello {{ $contact->name }},

{{ $replyMessage }}

Warm regards,
{{ $adminName }}
FutureSelf Team ({{ $frontendUrl }})

---
Your Original Inquiry ({{ $contact->created_at ? $contact->created_at->format('M d, Y') : 'Recent' }}):
Subject: {{ $contact->subject }}

"{{ $contact->message }}"
---

You received this message in response to your inquiry on FutureSelf.
To reply, simply respond directly to this email.
