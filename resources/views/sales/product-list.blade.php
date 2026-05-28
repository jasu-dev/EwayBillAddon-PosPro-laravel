@forelse ($products as $product)
    @php
        $firstStock = $product->stocks->first();
        $purchasePrice = $firstStock->productPurchasePrice ?? 0;
        $salePrice = $product->product_type === 'combo' ? ($product->productSalePrice ?? 0): ($firstStock->productSalePrice ?? 0);
        $price_without_tax = $product->product_type === 'combo' ? ($product->price_without_tax ?? 0): ($firstStock->exclusive_price ?? 0);
    @endphp
    <div class="single-product {{ $product->id }}"
        data-product_id="{{ $product->id }}"
        data-product_type="{{ $product->product_type }}"
        data-has_serial="{{ $product->has_serial }}"
        data-default_price="{{ $salePrice }}"
        data-product_code="{{ $product->productCode }}"
        data-product_unit_id="{{ $product->unit->id ?? null }}"
        data-product_unit_name="{{ $product->unit->unitName ?? null }}"
        data-product_image="{{ $product->productPicture }}"
        data-product_name="{{ $product->productName }}"
        data-purchase_price="{{ $purchasePrice }}"
        data-batch_count="{{ $product->stocks->count() }}"
        data-price_without_tax="{{ $price_without_tax }}"
        data-stocks='@json($product->stocks)'
        data-route="{{ route('business.eway-carts.store') }}"
    >
        <div class="pro-img w-100">
            <img src="{{ asset($product->productPicture ?? 'assets/images/products/box.svg') }}" alt="">
        </div>
        <div class="product-con">
            <h6 class="pro-title product_name">{{ $product->productName }}</h6>
            <p class="pro-category">{{ $product->category->categoryName ?? '' }}</p>
            <div class="price">
                <h6 class="pro-price product_price">{{ currency_format($salePrice, currency: business_currency()) }}</h6>
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-danger not-found mt-1" role="alert">
        {{ __('No product found') }}
    </div>
@endforelse
