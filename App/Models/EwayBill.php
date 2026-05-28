<?php

namespace Modules\EwayBill\App\Models;

use App\Models\Business;
use App\Models\Party;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EwayBill extends Model
{
    use HasFactory;

    protected $table = 'eway_bills';

    protected $fillable = [
        'business_id',
        'party_id',
        'user_id',
        'vat_id',
        'invoice_number',
        'invoice_date',
        'supply_type',
        'sub_type',
        'document_type',
        'from_name',
        'from_gstin',
        'from_address',
        'from_pincode',
        'from_state',
        'to_name',
        'to_gstin',
        'to_address',
        'to_pincode',
        'to_state',
        'transporter_name',
        'transporter_id',
        'transport_mode',
        'distance',
        'vehicle_number',
        'vehicle_type',
        'sub_total',
        'discount_amount',
        'tax_amount',
        'shipping_charge',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'vat_id' => 'integer',
        'distance' => 'integer',
        'sub_total' => 'double',
        'discount_amount' => 'double',
        'tax_amount' => 'double',
        'shipping_charge' => 'double',
        'total_amount' => 'double',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vat(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Vat::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(EwayBillDetail::class, 'eway_bill_id', 'id');
    }
}
