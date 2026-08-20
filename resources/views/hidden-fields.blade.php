@php
    $prefix = $prefix ?? '';
    $skip = $skip ?? [];
    $skipArrays = $skipArrays ?? false;
@endphp
@foreach ($fields as $field => $valor)
    @php
        $name = $prefix !== '' ? $prefix . '[' . $field . ']' : $field;
        $isRoot = $prefix === '';
    @endphp
    @if ($isRoot && in_array($field, $skip, true))
        {{-- Keep live search fields out of the hidden set. --}}
    @elseif (is_array($valor))
        @if (!($skipArrays && $isRoot))
            @include('Simplegrid::hidden-fields', [
                'fields' => $valor,
                'prefix' => $name,
                'skip' => [],
                'skipArrays' => false,
            ])
        @endif
    @elseif (!is_object($valor))
        <input type="hidden" name="{{ $name }}" value="{{ $valor }}">
    @endif
@endforeach
