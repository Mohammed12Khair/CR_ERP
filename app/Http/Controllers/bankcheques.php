<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\bankcheques_payment;
use App\Contact;
use App\Transaction;
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
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            // $cheques = DB::select(DB::raw("select a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id
            // from bankcheques_payments a,transaction_payments b,users c where CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and a.business_id=:business_id"), ["business_id" => $business_id]);
            // $cheques = DB::select(DB::raw("select a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id
            // from bankcheques_payments a,transaction_payments b,users c where CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and a.business_id=:business_id"), ["business_id" => $business_id]);
            $cheques = DB::select(DB::raw("select e.name client,a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id from bankcheques_payments a,transaction_payments b,users c,transactions d,contacts e where
            CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and d.id=a.transaction_id and e.id=d.contact_id and  a.business_id=:business_id"), ["business_id" => $business_id]);
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
                        $action .= '<a href="' . action('TransactionPaymentController@addPayment_cheque_accept', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id, $row->amount - $total_payment]) . '" class="add_payment_modal  btn btn-success btn-xs"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("lang_v1.cheque_accept") . '</a>';
                        // $action .= '<button data-href="' . action('TransactionPaymentController@addPayment_cheque_pass', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id, $row->amount - $total_payment]) . '" class="btn btn-xs btn-primary"><i class="fas fa-check">Collect</i></button>';
                    } else {
                        $action .= '<a href="' . action('bankcheques@EditPayment', [str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id]) . '" class="btn btn-info btn-xs"><i class="fas fa-eye" aria-hidden="true"></i>' . __("cheque.edit_payment") . '</a>';
                    }
                    if ($total_payment != $row->amount) {
                        $action .= '<a href="' . action('TransactionPaymentController@addPayment_cheque', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id, $row->amount - $total_payment]) . '" class="add_payment_modal  btn btn-warning btn-xs"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("purchase.add_payment") . '</a>';
                    }
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
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at;
                })
                ->addColumn('username', function ($row) {
                    return  $row->username;
                })
                ->addColumn('amount', function ($row) {
                    return  $row->amount;
                })
                ->addColumn('transaction_type', function ($row) {
                    return  $row->transaction_type;
                })
                ->addColumn('cheque_date', function ($row) {
                    return  $row->cheque_date;
                })
                ->addColumn('cheque_number', function ($row) {
                    return  $row->cheque_number;
                })
                ->addColumn('transaction_id', function ($row) {
                    return  $row->transaction_id;
                })
                ->addColumn('payment_id', function ($row) {
                    return   $row->client;
                })
                ->addColumn('id', function ($row) {
                    return  $row->id;
                })
                // ->rawColumns(['action'])
                ->make(true);
        }
        return view('cheques.index');
    }

    public function AdvanceSearch()
    {
        $business_id = request()->session()->get('user.business_id');
        $cheques = DB::select(DB::raw("select e.name client,a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id from bankcheques_payments a,transaction_payments b,users c,transactions d,contacts e where
        CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and d.id=a.transaction_id and e.id=d.contact_id and  a.business_id=:business_id"), ["business_id" => $business_id]);

        $client_data = [];
        $transaction_tp = [];
        $status = ['New', 'Partial', 'Paid'];
        foreach ($cheques as $cheque) {
            array_push($client_data, $cheque->client);
            array_push($transaction_tp, $cheque->transaction_type);
        }
        $client_data = array_unique($client_data);
        $transaction_tp = array_unique($transaction_tp);


        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            // $cheques = DB::select(DB::raw("select a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id
            // from bankcheques_payments a,transaction_payments b,users c where CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and a.business_id=:business_id"), ["business_id" => $business_id]);
            // $cheques = DB::select(DB::raw("select a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id
            // from bankcheques_payments a,transaction_payments b,users c where CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and a.business_id=:business_id"), ["business_id" => $business_id]);
            $cheques = DB::select(DB::raw("select e.name client,a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id from bankcheques_payments a,transaction_payments b,users c,transactions d,contacts e where
            CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and d.id=a.transaction_id and e.id=d.contact_id and  a.business_id=:business_id"), ["business_id" => $business_id]);
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
                        $action .= '<a href="' . action('TransactionPaymentController@addPayment_cheque_accept', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id, $row->amount - $total_payment]) . '" class="add_payment_modal  btn btn-success btn-xs"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("lang_v1.cheque_accept") . '</a>';
                        // $action .= '<button data-href="' . action('TransactionPaymentController@addPayment_cheque_pass', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id, $row->amount - $total_payment]) . '" class="btn btn-xs btn-primary"><i class="fas fa-check">Collect</i></button>';
                    } else {
                        $action .= '<a href="' . action('bankcheques@EditPayment', [str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id]) . '" class="btn btn-info btn-xs"><i class="fas fa-eye" aria-hidden="true"></i>' . __("cheque.edit_payment") . '</a>';
                    }
                    if ($total_payment != $row->amount) {
                        $action .= '<a href="' . action('TransactionPaymentController@addPayment_cheque', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id, $row->amount - $total_payment]) . '" class="add_payment_modal  btn btn-warning btn-xs"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("purchase.add_payment") . '</a>';
                    }
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
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at;
                })
                ->addColumn('username', function ($row) {
                    return  $row->username;
                })
                ->addColumn('amount', function ($row) {
                    return  $row->amount;
                })
                ->addColumn('transaction_type', function ($row) {
                    return  $row->transaction_type;
                })
                ->addColumn('cheque_date', function ($row) {
                    return  $row->cheque_date;
                })
                ->addColumn('cheque_number', function ($row) {
                    return  $row->cheque_number;
                })
                ->addColumn('transaction_id', function ($row) {
                    return  $row->transaction_id;
                })
                ->addColumn('payment_id', function ($row) {
                    return   $row->client;
                })
                ->addColumn('id', function ($row) {
                    return  $row->id;
                })
                // ->rawColumns(['action'])
                ->make(true);
        }
        return view('cheques.search')->with('client_data', $client_data)->with('transaction_tp', $transaction_tp)->with('status', $status);
    }

    public function GetAdvanceSearch(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $client = $request->input('client');
        // return $client;
        $transaction_type = $request->input('transaction_type');
        $status = $request->input('status');
        $cheque_number = $request->input('cheque_number');


      

        $cheques = DB::select(DB::raw("select e.name client,a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id from bankcheques_payments a,transaction_payments b,users c,transactions d,contacts e where
        CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and d.id=a.transaction_id and e.id=d.contact_id and  a.business_id=:business_id "), ["business_id" => $business_id]);

        // if ($client != null) {
        //     $cheques = DB::select(DB::raw("select e.name client,a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id from bankcheques_payments a,transaction_payments b,users c,transactions d,contacts e where
        //     CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and d.id=a.transaction_id and e.id=d.contact_id and e.name=:client"), ["client" => $client]);
        // }

      

      

        if ($transaction_type != null) {
            $cheques = DB::select(DB::raw("select e.name client,a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id from bankcheques_payments a,transaction_payments b,users c,transactions d,contacts e where
            CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and d.id=a.transaction_id and e.id=d.contact_id and a.transaction_type=:transaction_type"), ["transaction_type" => $transaction_type]);
        }

  

        if ($cheque_number != null) {
            $cheques = DB::select(DB::raw("select e.name client,a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id from bankcheques_payments a,transaction_payments b,users c,transactions d,contacts e where
            CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and d.id=a.transaction_id and e.id=d.contact_id and a.cheque_number=:cheque_number"), ["cheque_number" => $cheque_number]);
        }

        return $cheques;

        return  $cheques;
    }



    //
    public function cheque_home()
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
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            // $cheques = DB::select(DB::raw("select a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id
            // from bankcheques_payments a,transaction_payments b,users c where CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and a.business_id=:business_id"), ["business_id" => $business_id]);
            // $cheques = DB::select(DB::raw("select a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id
            // from bankcheques_payments a,transaction_payments b,users c where CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and a.business_id=:business_id"), ["business_id" => $business_id]);
            $cheques = DB::select(DB::raw("select e.name client,a.id id,b.id payment_id,a.transaction_id transaction_id,a.cheque_number cheque_number,a.cheque_date cheque_date,a.transaction_type transaction_type,a.amount amount,c.username username,a.created_at created_at ,a.cheque_ref cheque_ref,a.business_id business_id from bankcheques_payments a,transaction_payments b,users c,transactions d,contacts e where
            CONCAT(a.transaction_id,a.cheque_ref)=CONCAT(b.transaction_id,b.payment_ref_no) and a.userid=c.id and d.id=a.transaction_id and e.id=d.contact_id and  a.business_id=:business_id"), ["business_id" => $business_id]);
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
                        $action .= '<a href="' . action('TransactionPaymentController@addPayment_cheque_accept', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id, $row->amount - $total_payment]) . '" class="add_payment_modal  btn btn-success btn-xs"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("lang_v1.cheque_accept") . '</a>';
                        // $action .= '<button data-href="' . action('TransactionPaymentController@addPayment_cheque_pass', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id, $row->amount - $total_payment]) . '" class="btn btn-xs btn-primary"><i class="fas fa-check">Collect</i></button>';
                    } else {
                        $action .= '<a href="' . action('bankcheques@EditPayment', [str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id]) . '" class="btn btn-info btn-xs"><i class="fas fa-eye" aria-hidden="true"></i>' . __("cheque.edit_payment") . '</a>';
                    }
                    if ($total_payment != $row->amount) {
                        $action .= '<a href="' . action('TransactionPaymentController@addPayment_cheque', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id, $row->amount - $total_payment]) . '" class="add_payment_modal  btn btn-warning btn-xs"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("purchase.add_payment") . '</a>';
                    }
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
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at;
                })
                ->addColumn('username', function ($row) {
                    return  $row->username;
                })
                ->addColumn('amount', function ($row) {
                    return  $row->amount;
                })
                ->addColumn('transaction_type', function ($row) {
                    return  $row->transaction_type;
                })
                ->addColumn('cheque_date', function ($row) {
                    return  $row->cheque_date;
                })
                ->addColumn('cheque_number', function ($row) {
                    return  $row->cheque_number;
                })
                ->addColumn('transaction_id', function ($row) {
                    return  $row->transaction_id;
                })
                ->addColumn('payment_id', function ($row) {
                    return   $row->client;
                })
                ->addColumn('id', function ($row) {
                    return  $row->id;
                })
                // ->rawColumns(['action'])
                ->make(true);
        }
        return view('cheques.home');
    }

    public function indexSearch()
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
                        $action .= '<a href="' . action('TransactionPaymentController@addPayment_cheque_accept', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id, $row->amount - $total_payment]) . '" class="add_payment_modal  btn btn-success btn-xs"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("lang_v1.cheque_accept") . '</a>';
                        // $action .= '<button data-href="' . action('TransactionPaymentController@addPayment_cheque_pass', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id, $row->amount - $total_payment]) . '" class="btn btn-xs btn-primary"><i class="fas fa-check">Collect</i></button>';
                    } else {
                        $action .= '<a href="' . action('bankcheques@EditPayment', [str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id]) . '" class="btn btn-info btn-xs"><i class="fas fa-eye" aria-hidden="true"></i>' . __("cheque.edit_payment") . '</a>';
                    }
                    if ($total_payment != $row->amount) {
                        $action .= '<a href="' . action('TransactionPaymentController@addPayment_cheque', [$row->transaction_id, str_replace('/', '_', $row->cheque_ref) . '_' . $row->payment_id, $row->amount - $total_payment]) . '" class="add_payment_modal  btn btn-warning btn-xs"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("purchase.add_payment") . '</a>';
                    }

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
                ->addColumn('created_at', function ($row) {
                    return $row->created_at;
                })
                ->addColumn('username', function ($row) {
                    return  $row->username;
                })
                ->addColumn('amount', function ($row) {
                    return  $row->amount;
                })
                ->addColumn('transaction_type', function ($row) {
                    return  $row->transaction_type;
                })
                ->addColumn('cheque_date', function ($row) {
                    return  $row->cheque_date;
                })
                ->addColumn('cheque_number', function ($row) {
                    return  $row->cheque_number;
                })
                ->addColumn('transaction_id', function ($row) {
                    return  $row->transaction_id;
                })
                ->addColumn('payment_id', function ($row) {
                    return  $row->payment_id;
                })
                ->addColumn('id', function ($row) {
                    return  $row->id;
                })
                // ->rawColumns(['action'])
                ->make(true);
        }
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

    public function Accounts()
    {
    }
}
