<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\bankcheques_payment;
use Exception;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class bankcheques extends Controller
{
    //
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        // if (!auth()->user()->can('unit.view') && !auth()->user()->can('unit.create')) {
        //     abort(403, 'Unauthorized action.');
        // }
        // $cheques = DB::select(DB::raw("select a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at 
        // from bankcheques_payments a,transaction_payments b,users c where CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and a.business_id=:business_id"), ["business_id" => $business_id]);

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $cheques = DB::select(DB::raw("select a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref
            from bankcheques_payments a,transaction_payments b,users c where CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and a.business_id=:business_id"), ["business_id" => $business_id]);
            return Datatables::of($cheques)
                ->addColumn('action', function ($row) {
                    return '<button onclick="action_take();">Test</button><button data-href="' . action('TransactionPaymentController@destroy', [$row->payment_id]) . '" class="btn btn-xs btn-danger delete_payment"><i class="glyphicon glyphicon-trash"></i></button>
                    <a href="' . action('TransactionPaymentController@addPayment_cheque', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("purchase.add_payment") . '</a></li>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at;
                })
                ->editColumn('username', function ($row) {
                    return  $row->username;
                })
                ->editColumn('amount', function ($row) {
                    return  $row->amount;
                })
                ->editColumn('transaction_type', function ($row) {
                    return  $row->transaction_type;
                })
                ->editColumn('cheque_date', function ($row) {
                    return  $row->cheque_date;
                })
                ->editColumn('cheque_number', function ($row) {
                    return  $row->cheque_number;
                })
                ->editColumn('transaction_id', function ($row) {
                    return  $row->transaction_id;
                })
                ->editColumn('payment_id', function ($row) {
                    return  $row->payment_id;
                })
                ->editColumn('id', function ($row) {
                    return  $row->id;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // 'select a.id,b.id payment_id,a.transaction_id,a.cheque_number,a.cheque_date,a.transaction_type,a.amount,c.username,a.created_at from bankcheques_payments a,transaction_payments b,users c where CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id'
        // return view('cheques.index')->with('cheques', $cheques);
        return view('cheques.index');
    }
}
