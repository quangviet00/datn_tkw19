<?php

namespace App\Http\Controllers\Employer;

use App\Enums\StatusCode;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\AccountPayment;
use App\Models\Employer;
use App\Models\Packageoffer;
use App\Models\packageofferbought;
use App\Models\Vnpay;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PackageController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public Vnpay $vnpay;
    public $vnp_HashSecret = 'KNAREAARTPBAELKXTPLZKBUMSTCJHIYE';
    public Packageoffer $package;
    public function __construct(Vnpay $vnpay, Packageoffer $package)
    {
        $this->vnpay = $vnpay;
        $this->package = $package;
    }
    public function index()
    {
        $pachageForEmployer = Packageofferbought::leftjoin('admin_package_offer', 'admin_package_offer.id', '=', 'package_offer_bought.package_offer_id')
            ->leftjoin('users', 'users.id', '=', 'package_offer_bought.company_id')
            ->leftjoin('employer', 'employer.user_id', '=', 'users.id')
            ->select('admin_package_offer.name as name_package', 'admin_package_offer.price as price', 'package_offer_bought.id as id', 'package_offer_bought.package_offer_id as package_id', 'package_offer_bought.start_time as start_time', 'package_offer_bought.end_time as end_time', 'package_offer_bought.lever', 'package_offer_bought.status')
            ->orderby('package_offer_bought.status', 'ASC')
            ->where('package_offer_bought.company_id', Auth::guard('user')->user()->id)
            ->get();

        $accPayment = AccountPayment::where('user_id', Auth::guard('user')->user()->id)->first();
        $package = Packageoffer::select('*')->whereNotIn('id', $pachageForEmployer->pluck('package_id'))->with(['timeofer', 'leverPackage'])->get();
        return view('employer.package.index', [
            'data' => $package,
            'accPayment' => $accPayment,
            'pachageForEmployer' => $pachageForEmployer,
            'total' => AccountPayment::where('user_id', Auth::guard('user')->user()->id)->with('user')->first(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showDetail($id)
    {
        return response()->json([
            'data' => $this->package->where('id', $id)->with('timeofer')->first()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function Payment(Request $request)
    {
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = route('employer.package.payment.return');
        $vnp_TmnCode = "S50PEHFY"; //Mã website tại VNPAY 
        $vnp_HashSecret = $this->vnp_HashSecret; //Chuỗi bí mật
        $vnp_TxnRef = rand(0000, 9999);
        $vnp_OrderInfo = $request->name . ',' . $request->lerve_package . ',' . $request->id;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount =  $request->price * 100;
        $vnp_Locale = 'vn';
        $vnp_IpAddr = '192.168.1.6';

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
            $inputData['vnp_Bill_State'] = $vnp_Bill_State;
        }


        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        $returnData = array(
            'code' => '00', 'message' => 'success', 'data' => $vnp_Url
        );
        if (isset($_POST['redirect'])) {
            header('Location: ' . $vnp_Url);
            die();
        } else {
            echo json_encode($returnData);
        }
    }
    public function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }
    public function vnpayReturn(Request $request)
    {
        $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        // $url_ipn = $request->getRequestUri();
        // dd($request->getRequestUri());
        $url_ipn = route('employer.package.payment.output', $_GET);
        // dd($url_ipn);
        $secureHash = hash_hmac('sha512', $hashData,  $this->vnp_HashSecret);
        if ($secureHash == $vnp_SecureHash) {
            if ($_GET['vnp_ResponseCode'] == '00') {
                $this->setFlash(__('Giao dịch thành công'));
                header('Location:' . $url_ipn);
                die;
            } else {
                $this->setFlash(__('Giao dịch bị hủy bỏ'), 'error');
            }
        } else {
            $this->setFlash(__('chu ky khong hop le'), 'error');
        }
        return redirect()->route('employer.package.index');
    }
    public function vnpayOutput(Request $request)
    {
        $inputData = array();
        $returnData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        $lever_package = explode(',', $request->vnp_OrderInfo)[1];
        $secureHash = hash_hmac('sha512', $hashData, $this->vnp_HashSecret);
        $vnpTranId = $inputData['vnp_TransactionNo']; //Mã giao dịch tại VNPAY
        $vnp_BankCode = $inputData['vnp_BankCode']; //Ngân hàng thanh toán
        $vnp_Amount = $inputData['vnp_Amount'] / 100; // Số tiền thanh toán VNPAY phản hồi
        $Status = 0; // Là trạng thái thanh toán của giao dịch chưa có IPN lưu tại hệ thống của merchant chiều khởi tạo URL thanh toán.
        $orderId = $inputData['vnp_TxnRef'];
        try {
            //Check Orderid    
            //Kiểm tra checksum của dữ liệu
            if ($secureHash == $vnp_SecureHash) {

                $invoice = Packageoffer::where('id', explode(',', $request->vnp_OrderInfo)[2])->first(); //$orderId
                if ($invoice != NULL) {
                    if ($invoice["price"] == $vnp_Amount) //Kiểm tra số tiền thanh toán của giao dịch: giả sử số tiền kiểm tra là đúng. //$order["Amount"] == $vnp_Amount
                    {
                        if ($invoice["status"] == NULL && $invoice["status"] == 0) {
                            if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
                                $Status = 1; // Trạng thái thanh toán thành công
                            } else {
                                $Status = 2; // Trạng thái thanh toán thất bại / lỗi
                            }
                            $package = new packageofferbought();
                            $employer = Employer::where('user_id', Auth::guard('user')->user()->id)->first();
                            $package->company_id = Auth::guard('user')->user()->id;
                            $package->package_offer_id = 1;
                            $package->status = $Status;
                            $package->start_time = Carbon::parse(Carbon::now());
                            $employer->prioritize += $lever_package;
                            $employer->position = 1;
                            if ($lever_package == 1) {
                                $package->end_time = Carbon::parse(Carbon::now())->addDay(1)->format('Y-m-d');
                                $package->lever = $lever_package;
                            } else if ($lever_package == 2) {
                                $package->end_time = Carbon::parse(Carbon::now())->addDay(7)->format('Y-m-d');
                                $package->lever = $lever_package;
                            } else if ($lever_package == 3) {
                                $package->end_time = Carbon::parse(Carbon::now())->addDay(30)->format('Y-m-d');
                                $package->lever = $lever_package;
                            } else if ($lever_package == 4) {
                                $package->end_time = Carbon::parse(Carbon::now())->addDay(365)->format('Y-m-d');
                                $package->lever = $lever_package;
                            }
                            $package->save();
                            $employer->save();
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Xác nhận thành công';
                        } else {
                            $returnData['RspCode'] = '02';
                            $returnData['Message'] = 'Đơn đặt hàng đã được xác nhận';
                        }
                    } else {
                        $returnData['RspCode'] = '04';
                        $returnData['Message'] = 'Số tiền không hợp lệ';
                    }
                } else {
                    $returnData['RspCode'] = '01';
                    $returnData['Message'] = 'Không tồn tại hóa đơn';
                }
            } else {
                $returnData['RspCode'] = '97';
                $returnData['Message'] = 'Chữ ký không hợp lệ';
            }
        } catch (Exception $e) {
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Lỗi không xác định';
        }
        if ($returnData['RspCode'] != 00) {
            $this->setFlash($returnData['Message'], 'error');
        } else {
            $this->setFlash($returnData['Message']);
        }
        return redirect()->route('employer.package.index');
    }
    public function byAccount(Request $request)
    {

        try {
            $employer = Employer::where('user_id', Auth::guard('user')->user()->id)->first();
            $package = new packageofferbought();
            $package->company_id = Auth::guard('user')->user()->id;
            $package->package_offer_id = $request['data']['id'];
            $package->status = 1;
            $package->start_time =
                Carbon::parse(Carbon::now());
            if ($request['data']['timeofer']['id'] == 1) {
                $employer->prioritize += 1;
                $package->end_time = Carbon::parse(Carbon::now())->addDay(1)->format('Y-m-d');
                $package->lever = 1;
            } else if ($request['data']['timeofer']['id'] == 2) {
                $employer->prioritize += 2;
                $package->end_time = Carbon::parse(Carbon::now())->addDay(7)->format('Y-m-d');
                $package->lever = 2;
            } else if ($request['data']['timeofer']['id'] == 3) {
                $employer->prioritize += 3;
                $package->end_time = Carbon::parse(Carbon::now())->addDay(30)->format('Y-m-d');
                $package->lever = 3;
            } else if ($request['data']['timeofer']['id'] == 4) {
                $employer->prioritize += 4;
                $package->end_time = Carbon::parse(Carbon::now())->addDay(365)->format('Y-m-d');
                $package->lever = 4;
            }
            $package->save();
            $employer->save();
            $account = AccountPayment::where('user_id', Auth::guard('user')->user()->id)->first();
            $account->surplus = $account->surplus - $request['data']['price'];
            $account->save();
            return response()->json([
                'message' => 'Thanh toán đơn hàng thành công',
                'status' => StatusCode::OK,
            ], StatusCode::OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Có một lỗi không xác định đã xảy ra',
                'message' =>  StatusCode::FORBIDDEN,
            ], StatusCode::OK,);
        }
    }
}