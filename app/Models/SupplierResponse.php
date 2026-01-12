<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SupplierResponse extends Model
{
    protected $guarded = [];
    protected $casts = ['request_payload' => 'array'];
}
