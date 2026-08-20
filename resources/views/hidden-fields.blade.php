@php
    $prefix = $prefix ?? '';
    $skip = $skip ?? [];
    $skipArrays = $skipArrays ?? false;
    $only = $only ?? null;
@endphp
@foreach ($fields as $field => $valor)
    @php
        $name = $prefix !== '' ? $prefix . '[' . $field . ']' : $field;
        $isRoot = $prefix === '';
    @endphp
    @if ($isRoot && in_array($field, $skip, true))
        {{-- Keep live search fields out of the hidden set. --}}
    @elseif ($isRoot && is_array($only) && !in_array($field, $only, true))
        {{-- Extra-search / live fields are submitted by the visible inputs. --}}
    @elseif (is_array($valor))
        @if (!($skipArrays && $isRoot))
            @include('Simplegrid::hidden-fields', [
                'fields' => $valor,
                'prefix' => $name,
                'skip' => [],
                'skipArrays' => false,
                'only' => null,
            ])
        @endif
    @elseif (!is_object($valor))
        <input type="hidden" name="{{ $name }}" value="{{ $valor }}">
    @endif
@endforeach
