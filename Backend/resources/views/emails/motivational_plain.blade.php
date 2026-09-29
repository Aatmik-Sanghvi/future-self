{{ $aiGreeting }}

Your future self wanted to check in with you.

---
A NOTE FROM FUTURE YOU
---

{{ $aiBody }}

ONE SMALL STEP YOU CAN TAKE TODAY:
{{ $aiActionableStep }}

— {{ $aiClosing }}

---

Resume your journey:
{{ $missionUrl }}

Progress isn't about perfection. Even opening this email means you haven't given up.

---

(c) {{ date('Y') }} FutureSelf, India

Manage your email preferences: {{ rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://futureself.in')), '/') }}/missions?settings=1

You received this because you have a FutureSelf account.
