<?php

namespace App\Models;

use App\Models\Blockchain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'created_amount', 'blockchain_id', 'img_path', 'current_amount', 'user_id', 'token_id', 'original_address', 'sent_to_address', 'properties', 'local_img_path', 'badge_created_at'];

    protected $casts = [
        'badge_created_at' => 'datetime:d-m-Y',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function blockchain(): BelongsTo
    {
        return $this->belongsTo(Blockchain::class);
    }
}
