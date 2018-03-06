@component('old-extended::document')

    {{-- @slot('title') Relatórios @endslot --}}

    {{-- Relatórios Disponíveis --}}

    <div class="row mt-5">

        <div class="col">

            @foreach(['xls', 'pdf', 'csv'] as $format)

                <a href="{{ route('report-collection.download', $format) }}"
                   class="btn btn-lg btn-info">
                   Download {{ strtoupper($format) }}
                </a>

            @endforeach

        </div>

    </div>

@endcomponent