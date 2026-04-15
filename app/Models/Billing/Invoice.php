<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $connection = 'system';
    protected $table = 'invoices';
    protected $fillable = [
        'tenant_id', 'number', 'provider_invoice_id', 'amount_due', 'amount_paid',
        'tax_amount', 'discount_amount', 'currency', 'status', 'hosted_invoice_url',
        'invoice_pdf_url', 'period_start', 'period_end', 'line_items'
    ];
    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'line_items' => 'array',
    ];
}

