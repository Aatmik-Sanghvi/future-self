Good morning, {{ $user->name }}!

Here is your daily mission to help you take another step toward your goals.

----------------------------------------
TODAY'S MISSION
----------------------------------------

{{ $mission->title }}

@if($mission->category)
Category: {{ $mission->category }}
@endif
@if($mission->estimated_minutes)
Estimated Time: {{ $mission->estimated_minutes }} minutes
@endif
@if($mission->difficulty)
Difficulty: {{ ucfirst($mission->difficulty) }}
@endif

{{ $mission->description }}

@if($mission->future_self_note)
Note from Future You:
"{{ $mission->future_self_note }}"
@endif

----------------------------------------

Open your mission here:
{{ $missionUrl }}

A gentle check-in is scheduled for {{ $reminderTime }} if you would like to revisit this today.

----------------------------------------

FutureSelf · India
© {{ date('Y') }} FutureSelf. All rights reserved.

Manage your email preferences: {{ $preferencesUrl }}

