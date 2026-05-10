<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'gw_template_id',
        'name',
        'template_type',
        'category',
        'language',
        'header_type',
        'asset_path',
        'body',
        'footer',
        'buttons',
        'status',
        'gw_payload',
        'gw_response',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'buttons' => 'array',
            'gw_payload' => 'array',
            'gw_response' => 'array',
        ];
    }
}
