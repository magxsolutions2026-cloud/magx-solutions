<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'caption',
        'image_path',
        'platform',
        'posted_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}
