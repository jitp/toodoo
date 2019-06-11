@component('mail::message')
# Welcome to TOODOO!

Hello {{ $participant->name }}!

{{ $inviting->name }} has invited you to collaborate with him in "{{ $todoList->name }}" todo list. If you agreed to, you can click the button

@component('mail::button', ['url' => env('FRONTEND_URL', '') . '/todo-list/' . $participant->participant->hash])
Collaborate!
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
