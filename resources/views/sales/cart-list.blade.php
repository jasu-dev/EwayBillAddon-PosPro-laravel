@if(isset($cart_contents))
    @php
        $modules = product_setting()->modules ?? [];
    @endphp
    @foreach($cart_contents as $cart)
        @php
            $product = \App\Models\Product::with('vat')->find($cart->id);
            $tax_rate = $product && $product->vat ? $product->vat->rate : 0;
        @endphp
        <tr
            data-row_id="{{ $cart->rowId }}"
            data-product_id="{{ $cart->id }}"
            data-tax_rate="{{ $tax_rate }}"
            data-update_route="{{ route('business.eway-carts.update', $cart->rowId) }}"
            data-destroy_route="{{ route('business.eway-carts.destroy', $cart->rowId) }}">
            <td>
                <img class="table-img" src="{{ asset($cart->options->product_image ?? 'assets/images/products/box.svg') }}">
            </td>
            <td>{{ $cart->name }}</td>
            <td>{{ $cart->options->product_code }}</td>
            <td>{{ $cart->options->batch_no ?? __('N/A') }}</td>
            <td>{{ $cart->options->product_unit_name }}</td>
            <td>
                <input class="text-center sales-input cart-price" type="number" step="any" min="0" value="{{ round($cart->price, 2) }}" placeholder="0">
            </td>
            @if ($modules['allow_product_discount'] ?? false)
                <td>
                    <input class="text-center sales-input cart-discount" type="number" step="any" min="0" value="{{ $cart->options->discount ?? 0 }}" placeholder="0">
                </td>
            @endif
            <td class="large-td">
                <div class="d-flex gap-2 align-items-center justify-content-center">
                    <button class="incre-decre minus-btn" type="button">
                        <i class="fas fa-minus icon"></i>
                    </button>
                    <input type="number" step="any" value="{{ $cart->qty }}" class="dynamic-width cart-qty " placeholder="{{ __('0') }}" >
                    <button class="incre-decre plus-btn" type="button">
                        <i class="fas fa-plus icon"></i>
                    </button>
                </div>
            </td>
            <td class="cart-subtotal">
                {{ currency_format(round(($cart->price - ($cart->options->discount ?? 0)) * $cart->qty, 2), currency: business_currency()) }}
            </td>
            <td>
                <button class='x-btn remove-btn' type="button">
                    <img src="{{ asset('assets/images/icons/x.svg') }}" alt="">
                </button>
            </td>
        </tr>
    @endforeach
@endif
