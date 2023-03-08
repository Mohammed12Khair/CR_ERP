<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountTransaction;
use App\accounttransactionsClone;
use App\bankcheques_payment;
use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\CustomerGroup;
use App\ExpenseCategory;
use App\Product;
use App\PurchaseLine;
use App\purchaselinesClone;
use App\RefundTable;
use App\TaxRate;
use App\Transaction;
use App\transaction_sell_linesClone;
use App\User;
use App\Utils\BusinessUtil;

use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;

use App\Variation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Spatie\Activitylog\Models\Activity;

use App\transactionsClone;
use Exception;
use Mockery\Generator\StringManipulation\Pass\Pass;

class PurchaseController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $productUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;

        $this->dummyPaymentLine = [
            'method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => '', 'cheque_date' => ''
        ];
    }


    public function edithistory()
    {

        // return 500;
        $business_id = request()->session()->get('user.business_id');
        $data = DB::select("SELECT * FROM transactions_clones_edit WHERE business_id=:business_id order by updated_at desc limit 1000", ["business_id" => $business_id]);

        return view('sell.edithistroy')->with('data', $data);
    }

    public function edithistorydetails($id, $type)
    {


        $business_id = request()->session()->get('user.business_id');
        if ($type == 'sell') {
            $data = DB::select("SELECT * FROM transaction_sell_linesClone_edit WHERE transaction_id=:transaction_id order by updated_at desc limit 1000", ["transaction_id" => $id]);
        }

        if ($type == 'purchase') {
            $data = DB::select("SELECT * FROM purchase_lines_edit  WHERE transaction_id=:transaction_id order by updated_at desc limit 1000", ["transaction_id" => $id]);
        }
        return view('sell.edithistroydetails')->with('data', $data)->with('type', $type);
    }



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function PurchaseHistory()
    {
        if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $purchases = $this->transactionUtil->getListPurchasesDelete($business_id);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $purchases->whereIn('transactions_clones.location_id', $permitted_locations);
            }

            if (!empty(request()->supplier_id)) {
                $purchases->where('contacts.id', request()->supplier_id);
            }
            if (!empty(request()->location_id)) {
                $purchases->where('transactions_clones.location_id', request()->location_id);
            }
            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $purchases->where('transactions_clones.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                $purchases->whereIn('transactions_clones.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions_clones.pay_term_number')
                    ->whereNotNull('transactions_clones.pay_term_type')
                    ->whereRaw("IF(transactions_clones.pay_term_type='days', DATE_ADD(transactions_clones.transaction_date, INTERVAL transactions_clones.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions_clones.transaction_date, INTERVAL transactions_clones.pay_term_number MONTH) < CURDATE())");
            }

            if (!empty(request()->status)) {
                $purchases->where('transactions_clones.status', request()->status);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $purchases->whereDate('transactions_clones.transaction_date', '>=', $start)
                    ->whereDate('transactions_clones.transaction_date', '<=', $end);
            }

            if (!auth()->user()->can('purchase.view') && auth()->user()->can('view_own_purchase')) {
                $purchases->where('transactions_clones.created_by', request()->session()->get('user.id'));
            }

            // Khair comment dont 23-Feb

            return Datatables::of($purchases)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false"><img src="img/gearload.png" width="30"><span class="caret"></span><span class="sr-only">Toggle Dropdown
                             </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    if (auth()->user()->can("purchase.view")) {
                        $html .= '<li><a href="#" data-href="' . action('PurchaseController@showhistory', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>' . __("messages.view") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.view")) {
                        //    $html .= '<li><a href="#" class="print-invoice" data-href="' . action('PurchaseController@printInvoice', [$row->id]) . '"><i class="fas fa-print" aria-hidden="true"></i>' . __("messages.print") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.update")) {
                        //    $html .= '<li><a href="' . action('PurchaseController@edit', [$row->id]) . '"><i class="fas fa-edit"></i>' . __("messages.edit") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.delete")) {
                        //    $html .= '<li><a href="' . action('PurchaseController@destroy', [$row->id]) . '" class="delete-purchase"><i class="fas fa-trash"></i>' . __("messages.delete") . '</a></li>';
                    }

                    // $html .= '<li><a href="' . action('LabelsController@show') . '?purchase_id=' . $row->id . '" data-toggle="tooltip" title="' . __('lang_v1.label_help') . '"><i class="fas fa-barcode"></i>' . __('barcode.labels') . '</a></li>';

                    if (auth()->user()->can("purchase.view") && !empty($row->document)) {
                        $document_name = !empty(explode("_", $row->document, 2)[1]) ? explode("_", $row->document, 2)[1] : $row->document;
                        $html .= '<li><a href="' . url('uploads/documents/' . $row->document) . '" download="' . $document_name . '"><i class="fas fa-download" aria-hidden="true"></i>' . __("purchase.download_document") . '</a></li>';
                        if (isFileImage($document_name)) {
                            $html .= '<li><a href="#" data-href="' . url('uploads/documents/' . $row->document) . '" class="view_uploaded_document"><i class="fas fa-image" aria-hidden="true"></i>' . __("lang_v1.view_document") . '</a></li>';
                        }
                    }

                    if (auth()->user()->can("purchase.create")) {
                        $html .= '<li class="divider"></li>';
                        if ($row->payment_status != 'paid' && auth()->user()->can("purchase.payments")) {
                            // $html .= '<li><a href="' . action('TransactionPaymentController@addPayment', [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("purchase.add_payment") . '</a></li>';
                        }
                        //$html .= '<li><a href="' . action('TransactionPaymentController@show', [$row->id]) .
                        //   '" class="view_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true" ></i>' . __("purchase.view_payments") . '</a></li>';
                    }

                    if (auth()->user()->can("purchase.update")) {
                        // $html .= '<li><a href="' . action('PurchaseReturnController@add', [$row->id]) .
                        //     '"><i class="fas fa-undo" aria-hidden="true" ></i>' . __("lang_v1.purchase_return") . '</a></li>';
                    }

                    if (auth()->user()->can("purchase.update") || auth()->user()->can("purchase.update_status")) {
                        // $html .= '<li><a href="#" data-purchase_id="' . $row->id .
                        //     '" data-status="' . $row->status . '" class="update_status"><i class="fas fa-edit" aria-hidden="true" ></i>' . __("lang_v1.update_status") . '</a></li>';
                    }

                    if ($row->status == 'ordered') {
                        // $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id, "template_for" => "new_order"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.new_order_notification") . '</a></li>';
                    } elseif ($row->status == 'received') {
                        // $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id, "template_for" => "items_received"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.item_received_notification") . '</a></li>';
                    } elseif ($row->status == 'pending') {
                        // $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id, "template_for" => "items_pending"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.item_pending_notification") . '</a></li>';
                    }

                    $html .=  '</ul></div>';
                    return $html;
                })
                ->removeColumn('id')
                ->editColumn('ref_no', function ($row) {
                    // Edit
                    $cheque_indicator = '';
                    $cheque = bankcheques_payment::where('transaction_id', $row->id);
                    if ($cheque->count() != 0) {
                        $cheque_indicator = '<i class="fas fa-money-bill-alt" aria-hidden="true"></i>';
                    }

                    return !empty($row->return_exists) ? $row->ref_no . ' <small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned') . '"><i class="fas fa-undo"></i></small>' : $row->ref_no . '  <strong style="color:brown;">' .  $cheque_indicator . '</strong>';
                })
                ->editColumn(
                    'final_total',
                    '<span class="final_total" data-orig-value="{{$final_total}}">@format_currency($final_total)</span>'
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn('name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$name}}')
                ->editColumn(
                    'status',
                    function ($row) {
                        return '<span style="font-weight:bold;">'  . __('lang_v1.' . $row->status) . '</span>';
                    }
                    // '<a href="#" @if(auth()->user()->can("purchase.update") || auth()->user()->can("purchase.update_status")) class="  no-print" data-purchase_id="{{$id}}" data-status="{{$status}}" @endif><span class="label @transaction_status($status) status-label" data-status-name="{{__(\'lang_v1.\' . $status)}}" data-orig-value="{{$status}}">{{__(\'lang_v1.\' . $status)}}
                    //     </span></a>'
                )
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        return __('lang_v1.' . $row->type);
                        // $payment_status = Transaction::getPaymentStatus($row);
                        // return (string) view('sell.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id, 'for_purchase' => true]);
                    }
                )
                ->addColumn('payment_due', function ($row) {
                    $due = $row->final_total - $row->amount_paid;
                    $due_html = '</strong> <span class="payment_due" data-orig-value="' . $due . '">' . $this->transactionUtil->num_f($due, true) . '</span>';

                    if (!empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $due_html .= '<br><strong>' . __('lang_v1.purchase_return') . ':</strong> <a href="' . action("TransactionPaymentController@show", [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="purchase_return" data-orig-value="' . $return_due . '">' . $this->transactionUtil->num_f($return_due, true) . '</span></a>';
                    }
                    return $due_html;
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("purchase.view")) {
                            return  action('PurchaseController@showhistory', [$row->id]);
                        } else {
                            return '';
                        }
                    }
                ])
                ->rawColumns(['final_total', 'action', 'payment_due', 'payment_status', 'status', 'ref_no', 'name'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->orderStatuses();

        return view('purchase.index_hist')
            ->with(compact('business_locations', 'suppliers', 'orderStatuses'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $purchases = $this->transactionUtil->getListPurchases($business_id);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $purchases->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->supplier_id)) {
                $purchases->where('contacts.id', request()->supplier_id);
            }
            if (!empty(request()->location_id)) {
                $purchases->where('transactions.location_id', request()->location_id);
            }
            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $purchases->where('transactions.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                $purchases->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
            }

            if (!empty(request()->status)) {
                $purchases->where('transactions.status', request()->status);
            }

            if (!empty(request()->payment_type)) {
                error_log("query_to_get_cheque");
                $transaction_with_cheque = [];
                $query_to_get_cheques = DB::select(DB::raw("Select a.transaction_id transactionIDTest from transaction_payments a,bankcheques_payments b where a.payment_ref_no =b.cheque_ref"));
                foreach ($query_to_get_cheques as $query_to_get_cheque) {
                    error_log($query_to_get_cheque->transactionIDTest);
                    error_log("query_to_get_cheque");
                    array_push($transaction_with_cheque, $query_to_get_cheque->transactionIDTest);
                }
                $purchases->whereIn('transactions.id', $transaction_with_cheque);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $purchases->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end);
            }

            if (!auth()->user()->can('purchase.view') && auth()->user()->can('view_own_purchase')) {
                $purchases->where('transactions.created_by', request()->session()->get('user.id'));
            }

            // Khair comment dont 23-Feb

            return Datatables::of($purchases)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false"><img src="img/gearload.png" width="30"><span class="caret"></span><span class="sr-only">Toggle Dropdown
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    if (auth()->user()->can("purchase.view")) {
                        $html .= '<li><a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>' . __("messages.view") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.view")) {
                        $html .= '<li><a href="#" class="print-invoice" data-href="' . action('PurchaseController@printInvoice', [$row->id]) . '"><i class="fas fa-print" aria-hidden="true"></i>' . __("messages.print") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.update")) {
                        $html .= '<li><a href="' . action('PurchaseController@edit', [$row->id]) . '"><i class="fas fa-edit"></i>' . __("messages.edit") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.delete")) {
                        // $html .= '<li><a href="' . action('PurchaseController@destroy', [$row->id]) . '" class="delete-purchase"><i class="fas fa-trash"></i>' . __("messages.delete") . '</a></li>';
                        $html .= '<li><a href="' . action('PurchaseController@deletePage', [$row->id]) . '" ><i class="fas fa-trash"></i>' . __("messages.delete") . '</a></li>';
                    }

                    // $html .= '<li><a href="' . action('LabelsController@show') . '?purchase_id=' . $row->id . '" data-toggle="tooltip" title="' . __('lang_v1.label_help') . '"><i class="fas fa-barcode"></i>' . __('barcode.labels') . '</a></li>';

                    if (auth()->user()->can("purchase.view") && !empty($row->document)) {
                        $document_name = !empty(explode("_", $row->document, 2)[1]) ? explode("_", $row->document, 2)[1] : $row->document;
                        $html .= '<li><a href="' . url('uploads/documents/' . $row->document) . '" download="' . $document_name . '"><i class="fas fa-download" aria-hidden="true"></i>' . __("purchase.download_document") . '</a></li>';
                        if (isFileImage($document_name)) {
                            $html .= '<li><a href="#" data-href="' . url('uploads/documents/' . $row->document) . '" class="view_uploaded_document"><i class="fas fa-image" aria-hidden="true"></i>' . __("lang_v1.view_document") . '</a></li>';
                        }
                    }

                    if (auth()->user()->can("purchase.create")) {
                        $html .= '<li class="divider"></li>';
                        if ($row->payment_status != 'paid' && auth()->user()->can("purchase.payments")) {
                            $html .= '<li><a href="' . action('TransactionPaymentController@addPayment', [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("purchase.add_payment") . '</a></li>';
                        }
                        $html .= '<li><a href="' . action('TransactionPaymentController@show', [$row->id]) .
                            '" class="view_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true" ></i>' . __("purchase.view_payments") . '</a></li>';
                    }

                    if (auth()->user()->can("purchase.update")) {
                        $html .= '<li><a href="' . action('PurchaseReturnController@add', [$row->id]) .
                            '"><i class="fas fa-undo" aria-hidden="true" ></i>' . __("lang_v1.purchase_return") . '</a></li>';
                    }

                    if (auth()->user()->can("purchase.update") || auth()->user()->can("purchase.update_status")) {
                        $html .= '<li><a href="#" data-purchase_id="' . $row->id .
                            '" data-status="' . $row->status . '" class="update_status"><i class="fas fa-edit" aria-hidden="true" ></i>' . __("lang_v1.update_status") . '</a></li>';
                    }

                    if ($row->status == 'ordered') {
                        // $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id, "template_for" => "new_order"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.new_order_notification") . '</a></li>';
                    } elseif ($row->status == 'received') {
                        // $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id, "template_for" => "items_received"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.item_received_notification") . '</a></li>';
                    } elseif ($row->status == 'pending') {
                        // $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id, "template_for" => "items_pending"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.item_pending_notification") . '</a></li>';
                    }

                    $html .=  '</ul></div>';
                    return $html;
                })
                ->removeColumn('id')
                ->editColumn('ref_no', function ($row) {

                    // Edit
                    $cheque_indicator = '';
                    $cheque = bankcheques_payment::where('transaction_id', $row->id)->where('status', 'unset');
                    if ($cheque->count() != 0) {
                        $cheque_indicator = '<span>cheque</span><i class="fas fa-money-bill-alt" aria-hidden="true"></i>';
                    }

                    $refund_indicator = '';
                    $refund = RefundTable::where('transaction_id', $row->id)->where('status', 0);
                    if ($refund->count() != 0) {
                        $refund_indicator = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cash-coin" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0z"/>
                        <path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1h-.003zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195l.054.012z"/>
                        <path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083c.058-.344.145-.678.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1H1z"/>
                        <path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 5.982 5.982 0 0 1 3.13-1.567z"/>
                      </svg>';
                    }
                    return !empty($row->return_exists) ? $row->ref_no . ' <small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned') . '"><i class="fas fa-undo"></i></small>' : $row->ref_no . '  <strong style="color:brown;">' .  $cheque_indicator . '<br>' . $refund_indicator . '</strong>';
                })
                ->editColumn(
                    'final_total',
                    '<span class="final_total" data-orig-value="{{$final_total}}">@format_currency($final_total)</span>'
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn('name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$name}}')
                ->editColumn(
                    'status',
                    '<a href="#" @if(auth()->user()->can("purchase.update") || auth()->user()->can("purchase.update_status")) class="update_status no-print" data-purchase_id="{{$id}}" data-status="{{$status}}" @endif><span class="label @transaction_status($status) status-label" data-status-name="{{__(\'lang_v1.\' . $status)}}" data-orig-value="{{$status}}">{{__(\'lang_v1.\' . $status)}}
                        </span></a>'
                )
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);
                        return (string) view('sell.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id, 'for_purchase' => true]);
                    }
                )
                ->addColumn('payment_due', function ($row) {
                    $due = $row->final_total - $row->amount_paid;
                    $due_html = '<strong>' . __('lang_v1.purchase') . ':</strong> <span class="payment_due" data-orig-value="' . $due . '">' . $this->transactionUtil->num_f($due, true) . '</span>';


                    if (!empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $due_html .= '<br><strong>' . __('lang_v1.purchase_return') . ':</strong> <a href="' . action("TransactionPaymentController@show", [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="purchase_return" data-orig-value="' . $return_due . '">' . $this->transactionUtil->num_f($return_due, true) . '</span></a>';
                    }
                    return $due_html;
                })  
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("purchase.view")) {
                            return  action('PurchaseController@show', [$row->id]);
                        } else {
                            return '';
                        }
                    }
                ])
                ->rawColumns(['final_total', 'action', 'payment_due', 'payment_status', 'status', 'ref_no', 'name'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->orderStatuses();

        return view('purchase.index')
            ->with(compact('business_locations', 'suppliers', 'orderStatuses'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function purchase_table_deleted2()
    {
        if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create') && !auth()->user()->can('view_own_purchase')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $purchases = $this->transactionUtil->getListPurchasesDelete($business_id);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $purchases->whereIn('transactions_clones.location_id', $permitted_locations);
            }

            if (!empty(request()->supplier_id)) {
                $purchases->where('contacts.id', request()->supplier_id);
            }
            if (!empty(request()->location_id)) {
                $purchases->where('transactions_clones.location_id', request()->location_id);
            }
            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $purchases->where('transactions_clones.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                $purchases->whereIn('transactions_clones.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions_clones.pay_term_number')
                    ->whereNotNull('transactions_clones.pay_term_type')
                    ->whereRaw("IF(transactions_clones.pay_term_type='days', DATE_ADD(transactions_clones.transaction_date, INTERVAL transactions_clones.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions_clones.transaction_date, INTERVAL transactions_clones.pay_term_number MONTH) < CURDATE())");
            }

            if (!empty(request()->status)) {
                $purchases->where('transactions_clones.status', request()->status);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $purchases->whereDate('transactions_clones.transaction_date', '>=', $start)
                    ->whereDate('transactions_clones.transaction_date', '<=', $end);
            }

            if (!auth()->user()->can('purchase.view') && auth()->user()->can('view_own_purchase')) {
                $purchases->where('transactions_clones.created_by', request()->session()->get('user.id'));
            }

            // Khair comment dont 23-Feb

            return Datatables::of($purchases)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false"><img src="img/gearload.png" width="30"><span class="caret"></span><span class="sr-only">Toggle Dropdown
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    if (auth()->user()->can("purchase.view")) {
                        $html .= '<li><a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>' . __("messages.view") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.view")) {
                        $html .= '<li><a href="#" class="print-invoice" data-href="' . action('PurchaseController@printInvoice', [$row->id]) . '"><i class="fas fa-print" aria-hidden="true"></i>' . __("messages.print") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.update")) {
                        $html .= '<li><a href="' . action('PurchaseController@edit', [$row->id]) . '"><i class="fas fa-edit"></i>' . __("messages.edit") . '</a></li>';
                    }
                    if (auth()->user()->can("purchase.delete")) {
                        $html .= '<li><a href="' . action('PurchaseController@destroy', [$row->id]) . '" class="delete-purchase"><i class="fas fa-trash"></i>' . __("messages.delete") . '</a></li>';
                    }

                    // $html .= '<li><a href="' . action('LabelsController@show') . '?purchase_id=' . $row->id . '" data-toggle="tooltip" title="' . __('lang_v1.label_help') . '"><i class="fas fa-barcode"></i>' . __('barcode.labels') . '</a></li>';

                    if (auth()->user()->can("purchase.view") && !empty($row->document)) {
                        $document_name = !empty(explode("_", $row->document, 2)[1]) ? explode("_", $row->document, 2)[1] : $row->document;
                        $html .= '<li><a href="' . url('uploads/documents/' . $row->document) . '" download="' . $document_name . '"><i class="fas fa-download" aria-hidden="true"></i>' . __("purchase.download_document") . '</a></li>';
                        if (isFileImage($document_name)) {
                            $html .= '<li><a href="#" data-href="' . url('uploads/documents/' . $row->document) . '" class="view_uploaded_document"><i class="fas fa-image" aria-hidden="true"></i>' . __("lang_v1.view_document") . '</a></li>';
                        }
                    }

                    if (auth()->user()->can("purchase.create")) {
                        $html .= '<li class="divider"></li>';
                        if ($row->payment_status != 'paid' && auth()->user()->can("purchase.payments")) {
                            $html .= '<li><a href="' . action('TransactionPaymentController@addPayment', [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("purchase.add_payment") . '</a></li>';
                        }
                        $html .= '<li><a href="' . action('TransactionPaymentController@show', [$row->id]) .
                            '" class="view_payment_modal"><i class="fas fa-money-bill-alt" aria-hidden="true" ></i>' . __("purchase.view_payments") . '</a></li>';
                    }

                    if (auth()->user()->can("purchase.update")) {
                        $html .= '<li><a href="' . action('PurchaseReturnController@add', [$row->id]) .
                            '"><i class="fas fa-undo" aria-hidden="true" ></i>' . __("lang_v1.purchase_return") . '</a></li>';
                    }

                    if (auth()->user()->can("purchase.update") || auth()->user()->can("purchase.update_status")) {
                        $html .= '<li><a href="#" data-purchase_id="' . $row->id .
                            '" data-status="' . $row->status . '" class="update_status"><i class="fas fa-edit" aria-hidden="true" ></i>' . __("lang_v1.update_status") . '</a></li>';
                    }

                    if ($row->status == 'ordered') {
                        // $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id, "template_for" => "new_order"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.new_order_notification") . '</a></li>';
                    } elseif ($row->status == 'received') {
                        // $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id, "template_for" => "items_received"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.item_received_notification") . '</a></li>';
                    } elseif ($row->status == 'pending') {
                        // $html .= '<li><a href="#" data-href="' . action('NotificationController@getTemplate', ["transaction_id" => $row->id, "template_for" => "items_pending"]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-envelope" aria-hidden="true"></i> ' . __("lang_v1.item_pending_notification") . '</a></li>';
                    }

                    $html .=  '</ul></div>';
                    return $html;
                })
                ->removeColumn('id')
                ->editColumn('ref_no', function ($row) {
                    // Edit cheque_indicator
                    $cheque_indicator = '';
                    $cheque = bankcheques_payment::where('transaction_id', $row->id)->where('status', 'unset');
                    if ($cheque->count() != 0) {
                        $cheque_indicator = '<i class="fas fa-money-bill-alt" aria-hidden="true">' . __('cheque.cheque') . '</i>';
                    }
                    return !empty($row->return_exists) ? $row->ref_no . ' <small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned') . '"><i class="fas fa-undo"></i></small>' : $row->ref_no . '  <strong style="color:brown;"> ' .  $cheque_indicator . '</strong>';
                })
                ->editColumn(
                    'final_total',
                    '<span class="final_total" data-orig-value="{{$final_total}}">@format_currency($final_total)</span>'
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn('name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$name}}')
                ->editColumn(
                    'status',
                    '<a href="#" @if(auth()->user()->can("purchase.update") || auth()->user()->can("purchase.update_status")) class="update_status no-print" data-purchase_id="{{$id}}" data-status="{{$status}}" @endif><span class="label @transaction_status($status) status-label" data-status-name="{{__(\'lang_v1.\' . $status)}}" data-orig-value="{{$status}}">{{__(\'lang_v1.\' . $status)}}
                        </span></a>'
                )
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);
                        return (string) view('sell.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id, 'for_purchase' => true]);
                    }
                )
                ->addColumn('payment_due', function ($row) {
                    $due = $row->final_total - $row->amount_paid;
                    $due_html = '<strong>' . __('lang_v1.purchase') . ':</strong> <span class="payment_due" data-orig-value="' . $due . '">' . $this->transactionUtil->num_f($due, true) . '</span>';


                    if (!empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $due_html .= '<br><strong>' . __('lang_v1.purchase_return') . ':</strong> <a href="' . action("TransactionPaymentController@show", [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="purchase_return" data-orig-value="' . $return_due . '">' . $this->transactionUtil->num_f($return_due, true) . '</span></a>';
                    }
                    return $due_html;
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("purchase.view")) {
                            return  action('PurchaseController@show', [$row->id]);
                        } else {
                            return '';
                        }
                    }
                ])
                ->rawColumns(['final_total', 'action', 'payment_due', 'payment_status', 'status', 'ref_no', 'name'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);
        $orderStatuses = $this->productUtil->orderStatuses();

        return view('purchase.index')
            ->with(compact('business_locations', 'suppliers', 'orderStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {


        // return auth()->user()->permitted_locations();

        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();
        $orderStatuses = $this->productUtil->orderStatuses();
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->productUtil->payment_types(null, true, $business_id);

        // $payment_line['cheque_date']='';



        //Accounts
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);
        $accounts_cheques = $this->moduleUtil->accountsDropdown_cheque($business_id, true);

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        return view('purchase.create')
            ->with(compact('business_details', 'taxes', 'orderStatuses', 'business_locations', 'currency_details', 'default_purchase_status', 'customer_groups', 'types', 'shortcuts', 'payment_line', 'payment_types', 'accounts', 'accounts_cheques', 'bl_attributes', 'common_settings'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('PurchaseController@index'));
            }

            $transaction_data = $request->only(['ref_no', 'status', 'contact_id', 'transaction_date', 'total_before_tax', 'location_id', 'discount_type', 'discount_amount', 'tax_id', 'tax_amount', 'shipping_details', 'shipping_charges', 'final_total', 'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids']);
            $refund_total = $request->get('refund_total');

            $exchange_rate = $transaction_data['exchange_rate'];


            // Khair
            //Reverse exchange rate and save it.
            //$transaction_data['exchange_rate'] = $transaction_data['exchange_rate'];

            //TODO: Check for "Undefined index: total_before_tax" issue
            //Adding temporary fix by validating
            $request->validate([
                'status' => 'required',
                'contact_id' => 'required',
                'transaction_date' => 'required',
                'total_before_tax' => 'required',
                'location_id' => 'required',
                'final_total' => 'required',
                'document' => 'file|max:' . (config('constants.document_size_limit') / 1000)
            ]);

            $user_id = $request->session()->get('user.id');
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

            //Update business exchange rate.
            Business::update_business($business_id, ['p_exchange_rate' => ($transaction_data['exchange_rate'])]);

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            //unformat input values
            $transaction_data['total_before_tax'] = $this->productUtil->num_uf($transaction_data['total_before_tax'], $currency_details) * $exchange_rate;

            // If discount type is fixed them multiply by exchange rate, else don't
            if ($transaction_data['discount_type'] == 'fixed') {
                $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details) * $exchange_rate;
            } elseif ($transaction_data['discount_type'] == 'percentage') {
                $transaction_data['discount_amount'] = $this->productUtil->num_uf($transaction_data['discount_amount'], $currency_details);
            } else {
                $transaction_data['discount_amount'] = 0;
            }

            $transaction_data['tax_amount'] = $this->productUtil->num_uf($transaction_data['tax_amount'], $currency_details) * $exchange_rate;
            $transaction_data['shipping_charges'] = $this->productUtil->num_uf($transaction_data['shipping_charges'], $currency_details) * $exchange_rate;
            $transaction_data['final_total'] = $this->productUtil->num_uf($transaction_data['final_total'], $currency_details) * $exchange_rate;

            $transaction_data['business_id'] = $business_id;
            $transaction_data['created_by'] = $user_id;
            $transaction_data['type'] = 'purchase';
            $transaction_data['payment_status'] = 'due';
            $transaction_data['transaction_date'] = $this->productUtil->uf_date($transaction_data['transaction_date'], true);

            //upload document
            $transaction_data['document'] = $this->transactionUtil->uploadFile($request, 'document', 'documents');

            $transaction_data['custom_field_1'] = $request->input('custom_field_1', null);
            $transaction_data['custom_field_2'] = $request->input('custom_field_2', null);
            $transaction_data['custom_field_3'] = $request->input('custom_field_3', null);
            $transaction_data['custom_field_4'] = $request->input('custom_field_4', null);

            $transaction_data['shipping_custom_field_1'] = $request->input('shipping_custom_field_1', null);
            $transaction_data['shipping_custom_field_2'] = $request->input('shipping_custom_field_2', null);
            $transaction_data['shipping_custom_field_3'] = $request->input('shipping_custom_field_3', null);
            $transaction_data['shipping_custom_field_4'] = $request->input('shipping_custom_field_4', null);
            $transaction_data['shipping_custom_field_5'] = $request->input('shipping_custom_field_5', null);

            if ($request->input('additional_expense_value_1') != '') {
                $transaction_data['additional_expense_key_1'] = $request->input('additional_expense_key_1');
                $transaction_data['additional_expense_value_1'] = $this->productUtil->num_uf($request->input('additional_expense_value_1'), $currency_details) * $exchange_rate;
            }

            if ($request->input('additional_expense_value_2') != '') {
                $transaction_data['additional_expense_key_2'] = $request->input('additional_expense_key_2');
                $transaction_data['additional_expense_value_2'] = $this->productUtil->num_uf($request->input('additional_expense_value_2'), $currency_details) * $exchange_rate;
            }

            if ($request->input('additional_expense_value_3') != '') {
                $transaction_data['additional_expense_key_3'] = $request->input('additional_expense_key_3');
                $transaction_data['additional_expense_value_3'] = $this->productUtil->num_uf($request->input('additional_expense_value_3'), $currency_details) * $exchange_rate;
            }

            if ($request->input('additional_expense_value_4') != '') {
                $transaction_data['additional_expense_key_4'] = $request->input('additional_expense_key_4');
                $transaction_data['additional_expense_value_4'] = $this->productUtil->num_uf($request->input('additional_expense_value_4'), $currency_details) * $exchange_rate;
            }

            DB::beginTransaction();

            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount($transaction_data['type']);
            //Generate reference number
            if (empty($transaction_data['ref_no'])) {
                $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber($transaction_data['type'], $ref_count);
            }

            $transaction = Transaction::create($transaction_data);

            $purchase_lines = [];
            $purchases = $request->input('purchases');

            $this->productUtil->createOrUpdatePurchaseLines($transaction, $purchases, $currency_details, $enable_product_editing);

            //Add Purchase payments
            $this->transactionUtil->createOrUpdatePaymentLines($transaction, $request->input('payment'));

            //update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            if (!empty($transaction->purchase_order_ids)) {
                $this->transactionUtil->updatePurchaseOrderStatus($transaction->purchase_order_ids);
            }

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            $this->transactionUtil->activityLog($transaction, 'added');

            DB::commit();

            // khair 11-Nov-2022
            $ItemsPurchases = PurchaseLine::where("transaction_id", $transaction->id)->get();
            $now = \Carbon::now()->toDateTimeString();
            foreach ($ItemsPurchases as $ItemsPurchase) {
                DB::select(DB::raw("INSERT INTO transaction_sell_lines_delivery (id,rowid,transaction_id,product_id,quantity,created) values (:id,:rowid,:transaction_id,:product_id,:quantity,:created)"), ["rowid" => $ItemsPurchase->id, "transaction_id" => $ItemsPurchase->transaction_id, "product_id" => $ItemsPurchase->product_id, "quantity" => $ItemsPurchase->quantity, "created" => $now, "id" => 0]);
            }

            // Add refund 
            if ($refund_total > 0) {
                $AddRefund = new RefundTable();
                $AddRefund->transaction_id = $transaction->id;
                $AddRefund->contact_id = $transaction_data['contact_id'];
                $AddRefund->refund_total = $refund_total;
                $AddRefund->business_id = $business_id;
                $AddRefund->save();
            }

            $output = [
                'success' => 1,
                'msg' => __('purchase.purchase_add_success')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
                // 'msg' => $e->getMessage()
            ];
        }

        return redirect('purchases')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showhistory($id)
    {

        // return $id;
        $business_id = request()->session()->get('user.business_id');
        $purchase = transactionsClone::where('business_id', $business_id)
            ->where('id', $id)->first();

        try {
            $nameExp = ExpenseCategory::where('id', $purchase->expense_category_id)->first()->name;
        } catch (Exception $e) {
            $nameExp = "";
        }

        // return  $purchase->type;
        // error_log($purchase);
        $purchase_Table = '<div class="col-md-12"><h5>تفاصيل العملية</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tbody>
                            <tr>
                                <th>تاريخ</th>
                                <th>الرقم المرجعي</th>
                                <th>حاله الفاتوره</th>
                                <th>حاله  </th>
                                <th> الدفع</th>
                                <th>ملاحظة</th>
                            </tr>
                            <tr class="text-center">
                                <td>' . $purchase->transaction_date . '</td>
                                <td>' . $purchase->ref_no . '</td>
                                <td>' .  $purchase->payment_status  . '</td>
                                <td>' .  $purchase->type  . ' ' . $nameExp  .  '</td>
                                <td>' .  $purchase->final_total   . '</td>
                                <td>' .  $purchase->updated_at    . '</td>
                            </tr>
                        </tbody>
                      </table>
                    </div>
                </div>';


        $accountpay_Table = '<div class="col-md-12"><h5>تفاصيل المدفوعات</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <tbody>
                        <tr>
                            <th>' . __('account.account_name') . '</th>
                            <th>' . __('lang_v1.total_paying') . '</th>
                            <th>reff_no</th>
                            <th>created_by</th>
                            <th>note</th>
                            <th>' . __('account.transaction_type') . '</th>
                            <th>deleted_at</th>
                        </tr>';
        $accountpay = accounttransactionsClone::where('transaction_id', $id)->get();
        foreach ($accountpay as $py) {
            $accountpay_Table .= '<tr class="text-center">
            <td>' . Account::where('id', $py->account_id)->first()->name . '</td>
            <td>' . $py->amount . '</td>
            <td>' . $py->reff_no  . '</td>
            <td>' . User::where('id', $py->created_by)->first()->username   . '</td>
            <td>' . $py->note     . '</td>
            <td>' . $py->type     . '</td>
            <td>' . $py->created_at    . '</td>
        </tr>';
        }

        $accountpay_Table .= '</tbody>
        </table>
      </div>
  </div>';



        $purchase_line_Table = '<div class="col-md-12"><h5>تفاصيل المنتجات</h5>
  <div class="table-responsive">
      <table class="table table-striped">
          <tbody>
          <tr>
              <th>' . __('product.name') . '</th>
              <th>quantity </th>
              <th>price </th>
              <th>created_at </th>
          </tr>';
        if ($purchase->type == 'sell') {
            error_log("Sells _____________");
            $purchase_line = transaction_sell_linesClone::where('transaction_id', $id)->get();
            //   $purchase_line = DB::select( DB::raw("select * from transaction_sell_linesClone where transaction_id=:id",["id"=>$id]));
        } elseif ($purchase->type == 'purchase') {
            error_log("Purchase _____________");
            // $purchase_line = transaction_sell_linesClone
            $purchase_line = purchaselinesClone::where('transaction_id', $id)->get();
        }
        // $purchase_line = purchaselinesClone::where('transaction_id', $id)->get();
        foreach ($purchase_line as $pyx) {
            $purchase_line_Table .= '<tr class="text-center">
    <td>' . Product::where('id', $pyx->product_id)->first()->name . '</td>
    <td>' . $pyx->quantity  . '</td>
    <td>' . $pyx->purchase_price   .  $pyx->unit_price . '</td>
    <td>' . $pyx->created_at      . '</td>
</tr>';
        }
        $purchase_line_Table .= '</tbody>
</table>
</div>
</div>';

        $resone = DB::select(DB::raw("select username,deletereasone,insert_at from deletereasone a,users b where b.id=a.userid and a.transaction_id=:id"), ["id" => $id]);
        $delete = "<p><bt>" . $resone[0]->insert_at . "<br>" . $resone[0]->username . "<br>" . $resone[0]->deletereasone . "<br></p>";
        $html = '<div class="modal-dialog modal-xl" role="document" >
        <div class="modal-content" >
          <div class="modal-footer" style="text-align:right!important;"><section class="content-header no-print">
          <h1>ألمشتريات الملغية<small></small>
          </h1>
      </section>' . $purchase_Table . $accountpay_Table . $purchase_line_Table . $delete . '     
            <button type="button" class="btn btn-primary no-print" aria-label="Print" 
            onclick="$(this).closest(\'div.modal-content\').printThis();"><i class="fa fa-print"></i> ' . __('messages.print') . '
            </button>
            <button type="button" class="btn btn-default no-print" data-dismiss="modal"> ' . __('messages.close') . '</button>
          </div>
        </div>
      </div>';


        //  $html ="ererferf";
        return  $html;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // if (!auth()->user()->can('purchase.view')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
            ->pluck('name', 'id');
        $purchase = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(
                'contact',
                'purchase_lines',
                'purchase_lines.product',
                'purchase_lines.product.unit',
                'purchase_lines.variations',
                'purchase_lines.variations.product_variation',
                'purchase_lines.sub_unit',
                'location',
                'payment_lines',
                'tax'
            )
            ->firstOrFail();

        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }

        $payment_methods = $this->productUtil->payment_types($purchase->location_id, true);

        $purchase_taxes = [];
        if (!empty($purchase->tax)) {
            if ($purchase->tax->is_tax_group) {
                $purchase_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($purchase->tax, $purchase->tax_amount));
            } else {
                $purchase_taxes[$purchase->tax->name] = $purchase->tax_amount;
            }
        }

        //Purchase orders
        $purchase_order_nos = '';
        $purchase_order_dates = '';
        if (!empty($purchase->purchase_order_ids)) {
            $purchase_orders = Transaction::find($purchase->purchase_order_ids);

            $purchase_order_nos = implode(', ', $purchase_orders->pluck('ref_no')->toArray());
            $order_dates = [];
            foreach ($purchase_orders as $purchase_order) {
                $order_dates[] = $this->transactionUtil->format_date($purchase_order->transaction_date, true);
            }
            $purchase_order_dates = implode(', ', $order_dates);
        }

        $activities = Activity::forSubject($purchase)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        $statuses = $this->productUtil->orderStatuses();

        $refund = RefundTable::where('transaction_id', $id)->first();

        return view('purchase.show')
            ->with(compact('taxes', 'purchase', 'payment_methods', 'purchase_taxes', 'activities', 'statuses', 'purchase_order_nos', 'purchase_order_dates', 'refund'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('PurchaseController@index'));
        }

        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', [
                    'success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])
                ]);
        }

        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', [
                'success' => 0,
                'msg' => __('lang_v1.return_exist')
            ]);
        }

        $business = Business::find($business_id);

        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();



        $purchase = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(
                'contact',
                'purchase_lines',
                'purchase_lines.product',
                'purchase_lines.product.unit',
                //'purchase_lines.product.unit.sub_units',
                'purchase_lines.variations',
                'purchase_lines.variations.product_variation',
                'location',
                'purchase_lines.sub_unit',
                'purchase_lines.purchase_order_line'
            )
            ->first();

        foreach ($purchase->purchase_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_purchase_line = $this->productUtil->changePurchaseLineUnit($value, $business_id);
                $purchase->purchase_lines[$key] = $formated_purchase_line;
            }
        }

        $orderStatuses = $this->productUtil->orderStatuses();

        $business_locations = BusinessLocation::forDropdown($business_id);

        $default_purchase_status = null;
        if (request()->session()->get('business.enable_purchase_status') != 1) {
            $default_purchase_status = 'received';
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];

        $purchase_orders = null;
        if (!empty($common_settings['enable_purchase_order'])) {
            $purchase_orders = Transaction::where('business_id', $business_id)
                ->where('type', 'purchase_order')
                ->where('contact_id', $purchase->contact_id)
                ->where(function ($q) use ($purchase) {
                    $q->where('status', '!=', 'completed');

                    if (!empty($purchase->purchase_order_ids)) {
                        $q->orWhereIn('id', $purchase->purchase_order_ids);
                    }
                })
                ->pluck('ref_no', 'id');
        }

        return view('purchase.edit')
            ->with(compact(
                'taxes',
                'purchase',
                'orderStatuses',
                'business_locations',
                'business',
                'currency_details',
                'default_purchase_status',
                'customer_groups',
                'types',
                'shortcuts',
                'purchase_orders',
                'common_settings',
                'business_details'
            ));
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
        if (!auth()->user()->can('purchase.update')) {
            abort(403, 'Unauthorized action.');
        }

        // return $id;

        try {
            // SnapShot
            DB::statement("insert into transactions_clones_edit select * from transactions where id=:id", ["id" => $id]);
            DB::statement("insert into purchase_lines_edit select * from purchase_lines where transaction_id=:id", ["id" => $id]);

            $transaction = Transaction::findOrFail($id);

            //Validate document size
            $request->validate([
                'document' => 'file|max:' . (config('constants.document_size_limit') / 1000)
            ]);

            $transaction = Transaction::findOrFail($id);
            $before_status = $transaction->status;
            $business_id = request()->session()->get('user.business_id');
            $enable_product_editing = $request->session()->get('business.enable_editing_product_from_purchase');

            $transaction_before = $transaction->replicate();

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            $update_data = $request->only([
                'ref_no', 'status', 'contact_id',
                'transaction_date', 'total_before_tax',
                'discount_type', 'discount_amount', 'tax_id',
                'tax_amount', 'shipping_details',
                'shipping_charges', 'final_total',
                'additional_notes', 'exchange_rate', 'pay_term_number', 'pay_term_type', 'purchase_order_ids'
            ]);

            $exchange_rate = $update_data['exchange_rate'];

            //Reverse exchage rate and save
            //$update_data['exchange_rate'] = number_format(1 / $update_data['exchange_rate'], 2);

            $update_data['transaction_date'] = $this->productUtil->uf_date($update_data['transaction_date'], true);

            //unformat input values
            $update_data['total_before_tax'] = $this->productUtil->num_uf($update_data['total_before_tax'], $currency_details) * $exchange_rate;

            // If discount type is fixed them multiply by exchange rate, else don't
            if ($update_data['discount_type'] == 'fixed') {
                $update_data['discount_amount'] = $this->productUtil->num_uf($update_data['discount_amount'], $currency_details) * $exchange_rate;
            } elseif ($update_data['discount_type'] == 'percentage') {
                $update_data['discount_amount'] = $this->productUtil->num_uf($update_data['discount_amount'], $currency_details);
            } else {
                $update_data['discount_amount'] = 0;
            }

            $update_data['tax_amount'] = $this->productUtil->num_uf($update_data['tax_amount'], $currency_details) * $exchange_rate;
            $update_data['shipping_charges'] = $this->productUtil->num_uf($update_data['shipping_charges'], $currency_details) * $exchange_rate;
            $update_data['final_total'] = $this->productUtil->num_uf($update_data['final_total'], $currency_details) * $exchange_rate;
            //unformat input values ends

            $update_data['custom_field_1'] = $request->input('custom_field_1', null);
            $update_data['custom_field_2'] = $request->input('custom_field_2', null);
            $update_data['custom_field_3'] = $request->input('custom_field_3', null);
            $update_data['custom_field_4'] = $request->input('custom_field_4', null);

            $update_data['shipping_custom_field_1'] = $request->input('shipping_custom_field_1', null);
            $update_data['shipping_custom_field_2'] = $request->input('shipping_custom_field_2', null);
            $update_data['shipping_custom_field_3'] = $request->input('shipping_custom_field_3', null);
            $update_data['shipping_custom_field_4'] = $request->input('shipping_custom_field_4', null);
            $update_data['shipping_custom_field_5'] = $request->input('shipping_custom_field_5', null);

            //upload document
            $document_name = $this->transactionUtil->uploadFile($request, 'document', 'documents');
            if (!empty($document_name)) {
                $update_data['document'] = $document_name;
            }

            $purchase_order_ids = $transaction->purchase_order_ids ?? [];

            $update_data['additional_expense_key_1'] = $request->input('additional_expense_key_1');
            $update_data['additional_expense_key_2'] = $request->input('additional_expense_key_2');
            $update_data['additional_expense_key_3'] = $request->input('additional_expense_key_3');
            $update_data['additional_expense_key_4'] = $request->input('additional_expense_key_4');

            $update_data['additional_expense_value_1'] = $request->input('additional_expense_value_1') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_1'), $currency_details) * $exchange_rate : 0;
            $update_data['additional_expense_value_2'] = $request->input('additional_expense_value_2') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_2'), $currency_details) * $exchange_rate : 0;
            $update_data['additional_expense_value_3'] = $request->input('additional_expense_value_3') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_3'), $currency_details) * $exchange_rate : 0;
            $update_data['additional_expense_value_4'] = $request->input('additional_expense_value_4') != '' ? $this->productUtil->num_uf($request->input('additional_expense_value_4'), $currency_details) * $exchange_rate : 0;

            DB::beginTransaction();

            //update transaction
            $transaction->update($update_data);

            //Update transaction payment status
            $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id);
            $transaction->payment_status = $payment_status;

            $purchases = $request->input('purchases');

            $delete_purchase_lines = $this->productUtil->createOrUpdatePurchaseLines($transaction, $purchases, $currency_details, $enable_product_editing, $before_status);

            //Update mapping of purchase & Sell.
            $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, $delete_purchase_lines);

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            $new_purchase_order_ids = $transaction->purchase_order_ids ?? [];
            $purchase_order_ids = array_merge($purchase_order_ids, $new_purchase_order_ids);
            if (!empty($purchase_order_ids)) {
                $this->transactionUtil->updatePurchaseOrderStatus($purchase_order_ids);
            }

            $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('purchase.purchase_update_success')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage()
            ];
            return back()->with('status', $output);
        }

        return redirect('purchases')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('purchase.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if (request()->ajax()) {
                $business_id = request()->session()->get('user.business_id');

                // Khair add restrinction for delete 
                $refund = RefundTable::where('transaction_id', $id)->where('status', 1)->where('business_id', $business_id)->exists();
                if ($refund) {
                    $output = [
                        'success' => false,
                        'msg' => __('messages.refund_purchase')
                    ];
                    return $output;
                }

                //Check if return exist then not allowed
                if ($this->transactionUtil->isReturnExist($id)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.return_exist')
                    ];
                    return $output;
                }
                $transaction = Transaction::where('id', $id)
                    ->where('business_id', $business_id)
                    ->with(['purchase_lines'])
                    ->first();




                //Check if lot numbers from the purchase is selected in sale
                if (request()->session()->get('business.enable_lot_number') == 1 && $this->transactionUtil->isLotUsed($transaction)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.lot_numbers_are_used_in_sale')
                    ];
                    return $output;
                }

                $delete_purchase_lines = $transaction->purchase_lines;
                DB::beginTransaction();

                $log_properities = [
                    'id' => $transaction->id,
                    'ref_no' => $transaction->ref_no
                ];
                $this->transactionUtil->activityLog($transaction, 'purchase_deleted', $log_properities);

                $transaction_status = $transaction->status;
                if ($transaction_status != 'received') {
                    // backup Transaction ::Khair 11-Apr
                    DB::statement("INSERT INTO transactions_clones SELECT * FROM transactions WHERE id=:id", ["id" => $id]);
                    $transaction->delete();
                } else {
                    //Delete purchase lines first
                    $delete_purchase_line_ids = [];
                    foreach ($delete_purchase_lines as $purchase_line) {
                        $delete_purchase_line_ids[] = $purchase_line->id;
                        $this->productUtil->decreaseProductQuantity(
                            $purchase_line->product_id,
                            $purchase_line->variation_id,
                            $transaction->location_id,
                            $purchase_line->quantity
                        );
                    }

                    // backup PurchaseLine ::Khair 11-Apr
                    try {
                        foreach ($delete_purchase_line_ids as $delete_purchase_line_id) {
                            DB::statement(
                                "INSERT INTO purchaselines_clones  SELECT * FROM purchase_lines  WHERE transaction_id=:transaction_id and id=:delete_purchase_line_id",
                                ["transaction_id" => $id, "delete_purchase_line_id" => $delete_purchase_line_id]
                            );
                        }
                    } catch (Exception $e) {
                        error_log($e);
                    }

                    PurchaseLine::where('transaction_id', $transaction->id)
                        ->whereIn('id', $delete_purchase_line_ids)
                        ->delete();

                    //Update mapping of purchase & Sell.
                    $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($transaction_status, $transaction, $delete_purchase_lines);
                }

                // backup Transaction ::Khair 11-Apr
                DB::statement("INSERT INTO transactions_clones SELECT * FROM transactions WHERE id=:id", ["id" => $id]);


                //Delete Transaction
                $transaction->delete();


                // backup account transactions::Khair 11-Apr
                DB::statement("INSERT INTO accounttransactions_clones  SELECT * FROM account_transactions  WHERE transaction_id=:transaction_id", ["transaction_id" => $id]);
                //Delete account transactions
                AccountTransaction::where('transaction_id', $id)->delete();
                DB::commit();
                $refund = RefundTable::where('transaction_id', $id)->where('business_id', $business_id)
                    ->update(["status" => 3]);
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.purchase_delete_success')
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }

        return $output;
    }

    /**
     * Retrieves supliers list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSuppliers()
    {
        if (request()->ajax()) {
            $term = request()->q;
            if (empty($term)) {
                return json_encode([]);
            }

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $query = Contact::where('business_id', $business_id)
                ->active();

            $selected_contacts = User::isSelectedContacts($user_id);
            if ($selected_contacts) {
                $query->join('user_contact_access AS uca', 'contacts.id', 'uca.contact_id')
                    ->where('uca.user_id', $user_id);
            }
            $suppliers = $query->where(function ($query) use ($term) {
                $query->where('name', 'like', '%' . $term . '%')
                    ->orWhere('supplier_business_name', 'like', '%' . $term . '%')
                    ->orWhere('contacts.contact_id', 'like', '%' . $term . '%');
            })
                ->select(
                    'contacts.id',
                    DB::raw('IF(name="", supplier_business_name, name) as text'),
                    'supplier_business_name as business_name',
                    'contacts.mobile',
                    'contacts.address_line_1',
                    'contacts.address_line_2',
                    'contacts.city',
                    'contacts.state',
                    'contacts.country',
                    'contacts.zip_code',
                    'contact_id',
                    'contacts.pay_term_type',
                    'contacts.pay_term_number',
                    'contacts.balance'
                )
                ->onlySuppliers()
                ->get();
            return json_encode($suppliers);
        }
    }

    /**
     * Retrieves products list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProducts()
    {
        if (request()->ajax()) {
            // return 'asdasd';
            $term = request()->term;

            $check_enable_stock = true;
            if (isset(request()->check_enable_stock)) {
                $check_enable_stock = filter_var(request()->check_enable_stock, FILTER_VALIDATE_BOOLEAN);
            }

            $only_variations = false;
            if (isset(request()->only_variations)) {
                $only_variations = filter_var(request()->only_variations, FILTER_VALIDATE_BOOLEAN);
            }

            if (empty($term)) {
                return json_encode([]);
            }

            $business_id = request()->session()->get('user.business_id');
            $q = Product::leftJoin(
                'variations',
                'products.id',
                '=',
                'variations.product_id'
            )
                ->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term . '%');
                    $query->orWhere('sku', 'like', '%' . $term . '%');
                    $query->orWhere('sub_sku', 'like', '%' . $term . '%');
                })
                ->active()
                ->where('business_id', $business_id)
                ->whereNull('variations.deleted_at')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.type',
                    // 'products.sku as sku',
                    'variations.id as variation_id',
                    'variations.name as variation',
                    'variations.sub_sku as sub_sku'
                )
                ->groupBy('variation_id');

            if ($check_enable_stock) {
                $q->where('enable_stock', 1);
            }
            if (!empty(request()->location_id)) {
                $q->ForLocation(request()->location_id);
            }
            $products = $q->get();

            $products_array = [];
            foreach ($products as $product) {
                $products_array[$product->product_id]['name'] = $product->name;
                $products_array[$product->product_id]['sku'] = $product->sub_sku;
                $products_array[$product->product_id]['type'] = $product->type;
                $products_array[$product->product_id]['variations'][]
                    = [
                        'variation_id' => $product->variation_id,
                        'variation_name' => $product->variation,
                        'sub_sku' => $product->sub_sku
                    ];
            }

            $result = [];
            $i = 1;
            $no_of_records = $products->count();
            if (!empty($products_array)) {
                foreach ($products_array as $key => $value) {
                    if ($no_of_records > 1 && $value['type'] != 'single' && !$only_variations) {
                        $result[] = [
                            'id' => $i,
                            'text' => $value['name'] . ' - ' . $value['sku'],
                            'variation_id' => 0,
                            'product_id' => $key
                        ];
                    }
                    $name = $value['name'];
                    foreach ($value['variations'] as $variation) {
                        $text = $name;
                        if ($value['type'] == 'variable') {
                            $text = $text . ' (' . $variation['variation_name'] . ')';
                        }
                        $i++;
                        $result[] = [
                            'id' => $i,
                            'text' => $text . ' - ' . $variation['sub_sku'],
                            'product_id' => $key,
                            'variation_id' => $variation['variation_id'],
                        ];
                    }
                    $i++;
                }
            }

            return json_encode($result);
        }
    }

    /**
     * Retrieves products list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchaseEntryRow(Request $request)
    {
        if (request()->ajax()) {
            $product_id = $request->input('product_id');
            $variation_id = $request->input('variation_id');
            $business_id = request()->session()->get('user.business_id');
            $location_id = $request->input('location_id');
            $is_purchase_order = $request->has('is_purchase_order');

            $hide_tax = 'hide';
            if ($request->session()->get('business.enable_inline_tax') == 1) {
                $hide_tax = '';
            }

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            if (!empty($product_id)) {
                $row_count = $request->input('row_count');
                $product = Product::where('id', $product_id)
                    ->with(['unit'])
                    ->first();

                $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit->id, false, $product_id);

                $query = Variation::where('product_id', $product_id)
                    ->with([
                        'product_variation',
                        'variation_location_details' => function ($q) use ($location_id) {
                            $q->where('location_id', $location_id);
                        }
                    ]);
                if ($variation_id !== '0') {
                    $query->where('id', $variation_id);
                }

                $variations =  $query->get();
                $taxes = TaxRate::where('business_id', $business_id)
                    ->ExcludeForTaxGroup()
                    ->get();

                return view('purchase.partials.purchase_entry_row')
                    ->with(compact(
                        'product',
                        'variations',
                        'row_count',
                        'variation_id',
                        'taxes',
                        'currency_details',
                        'hide_tax',
                        'sub_units',
                        'is_purchase_order'
                    ));
            }
        }
    }

    public function getPurchaseOrderLines($purchase_order_id)
    {
        $business_id = request()->session()->get('user.business_id');

        $purchase_order = Transaction::where('business_id', $business_id)
            ->where('type', 'purchase_order')
            ->with([
                'purchase_lines', 'purchase_lines.variations',
                'purchase_lines.product', 'purchase_lines.product.unit', 'purchase_lines.variations.product_variation'
            ])
            ->findOrFail($purchase_order_id);

        $taxes = TaxRate::where('business_id', $business_id)
            ->ExcludeForTaxGroup()
            ->get();

        $sub_units_array = [];
        foreach ($purchase_order->purchase_lines as $pl) {
            $sub_units_array[$pl->id] = $this->productUtil->getSubUnits($business_id, $pl->product->unit->id, false, $pl->product_id);
        }
        $hide_tax = request()->session()->get('business.enable_inline_tax') == 1 ? '' : 'hide';
        $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
        $row_count = request()->input('row_count');

        $html =  view('purchase.partials.purchase_order_lines')
            ->with(compact(
                'purchase_order',
                'taxes',
                'hide_tax',
                'currency_details',
                'row_count',
                'sub_units_array'
            ))->render();

        return [
            'html' => $html,
            'po' => $purchase_order
        ];
    }

    /**
     * Checks if ref_number and supplier combination already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkRefNumber(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $contact_id = $request->input('contact_id');
        $ref_no = $request->input('ref_no');
        $purchase_id = $request->input('purchase_id');

        $count = 0;
        if (!empty($contact_id) && !empty($ref_no)) {
            //check in transactions table
            $query = Transaction::where('business_id', $business_id)
                ->where('ref_no', $ref_no)
                ->where('contact_id', $contact_id);
            if (!empty($purchase_id)) {
                $query->where('id', '!=', $purchase_id);
            }
            $count = $query->count();
        }
        if ($count == 0) {
            echo "true";
            exit;
        } else {
            echo "false";
            exit;
        }
    }

    /**
     * Checks if ref_number and supplier combination already exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printInvoice($id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $taxes = TaxRate::where('business_id', $business_id)
                ->pluck('name', 'id');
            $purchase = Transaction::where('business_id', $business_id)
                ->where('id', $id)
                ->with(
                    'contact',
                    'purchase_lines',
                    'purchase_lines.product',
                    'purchase_lines.variations',
                    'purchase_lines.variations.product_variation',
                    'location',
                    'payment_lines'
                )
                ->first();
            $payment_methods = $this->productUtil->payment_types(null, false, $business_id);

            //Purchase orders
            $purchase_order_nos = '';
            $purchase_order_dates = '';
            if (!empty($purchase->purchase_order_ids)) {
                $purchase_orders = Transaction::find($purchase->purchase_order_ids);

                $purchase_order_nos = implode(', ', $purchase_orders->pluck('ref_no')->toArray());
                $order_dates = [];
                foreach ($purchase_orders as $purchase_order) {
                    $order_dates[] = $this->transactionUtil->format_date($purchase_order->transaction_date, true);
                }
                $purchase_order_dates = implode(', ', $order_dates);
            }

            $output = ['success' => 1, 'receipt' => [], 'print_title' => $purchase->ref_no];
            $output['receipt']['html_content'] = view('purchase.partials.show_details', compact('taxes', 'purchase', 'payment_methods', 'purchase_order_nos', 'purchase_order_dates'))->render();
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return $output;
    }


    public function deletePage(Request $request, $id)
    {
        $user_id = $request->session()->get('user.id');
        return view('purchase.delete')->with('id', $id)->with('user_id', $user_id);
        return $id;
    }

    public function deleteaction(Request $request)
    {
        $deletereasone = $request->input('deletereasone');
        $user_id = $request->input('user_id');
        $id = $request->input('transaction_id');




        try {
            $business_id = request()->session()->get('user.business_id');
            // Khair add restrinction for delete 
            $refund = RefundTable::where('transaction_id', $id)->where('status', 1)->where('business_id', $business_id)->exists();
            if ($refund) {
                $output = [
                    'success' => false,
                    'msg' => __('messages.refund_purchase')
                ];
                return $output;
            }

            //Check if return exist then not allowed
            if ($this->transactionUtil->isReturnExist($id)) {
                $output = [
                    'success' => false,
                    'msg' => __('lang_v1.return_exist')
                ];
                return $output;
            }
            $transaction = Transaction::where('id', $id)
                ->where('business_id', $business_id)
                ->with(['purchase_lines'])
                ->first();




            //Check if lot numbers from the purchase is selected in sale
            if (request()->session()->get('business.enable_lot_number') == 1 && $this->transactionUtil->isLotUsed($transaction)) {
                $output = [
                    'success' => false,
                    'msg' => __('lang_v1.lot_numbers_are_used_in_sale')
                ];
                return $output;
            }

            $delete_purchase_lines = $transaction->purchase_lines;
            DB::beginTransaction();

            $log_properities = [
                'id' => $transaction->id,
                'ref_no' => $transaction->ref_no
            ];
            $this->transactionUtil->activityLog($transaction, 'purchase_deleted', $log_properities);

            $transaction_status = $transaction->status;
            if ($transaction_status != 'received') {
                // backup Transaction ::Khair 11-Apr
                DB::statement("INSERT INTO transactions_clones SELECT * FROM transactions WHERE id=:id", ["id" => $id]);
                $transaction->delete();
            } else {
                //Delete purchase lines first
                $delete_purchase_line_ids = [];
                foreach ($delete_purchase_lines as $purchase_line) {
                    $delete_purchase_line_ids[] = $purchase_line->id;
                    $this->productUtil->decreaseProductQuantity(
                        $purchase_line->product_id,
                        $purchase_line->variation_id,
                        $transaction->location_id,
                        $purchase_line->quantity
                    );
                }

                // backup PurchaseLine ::Khair 11-Apr
                try {
                    foreach ($delete_purchase_line_ids as $delete_purchase_line_id) {
                        DB::statement(
                            "INSERT INTO purchaselines_clones  SELECT * FROM purchase_lines  WHERE transaction_id=:transaction_id and id=:delete_purchase_line_id",
                            ["transaction_id" => $id, "delete_purchase_line_id" => $delete_purchase_line_id]
                        );
                    }
                } catch (Exception $e) {
                    error_log($e);
                }

                PurchaseLine::where('transaction_id', $transaction->id)
                    ->whereIn('id', $delete_purchase_line_ids)
                    ->delete();

                //Update mapping of purchase & Sell.
                $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($transaction_status, $transaction, $delete_purchase_lines);
            }

            // backup Transaction ::Khair 11-Apr
            DB::statement("INSERT INTO transactions_clones SELECT * FROM transactions WHERE id=:id", ["id" => $id]);


            //Delete Transaction
            $transaction->delete();


            // backup account transactions::Khair 11-Apr
            DB::statement("INSERT INTO accounttransactions_clones  SELECT * FROM account_transactions  WHERE transaction_id=:transaction_id", ["transaction_id" => $id]);
            //Delete account transactions
            AccountTransaction::where('transaction_id', $id)->delete();
            DB::commit();

            $refund = RefundTable::where('transaction_id', $id)->where('business_id', $business_id)
                ->update(["status" => 3]);


            DB::statement("INSERT INTO deletereasone (userid,transaction_id,deletereasone) VALUES (:userid,:transaction_id,:deletereasone)", [
                "userid" => $user_id,
                "transaction_id" => $id,
                "deletereasone" => $deletereasone
            ]);


            $output = [
                'success' => true,
                'msg' => __('lang_v1.purchase_delete_success')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }

        return redirect('purchases')->with('status', $output);
        // return $request;
    }

    /**
     * Update purchase status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request)
    {
        if (!auth()->user()->can('purchase.update') && !auth()->user()->can('purchase.update_status')) {
            abort(403, 'Unauthorized action.');
        }
        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($request->input('purchase_id'), $edit_days)) {
            return [
                'success' => 0,
                'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])
            ];
        }

        try {
            $business_id = request()->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'purchase')
                ->with(['purchase_lines'])
                ->findOrFail($request->input('purchase_id'));

            $before_status = $transaction->status;


            $update_data['status'] = $request->input('status');


            DB::beginTransaction();

            //update transaction
            $transaction->update($update_data);

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);
            foreach ($transaction->purchase_lines as $purchase_line) {
                $this->productUtil->updateProductStock($before_status, $transaction, $purchase_line->product_id, $purchase_line->variation_id, $purchase_line->quantity, $purchase_line->quantity, $currency_details);
            }

            //Update mapping of purchase & Sell.
            $this->transactionUtil->adjustMappingPurchaseSellAfterEditingPurchase($before_status, $transaction, null);

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('purchase.purchase_update_success')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage()
            ];
        }

        return $output;
    }
}
