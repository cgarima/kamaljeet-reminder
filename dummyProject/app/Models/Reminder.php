<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reminder extends Model
{
    use HasFactory;
    protected $table = 'reminders';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'date_of_origin',
        'status',
    ];

    public function setDateOfOriginAttribute($value)
    {
        $this->attributes['date_of_origin'] = Carbon::createFromFormat('m/d/Y', $value)->format('Y-m-d');
    }

    public function getDateOfOriginAttribute()
    {
        return Carbon::createFromFormat('Y-m-d', $this->attributes['date_of_origin'])->format('m/d/Y');
    }
}
