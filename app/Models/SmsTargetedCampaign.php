<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTargetedCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_name',
        'sender_name',
        'message',
        'recipient_total',
        'delivery_date',
        'delivery_time',
        'control_number',
        'channel',
        'mms_file',
        'gw_payload',
        'gw_response',
        'gw_campaign_id',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'recipient_total' => 'integer',
            'gw_payload' => 'array',
            'gw_response' => 'array',
        ];
    }
}
