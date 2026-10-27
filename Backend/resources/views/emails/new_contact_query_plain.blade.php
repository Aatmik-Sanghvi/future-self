NEW CONTACT QUERY — FUTURE SELF
================================

Subject: {{ $contact->subject }}
Category: {{ ucfirst($contact->category ?? 'General') }}
From: {{ $contact->name }}
Email: {{ $contact->email }}
Received: {{ $contact->created_at ? $contact->created_at->format('M d, Y - h:i A') : 'Just now' }}
Account: {{ $contact->user_id ? 'Registered User (#' . $contact->user_id . ')' : 'Guest User' }}

MESSAGE:
--------------------------------
{{ $contact->message }}
--------------------------------

Reply directly to this email or visit the admin panel:
{{ $adminContactsUrl }}
