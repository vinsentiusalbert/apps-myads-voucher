<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLbaCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_name',
        'sender_name',
        'message',
        'content_name',
        'recipient_total',
        'delivery_start_date',
        'delivery_end_date',
        'delivery_start_time',
        'delivery_end_time',
        'control_number',
        'location_name',
        'location_lat',
        'location_lng',
        'location_radius',
        'location_type',
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
            'location_lat' => 'float',
            'location_lng' => 'float',
            'location_radius' => 'integer',
            'gw_payload' => 'array',
            'gw_response' => 'array',
        ];
    }
}
