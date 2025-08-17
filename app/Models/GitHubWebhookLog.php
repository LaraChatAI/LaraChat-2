<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GitHubWebhookLog extends Model
{
    use HasFactory;

    protected $table = 'github_webhook_logs';

    protected $fillable = [
        'event_type',
        'delivery_id',
        'repository',
        'payload',
        'status',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}