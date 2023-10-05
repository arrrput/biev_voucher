<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\GuestListController;
use App\Http\Controllers\QrCodeVoucherController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [UserController::class,'login'])->name('login');



// generate QR
Route::get('/create_voucher', [QrCodeVoucherController::class, 'index']);

//Guest List Create
Route::post('/guest_list/store', [GuestListController::class, 'store']);
//Guest List Delete
Route::delete('/guest_list/delete/{id}', [GuestListController::class, 'destroy']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [UserController::class,'logout']); 
    
    Route::post('/import_guest', [GuestListController::class,'import_excel']);  
    
    Route::post('/use_voucher', [QrCodeVoucherController::class,'useVoucher']);  
    //Guest List 
    Route::get('/guest_list', [GuestListController::class, 'index']);
    

    Route::get('/get_qr/{id}', [QrCodeVoucherController::class, 'getQr']);
    Route::get('/voucher/{code}', [QrCodeVoucherController::class, 'getVoucher']);

    Route::get('/report_voucher/{id}', [QrCodeVoucherController::class, 'reportGuest']);

    Route::get('/guest_voucher/{id}', [QrCodeVoucherController::class, 'getUserQR']);
    Route::get('/report_voucher', [QrCodeVoucherController::class, 'reportQr']);
});
