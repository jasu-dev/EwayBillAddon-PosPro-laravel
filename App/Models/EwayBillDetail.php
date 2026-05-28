<?php

namespace Modules\EwayBill\App\Models;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EwayBillDetail extends Model
{
    use HasFactory;

    protected $table = 'eway_bill_details';

    protected $fillable = [
        'eway_bill_id',
        'product_id',
        'stock_id',
        'price',
        'quantity',
        'discount',
        'hsn_code',
        'tax_percent',
        'tax_amount',
        'total',
    ];

    protected $casts = [
        'price' => 'double',
        'quantity' => 'integer',
        'discount' => 'double',
        'tax_percent' => 'double',
        'tax_amount' => 'double',
        'total' => 'double',
    ];

    public function eway_bill(): BelongsTo
    {
        return $this->belongsTo(EwayBill::class, 'eway_bill_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
