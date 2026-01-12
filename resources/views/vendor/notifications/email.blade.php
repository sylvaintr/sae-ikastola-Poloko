@component('mail::message')
    @component('mail::panel')
        <strong style="font-size:18px; color:#111827;">
            {{ $greeting ?: __('mail.greeting') }}
        </strong>
    @endcomponent

    @foreach ($introLines as $line)
        {{ $line }}
    @endforeach

    @isset($actionText)
        @component('mail::button', ['url' => $actionUrl, 'color' => 'primary'])
            {{ $actionText }}
        @endcomponent
    @endisset

    @foreach ($outroLines as $line)
        {{ $line }}
    @endforeach

    @if (!empty($salutation))
        {{ $salutation }}
    @else
        {{ __('mail.salutation') }}
        {{ config('app.name') }}
    @endif

    @isset($actionText)
        @slot('subcopy')
            {{ __('mail.trouble_clicking', ['actionText' => $actionText]) }}
            {{ $actionUrl }}
        @endslot
    @endisset
@endcomponent
