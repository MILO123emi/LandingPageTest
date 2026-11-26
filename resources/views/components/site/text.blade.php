@props(['value' => ''])
@foreach (\App\Support\PendingText::lines(is_string($value) ? $value : '') as $line)@if (! $loop->first)<br>@endif @foreach ($line as $segment)@if ($segment['pending'])<span class="pendiente">{{ $segment['text'] }}</span>@else{{ $segment['text'] }}@endif @endforeach @endforeach
