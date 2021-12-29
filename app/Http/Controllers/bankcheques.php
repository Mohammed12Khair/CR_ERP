<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\bankcheques_payment;
use App\TransactionPayment;
use Exception;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Routing\Redirector;

class bankcheques extends Controller
{


    function getOpenAmount($key, $open_amount)
    {
        $business_id = request()->session()->get('user.business_id');
        $total_payment = TransactionPayment::where([
            ['note', '=', $key],
            ['business_id', '=', $business_id]
        ])->sum('amount')->get();
        // $total_payment = DB::select(DB::raw("select sum(amount) total from transaction_payments a where a.note=:key and a.business_id=:business_id"), ["key" => $key, "business_id" => $business_id]);
        return $total_payment;
    }
    //
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $total_payment = TransactionPayment::where([
                ['note', '=', 'PP2021_0025_32'],
                ['business_id', '=', $business_id]
            ])->sum('amount');
            error_log($total_payment);
        } catch (Exception $e) {
            error_log($e);
        }


        // if (!auth()->user()->can('unit.view') && !auth()->user()->can('unit.create')) {
        //     abort(403, 'Unauthorized action.');
        // }
        // $cheques = DB::select(DB::raw("select a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at 
        // from bankcheques_payments a,transaction_payments b,users c where CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and a.business_id=:business_id"), ["business_id" => $business_id]);

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $cheques = DB::select(DB::raw("select a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id
            from bankcheques_payments a,transaction_payments b,users c where CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and a.business_id=:business_id"), ["business_id" => $business_id]);
            return Datatables::of($cheques)
                ->addColumn('action', function ($row) {
                    $key = str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id;
                    $total_payment = TransactionPayment::where([
                        ['note', '=', $key],
                        ['business_id', '=', $row->business_id]
                    ])->sum('amount');
                    $action = '';
                    if ($total_payment == 0) {
                        $action .= '<button data-href="' . action('TransactionPaymentController@destroy', [$row->payment_id]) . '" class="btn btn-xs btn-danger delete_payment"><i class="glyphicon glyphicon-trash"></i></button>';
                    } else {
                        $action .= '<a href="' . action('bankcheques@EditPayment', [str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id]) . '" class="btn btn-info btn-xs"><i class="fas fa-eye" aria-hidden="true"></i>' . __("cheque.edit_payment") . '</a></li>';
                    }
                    $action .= '<a href="' . action('TransactionPaymentController@addPayment_cheque', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id, $row->amount - $total_payment]) . '" class="add_payment_modal  btn btn-success btn-xs"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("purchase.add_payment") . '</a></li>';
                    // $action .= '<a href="' . action('bankcheques@EditCheque', [$row->id]) . '" class="btn btn-info btn-xs"><i class="fas fa-paper" aria-hidden="true"></i></a></li>';

                    return $action;
                })
                ->editColumn('Status', function ($row) {
                    $key = str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id;
                    $total_payment = TransactionPayment::where([
                        ['note', '=', $key],
                        ['business_id', '=', $row->business_id]
                    ])->sum('amount');
                    if ($total_payment == 0) {
                        return 'New';
                    } elseif ($total_payment > 0 && $total_payment != $row->amount) {
                        return 'Partial';
                    } elseif ($total_payment == $row->amount) {
                        return 'Paid';
                    }
                    // return $row->created_at;
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

    public function EditPayment($key)
    {
        $business_id = request()->session()->get('user.business_id');
        $cheque_payments = TransactionPayment::where([
            ['note', '=', $key],
            ['business_id', '=', $business_id]
        ])->get();

        if ($cheque_payments->count() == 0) {
            return redirect()->back();
        }
        return view('cheques.cheque_payment')->with('cheque_payments', $cheque_payments);
    }

    public function EditCheque($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $cheque_payments = bankcheques_payment::where([
            ['id', '=', $id],
            ['business_id', '=', $business_id]
        ])->get();

        if ($cheque_payments->count() == 0) {
            return redirect()->back();
        }
        return view('cheques.cheque_edit')->with('cheque_payments', $cheque_payments);
    }

    public function EditChequeSave(Request $request)
    {
        try {
            $ID = $request->input('id');
            $cheque_number = $request->input('cheque_number');
            $cheque_date = $request->input('cheque_date');
            $values = array('cheque_number' =>  $cheque_number, 'cheque_date' =>  $cheque_date);
            bankcheques_payment::where('id', $ID)->update($values);
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
        return redirect()->back()->with('status', $output);
    }

    public function Accounts(){

    }
}
