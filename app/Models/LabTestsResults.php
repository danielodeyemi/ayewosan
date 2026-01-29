<?php

namespace App\Models;

use App\Models\Bills;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestsResults extends Model
{
    use HasFactory;

    protected $fillable = [
        'bills_id',
        'result_date',
        'delivery_date_time',
        'performed_by',
        'delivered_by',
        'result_content',
        'report_remarks',
        'result_status',
    ];

    protected $casts = [
        'result_date' => 'datetime',
        'delivery_date_time' => 'datetime',
    ];

    public function bills()
    {
        return $this->belongsTo(Bills::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function deliveredBy()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function template()
    {
        return $this->belongsTo(LabTestsResultsTemplate::class);
    }
}
