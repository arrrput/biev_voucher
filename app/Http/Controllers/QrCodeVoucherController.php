<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\GuestListModel;
use App\Models\QrCodeVoucherModel;
use App\Models\VoucherReportModel;
use Illuminate\Support\Facades\DB;

class QrCodeVoucherController extends Controller
{
    //
    public function index(Request $request){

        
        $skrg = Carbon::now();
        $bulan_tahun = Carbon::now()->addMonth(1)->format('Y-m');
        $tgl_exp = $bulan_tahun."-05";
       
        $cek = QrCodeVoucherModel::select('created_at')
                ->whereDate('created_at',Carbon::today())->first();
        if(empty($cek)){
            $guest = GuestListModel::select('id', 'name')->get();
            foreach($guest as $list){
                for($j = 0; $j < 7; $j++){
                    for($i =0; $i < 3; $i++){
                        $data = QrCodeVoucherModel::create(
                            [
                                'id_guest_list' => $list->id,
                                'status' => 0,
                                'nominal' => 0,
                                'expired_date' =>$tgl_exp,
                                'created_at' => Carbon::now()->startOfWeek()->addDays($j),
                                'updated_at' => Carbon::now()->startOfWeek()->addDays($j)
                            ]
                        );
                    }
                }
                
            }
            $pesan = array('code' =>200,
                        'message' => 'QR Generate Succussfully'
            );
            return response()->json($pesan, 200);
        }else{
            $pesan = array('code' =>200,
                        'message' => 'QR was generate before'
            );
            return response()->json($pesan, 200);
        }        
        
    }

    public function getVoucher($code){
        $data =  QrCodeVoucherModel::select('*')
                ->join('guest_list','qrcode_voucher.id_guest_list', 'guest_list.id')
                ->where('code',$code)
                ->first();

        // dd($data);
        $skrg = Carbon::today();
        $status_exp = 0;
        if($data->expired_date < $skrg){
            $status_exp = 1;
        }

        if($data->status == 0){
            $update_qr = QrCodeVoucherModel::select('*')
                    ->where('code',$code)
                    ->first();
            $update_qr->status = 1;
            $update_qr->nominal = 0;
            $update_qr->remark = "N/A";
            $update_qr->save();
        }
        

        $pesan = array(
            'id' => $data->id,
            'id_guest_list' => $data->id_guest_list,
            'code' => $data->code,
            'status' => $data->status,
            'nominal' => $data->nominal,
            'expired_date' => Carbon::parse($data->created_at)->format('D, d M Y'),
            'created_at' => Carbon::parse($data->created_at)->format('d M Y H:i'),
            'remark' => $data->remark,
            'shift_pattern' => $data->shift_pattern,
            'name' => $data->name,
            'phone_number' => $data->phone_number,
            'position' => $data->position,
            'bento_box' => $data->bento_box,
            'status_exp' => $status_exp,
        );
        return response()->json($pesan, 200);
    }

    public function useVoucher(Request $request){
       
        $store = VoucherReportModel::create([
            'id_guest_list' => $request->id_guest_list,
            'name' => $request->name,
            'position' => $request->position,
            'nominal' => $request->nominal,
            'remark' => $request->remark,
        ]);

        $pesan = array(
            'code'=>200,
            'message' => 'Voucher Successfully use!'
        );
        return response()->json($pesan, 200);
        
    }

    public function getQr($id){
        $data = QrCodeVoucherModel::select('*')
                ->where('id', $id)
                ->whereDate('created_at',Carbon::today())
                ->get();
        
        return response()->json($data, 200);
    }

    public function getUserQR($id){
        $data = QrCodeVoucherModel::select('guest_list.id','guest_list.name','qrcode_voucher.code','qrcode_voucher.expired_date','qrcode_voucher.created_at')
                ->join('guest_list','qrcode_voucher.id_guest_list','guest_list.id')
                ->where('guest_list.id', $id)
                ->whereDate('qrcode_voucher.created_at','>=',Carbon::now()->startOfWeek())
                ->whereDate('qrcode_voucher.created_at','<=',Carbon::now()->endOfWeek())
                ->get();
        foreach($data as $key => $list){
            $response[$key] = array(
                'id'=> $list->id,
                'name'=> $list->name,
                'code'=> $list->code,
                'expired_date'=> Carbon::parse($list->expired_date)->format('d M Y'),
                'created_at'=>   Carbon::parse($list->created_at)->format('d M Y ') 
            );
        }
        return response()->json($response, 200);
    }

    public function reportQr(){
        $total_nominal = VoucherReportModel::select('nominal')
                    ->sum('nominal');

        $total_qr =  QrCodeVoucherModel::where('status',1)
                        ->count('status');
        $total_guest =  GuestListModel::count('id');
       
        $list_guest = GuestListModel::select('*')->get();
        foreach($list_guest as $key => $list){
            $nominal_qr = VoucherReportModel::select('nominal')->where('id_guest_list', $list->id)
                        ->sum('nominal');
            $list_data[$key] = array(
                'id'=> $list->id,
                'name' => $list->name,
                'position'=> $list->position,
                'phone_number'=> $list->phone_number,
                'nominal' => "Rp ". number_format($nominal_qr)
            ) ;
        }
        
        $pesan = array(
            'total_nominal' => number_format($total_nominal),
            'total_qr_use' => $total_qr,
            'total_guest' => $total_guest,
            'guest_list'=> $list_data
            
        );
        
        return response()->json($pesan, 200);
    }

    public function reportGuest($id){
        $data = VoucherReportModel::select('*')
                ->where('id_guest_list', $id)
                ->orderBy('id','DESC')
                ->get();

        foreach($data as $key => $list){
            $pesan[$key] = array(
                'remark' => $list->remark,
                'date'=>  Carbon::parse($list->updated_at)->format('D, d M Y, H:i:s'),
                'nominal' => "Rp ". number_format($list->nominal)
            ) ;
        }

        return response()->json($pesan, 200);
    }
   
}
