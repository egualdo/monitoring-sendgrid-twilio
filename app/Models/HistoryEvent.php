<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoryEvent extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable=[
            'sg_message_id',    //14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0
            'email',            //test@email.com
            'event',            //open,clicked...
    ];
}
