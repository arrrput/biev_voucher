<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Monurakkaya\Lucg\Traits\HasUniqueCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QrCodeVoucherModel extends Model
{
    use HasUniqueCode;
    use HasFactory;

    public $table = 'qrcode_voucher';
    protected $fillable = ['id_guest_list', 'code', 'status', 'nominal', 'remark', 'expired_date'];
    
    protected static function uniqueCodeColumnName()
    {
        return 'code';
    }

    protected static function uniqueCodeType()
    {
        return 'random_uppercase';
    }
    protected static function uniqueCodeLength()
    {
        return 20;
    }
}
