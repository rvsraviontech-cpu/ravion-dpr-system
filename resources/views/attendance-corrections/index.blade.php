@extends('layouts.app')

@section('content')

<x-rds.page-header
    title="Attendance Corrections"
    subtitle="Manage Attendance Change Requests, approvals, and attendance adjustments."
>
    <x-slot:actions>
        @if(auth()->user()->hasPermission('attendance_corrections.create'))
            <x-rds.button
                href="{{ route('attendance-corrections.create') }}"
                variant="primary"
            >
                New Attendance Correction
            </x-rds.button>
        @endif
    </x-slot:actions>
</x-rds.page-header>

<x-rds.alert />

@include('attendance-corrections.partials.filters')
@include('attendance-corrections.partials.statistics')

<style>
    #attendance-corrections-top-scroll {
        overflow-x: scroll;
        overflow-y: hidden;
        height: 18px;
        scrollbar-gutter: stable;
    }

    #attendance-corrections-top-scroll-width {
        height: 1px;
    }

    #attendance-corrections-table-scroll {
        max-height: 370px;
        overflow-x: auto;
        overflow-y: scroll;
        scrollbar-gutter: stable;
    }

    #attendance-corrections-top-scroll::-webkit-scrollbar,
    #attendance-corrections-table-scroll::-webkit-scrollbar {
        height: 14px;
        width: 14px;
    }

    #attendance-corrections-top-scroll::-webkit-scrollbar-track,
    #attendance-corrections-table-scroll::-webkit-scrollbar-track {
        background: #f3f4f6;
    }

    #attendance-corrections-top-scroll::-webkit-scrollbar-thumb,
    #attendance-corrections-table-scroll::-webkit-scrollbar-thumb {
        background: #9ca3af;
        border: 3px solid #f3f4f6;
        border-radius: 9999px;
    }

    #attendance-corrections-top-scroll::-webkit-scrollbar-thumb:hover,
    #attendance-corrections-table-scroll::-webkit-scrollbar-thumb:hover {
        background: #6b7280;
    }
</style>

<div class="rounded-xl border border-gray-200 bg-white shadow-sm">
    <div
        id="attendance-corrections-top-scroll"
        class="border-b border-gray-200 bg-gray-50"
        aria-label="Horizontal table navigation"
    >
        <div id="attendance-corrections-top-scroll-width"></div>
    </div>

    <div
        id="attendance-corrections-table-scroll"
        style="max-height: 370px; overflow-x: auto; overflow-y: scroll;"
    >
        @include('attendance-corrections.partials.table')
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const topScroller = document.getElementById('attendance-corrections-top-scroll');
        const topWidth = document.getElementById('attendance-corrections-top-scroll-width');
        const tableScroller = document.getElementById('attendance-corrections-table-scroll');

        if (!topScroller || !topWidth || !tableScroller) {
            return;
        }

        let fromTop = false;
        let fromTable = false;

        const updateTopRail = function () {
            const table = tableScroller.querySelector('table');

            if (!table) {
                return;
            }

            const fullWidth = Math.max(
                table.scrollWidth,
                table.offsetWidth,
                tableScroller.scrollWidth
            );

            topWidth.style.width = fullWidth + 'px';

            topScroller.style.display =
                fullWidth > tableScroller.clientWidth + 1
                    ? 'block'
                    : 'none';

            topScroller.scrollLeft = tableScroller.scrollLeft;
        };

        topScroller.addEventListener('scroll', function () {
            if (fromTable) {
                return;
            }

            fromTop = true;
            tableScroller.scrollLeft = topScroller.scrollLeft;

            requestAnimationFrame(function () {
                fromTop = false;
            });
        });

        tableScroller.addEventListener('scroll', function () {
            if (fromTop) {
                return;
            }

            fromTable = true;
            topScroller.scrollLeft = tableScroller.scrollLeft;

            requestAnimationFrame(function () {
                fromTable = false;
            });
        });

        updateTopRail();
        window.addEventListener('resize', updateTopRail);

        if (typeof ResizeObserver !== 'undefined') {
            const observer = new ResizeObserver(updateTopRail);
            observer.observe(tableScroller);

            const table = tableScroller.querySelector('table');
            if (table) {
                observer.observe(table);
            }
        }

        window.addEventListener('load', updateTopRail);
        setTimeout(updateTopRail, 150);
    });
</script>
@endpush

@endsection
