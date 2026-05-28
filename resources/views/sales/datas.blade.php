<div class="responsive-table m-0">
    <table class="table" id="datatable">
        <thead>
            <tr>
                <th class="">{{ __('SL') }}.</th>
                <th class="text-start">{{ __('Date') }}</th>
                <th class="text-start">{{ __('Invoice No') }}</th>
                <th class="text-start">{{ __('Party Name') }}</th>
                <th class="text-start">{{ __('Transporter') }}</th>
                <th class="text-start">{{ __('Vehicle No') }}</th>
                <th class="text-start">{{ __('Mode') }}</th>
                <th class="text-start">{{ __('Distance') }}</th>
                <th class="text-start">{{ __('Total Value') }}</th>
                <th>{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($eway_bills as $bill)
                <tr>
                    <td>{{ ($eway_bills->currentPage() - 1) * $eway_bills->perPage() + $loop->iteration }}</td>
                    <td class="text-start">{{ formatted_date($bill->invoice_date) }}</td>
                    <td class="text-start">{{ $bill->invoice_number }}</td>
                    <td class="text-start">{{ $bill->party->name ?? $bill->to_name }}</td>
                    <td class="text-start">{{ $bill->transporter_name ?: __('N/A') }}</td>
                    <td class="text-start">{{ $bill->vehicle_number ?: __('N/A') }}</td>
                    <td class="text-start">{{ $bill->transport_mode }}</td>
                    <td class="text-start">{{ $bill->distance }} KM</td>
                    <td class="text-start">{{ currency_format($bill->total_amount, currency: business_currency()) }}</td>
                    <td class="d-print-none">
                        <div class="dropdown table-action">
                            <button type="button" data-bs-toggle="dropdown">
                                <i class="far fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a target="_blank" href="{{ route('business.eway-bills.invoice', $bill->id) }}">
                                        <i class="far fa-eye me-2"></i> {{ __('View Invoice') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('business.eway-bills.pdf', $bill->id) }}">
                                        <i class="far fa-file-pdf me-2"></i> {{ __('Download PDF') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('business.eway-bills.edit', $bill->id) }}">
                                        <i class="far fa-edit me-2"></i> {{ __('Edit') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('business.eway-bills.destroy', $bill->id) }}"
                                        class="confirm-action" data-method="DELETE">
                                        <i class="far fa-trash me-2"></i> {{ __('Delete') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted">{{ __('No E-Way bills found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">
    {{ $eway_bills->links('vendor.pagination.bootstrap-5') }}
</div>
