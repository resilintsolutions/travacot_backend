<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PinnedHotel extends Model
{
    protected $guarded = [];
    public function hotel(){ return $this->belongsTo(Hotel::class); }
    public function user(){ return $this->belongsTo(\App\Models\User::class); }
}
