<?php

namespace Modules\EwayBill\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Gloudemans\Shoppingcart\Exceptions\InvalidRowIDException;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class EwayCartController extends Controller
{
    public function index()
    {
        $cart_contents = Cart::instance('eway')->content();
        $stockIds = $cart_contents->pluck('options.stock_id')->filter()->unique();
        $batchNos = Stock::whereIn('id', $stockIds)->pluck('batch_no', 'id');
        
        foreach ($cart_contents as $cartItem) {
            $stockId = $cartItem->options->stock_id ?? null;
            if ($stockId && isset($batchNos[$stockId])) {
                $newOptions = $cartItem->options->merge([
                    'batch_no' => $batchNos[$stockId],
                ]);
                Cart::instance('eway')->update($cartItem->rowId, [
                    'qty' => $cartItem->qty,
                    'options' => $newOptions,
                ]);
            }
        }

        $modules = product_setting()->modules ?? [];

        return view('ewaybill::sales.cart-list', compact('cart_contents', 'modules'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'stock_id' => 'nullable|exists:stocks,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'id' => 'required|integer',
            'name' => 'nullable|string',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'product_code' => 'nullable|string',
            'product_unit_id' => 'nullable|integer',
            'product_unit_name' => 'nullable|string',
            'product_image' => 'nullable|string',
            'expire_date' => 'nullable|date',
            'product_type' => 'nullable|in:single,variant,combo',
            'variant_name' => 'nullable|string',
            'serial_numbers' => 'nullable|array',
            'has_serial' => 'nullable|boolean',
            'price_without_tax' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
            'sales_price' => 'nullable|numeric',
            'batch_no' => 'nullable|string',
        ]);

        $incomingSerials = $request->serial_numbers ?? [];

        // Serial mode if serials exist
        if (!empty($incomingSerials)) {
            $stocks = Stock::where('business_id', auth()->user()->business_id)
                ->where('product_id', $request->id)
                ->where(function ($query) use ($incomingSerials) {
                    foreach ($incomingSerials as $serial) {
                        $query->orWhereJsonContains('serial_numbers', $serial);
                    }
                })->get();

            $serialStockMap = [];
            foreach ($stocks as $stock) {
                foreach ($incomingSerials as $serial) {
                    if (in_array($serial, $stock->serial_numbers ?? [])) {
                        $serialStockMap[$stock->id][] = $serial;
                    }
                }
            }

            foreach ($serialStockMap as $stockId => $serialsForStock) {
                $resolvedStock = $stocks->firstWhere('id', $stockId);
                $qty = count($serialsForStock);

                $price = round($resolvedStock->productSalePrice ?? 0, 2);

                $existingCartItem = Cart::instance('eway')->search(function ($item) use ($request, $stockId) {
                    return $item->id == $request->id && $item->options->stock_id == $stockId;
                })->first();

                if ($existingCartItem) {
                    $existingSerials = $existingCartItem->options->serial_numbers ?? [];
                    $newSerials = array_diff($serialsForStock, $existingSerials);
                    $duplicates = array_intersect($existingSerials, $newSerials);

                    if (!empty($duplicates)) {
                        return response()->json([
                            'success' => false,
                            'message' => __('Serial already exists in cart'),
                            'duplicates' => array_values($duplicates),
                        ], 422);
                    }

                    $mergedSerials = array_values(array_unique(array_merge($existingSerials, $serialsForStock)));

                    Cart::instance('eway')->update($existingCartItem->rowId, [
                        'qty' => count($mergedSerials),
                        'options' => $existingCartItem->options->merge([
                            'serial_numbers' => $mergedSerials,
                        ]),
                    ]);
                } else {
                    Cart::instance('eway')->add([
                        'id' => $request->id,
                        'name' => $request->name,
                        'qty' => $qty,
                        'price' => $price,
                        'options' => [
                            'product_code' => $request->product_code,
                            'product_unit_id' => $request->product_unit_id,
                            'product_unit_name' => $request->product_unit_name,
                            'product_image' => $request->product_image,
                            'product_type' => $request->product_type,
                            'variant_name' => $request->variant_name,
                            'stock_id' => $resolvedStock->id,
                            'warehouse_id' => $resolvedStock->warehouse_id,
                            'sales_price' => $resolvedStock->productSalePrice,
                            'whole_sale_price' => $resolvedStock->productWholeSalePrice,
                            'dealer_price' => $resolvedStock->productDealerPrice,
                            'serial_numbers' => $serialsForStock,
                            'has_serial' => $request->has_serial ?? 1,
                            'price_without_tax' => $resolvedStock->price_without_tax,
                            'discount' => $request->discount ?? 0,
                        ]
                    ]);
                }
            }
        } else {
            // Normal product (without serial)
            $existingCartItem = Cart::instance('eway')->search(function ($item) use ($request) {
                return $item->id == $request->id && $item->options->stock_id == $request->stock_id;
            })->first();

            if ($existingCartItem) {
                Cart::instance('eway')->update($existingCartItem->rowId, [
                    'qty' => $existingCartItem->qty + $request->quantity
                ]);
            } else {
                Cart::instance('eway')->add([
                    'id' => $request->id,
                    'name' => $request->name,
                    'qty' => $request->quantity,
                    'price' => round($request->price, 2),
                    'options' => [
                        'product_code' => $request->product_code,
                        'product_unit_id' => $request->product_unit_id,
                        'product_unit_name' => $request->product_unit_name,
                        'product_image' => $request->product_image,
                        'product_type' => $request->product_type,
                        'variant_name' => $request->variant_name,
                        'stock_id' => $request->stock_id,
                        'batch_no' => $request->batch_no,
                        'expire_date' => $request->expire_date,
                        'purchase_price' => $request->purchase_price,
                        'sales_price' => $request->sales_price,
                        'warehouse_id' => $request->warehouse_id,
                        'has_serial' => $request->has_serial ?? 0,
                        'price_without_tax' => $request->price_without_tax,
                        'discount' => $request->discount ?? 0,
                    ]
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('Item added to cart successfully.')
        ]);
    }

    public function update(Request $request, $id)
    {
        $cart = Cart::instance('eway')->get($id);
        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => __('Item not found in cart')
            ]);
        }

        $qty = $request->qty ?? $cart->qty;
        if ($qty < 0) {
            return response()->json([
                'success' => false,
                'message' => __('Enter a valid quantity')
            ]);
        }

        $incomingSerials = $request->serial_numbers ?? null;
        if (is_array($incomingSerials)) {
            $existingSerials = $incomingSerials;
        } else {
            $existingSerials = $cart->options->serial_numbers ?? [];
        }

        if (!is_array($existingSerials)) {
            $existingSerials = !empty($existingSerials) ? [$existingSerials] : [];
        }

        $hasSerial = $cart->options->has_serial ?? 0;
        if ($hasSerial) {
            $qty = count($existingSerials);
        }

        Cart::instance('eway')->update($id, [
            'qty' => $qty,
            'price' => round($request->price ?? $cart->price, 2),
            'options' => [
                'expire_date' => $request->expire_date ?? $cart->options->expire_date,
                'stock_id' => $request->stock_id ?? $cart->options->stock_id,
                'batch_no' => $request->batch_no ?? $cart->options->batch_no,
                'product_code' => $cart->options->product_code,
                'product_unit_id' => $cart->options->product_unit_id,
                'product_unit_name' => $cart->options->product_unit_name,
                'product_image' => $cart->options->product_image,
                'sales_price' => $cart->options->sales_price,
                'discount' => $request->discount ?? $cart->options->discount,
                'purchase_price' => $cart->options->purchase_price,
                'product_type' => $cart->options->product_type,
                'warehouse_id' => $cart->options->warehouse_id,
                'variant_name' => $cart->options->variant_name,
                'price_without_tax' => $cart->options->price_without_tax,
                'has_serial' => $hasSerial,
                'serial_numbers' => $existingSerials,
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Cart updated successfully')
        ]);
    }

    public function destroy($id)
    {
        try {
            Cart::instance('eway')->remove($id);
            return response()->json(['success' => true, 'message' => __('Item removed from cart')]);
        } catch (InvalidRowIDException $e) {
            return response()->json(['success' => false, 'message' => __('The cart does not contain this item')]);
        }
    }

    public function removeAllCart(Request $request)
    {
        Cart::instance('eway')->destroy();
        return response()->json([
            'success' => true,
            'message' => __('All cart items removed successfully!')
        ]);
    }
}
