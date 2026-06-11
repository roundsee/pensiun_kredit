<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailMergeTemplate extends Model
{
    use HasFactory;

    protected $table = 'mail_merge_templates';

    protected $fillable = [
        'name',
        'document_type',
        'source_pdf_path',
        'generated_view_path',
            'existing_blade_view',
        'template_html',
        'slot_definitions',
        'mappings',
    ];

    protected $casts = [
        'slot_definitions' => 'array',
        'mappings' => 'array',
    ];
}