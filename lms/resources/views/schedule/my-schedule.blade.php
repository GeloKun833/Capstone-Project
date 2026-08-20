@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">My Schedule</h3>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">My Schedule</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Schedule Grid - Full Page -->
            <div class="row">
                <div class="col-12">
                    @include('components.my-schedule')
                </div>
            </div>
        </div>
    </div>
@endsection 