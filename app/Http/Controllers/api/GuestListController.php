<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\GuestListModel;
use App\Imports\GuestListImport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class GuestListController extends Controller
{
    //
    public function index(){
        $list = GuestListModel::select('*')->get();

        return response()->json($list, 200);
    }

    public function import_excel(Request $request) 
	{
		// validasi
		$this->validate($request, [
			'file' => 'required|mimes:csv,xls,xlsx'
		]);
		Excel::import(new GuestListImport, $request->file('file')->store('temp') );
		// dd($file);
	}
}
