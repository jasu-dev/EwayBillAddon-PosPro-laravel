@extends('layouts.business.master')

@section('title')
    {{ __('E-Way Bills List') }}
@endsection

@section('main_content')
    <div class="erp-table-section">
        <div class="container-fluid">
            <div class="card">
                <div class="card-bodys">
                    <div class="table-header p-16 d-flex justify-content-between align-items-center">
                        <h4>{{ __('E-Way Bills') }}</h4>
                        <a href="{{ route('business.eway-bills.create') }}" class="theme-btn theme-btn-sm">
                            <i class="far fa-plus me-1"></i> {{ __('Add E-Way Bill') }}
                        </a>
                    </div>
                    <div class="table-top-form p-16-0">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="d-flex align-items-center gap-3 flex-wrap margin-lr-16 w-100">
                                <form action="{{ route('business.eway-bills.index') }}" method="GET" class="report-filter-form w-100" table="#eway-data">
                                    <div class="table-top-left d-flex gap-3 flex-wrap align-items-center">
                                        <div class="gpt-up-down-arrow position-relative">
                                            <select name="per_page" class="form-control">
                                                <option @selected(request('per_page') == 20) value="20">{{ __('Show 20') }}</option>
                                                <option @selected(request('per_page') == 50) value="50">{{ __('Show 50') }}</option>
                                                <option @selected(request('per_page') == 100) value="100">{{ __('Show 100') }}</option>
                                                <option @selected(request('per_page') == 500) value="500">{{ __('Show 500') }}</option>
                                            </select>
                                            <span></span>
                                        </div>

                                        <div class="table-search position-relative">
                                            <input type="text" name="search" class="form-control" placeholder="{{ __('Search bills...') }}" value="{{ request('search') }}">
                                            <span class="position-absolute">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M14.582 14.582L18.332 18.332" stroke="#4D4D4D"
                                                          stroke-width="1.25" stroke-linecap="round"
                                                          stroke-linejoin="round"/>
                                                    <path
                                                        d="M16.668 9.16797C16.668 5.02584 13.3101 1.66797 9.16797 1.66797C5.02584 1.66797 1.66797 5.02584 1.66797 9.16797C1.66797 13.3101 5.02584 16.668 9.16797 16.668C13.3101 16.668 16.668 13.3101 16.668 9.16797Z"
                                                        stroke="#4D4D4D" stroke-width="1.25" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </div>

                                        <div class="custom-from-to align-items-center date-filters d-none">
                                            <label>{{ __('From Date') }}</label>
                                            <input type="date" name="from_date" value="{{ request('from_date') ?? now()->format('Y-m-d') }}" class="form-control">
                                        </div>

                                        <div class="custom-from-to align-items-center date-filters d-none">
                                            <label>{{ __('To Date') }}</label>
                                            <input type="date" name="to_date" value="{{ request('to_date') ?? now()->format('Y-m-d') }}" class="form-control">
                                        </div>
                                        <div class="gpt-up-down-arrow position-relative d-print-none custom-date-filter">
                                            <select name="custom_days" class="form-control custom-days">
                                                <option value="today" {{ request()->get('custom_days') == 'today' ? 'selected' : '' }}>{{ __('Today') }}</option>
                                                <option value="yesterday" {{ request()->get('custom_days') == 'yesterday' ? 'selected' : '' }}>{{ __('Yesterday') }}</option>
                                                <option value="last_seven_days" {{ request()->get('custom_days') == 'last_seven_days' ? 'selected' : '' }}>{{ __('Last 7 Days') }}</option>
                                                <option value="last_thirty_days" {{ request()->get('custom_days') == 'last_thirty_days' ? 'selected' : '' }}>{{ __('Last 30 Days') }}</option>
                                                <option value="current_month" {{ request()->get('custom_days') == 'current_month' ? 'selected' : '' }}>{{ __('Current Month') }}</option>
                                                <option value="last_month" {{ request()->get('custom_days') == 'last_month' ? 'selected' : '' }}>{{ __('Last Month') }}</option>
                                                <option value="current_year" {{ request()->get('custom_days') == 'current_year' ? 'selected' : '' }}>{{ __('Current Year') }}</option>
                                                <option value="custom_date" {{ request()->get('custom_days') == 'custom_date' ? 'selected' : '' }}>{{ __('Custom Date') }}</option>
                                            </select>
                                            <span></span>
                                            <div class="calendar-icon">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12.6667 2.67188H3.33333C2.59695 2.67188 2 3.26883 2 4.00521V13.3385C2 14.0749 2.59695 14.6719 3.33333 14.6719H12.6667C13.403 14.6719 14 14.0749 14 13.3385V4.00521C14 3.26883 13.403 2.67188 12.6667 2.67188Z" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M10.6641 1.32812V3.99479" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M5.33594 1.32812V3.99479" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M2 6.67188H14" stroke="#4B5563" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="eway-data">
                    @include('ewaybill::sales.datas')
                </div>

            </div>
        </div>
    </div>
@endsection

@push('modal')
    @include('business::component.delete-modal')
@endpush
