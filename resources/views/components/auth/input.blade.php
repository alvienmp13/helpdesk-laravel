@props(['name', 'label', 'type' => 'text', 'autocomplete' => null, 'hint' => null, 'autofocus' => false, 'maxlength' => null])
<div class="auth-field">
    <label for="{{ $name }}">{{ $label }}</label>
    <div class="auth-input-wrap">
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if($type !== 'password') value="{{ old($name) }}" @endif @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif @if($maxlength) maxlength="{{ $maxlength }}" @endif @if($autofocus && $errors->isEmpty()) autofocus @endif required aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}" @if($hint || $errors->has($name)) aria-describedby="{{ $hint ? $name.'-hint' : '' }} {{ $errors->has($name) ? $name.'-error' : '' }}" @endif>
        @if($type === 'password')<button type="button" class="auth-password-toggle" data-password-toggle="{{ $name }}" aria-controls="{{ $name }}" aria-pressed="false" aria-label="Tampilkan {{ strtolower($label) }}">Tampilkan</button>@endif
    </div>
    @if($hint)<p id="{{ $name }}-hint" class="auth-field-hint">{{ $hint }}</p>@endif
    @error($name)<p id="{{ $name }}-error" class="auth-field-error">{{ $message }}</p>@enderror
</div>
