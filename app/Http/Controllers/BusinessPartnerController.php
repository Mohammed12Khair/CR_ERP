<?php

namespace App\Http\Controllers;

use App\BusinessPartner;
use App\BusinessPartnerPayments;
use App\BusinessPartnerTransactions;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use App\User;
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
        //        $business_id = request()->session()->get('user.business_id');
        $business_partners = BusinessPartner::where("is_active", 0);
        //return $business_partners;
        if (request()->ajax()) {
            $business_partners = BusinessPartner::where("is_active", 0); //where('business_id', $business_id);
            return Datatables::of($business_partners)
                ->editColumn('action', function ($row) {
                    $delete_btn = '<a href="' . action('BusinessPartnerController@show', [$row->id]) . '" class="btn btn-danger btn-sm" >View</a>';
                    $delete_btn .= '<a href="' . action('BusinessPartnerController@showEdit', [$row->id]) . '" class="btn btn-danger btn-sm" >edit</a>';
                    $delete_btn .= '<button row="' . $row->id . '" href="' . action('BusinessPartnerController@DeletePartner') . '" class="btn btn-danger btn-sm delete_partner" >Delete</button>';
                    $delete_btn .= '<a href="' . action('BusinessPartnerController@Transactions', [$row->id]) . '" class="btn btn-info btn-sm" >transactions</a>';
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
        try {
            // $ID = $request->input('id');
            $name = $request->input('name');
            $mobile = $request->input('mobile');
            $address = $request->input('address');
            $Data = array('name' =>  $name, 'mobile' =>  $mobile, 'address' => $address);
            $business_partner_save = new BusinessPartner();
            $business_partner_save['name'] = $name;
            $business_partner_save['mobile'] = $mobile;
            $business_partner_save['address'] = $address;
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
        $business_partners = BusinessPartner::where('id', $id)->where('is_active', 0)->first();
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
        $business_partners = BusinessPartner::where('id', $id)->where('is_active', 0)->first();
        return view('business_partner.edit')->with('business_partners', $business_partners);
        // return "this is show";
    }

    public function Transactions($id)
    {
        $business_partners = BusinessPartner::where('id', $id)->where('is_active', 0)->first();
        return view('business_partner.transactions')->with('business_partners', $business_partners);
    }

    public function getCredit($id)
    {
        $credit = BusinessPartnerTransactions::where('owner', $id)->get();
        //return  $credit;
        //return $business_partners;
        if (request()->ajax()) {
            $credit = BusinessPartnerTransactions::where('owner', $id)->where('is_active', 0)
                ->where('type', 'credit')->get(); //where('business_id', $business_id);
            return Datatables::of($credit)
                ->editColumn('action', function ($row) {
                    $payment_made = BusinessPartnerPayments::where('account_id', $row->id)->where('is_active', 0)->get();
                    $paymet_view = 0;
                    foreach ($payment_made as $line) {
                        $paymet_view += $line->amount;
                    }

                    $max = $row->amount - $paymet_view;

                    $delete_btn = '<a href="' . action('TransactionPaymentController@getPayContactDue_Partner', [$row->owner]) . '?type=sell&payment_id=' . $row->id . '&max=' . $max . '" class="pay_sale_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>  قبض من المتسلف</a><br>';
                    // $delete_btn = '<a href="' . action('TransactionPaymentController@getPayContactDue_Partner', [$row->id]) . '?type=purchase" class="pay_purchase_due"><i class="fas fa-money-bill-alt" aria-hidden="true">' . __("lang_v1.pay") . 'debit</a>';
                    // $delete_btn = '<a href="' . action('TransactionPaymentController@getPayContactDue', [$row->id]) . '?type=purchase" class="pay_purchase_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("lang_v1.pay") . '</a></li>';
                    // $delete_btn .= '<a href="' . action('BusinessPartnerController@showEdit', [$row->id]) . '" class="btn btn-danger btn-sm" >edit</a>';
                    // $delete_btn .= '<button row="' . $row->id . '" href="' . action('BusinessPartnerController@DeletePartner') . '" class="btn btn-danger btn-sm delete_partner" >Delete</button>';
                    // $delete_btn .= '<a href="' . action('BusinessPartnerController@Transactions', [$row->id]) . '" class="btn btn-info btn-sm" >transactions</a>';
                    // //  $edit_btn='<a href="' . action('BusinessPartnerController@edit',[$row->id]) . '" class="btn btn-info btn-sm" >edit</a>';
                    return $delete_btn; //. $edit_btn;
                })
                ->addColumn('amount', function ($row) {
                    //account_id
                    $payment_made = BusinessPartnerPayments::where('account_id', $row->id)->where('is_active', 0)->get();
                    $paymet_view = 0;
                    foreach ($payment_made as $line) {
                        $paymet_view += $line->amount;
                    }
                    return $row->amount . '(' . $paymet_view  . ')';
                })
                ->addColumn('name', function ($row) {
                    return $row->name;
                })
                ->addColumn('type', function ($row) {
                    return $row->type;
                })
                ->addColumn('note', function ($row) {
                    return $row->note;
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
        //return  $credit;
        //return $business_partners;
        if (request()->ajax()) {
            $credit = BusinessPartnerTransactions::where('owner', $id)->where('is_active', 0)
                ->where('type', 'debit')->get(); //where('business_id', $business_id);
            return Datatables::of($credit)
                ->editColumn('action', function ($row) {
                    $delete_btn = '<a href="' . action('TransactionPaymentController@getPayContactDue_Partner', [$row->owner]) . '?type=purchase" class="pay_purchase_due"><i class="fas fa-money-bill-alt" aria-hidden="true"><i>دفع لصاخب عهده</a>';

                    //   $delete_btn = '<a href="' . action('BusinessPartnerController@show', [$row->id]) . '" class="btn btn-danger btn-sm" >View</a>';
                    // $delete_btn = '<a href="' . action('BusinessPartnerController@showEdit', [$row->id]) . '" class="btn btn-danger btn-sm" >edit</a>';
                    // $delete_btn .= '<button row="' . $row->id . '" href="' . action('BusinessPartnerController@DeletePartner') . '" class="btn btn-danger btn-sm delete_partner" >Delete</button>';
                    // $delete_btn .= '<a href="' . action('BusinessPartnerController@Transactions', [$row->id]) . '" class="btn btn-info btn-sm" >transactions</a>';
                    // //  $edit_btn='<a href="' . action('BusinessPartnerController@edit',[$row->id]) . '" class="btn btn-info btn-sm" >edit</a>';
                    return $delete_btn; //. $edit_btn;
                })
                ->addColumn('amount', function ($row) {
                    return $row->amount;
                })
                ->addColumn('name', function ($row) {
                    return $row->name;
                })
                ->addColumn('type', function ($row) {
                    return $row->type;
                })
                ->addColumn('note', function ($row) {
                    return $row->note;
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
            BusinessPartner::where('id', $request->input('id'))->update(["is_active" => 1]);

            $output = [
                'success' => True,
                'msg' => 'Done'
            ];
        } catch (Exception $e) {
            $output = [
                'success' => False,
                'msg' => ''
            ];
        }

        return $output;
    }
}
