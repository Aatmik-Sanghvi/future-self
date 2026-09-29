Hi {{ $user->name }},

Your daily mission is still pending. A few minutes of focused effort keeps your progress alive.

---
PENDING MISSION
---

{{ $mission->title }}

@if($mission->estimated_minutes)
Estimated Time: {{ $mission->estimated_minutes }} minutes
@endif

{{ $mission->description }}

@if($streak > 0)
Your current streak: {{ $streak }} days
@endif

"Real growth happens in genuine effort, not just checking off a box. Your future self believes in you."

---

Complete and reflect here:
{{ $missionUrl }}

---

(c) {{ date('Y') }} FutureSelf, India

Manage your email preferences: {{ rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://futureself.in')), '/') }}/missions?settings=1

You received this because you have a FutureSelf account.
