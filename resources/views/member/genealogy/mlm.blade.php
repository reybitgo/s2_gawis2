@extends('layouts.admin')

@section('title', 'MLM Genealogy')
@section('page-title', 'MLM Genealogy')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-4">MLM Genealogy</h2>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-muted small">Total Downlines</div>
                        <div class="fs-4 fw-semibold">{{ $stats['total_downlines'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-muted small">Active Downlines</div>
                        <div class="fs-4 fw-semibold text-success">{{ $stats['active_downlines'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">My Downline Tree</h5>
                <div class="w-25">
                    <input type="text" id="genealogy-search" class="form-control form-control-sm" placeholder="Search by name or username...">
                </div>
            </div>
                @if (!empty($tree))
                    <ul class="genealogy-tree">
                        @foreach ($tree as $member)
                            <x-genealogy-node :member="$member" :earnings-label="'MLM Commission'" />
                        @endforeach
                    </ul>
                @else
                    <p class="text-center text-muted">You do not have any downlines yet.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
