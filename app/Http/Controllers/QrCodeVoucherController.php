<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\GuestListModel;
use App\Models\QrCodeVoucherModel;
use App\Models\VoucherReportModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QrCodeVoucherController extends Controller
{
    //

    public function date(){
        $now = Carbon::now()->addDays(3);

        dd($now);
    } 


    public function index(Request $request){
        $skrg = Carbon::now();
        $bulan_tahun = Carbon::now()->addMonth(1)->format('Y-m');
        $tgl_exp = $bulan_tahun."-05";
        $cek = QrCodeVoucherModel::select('created_at')
                ->whereDate('created_at',Carbon::now()->addDays(3))->first();
        // dd($cek);
        if(empty($cek)){
            $guest = GuestListModel::select('id', 'name')->get();
            foreach($guest as $list){
               $skrg = Carbon::now()->addDays(3);
                for($j = 0; $j < 7; $j++){
                    for($i =0; $i < 3; $i++){
                        $data = QrCodeVoucherModel::create(
                            [
                                'id_guest_list' => $list->id,
                                'status' => 0,
                                'nominal' => 0,
                                'expired_date' =>$tgl_exp,
                                'created_at' => $skrg->startOfWeek()->addDays($j),
                                'updated_at' => $skrg->startOfWeek()->addDays($j)
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
            $user = Auth::user();
            $update_qr = QrCodeVoucherModel::select('*')
                    ->where('code',$code)
                    ->first();
            $update_qr->status = 1;
            $update_qr->nominal = 0;
            $update_qr->remark = "N/A";
            $update_qr->id_user = $user->id;
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
        $skrg = Carbon::now()->addDays(3);
        $data = QrCodeVoucherModel::select('guest_list.id','guest_list.name','qrcode_voucher.code','qrcode_voucher.expired_date','qrcode_voucher.created_at')
                ->join('guest_list','qrcode_voucher.id_guest_list','guest_list.id')
                ->where('guest_list.id', $id)
                ->whereDate('qrcode_voucher.created_at','>=',$skrg->startOfWeek())
                ->whereDate('qrcode_voucher.created_at','<=',$skrg->endOfWeek())
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
        $total_qr =  QrCodeVoucherModel::where('status',1)     
                        ->whereDate('updated_at', Carbon::now());
        if(Auth::user()->status ==1){
            $total_qr->where('id_user', Auth::user()->id);
        }
        $total_guest =  GuestListModel::count('id');
       
        $list_guest = GuestListModel::select('*')   
                    ->orderBy('created_at','DESC')
                    ->get();
        foreach($list_guest as $key => $list){
            $nominal_qr = QrCodeVoucherModel::select('status','nominal','id_user')
                        ->where('id_guest_list', $list->id)
                        ->whereDate('updated_at', Carbon::now());
            if(Auth::user()->status ==1){
                $nominal_qr->where('id_user', Auth::user()->id);
            }
            
            $list_data[$key] = array(
                'id'=> $list->id,
                'name' => $list->name,
                'position'=> $list->position,
                'phone_number'=> $list->phone_number,
                'nominal' => "Rp ". number_format($nominal_qr->sum('status') * 50000),
                'total_qr' => $nominal_qr->sum('status') ." Voucher",
                'updated_at' => Carbon::now()->format('Y-m-d')
            );
        }
        
        $pesan = array(
            'total_nominal' =>  number_format($total_qr->count('status') * 50000),
            'total_qr_use' => $total_qr->count('status'),
            'total_guest' => $total_guest,
            'guest_list'=> $list_data          
        );
        
        return response()->json($pesan, 200);
    }


    public function reportGuest($id, $date){
        $data = QrCodeVoucherModel::select('guest_list.*','qrcode_voucher.code','qrcode_voucher.status',"qrcode_voucher.updated_at", 'qrcode_voucher.created_at as terbit')
                ->join('guest_list','qrcode_voucher.id_guest_list','guest_list.id')
                ->where('status',1)
                ->where('id_guest_list', $id)
                ->whereDate('qrcode_voucher.updated_at',$date)
                ->orderBy('id','DESC');
        if(Auth::user()->status == 1){
            $data->where('qrcode_voucher.id_user', Auth::user()->id);
        }
        
        foreach($data->get() as $key => $list){
            $pesan[$key] = array(
                'remark' => $list->code.' (date of issue '. Carbon::parse($list->terbit)->format('d M Y')  .')',
                'date'=>  Carbon::parse($list->updated_at)->format('D, d M Y, H:i:s'),
                'nominal' => "Rp ". number_format($list->status * 50000),
                'code' => $list->code.' (date of issue '. $list->terbit .')'
            );
        }

        return response()->json($pesan, 200);
    }

    public function reportByDate($date){
        $total_qr =  QrCodeVoucherModel::where('status',1)     
                        ->whereDate('updated_at',$date);
        if(Auth::user()->status ==1){
            $total_qr->where('id_user', Auth::user()->id);
        }
        $total_guest =  GuestListModel::count('id');
       
        $list_guest = GuestListModel::select('*')   
                    ->orderBy('created_at', 'DESC')
                    ->get();
        foreach($list_guest as $key => $list){
            $nominal_qr = QrCodeVoucherModel::select('status','nominal','id_user')
                        ->where('id_guest_list', $list->id)
                        ->whereDate('updated_at', $date);
            if(Auth::user()->status ==1){
                $nominal_qr->where('id_user', Auth::user()->id);
            }
            
            $list_data[$key] = array(
                'id'=> $list->id,
                'name' => $list->name,
                'position'=> $list->position,
                'phone_number'=> $list->phone_number,
                'nominal' => "Rp ". number_format($nominal_qr->sum('status') * 50000),
                'total_qr' => $nominal_qr->sum('status') ." Voucher",
                'updated_at' => $date
            );
        }
        
        $pesan = array(
            'total_nominal' =>  number_format($total_qr->count('status') * 50000),
            'total_qr_use' => $total_qr->count('status'),
            'total_guest' => $total_guest,
            'guest_list'=> $list_data,
            'updated_at' => $date          
        );
        
        return response()->json($pesan, 200);
    }

    public function generateQrUser($id){

        $skrg = Carbon::now();
        $bulan_tahun = Carbon::now()->addMonth(1)->format('Y-m');
        $tgl_exp = $bulan_tahun."-05";
        $cek = QrCodeVoucherModel::select('created_at')
                ->whereDate('created_at',Carbon::now()->addDays(3))
                ->where('id_guest_list', $id)
                ->first();
        // dd($cek);
        if(empty($cek)){
            // $guest = GuestListModel::select('id', 'name')
            //         ->where('id_guest_list',$id)
            //         ->first();
            
               $skrg = Carbon::now()->addDays(3);
                for($j = 0; $j < 7; $j++){
                    for($i =0; $i < 3; $i++){
                        $data = QrCodeVoucherModel::create(
                            [
                                'id_guest_list' => $id,
                                'status' => 0,
                                'nominal' => 0,
                                'expired_date' =>$tgl_exp,
                                'created_at' => $skrg->startOfWeek()->addDays($j),
                                'updated_at' => $skrg->startOfWeek()->addDays($j)
                            ]
                        );
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
   
}
