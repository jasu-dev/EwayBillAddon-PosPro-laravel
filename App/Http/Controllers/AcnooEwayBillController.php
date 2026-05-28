<?php

namespace Modules\EwayBill\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Business;
use App\Models\Category;
use App\Models\Party;
use App\Models\Product;
use App\Models\Stock;
use App\Models\State;
use App\Models\Vat;
use App\Models\Warehouse;
use App\Models\PaymentType;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\EwayBill\App\Models\EwayBill;
use Modules\EwayBill\App\Models\EwayBillDetail;

class AcnooEwayBillController extends Controller
{
    public function index(Request $request)
    {
        $business_id = auth()->user()->business_id;

        $ewayQuery = EwayBill::with(['user:id,name', 'party:id,name,email,phone,type', 'details'])
            ->where('business_id', $business_id);

        // Date Filters
        $startDate = Carbon::today()->format('Y-m-d');
        $endDate = Carbon::today()->format('Y-m-d');

        if ($request->custom_days === 'yesterday') {
            $startDate = Carbon::yesterday()->format('Y-m-d');
            $endDate = Carbon::yesterday()->format('Y-m-d');
        } elseif ($request->custom_days === 'last_seven_days') {
            $startDate = Carbon::today()->subDays(6)->format('Y-m-d');
        } elseif ($request->custom_days === 'last_thirty_days') {
            $startDate = Carbon::today()->subDays(29)->format('Y-m-d');
        } elseif ($request->custom_days === 'current_month') {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        } elseif ($request->custom_days === 'last_month') {
            $startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        } elseif ($request->custom_days === 'current_year') {
            $startDate = Carbon::now()->startOfYear()->format('Y-m-d');
            $endDate = Carbon::now()->endOfYear()->format('Y-m-d');
        } elseif ($request->custom_days === 'custom_date' && $request->from_date && $request->to_date) {
            $startDate = Carbon::parse($request->from_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->to_date)->format('Y-m-d');
        }

        $ewayQuery->whereDate('invoice_date', '>=', $startDate)
            ->whereDate('invoice_date', '<=', $endDate);

        if ($request->filled('search')) {
            $ewayQuery->where(function ($query) use ($request) {
                $query->where('invoice_number', 'like', '%' . $request->search . '%')
                    ->orWhere('transporter_name', 'like', '%' . $request->search . '%')
                    ->orWhere('vehicle_number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('party', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $perPage = $request->input('per_page', 20);
        $eway_bills = $ewayQuery->latest()->paginate($perPage)->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'data' => view('ewaybill::sales.datas', compact('eway_bills'))->render(),
            ]);
        }

        return view('ewaybill::index', compact('eway_bills'));
    }

    public function create()
    {
        $business_id = auth()->user()->business_id;

        // Clear E-Way Cart
        Cart::instance('eway')->destroy();

        $customers = Party::with('state')->where('type', '!=', 'Supplier')
            ->where('business_id', $business_id)
            ->latest()
            ->get();

        $products = Product::with([
            'unit:id,unitName',
            'vat:id,rate',
            'brand:id,brandName',
            'category:id,categoryName',
            'product_model:id,name',
            'stocks',
        ])
            ->withSum('stocks', 'productStock')
            ->where('business_id', $business_id)
            ->whereHas('stocks', function ($s) {
                $s->where('productStock', '>', 0);
            })
            ->latest()
            ->get();

        $categories = Category::where('business_id', $business_id)->latest()->get();
        $brands = Brand::where('business_id', $business_id)->latest()->get();
        $vats = Vat::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $warehouses = Warehouse::select('id', 'name')->where('business_id', $business_id)->latest()->get();
        $states = State::orderBy('name')->get();

        // Retrieve business details for auto-filling consignor/shipper GSTIN
        $business = Business::with('state')->find($business_id);

        $eway_id = (EwayBill::max('id') ?? 0) + 1;
        $invoice_no = 'EWAY-' . str_pad($eway_id, 5, '0', STR_PAD_LEFT);

        return view('ewaybill::create', compact(
            'customers',
            'products',
            'invoice_no',
            'categories',
            'brands',
            'vats',
            'warehouses',
            'states',
            'business'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'party_id' => 'nullable|exists:parties,id',
            'vat_id' => 'nullable|exists:vats,id',
            'invoiceNumber' => 'required|string',
            'invoiceDate' => 'required|date',
            'supply_type' => 'required|string',
            'sub_type' => 'required|string',
            'document_type' => 'required|string',
            'from_name' => 'required|string',
            'from_gstin' => 'nullable|string',
            'from_address' => 'required|string',
            'from_pincode' => 'required|string',
            'from_state' => 'required|string',
            'to_name' => 'required|string',
            'to_gstin' => 'nullable|string',
            'to_address' => 'required|string',
            'to_pincode' => 'required|string',
            'to_state' => 'required|string',
            'transporter_name' => 'nullable|string',
            'transporter_id' => 'nullable|string',
            'transport_mode' => 'required|string',
            'distance' => 'required|integer|min:0',
            'vehicle_number' => 'nullable|string',
            'vehicle_type' => 'required|string',
            'discountAmount' => 'nullable|numeric|min:0',
            'shipping_charge' => 'nullable|numeric|min:0',
        ]);

        $business_id = auth()->user()->business_id;
        $carts = Cart::instance('eway')->content();

        if ($carts->count() < 1) {
            return response()->json(['message' => __('Cart is empty. Add items first!')], 400);
        }

        DB::beginTransaction();
        try {
            $rawSubtotal = $carts->sum(fn($item) => (float)$item->subtotal);
            $cartWiseDiscountTotal = $carts->sum(fn($item) => (float)($item->options->discount ?? 0) * $item->qty);
            $subtotal = $rawSubtotal - $cartWiseDiscountTotal;

            $discountAmount = $request->discountAmount ?? 0;
            if ($discountAmount > $subtotal) {
                return response()->json(['message' => __('Discount cannot be more than subtotal!')], 400);
            }

            $subtotalAfterDiscount = $subtotal - $discountAmount;

            // Calculate tax amount dynamically from selected vat_id
            $vat = Vat::find($request->vat_id);
            $taxRate = $vat ? $vat->rate : 0;
            $taxAmount = ($subtotalAfterDiscount * $taxRate) / 100;

            $shippingCharge = $request->shipping_charge ?? 0;
            $totalAmount = $subtotalAfterDiscount + $taxAmount + $shippingCharge;

            $eway_bill = EwayBill::create([
                'business_id' => $business_id,
                'party_id' => $request->party_id,
                'user_id' => auth()->id(),
                'vat_id' => $request->vat_id,
                'invoice_number' => $request->invoiceNumber,
                'invoice_date' => Carbon::parse($request->invoiceDate),
                'supply_type' => $request->supply_type,
                'sub_type' => $request->sub_type,
                'document_type' => $request->document_type,
                'from_name' => $request->from_name,
                'from_gstin' => $request->from_gstin,
                'from_address' => $request->from_address,
                'from_pincode' => $request->from_pincode,
                'from_state' => $request->from_state,
                'to_name' => $request->to_name,
                'to_gstin' => $request->to_gstin,
                'to_address' => $request->to_address,
                'to_pincode' => $request->to_pincode,
                'to_state' => $request->to_state,
                'transporter_name' => $request->transporter_name,
                'transporter_id' => $request->transporter_id,
                'transport_mode' => $request->transport_mode,
                'distance' => $request->distance,
                'vehicle_number' => $request->vehicle_number,
                'vehicle_type' => $request->vehicle_type,
                'sub_total' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'shipping_charge' => $shippingCharge,
                'total_amount' => $totalAmount,
                'notes' => $request->note,
            ]);

            foreach ($carts as $cartItem) {
                $qty = $cartItem->qty;
                $product = Product::find($cartItem->id);

                if (!$product) {
                    return response()->json(['message' => __("Product not found: {$cartItem->name}")], 400);
                }

                $stock = Stock::where('id', $cartItem->options->stock_id)->first();
                if (!$stock) {
                    return response()->json(['message' => __("Stock not found for item: {$cartItem->name}")], 400);
                }

                if ($stock->productStock < $qty) {
                    return response()->json(['message' => __($cartItem->name . ' - stock not available. Available: ' . $stock->productStock)], 400);
                }

                // Decrement Stock
                $stock->decrement('productStock', $qty);

                $itemPrice = $cartItem->price;
                $itemDiscount = $cartItem->options->discount ?? 0;
                $itemSub = ($itemPrice - $itemDiscount) * $qty;
                // Ratio discount to item subtotal
                $overallDiscount = $subtotal > 0 ? ($itemSub / $subtotal) * $discountAmount : 0;
                $itemTaxable = $itemSub - $overallDiscount;
                $itemTaxAmount = ($itemTaxable * $taxRate) / 100;

                EwayBillDetail::create([
                    'eway_bill_id' => $eway_bill->id,
                    'product_id' => $product->id,
                    'stock_id' => $stock->id,
                    'price' => $itemPrice,
                    'quantity' => $qty,
                    'discount' => $itemDiscount,
                    'hsn_code' => $product->hsn_code,
                    'tax_percent' => $taxRate,
                    'tax_amount' => $itemTaxAmount,
                    'total' => $itemSub + $itemTaxAmount,
                ]);
            }

            Cart::instance('eway')->destroy();
            DB::commit();

            return response()->json([
                'message' => __('E-Way Invoice created successfully.'),
                'redirect' => route('business.eway-bills.index'),
                'secondary_redirect_url' => route('business.eway-bills.invoice', $eway_bill->id),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function edit($id)
    {
        $business_id = auth()->user()->business_id;

        // Clear E-Way Cart
        Cart::instance('eway')->destroy();

        $eway_bill = EwayBill::with(['details', 'details.stock', 'details.product'])->findOrFail($id);

        $customers = Party::with('state')->where('type', '!=', 'Supplier')
            ->where('business_id', $business_id)
            ->latest()
            ->get();

        $products = Product::with('category:id,categoryName', 'unit:id,unitName', 'stocks')
            ->where('business_id', $business_id)
            ->whereHas('stocks', function ($q) {
                $q->where('productStock', '>', 0);
            })
            ->latest()
            ->get();

        $categories = Category::where('business_id', $business_id)->latest()->get();
        $brands = Brand::where('business_id', $business_id)->latest()->get();
        $vats = Vat::where('business_id', $business_id)->whereStatus(1)->latest()->get();
        $warehouses = Warehouse::select('id', 'name')->where('business_id', $business_id)->latest()->get();
        $states = State::orderBy('name')->get();
        $business = Business::find($business_id);

        // Prepopulate Cart from Details
        foreach ($eway_bill->details as $detail) {
            Cart::instance('eway')->add([
                'id' => $detail->product_id,
                'name' => $detail->product->productName ?? '',
                'qty' => $detail->quantity,
                'price' => $detail->price,
                'options' => [
                    'product_code' => $detail->product->productCode ?? '',
                    'product_unit_id' => $detail->product->unit_id ?? null,
                    'product_unit_name' => $detail->product->unit->unitName ?? '',
                    'product_image' => $detail->product->productPicture ?? '',
                    'stock_id' => $detail->stock_id,
                    'batch_no' => $detail->stock->batch_no ?? '',
                    'expire_date' => $detail->stock->expire_date ?? '',
                    'sales_price' => $detail->price,
                    'discount' => $detail->discount,
                    'has_serial' => $detail->product->has_serial ?? 0,
                    'price_without_tax' => $detail->product->price_without_tax ?? 0,
                ]
            ]);
        }

        $cart_contents = Cart::instance('eway')->content();

        return view('ewaybill::edit', compact(
            'eway_bill',
            'customers',
            'products',
            'cart_contents',
            'categories',
            'brands',
            'vats',
            'warehouses',
            'states',
            'business'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'party_id' => 'nullable|exists:parties,id',
            'vat_id' => 'nullable|exists:vats,id',
            'invoiceNumber' => 'required|string',
            'invoiceDate' => 'required|date',
            'supply_type' => 'required|string',
            'sub_type' => 'required|string',
            'document_type' => 'required|string',
            'from_name' => 'required|string',
            'from_gstin' => 'nullable|string',
            'from_address' => 'required|string',
            'from_pincode' => 'required|string',
            'from_state' => 'required|string',
            'to_name' => 'required|string',
            'to_gstin' => 'nullable|string',
            'to_address' => 'required|string',
            'to_pincode' => 'required|string',
            'to_state' => 'required|string',
            'transporter_name' => 'nullable|string',
            'transporter_id' => 'nullable|string',
            'transport_mode' => 'required|string',
            'distance' => 'required|integer|min:0',
            'vehicle_number' => 'nullable|string',
            'vehicle_type' => 'required|string',
            'discountAmount' => 'nullable|numeric|min:0',
            'shipping_charge' => 'nullable|numeric|min:0',
        ]);

        $carts = Cart::instance('eway')->content();

        if ($carts->count() < 1) {
            return response()->json(['message' => __('Cart is empty. Add items first!')], 400);
        }

        DB::beginTransaction();
        try {
            $eway_bill = EwayBill::findOrFail($id);
            $prevDetails = $eway_bill->details;

            // Restore Previous stock quantities
            foreach ($prevDetails as $prevItem) {
                if ($prevItem->stock_id) {
                    $stock = Stock::find($prevItem->stock_id);
                    if ($stock) {
                        $stock->increment('productStock', $prevItem->quantity);
                    }
                }
            }

            // Remove old details
            EwayBillDetail::where('eway_bill_id', $eway_bill->id)->delete();

            $rawSubtotal = $carts->sum(fn($item) => (float)$item->subtotal);
            $cartWiseDiscountTotal = $carts->sum(fn($item) => (float)($item->options->discount ?? 0) * $item->qty);
            $subtotal = $rawSubtotal - $cartWiseDiscountTotal;

            $discountAmount = $request->discountAmount ?? 0;
            if ($discountAmount > $subtotal) {
                return response()->json(['message' => __('Discount cannot be more than subtotal!')], 400);
            }

            $subtotalAfterDiscount = $subtotal - $discountAmount;

            // Recalculate tax based on selected vat_id
            $vat = Vat::find($request->vat_id);
            $taxRate = $vat ? $vat->rate : 0;
            $taxAmount = ($subtotalAfterDiscount * $taxRate) / 100;

            $shippingCharge = $request->shipping_charge ?? 0;
            $totalAmount = $subtotalAfterDiscount + $taxAmount + $shippingCharge;

            $eway_bill->update([
                'party_id' => $request->party_id,
                'invoice_number' => $request->invoiceNumber,
                'invoice_date' => Carbon::parse($request->invoiceDate),
                'vat_id' => $request->vat_id,
                'supply_type' => $request->supply_type,
                'sub_type' => $request->sub_type,
                'document_type' => $request->document_type,
                'from_name' => $request->from_name,
                'from_gstin' => $request->from_gstin,
                'from_address' => $request->from_address,
                'from_pincode' => $request->from_pincode,
                'from_state' => $request->from_state,
                'to_name' => $request->to_name,
                'to_gstin' => $request->to_gstin,
                'to_address' => $request->to_address,
                'to_pincode' => $request->to_pincode,
                'to_state' => $request->to_state,
                'transporter_name' => $request->transporter_name,
                'transporter_id' => $request->transporter_id,
                'transport_mode' => $request->transport_mode,
                'distance' => $request->distance,
                'vehicle_number' => $request->vehicle_number,
                'vehicle_type' => $request->vehicle_type,
                'sub_total' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'shipping_charge' => $shippingCharge,
                'total_amount' => $totalAmount,
                'notes' => $request->note,
            ]);

            foreach ($carts as $cartItem) {
                $qty = $cartItem->qty;
                $product = Product::find($cartItem->id);

                if (!$product) {
                    return response()->json(['message' => __("Product not found: {$cartItem->name}")], 400);
                }

                $stock = Stock::where('id', $cartItem->options->stock_id)->first();
                if (!$stock) {
                    return response()->json(['message' => __("Stock not found for item: {$cartItem->name}")], 400);
                }

                if ($stock->productStock < $qty) {
                    return response()->json(['message' => __($cartItem->name . ' - stock not available. Available: ' . $stock->productStock)], 400);
                }

                // Decrement stock
                $stock->decrement('productStock', $qty);

                $itemPrice = $cartItem->price;
                $itemDiscount = $cartItem->options->discount ?? 0;
                $itemSub = ($itemPrice - $itemDiscount) * $qty;
                $overallDiscount = $subtotal > 0 ? ($itemSub / $subtotal) * $discountAmount : 0;
                $itemTaxable = $itemSub - $overallDiscount;
                $itemTaxAmount = ($itemTaxable * $taxRate) / 100;

                EwayBillDetail::create([
                    'eway_bill_id' => $eway_bill->id,
                    'product_id' => $product->id,
                    'stock_id' => $stock->id,
                    'price' => $itemPrice,
                    'quantity' => $qty,
                    'discount' => $itemDiscount,
                    'hsn_code' => $product->hsn_code,
                    'tax_percent' => $taxRate,
                    'tax_amount' => $itemTaxAmount,
                    'total' => $itemSub + $itemTaxAmount,
                ]);
            }

            Cart::instance('eway')->destroy();
            DB::commit();

            return response()->json([
                'message' => __('E-Way Invoice updated successfully.'),
                'redirect' => route('business.eway-bills.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        $eway_bill = EwayBill::with('details')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Restore stock
            foreach ($eway_bill->details as $detail) {
                if ($detail->stock_id) {
                    $stock = Stock::find($detail->stock_id);
                    if ($stock) {
                        $stock->increment('productStock', $detail->quantity);
                    }
                }
            }

            $eway_bill->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('E-Way Bill deleted successfully.'),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getInvoice($id)
    {
        $eway_bill = EwayBill::with([
            'party',
            'business',
            'business.state',
            'user',
            'details',
            'details.product',
            'details.stock'
        ])->findOrFail($id);

        return view('ewaybill::invoice', compact('eway_bill'));
    }

    public function generatePDF($id)
    {
        $eway_bill = EwayBill::with([
            'party',
            'business',
            'business.state',
            'user',
            'details',
            'details.product',
            'details.stock'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('ewaybill::pdf', compact('eway_bill'));
        return $pdf->download('eway-invoice-' . $eway_bill->invoice_number . '.pdf');
    }

    public function productFilter(Request $request)
    {
        $total_products_count = Product::where('business_id', auth()->user()->business_id)
            ->whereHas('stocks', function ($s) {
                $s->where('productStock', '>', 0);
            })
            ->count();

        $products = Product::where('business_id', auth()->user()->business_id)
            ->whereHas('stocks', function ($s) use ($request) {
                $s->when($request->warehouse_id, function ($sw) use ($request) {
                    $sw->where('warehouse_id', $request->warehouse_id);
                })
                    ->where('productStock', '>', 0);
            })
            ->with(['stocks' => function ($s) use ($request) {
                $s->when($request->warehouse_id, function ($sw) use ($request) {
                    $sw->where('warehouse_id', $request->warehouse_id);
                })
                    ->where('productStock', '>', 0);
            }])
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('productName', 'like', '%' . $request->search . '%')
                        ->orWhere('productCode', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->category_id, function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->brand_id, function ($query) use ($request) {
                $query->where('brand_id', $request->brand_id);
            })
            ->latest()
            ->get();

        $categories = Category::where('business_id', auth()->user()->business_id)
            ->when($request->search, function ($query) use ($request) {
                $query->where('categoryName', 'like', '%' . $request->search . '%');
            })
            ->get();

        $brands = Brand::where('business_id', auth()->user()->business_id)
            ->when($request->search, function ($query) use ($request) {
                $query->where('brandName', 'like', '%' . $request->search . '%');
            })
            ->get();

        $total_products = $products->count();

        if ($request->ajax()) {
            return response()->json([
                'total_products' => $total_products,
                'total_products_count' => $total_products_count,
                'product_id' => $total_products == 1 ? $products->first()->id : null,
                'has_serial' => $total_products == 1 ? $products->first()->has_serial : null,
                'data' => view('ewaybill::sales.product-list', compact('products'))->render(),
                'categories' => view('ewaybill::sales.category-list', compact('categories'))->render(),
                'brands' => view('ewaybill::sales.brand-list', compact('brands'))->render(),
            ]);
        }

        return redirect(url()->previous());
    }

    public function categoryFilter(Request $request)
    {
        $search = $request->search;
        $categories = Category::where('business_id', auth()->user()->business_id)
            ->when($search, function ($query) use ($search) {
                $query->where('categoryName', 'like', '%' . $search . '%');
            })
            ->get();

        return response()->json([
            'categories' => view('ewaybill::sales.category-list', compact('categories'))->render(),
        ]);
    }

    public function brandFilter(Request $request)
    {
        $search = $request->search;
        $brands = Brand::where('business_id', auth()->user()->business_id)
            ->when($search, function ($query) use ($search) {
                $query->where('brandName', 'like', '%' . $search . '%');
            })
            ->get();

        return response()->json([
            'brands' => view('ewaybill::sales.brand-list', compact('brands'))->render(),
        ]);
    }
}
