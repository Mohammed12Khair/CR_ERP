<?php

namespace App\Http\Controllers;

use App\Account;

use App\BusinessPartner;
use App\BusinessPartnerPayments;
use App\AccountTransaction;
use App\TransactionPayment;
use App\Utils\TransactionUtil;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\BusinessLocation;
use App\Contact;
use App\Transaction;
use Exception;

class AccountReportsController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $transactionUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function balanceSheet()
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');
        if (request()->ajax()) {
            $end_date = !empty(request()->input('end_date')) ? $this->transactionUtil->uf_date(request()->input('end_date')) : \Carbon::now()->format('Y-m-d');
            $location_id = !empty(request()->input('location_id')) ? request()->input('location_id') : null;

            $purchase_details = $this->transactionUtil->getPurchaseTotals(
                $business_id,
                null,
                $end_date,
                $location_id
            );
            $sell_details = $this->transactionUtil->getSellTotals(
                $business_id,
                null,
                $end_date,
                $location_id
            );

            $transaction_types = ['sell_return'];

            $sell_return_details = $this->transactionUtil->getTransactionTotals(
                $business_id,
                $transaction_types,
                null,
                $end_date,
                $location_id
            );

            $account_details = $this->getAccountBalance($business_id, $end_date, 'others', $location_id);
            // $capital_account_details = $this->getAccountBalance($business_id, $end_date, 'capital');

            //Get Closing stock
            $closing_stock = $this->transactionUtil->getOpeningClosingStock(
                $business_id,
                $end_date,
                $location_id
            );

            // Khair 25-apr
            try {
                $supplier_list = Contact::where('business_id', $business_id)->where('contact_status', 'active')
                    ->where('type', 'supplier')->get();
                $supplier_ids = [];
                foreach ($supplier_list as $supplier) {
                    array_push($supplier_ids, $supplier->id);
                }


                $all_transactions = Transaction::where('business_id', $business_id)->where('type', 'opening_balance')
                    ->whereIn('contact_id', $supplier_ids)->get();

                $sumOpen = 0;
                foreach ($all_transactions as $all_transaction) {
                    $sumOpen += $all_transaction->final_total;
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
            }


            
        
        $credit = 0;
        $debit = 0;

        // Add solaf and ohad
        $business_partners = BusinessPartner::where("is_active", 0)->where('business_id', $business_id)->get(); //where('business_id', $business_id);
        foreach ($business_partners as $row) {
            // Get Payments 
            $business_pyments = BusinessPartnerPayments::where('owner',  $row->id)->where('is_active', 0)->get();

            $PymentId = [];
            foreach ($business_pyments as $business_pyment) {
                array_push($PymentId, $business_pyment->payment_id);
            }

            // Get Payment frmo account transactions
            $account_transactions = AccountTransaction::whereIn('id', $PymentId)->get();

            // loop and calcualte balance

            foreach ($account_transactions as $account_transaction) {
                if ($account_transaction->type == "credit") {
                    $credit += $account_transaction->amount;
                }
                if ($account_transaction->type == "debit") {
                    $debit += $account_transaction->amount;
                }
            }

            // MAtch with open balance
            if ($row->type == "credit") {
                $credit += $row->open_balance;
            }
            // MAtch with open balance
            if ($row->type == "debit") {
                $debit += $row->open_balance;
            }

            $final_amount = $credit - $debit;
        }

            


            $output = [
                // 'supplier_due' => $purchase_details['purchase_due'],
                'supplier_due' => $purchase_details['purchase_due'] + $sumOpen ,
                'customer_due' => $sell_details['invoice_due'] - $sell_return_details['total_sell_return_inc_tax'],
                'account_balances' => $account_details ,
                'credit_due' => $credit,
                'debit_due' => $debit,
                'closing_stock' => $closing_stock  ,
                'capital_account_details' => null
            ];

            return $output;
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);

          
        
        $credit = 0;
        $debit = 0;

        // Add solaf and ohad
        $business_partners = BusinessPartner::where("is_active", 0)->where('business_id', $business_id)->get(); //where('business_id', $business_id);
        foreach ($business_partners as $row) {
            // Get Payments 
            $business_pyments = BusinessPartnerPayments::where('owner',  $row->id)->where('is_active', 0)->get();

            $PymentId = [];
            foreach ($business_pyments as $business_pyment) {
                array_push($PymentId, $business_pyment->payment_id);
            }

            // Get Payment frmo account transactions
            $account_transactions = AccountTransaction::whereIn('id', $PymentId)->get();

            // loop and calcualte balance

            foreach ($account_transactions as $account_transaction) {
                if ($account_transaction->type == "credit") {
                    $credit += $account_transaction->amount;
                }
                if ($account_transaction->type == "debit") {
                    $debit += $account_transaction->amount;
                }
            }

            // MAtch with open balance
            if ($row->type == "credit") {
                $credit += $row->open_balance;
            }
            // MAtch with open balance
            if ($row->type == "debit") {
                $debit += $row->open_balance;
            }

            $final_amount = $credit - $debit;
        }
        return view('account_reports.balance_sheet')->with(compact('business_locations','credit','debit'));
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function trialBalance()
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');

        if (request()->ajax()) {
            $end_date = !empty(request()->input('end_date')) ? $this->transactionUtil->uf_date(request()->input('end_date')) : \Carbon::now()->format('Y-m-d');
            $location_id = !empty(request()->input('location_id')) ? request()->input('location_id') : null;

            $purchase_details = $this->transactionUtil->getPurchaseTotals(
                $business_id,
                null,
                $end_date,
                $location_id
            );
            $sell_details = $this->transactionUtil->getSellTotals(
                $business_id,
                null,
                $end_date,
                $location_id
            );

            $account_details = $this->getAccountBalance($business_id, $end_date, 'others', $location_id);

            // $capital_account_details = $this->getAccountBalance($business_id, $end_date, 'capital');

            $output = [
                'supplier_due' => $purchase_details['purchase_due'],
                'customer_due' => $sell_details['invoice_due'],
                'account_balances' => $account_details,
                'capital_account_details' => null
            ];

            return $output;
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);


        $credit = 0;
        $debit = 0;

        // Add solaf and ohad
        $business_partners = BusinessPartner::where("is_active", 0)->where('business_id', $business_id)->get(); //where('business_id', $business_id);
        foreach ($business_partners as $row) {
            // Get Payments 
            $business_pyments = BusinessPartnerPayments::where('owner',  $row->id)->where('is_active', 0)->get();

            $PymentId = [];
            foreach ($business_pyments as $business_pyment) {
                array_push($PymentId, $business_pyment->payment_id);
            }

            // Get Payment frmo account transactions
            $account_transactions = AccountTransaction::whereIn('id', $PymentId)->get();

            // loop and calcualte balance

            foreach ($account_transactions as $account_transaction) {
                if ($account_transaction->type == "credit") {
                    $credit += $account_transaction->amount;
                }
                if ($account_transaction->type == "debit") {
                    $debit += $account_transaction->amount;
                }
            }

            // MAtch with open balance
            if ($row->type == "credit") {
                $credit += $row->open_balance;
            }
            // MAtch with open balance
            if ($row->type == "debit") {
                $debit += $row->open_balance;
            }

            $final_amount = $credit - $debit;
        }

    
    

        return view('account_reports.trial_balance')->with(compact('business_locations','credit','debit'));
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function trialBalance_old()
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');

        if (request()->ajax()) {
            $end_date = !empty(request()->input('end_date')) ? $this->transactionUtil->uf_date(request()->input('end_date')) : \Carbon::now()->format('Y-m-d');
            $location_id = !empty(request()->input('location_id')) ? request()->input('location_id') : null;

            $purchase_details = $this->transactionUtil->getPurchaseTotals(
                $business_id,
                null,
                $end_date,
                $location_id
            );
            $sell_details = $this->transactionUtil->getSellTotals(
                $business_id,
                null,
                $end_date,
                $location_id
            );

            $account_details = $this->getAccountBalance($business_id, $end_date, 'others', $location_id);

            // $capital_account_details = $this->getAccountBalance($business_id, $end_date, 'capital');

            $output = [
                'supplier_due' => $purchase_details['purchase_due'],
                'customer_due' => $sell_details['invoice_due'],
                'account_balances' => $account_details,
                'capital_account_details' => null
            ];

            return $output;
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('account_reports.trial_balance')->with(compact('business_locations'));
    }

    /**
     * Retrives account balances.
     * @return Obj
     */
    private function getAccountBalance($business_id, $end_date, $account_type = 'others', $location_id = null)
    {
        $query = Account::leftjoin(
            'account_transactions as AT',
            'AT.account_id',
            '=',
            'accounts.id'
        )
            // ->NotClosed()
            ->whereNull('AT.deleted_at')
            ->where('business_id', $business_id)
            ->whereDate('AT.operation_date', '<=', $end_date);

        // if ($account_type == 'others') {
        //    $query->NotCapital();
        // } elseif ($account_type == 'capital') {
        //     $query->where('account_type', 'capital');
        // }

        $permitted_locations = auth()->user()->permitted_locations();
        $account_ids = [];
        if ($permitted_locations != 'all') {
            $locations = BusinessLocation::where('business_id', $business_id)
                ->whereIn('id', $permitted_locations)
                ->get();

            foreach ($locations as $location) {
                if (!empty($location->default_payment_accounts)) {
                    $default_payment_accounts = json_decode($location->default_payment_accounts, true);
                    foreach ($default_payment_accounts as $key => $account) {
                        if (!empty($account['is_enabled']) && !empty($account['account'])) {
                            $account_ids[] = $account['account'];
                        }
                    }
                }
            }

            $account_ids = array_unique($account_ids);
        }

        if ($permitted_locations != 'all') {
            $query->whereIn('accounts.id', $account_ids);
        }

        if (!empty($location_id)) {
            $location = BusinessLocation::find($location_id);
            if (!empty($location->default_payment_accounts)) {
                $default_payment_accounts = json_decode($location->default_payment_accounts, true);
                $account_ids = [];
                foreach ($default_payment_accounts as $key => $account) {
                    if (!empty($account['is_enabled']) && !empty($account['account'])) {
                        $account_ids[] = $account['account'];
                    }
                }

                $query->whereIn('accounts.id', $account_ids);
            }
        }

        $account_details = $query->select([
            'name',
            DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")
        ])
            ->groupBy('accounts.id')
            ->get()
            ->pluck('balance', 'name');

        return $account_details;
    }

    /**
     * Displays payment account report.
     * @return Response
     */
    public function paymentAccountReport()
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');

        if (request()->ajax()) {
            $query = TransactionPayment::leftjoin(
                'transactions as T',
                'transaction_payments.transaction_id',
                '=',
                'T.id'
            )
                ->leftjoin('accounts as A', 'transaction_payments.account_id', '=', 'A.id')
                ->where('transaction_payments.business_id', $business_id)
                ->whereNull('transaction_payments.parent_id')
                ->where('transaction_payments.method', '!=', 'advance')
                ->select([
                    'paid_on',
                    'payment_ref_no',
                    'T.ref_no',
                    'T.invoice_no',
                    'T.type',
                    'T.id as transaction_id',
                    'A.name as account_name',
                    'A.account_number',
                    'transaction_payments.id as payment_id',
                    'transaction_payments.account_id'
                ]);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('T.location_id', $permitted_locations);
            }

            $start_date = !empty(request()->input('start_date')) ? request()->input('start_date') : '';
            $end_date = !empty(request()->input('end_date')) ? request()->input('end_date') : '';

            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(paid_on)'), [$start_date, $end_date]);
            }

            $account_id = !empty(request()->input('account_id')) ? request()->input('account_id') : '';

            if ($account_id == 'none') {
                $query->whereNull('account_id');
            } elseif (!empty($account_id)) {
                $query->where('account_id', $account_id);
            }

            return DataTables::of($query)
                ->editColumn('paid_on', function ($row) {
                    return $this->transactionUtil->format_date($row->paid_on, true);
                })
                ->addColumn('action', function ($row) {
                    $action = '<button type="button" class="btn btn-info 
                        btn-xs btn-modal"
                        data-container=".view_modal" 
                        data-href="' . action('AccountReportsController@getLinkAccount', [$row->payment_id]) . '">' . __('account.link_account') . '</button>';

                    return $action;
                })
                ->addColumn('account', function ($row) {
                    $account = '';
                    if (!empty($row->account_id)) {
                        $account = $row->account_name . ' - ' . $row->account_number;
                    }
                    return $account;
                })
                ->addColumn('transaction_number', function ($row) {
                    $html = $row->ref_no;
                    if ($row->type == 'sell') {
                        $html = '<button type="button" class="btn btn-link btn-modal"
                                    data-href="' . action('SellController@show', [$row->transaction_id]) . '" data-container=".view_modal">' . $row->invoice_no . '</button>';
                    } elseif ($row->type == 'purchase') {
                        $html = '<button type="button" class="btn btn-link btn-modal"
                                    data-href="' . action('PurchaseController@show', [$row->transaction_id]) . '" data-container=".view_modal">' . $row->ref_no . '</button>';
                    }
                    return $html;
                })
                ->editColumn('type', function ($row) {
                    $type = $row->type;
                    if ($row->type == 'sell') {
                        $type = __('sale.sale');
                    } elseif ($row->type == 'purchase') {
                        $type = __('lang_v1.purchase');
                    } elseif ($row->type == 'expense') {
                        $type = __('lang_v1.expense');
                    }
                    return $type;
                })
                ->filterColumn('account', function ($query, $keyword) {
                    $query->where('A.name', 'like', ["%{$keyword}%"])
                        ->orWhere('account_number', 'like', ["%{$keyword}%"]);
                })
                ->filterColumn('transaction_number', function ($query, $keyword) {
                    $query->where('T.invoice_no', 'like', ["%{$keyword}%"])
                        ->orWhere('T.ref_no', 'like', ["%{$keyword}%"]);
                })
                ->rawColumns(['action', 'transaction_number'])
                ->make(true);
        }

        $accounts = Account::forDropdown($business_id, false);
        $accounts = ['' => __('messages.all'), 'none' => __('lang_v1.none')] + $accounts;

        return view('account_reports.payment_account_report')
            ->with(compact('accounts'));
    }

    /**
     * Shows form to link account with a payment.
     * @return Response
     */
    public function getLinkAccount($id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');
        if (request()->ajax()) {
            $payment = TransactionPayment::where('business_id', $business_id)->findOrFail($id);
            $accounts = Account::forDropdown($business_id, false);

            return view('account_reports.link_account_modal')
                ->with(compact('accounts', 'payment'));
        }
    }

    /**
     * Links account with a payment.
     * @param  Request $request
     * @return Response
     */
    public function postLinkAccount(Request $request)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = session()->get('user.business_id');
            if (request()->ajax()) {
                $payment_id = $request->input('transaction_payment_id');
                $account_id = $request->input('account_id');

                $payment = TransactionPayment::with(['transaction'])->where('business_id', $business_id)->findOrFail($payment_id);
                $payment->account_id = $account_id;
                $payment->save();

                $payment_type = !empty($payment->transaction->type) ? $payment->transaction->type : null;
                if (empty($payment_type)) {
                    $child_payment = TransactionPayment::where('parent_id', $payment->id)->first();
                    $payment_type = !empty($child_payment->transaction->type) ? $child_payment->transaction->type : null;
                }

                AccountTransaction::updateAccountTransaction($payment, $payment_type);
            }
            $output = [
                'success' => true,
                'msg' => __("account.account_linked_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }
}
