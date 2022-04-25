<?php

namespace App\Http\Controllers;

use App\BusinessPartnerTransactions;
use Illuminate\Http\Request;

use App\AccountTransaction;
use App\Business;
use App\BusinessPartner;

class BusinessPartnerTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }


    public function createTransaction(Request $request)
    {


        $Owner_Name=BusinessPartner::where('id',$request->input('owner'))->first();
        
        $business_id = request()->session()->get('user.business_id');
        try {
            $data = [
                'amount' => $request->input('amount'),
                'account_id' => $request->input('account_id'),
                'type' => $request->input('type'),
                'created_by' => 1,
                'sub_type' =>  null,
                'operation_date' => \Carbon::now(),
                'transaction_id' => null,
                'transaction_payment_id' => null,
                'note' => "BusinessPartner " . $Owner_Name->name . "(" . $Owner_Name->id . ")   " . $request->input('type'),
                'transfer_transaction_id' =>  null,
            ];

            $account_transaction = AccountTransaction::create($data);

            $BusinessPartnerTransaction = new BusinessPartnerTransactions();
            $BusinessPartnerTransaction['owner'] = $request->input('owner');
            $BusinessPartnerTransaction['amount'] = $account_transaction->amount;
            $BusinessPartnerTransaction['note'] = $account_transaction->note;
            $BusinessPartnerTransaction['type'] = $account_transaction->type;
            $BusinessPartnerTransaction['business_id'] =  $business_id;
            $BusinessPartnerTransaction['transaction_id'] =  $account_transaction->id;
            $BusinessPartnerTransaction['created_by'] = $request->user()->id;
            $BusinessPartnerTransaction->save();

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
    public function show($id)
    {
        //
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
}
