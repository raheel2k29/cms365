<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $guarded = [];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function email()
    {
        return $this->belongsTo(Email::class);
    }
}
