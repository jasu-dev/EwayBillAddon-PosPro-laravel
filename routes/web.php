<?php

use Illuminate\Support\Facades\Route;
use Modules\EwayBill\App\Http\Controllers\AcnooEwayBillController;
use Modules\EwayBill\App\Http\Controllers\EwayCartController;

Route::group(['domain' => request()->getHost(), 'as' => 'business.', 'prefix' => 'business', 'middleware' => ['users', 'expired']], function () {
    // E-Way Bills resource
    Route::resource('eway-bills', AcnooEwayBillController::class)->except(['show']);
    Route::get('eway-bills/{id}/invoice', [AcnooEwayBillController::class, 'getInvoice'])->name('eway-bills.invoice');
    Route::get('eway-bills/{id}/pdf', [AcnooEwayBillController::class, 'generatePDF'])->name('eway-bills.pdf');

    // AJAX filters for product search panel
    Route::post('eway-bills/product-filter', [AcnooEwayBillController::class, 'productFilter'])->name('eway-bills.product-filter');
    Route::post('eway-bills/category-filter', [AcnooEwayBillController::class, 'categoryFilter'])->name('eway-bills.category-filter');
    Route::post('eway-bills/brand-filter', [AcnooEwayBillController::class, 'brandFilter'])->name('eway-bills.brand-filter');

    // Eway Cart resource
    Route::resource('eway-carts', EwayCartController::class);
    Route::post('eway-carts/remove-all', [EwayCartController::class, 'removeAllCart'])->name('eway-carts.remove-all');
});
