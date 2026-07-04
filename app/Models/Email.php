<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $fillable = [
        'quote_id',
        'graph_message_id',
        'conversation_id',
        'thread_type',
        'direction',
        'from_name',
        'from_email',
        'to_email',
        'subject',
        'body_html',
        'body_text',
        'has_attachments',
        'sent_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'has_attachments' => 'boolean',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
