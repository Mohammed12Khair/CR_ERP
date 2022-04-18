<?php

namespace App\Http\Controllers;

use App\BusinessPartnerTransactions;
use Illuminate\Http\Request;

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

        error_log($request->input('owner'));
        error_log($request->input('amount'));
        error_log($request->input('note'));
        error_log($request->input('type'));
        $business_id = request()->session()->get('user.business_id');
        try {
            $BusinessPartnerTransaction = new BusinessPartnerTransactions();
            $BusinessPartnerTransaction['owner'] = $request->input('owner');
            $BusinessPartnerTransaction['amount'] = $request->input('amount');
            $BusinessPartnerTransaction['note'] = $request->input('note');
            $BusinessPartnerTransaction['type'] = $request->input('type');
            $BusinessPartnerTransaction['business_id'] =  $business_id;
            $BusinessPartnerTransaction['created_by'] = $request->user()->id;
            $BusinessPartnerTransaction->save();
            //::where('id', $request->input('id'))->delete();
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
