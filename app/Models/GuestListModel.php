<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestListModel extends Model
{
    use HasFactory;
    public $table = 'guest_list';
    protected $fillable = ['id','shift_pattern', 'name', 'phone_number', 'position', 'bento_box', 'remark'];

}
