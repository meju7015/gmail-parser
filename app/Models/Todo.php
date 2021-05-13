<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $hidden = ['user_id'];

    protected $fillable = ['text', 'state'];

    protected function getCreatedAtAttribute()
    {
        return Carbon::create($this->attributes['created_at'])
            ->format('Y-m-d H:i:s');
    }

    protected function getUpdatedAtAttribute()
    {
        if ($this->attributes['updated_at'] !== null) {
            return Carbon::create($this->attributes['updated_at'])
                ->format('Y-m-d H:i:s');
        }

        return '';
    }
}
