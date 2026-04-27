<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeRequest extends Model
{
    protected $fillable = [
        'member_id',
        'field_name',
        'old_value',
        'new_value',
        'reason',
        'status',
        'admin_notiz',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
