@props(['number', 'title', 'hint' => null])
<section {{ $attributes->class(['create-form-section']) }} aria-labelledby="create-section-{{ $number }}">
    <div class="create-section-heading"><h2 id="create-section-{{ $number }}"><span class="form-step-number">{{ $number }}</span>{{ $title }}</h2>@if($hint)<span class="form-section-hint">{{ $hint }}</span>@endif</div>
    {{ $slot }}
</section>
