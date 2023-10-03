<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherReportModel extends Model
{
    use HasFactory;
    public $table = 'voucher_use_report';
    protected $fillable = ['id_guest_list', 'name', 'position', 'nominal', 'remark'];
   
}
