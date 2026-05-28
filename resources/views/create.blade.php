@extends('layouts.business.master')

@section('title')
    {{ __('Create E-Way Bill') }}
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/choices.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/calculator.css') }}">
@endpush

@section('main_content')
    @php
        $modules = product_setting()->modules ?? [];
    @endphp
    <div class="container-fluid">
        <div class="grid row sales-main-container p-lr">
            <div class="sales-container">
                <!-- Quick Action Section -->
                <div class="quick-act-header">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
                        <div class="mb-2 mb-sm-0">
                            <h4 class='quick-act-title'>{{ __('Create E-Way Bill') }}</h4>
                        </div>
                        <div class="quick-actions-container">
                            <a href="{{ route('business.eway-bills.index') }}"
                                class='sales-btn d-flex align-items-center gap-1'>
                                <img src="{{ asset('assets/images/icons/sales.svg') }}" alt="">
                                {{ __('E-Way Bills List') }}
                            </a>
                            <button data-bs-toggle="modal" data-bs-target="#calculatorModal"
                                class='calculator-btn d-flex align-items-center gap-1'>
                                <img src="{{ asset('assets/images/icons/calculator.svg') }}" alt="">
                                {{ __('Calculator') }}
                            </button>
                            <a href="{{ route('business.dashboard.index') }}"
                                class='dashboard-btn d-flex align-items-center gap-1'>
                                <img src="{{ asset('assets/images/icons/dashboard.svg') }}" alt="">
                                {{ __('Dashboard') }}
                            </a>
                        </div>
                    </div>
                </div>

                <form action="{{ route('business.eway-bills.store') }}" method="post" enctype="multipart/form-data"
                    class="ajaxform">
                    @csrf
                    <div class="mt-4 mb-3">
                        <div class="row g-3">
                            <!-- Invoice Info -->
                            <div class="col-12 col-md-4">
                                <label class="form-label">{{ __('Invoice No.') }}</label>
                                <input type="text" name="invoiceNumber" value="{{ $invoice_no }}" class="form-control" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">{{ __('Invoice Date') }}</label>
                                <input type="date" name="invoiceDate" class="form-control" value="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">{{ __('Customer (Party)') }}</label>
                                <div class="d-flex align-items-center">
                                    <select required name="party_id" id="party_id" class="form-select customer-select choices-select">
                                        <option value="">{{ __('Select Customer') }}</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" 
                                                data-name="{{ $customer->name }}"
                                                data-type="{{ $customer->type }}"
                                                data-phone="{{ $customer->phone }}"
                                                data-tax_no="{{ $customer->tax_no }}"
                                                data-address="{{ $customer->address }}"
                                                data-pincode="{{ $customer->meta['pincode'] ?? '' }}"
                                                data-state="{{ $customer->state->name ?? '' }}">
                                                {{ $customer->name }} ({{ $customer->phone }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <a type="button" href="#customer-create-modal" data-bs-toggle="modal"
                                        class="btn btn-danger square-btn d-flex justify-content-center align-items-center ms-2">
                                        <img src="{{ asset('assets/images/icons/plus-square.svg') }}" alt="">
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- E-Way Transaction Parameters -->
                        <div class="card mt-3" style="min-height:auto !important">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">{{ __('E-Way Transactions Details') }}</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">{{ __('Supply Type') }}</label>
                                        <select name="supply_type" class="form-select">
                                            <option value="Outward">{{ __('Outward') }}</option>
                                            <option value="Inward">{{ __('Inward') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">{{ __('Sub Type') }}</label>
                                        <select name="sub_type" class="form-select">
                                            <option value="Supply">{{ __('Supply') }}</option>
                                            <option value="Export">{{ __('Export') }}</option>
                                            <option value="Job Work">{{ __('Job Work') }}</option>
                                            <option value="Own Use">{{ __('Own Use') }}</option>
                                            <option value="Exhibition">{{ __('Exhibition / Fairs') }}</option>
                                            <option value="Others">{{ __('Others') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">{{ __('Doc Type') }}</label>
                                        <select name="document_type" class="form-select">
                                            <option value="Tax Invoice">{{ __('Tax Invoice') }}</option>
                                            <option value="Bill of Supply">{{ __('Bill of Supply') }}</option>
                                            <option value="Delivery Challan">{{ __('Delivery Challan') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Consignor & Consignee details -->
                        <div class="row mt-3 g-3">
                            <!-- Bill From (Consignor) -->
                            <div class="col-12 col-md-6">
                                <div class="card" style="min-height:auto !important">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">{{ __('Bill From (Consignor)') }}</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <label class="form-label">{{ __('Dispatch Name') }}</label>
                                            <input type="text" name="from_name" value="{{ $business->companyName }}" class="form-control" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">{{ __('GSTIN') }}</label>
                                            <input type="text" name="from_gstin" value="{{ $business->vat_no }}" class="form-control">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">{{ __('Address') }}</label>
                                            <input type="text" name="from_address" value="{{ $business->address }}" class="form-control" required>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label">{{ __('PIN Code') }}</label>
                                                <input type="text" name="from_pincode" value="{{ $business->meta['pincode'] ?? '' }}" class="form-control" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">{{ __('State') }}</label>
                                                <select name="from_state" class="form-select" required>
                                                    <option value="">{{ __('Select State') }}</option>
                                                    @foreach($states as $state)
                                                        <option value="{{ $state->name }}" @selected($business->state_id == $state->id)>{{ $state->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bill To (Consignee) -->
                            <div class="col-12 col-md-6">
                                <div class="card" style="min-height:auto !important">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">{{ __('Bill To (Consignee)') }}</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <label class="form-label">{{ __('Delivery Name') }}</label>
                                            <input type="text" name="to_name" id="to_name" class="form-control" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">{{ __('GSTIN') }}</label>
                                            <input type="text" name="to_gstin" id="to_gstin" class="form-control">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">{{ __('Address') }}</label>
                                            <input type="text" name="to_address" id="to_address" class="form-control" required>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label">{{ __('PIN Code') }}</label>
                                                <input type="text" name="to_pincode" id="to_pincode" class="form-control" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">{{ __('State') }}</label>
                                                <select name="to_state" id="to_state" class="form-select" required>
                                                    <option value="">{{ __('Select State') }}</option>
                                                    @foreach($states as $state)
                                                        <option value="{{ $state->name }}">{{ $state->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transporter parameters -->
                        <div class="card mt-3" style="min-height:auto !important">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">{{ __('Transporter / Vehicle Details') }}</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">{{ __('Transporter Name') }}</label>
                                        <input type="text" name="transporter_name" class="form-control">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">{{ __('Transporter ID') }}</label>
                                        <input type="text" name="transporter_id" class="form-control">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">{{ __('Transport Mode') }}</label>
                                        <select name="transport_mode" class="form-select">
                                            <option value="Road">{{ __('Road') }}</option>
                                            <option value="Rail">{{ __('Rail') }}</option>
                                            <option value="Air">{{ __('Air') }}</option>
                                            <option value="Ship">{{ __('Ship') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">{{ __('Distance (KM)') }}</label>
                                        <input type="number" name="distance" min="0" value="0" class="form-control" required>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">{{ __('Vehicle Number') }}</label>
                                        <input type="text" name="vehicle_number" class="form-control" placeholder="e.g. DL1CA1234">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">{{ __('Vehicle Type') }}</label>
                                        <select name="vehicle_type" class="form-select">
                                            <option value="Regular">{{ __('Regular') }}</option>
                                            <option value="Over Dimensional Cargo">{{ __('Over Dimensional Cargo (ODC)') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Cart table -->
                    <div class="cart-payment mt-4">
                        <div class="table-responsive">
                            <table class="table table-bordered text-center">
                                <thead>
                                    <tr>
                                        <th class="border table-background">{{ __('Image') }}</th>
                                        <th class="border table-background">{{ __('Items') }}</th>
                                        <th class="border table-background">{{ __('Code') }}</th>
                                        <th class="border table-background">{{ __('Batch') }}</th>
                                        <th class="border table-background">{{ __('Unit') }}</th>
                                        <th class="border table-background">{{ __('Sale Price') }}</th>
                                        @if ($modules['allow_product_discount'] ?? false)
                                            <th class="border table-background">{{ __('Discount') }}</th>
                                        @endif
                                        <th class="border table-background">{{ __('Qty') }}</th>
                                        <th class="border table-background">{{ __('Sub Total') }}</th>
                                        <th class="border table-background">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="eway_cart_list">
                                    @include('ewaybill::sales.cart-list')
                                </tbody>
                            </table>
                        </div>

                        <div class="hr-container">
                            <hr>
                        </div>

                        <!-- Calculations summary -->
                        <div class="grid row py-3 payment-section">
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div class="amount-info-container">
                                    <div class="row amount-container align-items-center mb-2">
                                        <h6 class="payment-title col-6">
                                            {{ get_business_option('business-settings')['vat_name'] ?? 'Tax' }}</h6>
                                        <div class="col-6 w-100 d-flex justify-content-between gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <select name="vat_id" class="form-select vat_select" id='form-ware'>
                                                    <option value="">{{ __('Select') }}</option>
                                                    @foreach ($vats as $vat)
                                                        <option value="{{ $vat->id }}"
                                                            data-rate="{{ $vat->rate }}">{{ $vat->name }}
                                                            ({{ $vat->rate }}%)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <input type="number" step="any" name="tax_amount" id="tax_amount"
                                                min="0" class="form-control right-start-input"
                                                placeholder="0" readonly>
                                        </div>
                                    </div>
                                    <div class="row amount-container align-items-center mb-2">
                                        <h6 class="payment-title">{{ __('Note / Remarks') }}</h6>
                                        <input type="text" name="note" class="form-control"
                                            placeholder="{{ __('Type note...') }}">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button class="save-btn cancel-sale-btn" type="button">{{ __('Cancel') }}</button>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-6 col-lg-6">
                                <div class="payment-container mb-3 amount-info-container">
                                    <div class="mb-2 d-flex align-items-center justify-content-between">
                                        <h6>{{ __('Sub Total') }}</h6>
                                        <h6 class="fw-bold" id="sub_total">0.00</h6>
                                    </div>
                                    <div class="row save-amount-container align-items-center mb-2">
                                        <h6 class="payment-title col-6">{{ __('Discount') }}</h6>
                                        <div class="col-6 w-100">
                                            <input type="number" step="any" name="discountAmount"
                                                id="discount_amount" min="0" value="0"
                                                class="form-control right-start-input" placeholder="{{ __('0') }}">
                                        </div>
                                    </div>
                                    <div class="mb-2 shopping-crg-grid">
                                        <h6 class="payment-title ">{{ __('Freight / Shipping Charge') }}</h6>
                                        <div class="">
                                            <input type="number" step="any" name="shipping_charge"
                                                id="shipping_charge" class="form-control right-start-input"
                                                value="0" placeholder="0">
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between fw-bold mt-3">
                                        <div class="fw-bold">{{ __('Total Amount') }}</div>
                                        <h6 class='fw-bold' id="total_amount">0.00</h6>
                                    </div>
                                </div>
                                <div class="sale-btn-container">
                                    <button class="submit-btn payment-btn" type="submit">{{ __('Generate E-Way Bill') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Product filter side panel -->
            <div class="main-container">
                <div class="products-header">
                    <div class="container-fluid p-0">
                        <div class="row g-2 w-100 align-items-center ">
                            <div class="w-100">
                                <form action="{{ route('business.eway-bills.product-filter') }}" method="post"
                                    class="product-filter product-filter-form w-100" table="#products-list">
                                    @csrf
                                    <div class="search-product">
                                        <div class="d-flex mb-2">
                                            <input type="text" name="search" id="sale_product_search"
                                                class="form-control search-input"
                                                placeholder="{{ __('Scan / search by code or name') }}">
                                            <button class="btn-search" type="button" data-bs-toggle="modal"
                                                data-bs-target="#scannerModal" data-target-input="sale_product_search">
                                                <svg width="30" height="30" viewBox="0 0 30 30" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M3.75 7.03125H5.625V22.9688H3.75V7.03125ZM13.5938 7.03125H15.4688V22.9688H13.5938V7.03125ZM24.375 7.03125H26.25V22.9688H24.375V7.03125ZM9.375 7.03125H10.3125V22.9688H9.375V7.03125ZM11.25 7.03125H12.1875V22.9688H11.25V7.03125ZM19.6875 7.03125H20.625V22.9688H19.6875V7.03125ZM21.5625 7.03125H22.5V22.9688H21.5625V7.03125ZM7.03125 7.03125H8.4375V22.9688H7.03125V7.03125ZM16.875 7.03125H18.2812V22.9688H16.875V7.03125Z"
                                                        fill="white" />
                                                    <path
                                                        d="M1.40625 9.84375C1.28193 9.84375 1.1627 9.79436 1.07479 9.70646C0.986886 9.61855 0.9375 9.49932 0.9375 9.375V4.6875C0.9375 4.56318 0.986886 4.44395 1.07479 4.35604C1.1627 4.26814 1.28193 4.21875 1.40625 4.21875H6.09375C6.21807 4.21875 6.3373 4.26814 6.42521 4.35604C6.51311 4.44395 6.5625 4.56318 6.5625 4.6875C6.5625 4.81182 6.51311 4.93105 6.42521 5.01896C6.3373 5.10686 6.21807 5.15625 6.09375 5.15625H1.875V9.375C1.875 9.49932 1.82561 9.61855 1.73771 9.70646C1.6498 9.79436 1.53057 9.84375 1.40625 9.84375ZM28.5938 9.84375C28.4694 9.84375 28.3502 9.79436 28.2623 9.70646C28.1744 9.61855 28.125 9.49932 28.125 9.375V5.15625H23.9062C23.7819 5.15625 23.6627 5.10686 23.5748 5.01896C23.4869 4.93105 23.4375 4.81182 23.4375 4.6875C23.4375 4.56318 23.4869 4.44395 23.5748 4.35604C23.6627 4.26814 23.7819 4.21875 23.9062 4.21875H28.5938C28.7181 4.21875 28.8373 4.26814 28.9252 4.35604C29.0131 4.44395 29.0625 4.56318 29.0625 4.6875V9.375C29.0625 9.49932 29.0131 9.61855 28.9252 9.70646C28.8373 9.79436 28.7181 9.84375 28.5938 9.84375ZM6.09375 25.7812H1.40625C1.28193 25.7812 1.1627 25.7319 1.07479 25.644C0.986886 25.556 0.9375 25.4368 0.9375 25.3125V20.625C0.9375 20.5007 0.986886 20.3815 1.07479 20.2935C1.1627 20.2056 1.28193 20.1562 1.40625 20.1562C1.53057 20.1562 1.6498 20.2056 1.73771 20.2935C1.82561 20.3815 1.875 20.5007 1.875 20.625V24.8438H6.09375C6.21807 24.8438 6.3373 24.8931 6.42521 24.981C6.51311 25.069 6.5625 25.1882 6.5625 25.3125C6.5625 25.4368 6.51311 25.556 6.42521 25.644C6.3373 25.7319 6.21807 25.7812 6.09375 25.7812ZM28.5938 25.7812H23.9062C23.7819 25.7812 23.6627 25.7319 23.5748 25.644C23.4869 25.556 23.4375 25.4368 23.4375 25.3125C23.4375 25.1882 23.4869 25.069 23.5748 24.981C23.6627 24.8931 23.7819 24.8438 23.9062 24.8438H28.125V20.625C28.125 20.5007 28.1744 20.3815 28.2623 20.2935C28.3502 20.2056 28.4694 20.1562 28.5938 20.1562C28.7181 20.1562 28.8373 20.2056 28.9252 20.2935C29.0131 20.3815 29.0625 20.5007 29.0625 20.625V25.3125C29.0625 25.4368 29.0131 25.556 28.9252 25.644C28.8373 25.7319 28.7181 25.7812 28.5938 25.7812Z"
                                                        fill="white" />
                                                    <path d="M1.40625 14.0625H28.5938V15.9375H1.40625V14.0625Z"
                                                        fill="white" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-end gap-2 ">
                                            <a data-bs-toggle="offcanvas" data-bs-target="#category-search-modal"
                                                aria-controls="offcanvasRight"
                                                class="btn btn-category w-100">{{ __('Category') }}</a>
                                            <a data-bs-toggle="offcanvas" data-bs-target="#brand-search-modal"
                                                aria-controls="offcanvasRight"
                                                class="btn btn-brand w-100">{{ __('Brand') }}</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="products-container">
                    <div class="p-3 scroll-card">
                        <div class="search-product-card products gap-2 @if (count($products) === 1) single-product @endif product-list-container"
                            id="products-list">
                            @include('ewaybill::sales.product-list')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden input parameters for JS -->
    <input type="hidden" value="{{ route('business.eway-carts.index') }}" id="get-cart">
    <input type="hidden" value="{{ route('business.eway-carts.remove-all') }}" id="clear-cart">
    <input type="hidden" id="cart-store-url" value="{{ route('business.eway-carts.store') }}">
    <input type="hidden" id="business_currency_symbol" value="{{ business_currency()->symbol }}">
@endsection

@push('modal')
    <!-- Scanner Modal -->
    <div class="modal fade" id="scannerModal" tabindex="-1" aria-labelledby="scannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scannerModalLabel">{{ __('Scan QR/Barcode') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <video id="qr-video" style="width: 100%; height: auto; border-radius: 8px;"></video>
                </div>
            </div>
        </div>
    </div>

    @include('business::sales.customer-create')
    @include('business::sales.stock-list')
    @include('business::sales.calculator')
    @include('business::sales.category-search')
    @include('business::sales.brand-search')
@endpush

@push('js')
    <script src="{{ asset('assets/js/choices.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom/eway.js') . '?v=' . time() }}"></script>
    <script type="module">
        import {
            BrowserMultiFormatReader,
            BrowserQRCodeReader
        } from 'https://esm.sh/@zxing/browser@0.1.5';
        import {
            DecodeHintType,
            BarcodeFormat
        } from 'https://esm.sh/@zxing/library@0.21.3';

        document.addEventListener('DOMContentLoaded', function() {
            let codeReader = null;
            let currentTargetInput = null;
            let controls = null;

            function isMobileDevice() {
                return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            }

            async function startScanner() {
                const videoEl = document.getElementById('qr-video');

                try {
                    const devices = await BrowserMultiFormatReader.listVideoInputDevices();

                    // Pick back camera on mobile, front on desktop
                    let selectedDevice;
                    if (isMobileDevice()) {
                        selectedDevice = devices.find(d =>
                            /back|rear|environment/i.test(d.label)
                        ) || devices[devices.length - 1]; // fallback: last device is usually back
                    } else {
                        selectedDevice = devices.find(d =>
                            /front|user|facetime|integrated/i.test(d.label)
                        ) || devices[0];
                    }

                    const hints = new Map();
                    hints.set(DecodeHintType.POSSIBLE_FORMATS, [BarcodeFormat.QR_CODE]);
                    hints.set(DecodeHintType.TRY_HARDER, true);

                    codeReader = new BrowserMultiFormatReader(hints);

                    controls = await codeReader.decodeFromVideoDevice(
                        selectedDevice?.deviceId ?? undefined,
                        videoEl,
                        (result, error) => {
                            if (result) onScanSuccess(result.getText());
                        }
                    );

                } catch (err) {
                    console.error('Failed to start scanner:', err);
                }
            }

            function stopScanner() {
                if (controls) {
                    controls.stop();
                    controls = null;
                }
                codeReader = null;
                const videoEl = document.getElementById('qr-video');
                if (videoEl) {
                    videoEl.srcObject = null;
                }
            }

            function onScanSuccess(decodedText) {
                let inputField = null;
                if (currentTargetInput) {
                    inputField = document.getElementById(currentTargetInput);
                } else {
                    inputField = document.getElementById('sale_product_search');
                }

                if (inputField) {
                    inputField.value = decodedText;
                    // Trigger input event to let the live search filter handle it
                    $(inputField).trigger('input');
                }

                const modal = bootstrap.Modal.getInstance(
                    document.getElementById('scannerModal')
                );
                modal.hide();
            }

            const scannerModal = document.getElementById('scannerModal');
            if (scannerModal) {
                scannerModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (button) currentTargetInput = button.getAttribute('data-target-input');
                });

                scannerModal.addEventListener('shown.bs.modal', startScanner);
                scannerModal.addEventListener('hidden.bs.modal', function() {
                    stopScanner();
                    currentTargetInput = null;
                });
            }
        });
    </script>
@endpush
