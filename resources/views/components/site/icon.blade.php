@props(['name' => null, 'stroke' => '1.8'])
{{-- Paths come from the fixed list in App\Support\Icons, never from user input. --}}
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}>{!! \App\Support\Icons::paths(is_string($name) ? $name : null) ?? \App\Support\Icons::paths('basket') !!}</svg>
