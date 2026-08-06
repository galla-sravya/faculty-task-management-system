@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'text-danger small mt-1 login-error-msg']) }}>
        @foreach ((array) $messages as $message)
            <div><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
        @endforeach
    </div>
@endif
