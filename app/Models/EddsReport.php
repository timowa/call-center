<?php

namespace App\Models;

use App\Condition;
use Illuminate\Database\Eloquent\Model;

class EddsReport extends Model
{
    protected $fillable = [
        'type',
        'company_id',
        'instruction',
        'message',
        'additional_info',
        'elimination_datetime',
        'is_consultation',
        'results',
        'responses',
    ];
    protected $casts = [
        'is_consultation' => 'boolean',
        'results' => 'array',
        'responses' => 'array',
        'condition' => Condition::class
    ];
    protected $guarded = ['condition'];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}
