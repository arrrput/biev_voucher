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

	public function store(Request $request){

		$store = GuestListModel::updateOrCreate([
				'id' => $request->id
			],[
			'shift_pattern' =>$request->shift_pattern,
			'name' => $request->name,
			'phone_number' => $request->phone_number,
			'position' => $request->position,
			'bento_box' => $request->bento_box,
			'remark' => $request->remark
		]);

		$pesan = array(
			'code' => 200,
			'message' => 'Data berhasil ditambahkan.'
		);

		return response()->json($store, 200);

	}

	public function destroy($id){
        GuestListModel::find($id)->delete($id);

            return Response()->json([
				'code' => 200,
                'message' => 'Data deleted successfully!'
            ], 200);
    }
}
