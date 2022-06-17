<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountTransaction;
use App\BusinessPartner;
use App\BusinessPartnerPayments;
use App\BusinessPartnerTransactions;
use App\TransactionPayment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use App\User;
use Exception;
use Twilio\Rest\Preview\TrustedComms\BusinessPage;

class BusinessPartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $business_partners = BusinessPartner::where("is_active", 0)
            ->where('business_id', $business_id);
        //return $business_partners;
        if (request()->ajax()) {
            $business_partners = BusinessPartner::where("is_active", 0)->where('business_id', $business_id); //where('business_id', $business_id);
            return Datatables::of($business_partners)
                ->editColumn('action', function ($row) {
                    $delete_btn = '<a href="' . action('BusinessPartnerController@show', [$row->id]) . '" class="btn btn-primary btn-sm" >' . __('business_partner.view') . '</a>';
                    $delete_btn .= '<a href="' . action('BusinessPartnerController@showEdit', [$row->id]) . '" class="btn btn-warning btn-sm" >' . __('business_partner.edit') . '</a>';
                    $delete_btn .= '<button row="' . $row->id . '" href="' . action('BusinessPartnerController@DeletePartner') . '" class="btn btn-danger btn-sm delete_partner" >' . __('business_partner.delete') . '</button>';
                    $delete_btn .= '<a href="' . action('BusinessPartnerController@Transactions', [$row->id]) . '" class="btn btn-info btn-sm" >' . __('business_partner.transactions') . '</a>';
                    //  $edit_btn='<a href="' . action('BusinessPartnerController@edit',[$row->id]) . '" class="btn btn-info btn-sm" >edit</a>';
                    return $delete_btn; //. $edit_btn;
                })
                ->addColumn('id', function ($row) {
                    return $row->id;
                })
                ->addColumn('name', function ($row) {
                    return $row->name;
                })
                ->addColumn('mobile', function ($row) {
                    return $row->mobile;
                })
                ->addColumn('address', function ($row) {
                    return $row->address;
                })
                ->addColumn('balance', function ($row) {
                    // Get Open balance
                    $business_partner = BusinessPartner::where('id', $row->id)->first();

                    // Get Payments 
                    $business_pyments = BusinessPartnerPayments::where('owner',  $row->id)->where('is_active', 0)->get();

                    $PymentId = [];
                    foreach ($business_pyments as $business_pyment) {
                        array_push($PymentId, $business_pyment->payment_id);
                    }

                    // Get Payment frmo account transactions
                    $account_transactions = AccountTransaction::whereIn('id', $PymentId)->get();

                    // loop and calcualte balance
                    $credit = 0;
                    $debit = 0;
                    foreach ($account_transactions as $account_transaction) {
                        if ($account_transaction->type == "credit") {
                            $credit += $account_transaction->amount;
                        }
                        if ($account_transaction->type == "debit") {
                            $debit += $account_transaction->amount;
                        }
                    }

                    // MAtch with open balance
                    if ($business_partner->type == "credit") {
                        $credit += $business_partner->open_balance;
                    }
                    // MAtch with open balance
                    if ($business_partner->type == "debit") {
                        $debit += $business_partner->open_balance;
                    }

                    $final_amount = $credit - $debit;
                    return $final_amount;
                })
                ->addColumn('created_by', function ($row) {
                    $UserName = User::where('id', $row->created_by)->first()->username;
                    return $UserName;
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at;
                })
                ->make(true);
        }

        return view('business_partner.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('business_partner.create');
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            // $ID = $request->input('id');
            $name = $request->input('name');
            $mobile = $request->input('mobile');
            $address = $request->input('address');
            $type = $request->input('type');
            $open_balance = $request->input('open_balance');
            $Data = array('name' =>  $name, 'mobile' =>  $mobile, 'address' => $address, 'type' => $type, 'open_balance' => $open_balance);
            $business_partner_save = new BusinessPartner();
            $business_partner_save['name'] = $name;
            $business_partner_save['mobile'] = $mobile;
            $business_partner_save['address'] = $address;
            $business_partner_save['business_id'] = $business_id;
            $business_partner_save['type'] = $type;
            $business_partner_save['open_balance'] = $open_balance;
            $business_partner_save['created_by'] = $request->user()->id;
            $business_partner_save->save();
            // ::create('id', $ID)->update($Data);
            $output = [
                'success' => True,
                'msg' => ''
            ];
        } catch (Exception $e) {
            $output = [
                'success' => False,
                'msg' => ''
            ];
        }
        return redirect('/BusinessPartner')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //

        $business_id = request()->session()->get('user.business_id');
        $business_partners = BusinessPartner::where('id', $id)->where('is_active', 0)->where('business_id', $business_id)->first();
        return view('business_partner.show')->with('business_partners', $business_partners);
        // return "this is show";
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showEdit($id)
    {
        //

        $business_id = request()->session()->get('user.business_id');
        $business_partners = BusinessPartner::where('id', $id)->where('is_active', 0)->where('business_id', $business_id)->first();
        return view('business_partner.edit')->with('business_partners', $business_partners);
        // return "this is show";
    }


    public function Transactions($id)
    {


        $accounts = Account::all();

        $business_id = request()->session()->get('user.business_id');

        // Get Open balance
        $business_partner = BusinessPartner::where('id', $id)->where('business_id', $business_id)->first();

        // Get Payments 
        $business_pyments = BusinessPartnerPayments::where('owner', $id)->where('is_active', 0)->where('business_id', $business_id)->get();

        $PymentId = [];
        foreach ($business_pyments as $business_pyment) {
            array_push($PymentId, $business_pyment->payment_id);
        }

        // Get Payment frmo account transactions
        $account_transactions = AccountTransaction::whereIn('id', $PymentId)->get();

        // loop and calcualte balance
        $credit = 0;
        $debit = 0;
        foreach ($account_transactions as $account_transaction) {
            if ($account_transaction->type == "credit") {
                $credit += $account_transaction->amount;
            }
            if ($account_transaction->type == "debit") {
                $debit += $account_transaction->amount;
            }
        }

        // MAtch with open balance
        if ($business_partner->type == "credit") {
            $credit += $business_partner->open_balance;
        }
        // MAtch with open balance
        if ($business_partner->type == "debit") {
            $debit += $business_partner->open_balance;
        }

        $final_amount = $credit - $debit;
        //$accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);
        $business_partners = BusinessPartner::where('id', $id)->where('is_active', 0)->where('business_id', $business_id)->first();
        return view('business_partner.transactions')->with('business_partners', $business_partners)->with('accounts', $accounts)
            ->with('final_amount', $final_amount)->with('business_partner',$business_partner);
    }



    public function DeleteTransaction(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');
        try {




            $payment_id = $request->input('payment_id');
            $id = $request->input('owner');
            // $AccountTransactionX = AccountTransaction::where('id', $payment_id)->get(); //->delete();
            AccountTransaction::where('id', $payment_id)->delete();
            // $BusinessPartnerTransactionsDataْْX = BusinessPartnerTransactions::where('transaction_id', $payment_id)->get(); //update(['is_active' => 1]);
            BusinessPartnerPayments::where('payment_id', $payment_id)->update(['is_active' => 1]);
            $tx = BusinessPartnerPayments::where('payment_id', $payment_id)->first();
            TransactionPayment::where('id', $tx->transaction_id)->delete();


            // Get Open balance
            $business_partner = BusinessPartner::where('id', $id)->where('business_id', $business_id)->first();

            // Get Payments 
            $business_pyments = BusinessPartnerPayments::where('owner', $id)->where('is_active', 0)->where('business_id', $business_id)->get();

            $PymentId = [];
            foreach ($business_pyments as $business_pyment) {
                array_push($PymentId, $business_pyment->payment_id);
            }

            // Get Payment frmo account transactions
            $account_transactions = AccountTransaction::whereIn('id', $PymentId)->get();

            // loop and calcualte balance
            $credit = 0;
            $debit = 0;
            if (!is_null($account_transactions)) {
                foreach ($account_transactions as $account_transaction) {
                    if ($account_transaction->type == "credit") {
                        $credit += $account_transaction->amount;
                    }
                    if ($account_transaction->type == "debit") {
                        $debit += $account_transaction->amount;
                    }
                }
            }

            // MAtch with open balance
            if (!is_null($business_partner->type)) {
                if ($business_partner->type == "credit") {
                    $credit += $business_partner->open_balance;
                }
                // MAtch with open balance
                if ($business_partner->type == "debit") {
                    $debit += $business_partner->open_balance;
                }
            }

            $final_amount = $credit - $debit;

            $output = [
                'success' => True,
                'msg' => 'Done',
                'final_amount' => $final_amount
            ];
        } catch (Exception $e) {
            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
                'final_amount' => "Error"
            ];
        }
        return $output;
    }

    public function getCredit($id)
    {
        $credit = BusinessPartnerTransactions::where('owner', $id)->get();
        if (request()->ajax()) {
            $credit_ids = BusinessPartnerTransactions::where('owner', $id)->where('is_active', 0)
                ->where('type', 'debit')->get(); //where('business_id', $business_id);
            $ids = [];
            foreach ($credit_ids as $credit_id) {
                array_push($ids, $credit_id->transaction_id);
            }
            $Account_Trasaction = AccountTransaction::whereIn('id', $ids)->get();
            return Datatables::of($Account_Trasaction)
                ->editColumn('action', function ($row) {
                    $payment_made = AccountTransaction::where('note', "BusinessPartner_" . $row->id)->get();
                    $paymet_view = 0;
                    foreach ($payment_made as $line) {
                        $paymet_view += $line->amount;
                    }

                    $max = $row->amount - $paymet_view;

                    $html = '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                            data-toggle="dropdown" aria-expanded="false">' .
                        __("messages.actions") .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    $html .= '<li><a href="' . action('TransactionPaymentController@getPayContactDue_Partner', [$row->id]) . '?type=sell&max=' . $max . '" class="pay_sale_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>  قبض من المتسلف</a></li>';
                    $html .= '<li><a href="' . action('BusinessPartnerController@showPayments', [$row->id]) . '"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>ShowPayments</a></li>';
                    if ($max == $row->amount) {
                        $html .= '<li><a payment_id="' . $row->id . '" class="delete_transaction" link="' . action('BusinessPartnerController@DeleteTransaction') . '"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>Delete</a></li>';
                    }
                    $html .= "</a></li>";
                    return $html;
                })
                ->addColumn('amount', function ($row) {
                    //business_transaction  
                    $key = "BusinessPartner_" . $row->id;
                    $payment_made = AccountTransaction::where('note', $key)->get();
                    $paymet_view = 0;
                    foreach ($payment_made as $line) {
                        $paymet_view += $line->amount;
                    }
                    return $row->amount . '(' . $paymet_view  . ')';
                })
                ->addColumn('type', function ($row) {
                    return $row->type;
                })
                ->addColumn('created_by', function ($row) {
                    $UserName = User::where('id', $row->created_by)->first()->username;
                    return $UserName;
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at;
                })
                ->make(true);
        }
    }
    public function getDebit($id)
    {
        $credit = BusinessPartnerTransactions::where('owner', $id)->get();
        if (request()->ajax()) {
            $credit_ids = BusinessPartnerTransactions::where('owner', $id)->where('is_active', 0)
                ->where('type', 'credit')->get(); //where('business_id', $business_id);
            $ids = [];
            foreach ($credit_ids as $credit_id) {
                array_push($ids, $credit_id->transaction_id);
            }
            $Account_Trasaction = AccountTransaction::whereIn('id', $ids)->get();
            return Datatables::of($Account_Trasaction)
                ->editColumn('action', function ($row) {
                    $payment_made = AccountTransaction::where('note', "BusinessPartner_" . $row->id)->get();
                    $paymet_view = 0;
                    foreach ($payment_made as $line) {
                        $paymet_view += $line->amount;
                    }

                    $max = $row->amount - $paymet_view;

                    $html = '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                            data-toggle="dropdown" aria-expanded="false">' .
                        __("messages.actions") .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    $html .= '<li><a href="' . action('TransactionPaymentController@getPayContactDue_Partner', [$row->id]) . '?type=purchase&max=' . $max . '" class="pay_sale_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>  قبض من المتسلف</a></li>';
                    $html .= '<li><a href="' . action('BusinessPartnerController@showPayments', [$row->id]) . '"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>ShowPayments</a></li>';
                    if ($max == $row->amount) {
                        $html .= '<li><a payment_id="' . $row->id . '" class="delete_transaction" link="' . action('BusinessPartnerController@DeleteTransaction') . '"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>Delete</a></li>';
                    }
                    $html .= "</a></li>";
                    return $html;
                })
                ->addColumn('amount', function ($row) {
                    //business_transaction  
                    $key = "BusinessPartner_" . $row->id;
                    $payment_made = AccountTransaction::where('note', $key)->get();
                    $paymet_view = 0;
                    foreach ($payment_made as $line) {
                        $paymet_view += $line->amount;
                    }
                    return $row->amount . '(' . $paymet_view  . ')';
                })
                ->addColumn('type', function ($row) {
                    return $row->type;
                })
                ->addColumn('created_by', function ($row) {
                    $UserName = User::where('id', $row->created_by)->first()->username;
                    return $UserName;
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at;
                })
                ->make(true);
        }
    }
    public function Business_partner_details($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $credit = BusinessPartnerPayments::where('owner', $id)->where('business_id', $business_id)->get();
        if (request()->ajax()) {
            

            $credit_ids = BusinessPartnerPayments::where('owner', $id)->where('is_active', 0)
                ->where('business_id', $business_id)->get();
            $ids = [];
            foreach ($credit_ids as $credit_id) {
                array_push($ids, $credit_id->payment_id);
            }
            $Account_Trasaction = AccountTransaction::whereIn('id', $ids)->get();

            // error_log($Account_Trasaction);
            return Datatables::of($Account_Trasaction)
                ->editColumn('action', function ($row) {
                    // $payment_made = AccountTransaction::where('note', "BusinessPartner_" . $row->id)->get();
                    // $paymet_view = 0;
                    // foreach ($payment_made as $line) {
                    //     $paymet_view += $line->amount;
                    // }

                    // $max = $row->amount - $paymet_view;

                    $html = '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                        data-toggle="dropdown" aria-expanded="false">' .
                        __("messages.actions") .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    // $html .= '<li><a href="' . action('TransactionPaymentController@getPayContactDue_Partner', [$row->id]) . '?type=purchase&max=' . $max . '" class="pay_sale_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>  قبض من المتسلف</a></li>';
                    // $html .= '<li><a href="' . action('BusinessPartnerController@showPayments', [$row->id]) . '"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>ShowPayments</a></li>';
                    // if ($max == $row->amount) {
                    $html .= '<li><a payment_id="' . $row->id . '" class="delete_transaction" link="' . action('BusinessPartnerController@DeleteTransaction') . '"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>Delete</a></li>';
                    // }
                    $html .= "</ul>";
                    return $html;
                })
                ->addColumn('account', function ($row) {

                    return Account::where('id', $row->account_id)->first()->name;
                })
                ->addColumn('method', function ($row) {
                    $transaction = TransactionPayment::where('id', $row->transaction_payment_id)->first();
                    return __('lang_v1.' . $transaction->method);
                })
                ->addColumn('amount', function ($row) {
                    if ($row->type == "debit") {
                        return $row->amount;
                    }
                    return '';
                })
                ->addColumn('amount_less', function ($row) {
                    if ($row->type == "credit") {
                        return $row->amount;
                    }
                    return '';
                })
                ->addColumn('balance', function ($row) use ($ids) {
                    $sum=AccountTransaction::whereIn('id', $ids)->
                    where('id','<=',$row->id)->get();
                    error_log("Data");
                    $balance=0;
                    foreach($sum as $line){
                        error_log($line->type);
                        error_log($line->amount);
                        if($line->type == "debit"){
                            $balance-=$line->amount;
                        }else{
                            $balance+=$line->amount;
                        }
                    }                  
                    return $balance;
                })
                ->addColumn('type', function ($row) {
                    return $row->type;
                })
                ->addColumn('created_by', function ($row) {
                    $UserName = User::where('id', $row->created_by)->first()->username;
                    return $UserName;
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at;
                })
                ->make(true);
        }
    }

    public function UpdatePartner(request $request)
    {
        try {
            $ID = $request->input('id');
            $name = $request->input('name');
            $mobile = $request->input('mobile');
            $address = $request->input('address');
            $Data = array('name' =>  $name, 'mobile' =>  $mobile, 'address' => $address);
            BusinessPartner::where('id', $ID)->where('is_active', 0)->update($Data);
            $output = [
                'success' => True,
                'msg' => ''
            ];
        } catch (Exception $e) {
            $output = [
                'success' => False,
                'msg' => ''
            ];
        }
        return redirect('/BusinessPartner')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function DeletePartner(request $request)
    {
        try {
            $id = $request->input('id');
            error_log($id);

            $Partener_Trnasactions = BusinessPartnerPayments::where('owner',  $id)->get();
            error_log($Partener_Trnasactions);
            //     return [   'success' => True,
            //     'msg' => $Partener_Trnasactions
            // ];

            foreach ($Partener_Trnasactions as $Partener_Trnasaction) {

                // error_log(AccountTransaction::where('id', $Partener_Trnasaction->payment_id)->first());
                AccountTransaction::where('id', $Partener_Trnasaction->payment_id)->delete();
                // error_log(TransactionPayment::where('id', $Partener_Trnasaction->transaction_id)->first());
                TransactionPayment::where('id', $Partener_Trnasaction->transaction_id)->delete();
                // error_log(BusinessPartnerPayments::where('id', $Partener_Trnasaction->transaction_id)->first());
                BusinessPartnerPayments::where('id', $Partener_Trnasaction->id)->update(["is_active" => 1]);
            }
            BusinessPartner::where('id',  $id)->update(["is_active" => 1]);


            $output = [
                'success' => True,
                'msg' => 'Done'
            ];
        } catch (Exception $e) {
            $output = [
                'success' => False,
                'msg' => $e->getMessage()
            ];
        }

        return $output;
    }


    public function showPayments($TransactionId)
    {
        $TransactionIdValue = $TransactionId;
        $id = "BusinessPartner_" . $TransactionId;
        $Account = AccountTransaction::where('note', $id)->get();
        if (request()->ajax()) {
            error_log("Is Ajax");
            $TransactionIdValue = $TransactionId;
            $id = "BusinessPartner_" . $TransactionId;
            $Account = AccountTransaction::where('note', $id)->get();

            return Datatables::of($Account)
                ->editColumn('action', function ($row) {
                    return "html";
                })
                ->addColumn('amount', function ($row) {
                    return $row->amount;
                })
                ->addColumn('created_by', function ($row) {
                    $UserName = User::where('id', $row->created_by)->first()->username;
                    return $UserName;
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at;
                })
                ->make(true);
        }
        return view('business_partner.payments')->with('TransactionId', $TransactionIdValue)
            ->with('Account', $Account);
    }

    public function GetPaymentsData(Request $request)
    {

        // $owner = $request->input('owner');
        // $OpenAmount=BusinessPartner::where('id',$owner)->first();
        // $transaction=BusinessPartner

        // return $output;
    }
}
