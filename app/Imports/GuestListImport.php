<?php

namespace App\Imports;

use App\Models\GuestListModel;
use Maatwebsite\Excel\Concerns\ToModel;

class GuestListImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new GuestListModel([
            //
            'shift_pattern' => $row[0],
            'name' => $row[1], 
            'phone_number' => $row[2], 
            'position' => $row[3], 
            'bento_box' => $row[4], 
        ]);

    }
}
