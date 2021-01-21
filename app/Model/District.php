<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
     protected $table = 'district';
    protected $fillable = ['name', 'STOCode','DTOCode'];
}
