@component('mail::message')

Hello {{ $participant->name }},

We want to inform you that "{{ $todoList->name }}" todo list has been deleted by {{ $deleter->name }}.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
