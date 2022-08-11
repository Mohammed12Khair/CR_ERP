<?php

namespace Modules\Manufacturing\Http\Controllers;

use App\BusinessLocation;
use App\Transaction;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Manufacturing\Entities\MfgRecipe;
use Modules\Manufacturing\Utils\ManufacturingUtil;
use Yajra\DataTables\Facades\DataTables;

class ProductionController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $moduleUtil;
    protected $productUtil;
    protected $transactionUtil;
    protected $mfgUtil;
    protected $businessUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil, ProductUtil $productUtil, TransactionUtil $transactionUtil, ManufacturingUtil $mfgUtil, BusinessUtil $businessUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->mfgUtil = $mfgUtil;
        $this->businessUtil = $businessUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module')) || !auth()->user()->can('manufacturing.access_production')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $productions = Transaction::join(
                'business_locations AS bl',
                'transactions.location_id',
                '=',
                'bl.id'
                )->join('purchase_lines as pl', 'pl.transaction_id', '=', 'transactions.id')
                ->leftJoin('units as su', 'pl.sub_unit_id', '=', 'su.id')
                ->join('variations as v', 'v.id', '=', 'pl.variation_id')
                ->join('product_variations as pv', 'pv.id', '=', 'v.product_variation_id')
                ->join('products as p', 'p.id', '=', 'v.product_id')
                ->join('units as u', 'p.unit_id', '=', 'u.id')
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'production_purchase')
                ->select(
                    'transactions.id',
                    'transaction_date',
                    'ref_no',
                    'bl.name as location_name',
                    DB::raw('IF(p.type="variable", 
                            CONCAT(p.name, " - ", pv.name, " - ", v.name, " (", v.sub_sku, ")"), 
                            CONCAT(p.name, " (", v.sub_sku, ")") 
                            ) as product_name'),
                    'pl.quantity',
                    'final_total',
                    'su.short_name as sub_unit_name',
                    'su.base_unit_multiplier',
                    'u.short_name as unit_name',
                    'mfg_is_final'
                )->groupBy('transactions.id');

            return Datatables::of($productions)
                ->addColumn('action', function ($row) {
                    $html = '<button data-href="' .  action('\Modules\Manufacturing\Http\Controllers\ProductionController@show', $row->id) . '" class="btn btn-info btn-xs btn-modal" data-container=".view_modal"><i class="fa fa-eye"></i> ' . __('messages.view') . '</button>';
                    if ($row->mfg_is_final == 0) {
                        $html .= ' <a href="' .  action('\Modules\Manufacturing\Http\Controllers\ProductionController@edit', $row->id) . '" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i> ' . __('messages.edit') . '</a>';

                        $html .= ' <button data-href="' . action('\Modules\Manufacturing\Http\Controllers\ProductionController@destroy', [$row->id]) . '" class="delete-production btn btn-xs btn-danger"><i class="fa fa-trash"></i> ' . __("messages.delete") . '</button>';
                    }

                    return $html;
                })
                ->editColumn(
                    'final_total',
                    '<span class="display_currency final_total" data-currency_symbol="true" data-orig-value="{{$final_total}}">{{$final_total}}</span>'
                )
                ->editColumn(
                    'quantity',
                    function ($row) {
                        $qty = empty($row->base_unit_multiplier) ? $row->quantity : $row->quantity / $row->base_unit_multiplier ;
                        $unit = empty($row->sub_unit_name) ? $row->unit_name : $row->sub_unit_name;
                        return "<span class='display_currency' data-currency_symbol='false' data-orig-value='$qty'>$qty</span> $unit";
                    }
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->rawColumns(['final_total', 'action', 'quantity'])
                ->filterColumn('product_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(p.name, ' - ', pv.name, ' - ', v.name, ' (', v.sub_sku, ')') like ?", ["%{$keyword}%"]);
                })
                ->make(true);
        }

        return view('manufacturing::production.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module')) || !auth()->user()->can('manufacturing.access_production')) {
            abort(403, 'Unauthorized action.');
        }

        $business_locations = BusinessLocation::forDropdown($business_id);

        $recipe_dropdown = MfgRecipe::forDropdown($business_id);

        return view('manufacturing::production.create')
                ->with(compact('business_locations', 'recipe_dropdown'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module')) || !auth()->user()->can('manufacturing.access_production')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'transaction_date' => 'required',
                'location_id' => 'required',
                'final_total' => 'required'
            ]);

            //Create Production purchase
            $manufacturing_settings = $this->mfgUtil->getSettings($business_id);
            $user_id = $request->session()->get('user.id');

            $transaction_data = $request->only([ 'ref_no', 'transaction_date', 'location_id', 'final_total']);

            $is_final = !empty($request->input('finalize')) ? 1 : 0;
            $transaction_data['business_id'] = $business_id;
            $transaction_data['created_by'] = $user_id;
            $transaction_data['type'] = 'production_purchase';
            $transaction_data['status'] = $is_final ? 'received' : 'pending';
            $transaction_data['payment_status'] = 'due';
            $transaction_data['transaction_date'] = $this->productUtil->uf_date($transaction_data['transaction_date'], true);
            $transaction_data['final_total'] = $this->productUtil->num_uf($transaction_data['final_total']);

            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount($transaction_data['type']);
            //Generate reference number
            if (empty($transaction_data['ref_no'])) {
                $prefix = !empty($manufacturing_settings['ref_no_prefix']) ? $manufacturing_settings['ref_no_prefix'] : null;
                $transaction_data['ref_no'] = $this->productUtil->generateReferenceNumber($transaction_data['type'], $ref_count, null, $prefix);
            }
            $variation_id = $request->input('variation_id');
            $variation = Variation::where('id', $variation_id)
                                ->with(['product'])
                                ->first();
            $final_total = $request->input('final_total');
            $quantity = $request->input('quantity');
            $waste_units = $this->productUtil->num_uf($request->input('mfg_wasted_units'));
            $uf_qty = $this->productUtil->num_uf($quantity);
            if (!empty($waste_units)) {
                $new_qty = $uf_qty - $waste_units;
                $uf_qty = $new_qty;
                $quantity = $this->productUtil->num_f($new_qty);
            }

            $final_total_uf = $this->productUtil->num_uf($final_total);

            $unit_purchase_line_total = $final_total_uf / $uf_qty;

            $unit_purchase_line_total_f = $this->productUtil->num_f($unit_purchase_line_total);

            $transaction_data['mfg_wasted_units'] = $waste_units;
            $transaction_data['mfg_production_cost'] = $this->productUtil->num_uf($request->input('production_cost'));
            $transaction_data['mfg_is_final'] = $is_final;
            $purchase_line_data = [
                'variation_id' => $variation_id,
                'quantity' => $quantity,
                'product_id' => $variation->product_id,
                'product_unit_id' => $variation->product->unit_id,
                'pp_without_discount' => $unit_purchase_line_total_f,
                'discount_percent' => 0,
                'purchase_price' => $unit_purchase_line_total_f,
                'purchase_price_inc_tax' => $unit_purchase_line_total_f,
                'item_tax' => 0,
                'purchase_line_tax_id' => null,
                'mfg_date' => $this->transactionUtil->format_date($transaction_data['transaction_date'])
            ];
            if (request()->session()->get('business.enable_lot_number') == 1) {
                $purchase_line_data['lot_number'] = $request->input('lot_number');
            }

            if (request()->session()->get('business.enable_product_expiry') == 1) {
                $purchase_line_data['exp_date'] = $request->input('exp_date');
            }

            if (!empty($request->input('sub_unit_id'))) {
                $purchase_line_data['sub_unit_id'] = $request->input('sub_unit_id');
            }

            DB::beginTransaction();

            $transaction = Transaction::create($transaction_data);

            $currency_details = $this->transactionUtil->purchaseCurrencyDetails($business_id);

            $update_product_price = !empty($manufacturing_settings['enable_updating_product_price']) && $is_final ? true : false;

            $this->productUtil->createOrUpdatePurchaseLines($transaction, [$purchase_line_data], $currency_details, $update_product_price);

            //Adjust stock over selling if found
            $this->productUtil->adjustStockOverSelling($transaction);

            //Create production sell
            $transaction_sell_data = [
                'business_id' => $business_id,
                'location_id' => $transaction->location_id,
                'transaction_date' => $transaction->transaction_date,
                'created_by' => $transaction->created_by,
                'status' => $is_final ? 'final' : 'draft',
                'type' => 'production_sell',
                'mfg_parent_production_purchase_id' => $transaction->id,
                'payment_status' => 'due',
                'final_total' => $transaction->final_total
            ];

            $sell_lines = [];
            $ingredient_quantities = !empty($request->input('ingredients')) ? $request->input('ingredients') : [];

            //Get ingredient details to create sell lines
            $recipe = MfgRecipe::where('variation_id', $variation_id)->first();

            $all_variation_details = $this->mfgUtil->getIngredientDetails($recipe, $business_id);

            foreach ($all_variation_details as $variation_details) {
                $variation = $variation_details['variation'];

                $line_sub_unit_id = !empty($ingredient_quantities[$variation_details['id']]['sub_unit_id']) ?
                                $ingredient_quantities[$variation_details['id']]['sub_unit_id'] : null;
                $line_multiplier = !empty($line_sub_unit_id) ? $variation_details['sub_units'][$line_sub_unit_id]['multiplier'] : 1;

                $mfg_waste_percent = !empty($ingredient_quantities[$variation_details['id']]['mfg_waste_percent']) ? $this->productUtil->num_uf($ingredient_quantities[$variation_details['id']]['mfg_waste_percent']) : 0;

                $sell_lines[] = [
                        'product_id' => $variation->product_id,
                        'variation_id' => $variation->id,
                        'quantity' => $this->productUtil->num_uf($ingredient_quantities[$variation_details['id']]['quantity']),
                        'item_tax' => 0,
                        'tax_id' => null,
                        'unit_price' => $variation->dpp_inc_tax * $line_multiplier,
                        'unit_price_inc_tax' => $variation->dpp_inc_tax * $line_multiplier,
                        'enable_stock' => $variation_details['enable_stock'],
                        'product_unit_id' => $variation->product->unit_id,
                        'sub_unit_id' => $line_sub_unit_id,
                        'base_unit_multiplier' => $line_multiplier,
                        'mfg_waste_percent' => $mfg_waste_percent
                    ];
            }

            //Create Sell Transfer transaction
            $production_sell = Transaction::create($transaction_sell_data);

            if (!empty($sell_lines)) {
                $this->transactionUtil->createOrUpdateSellLines($production_sell, $sell_lines, $transaction_sell_data['location_id'], null, null, ['mfg_waste_percent' => 'mfg_waste_percent']);
            }

            if ($production_sell->status == 'final') {
                foreach ($sell_lines as $sell_line) {
                    if ($sell_line['enable_stock']) {
                        $line_qty = $sell_line['quantity'] * $sell_line['base_unit_multiplier'];
                        $this->productUtil->decreaseProductQuantity(
                            $sell_line['product_id'],
                            $sell_line['variation_id'],
                            $production_sell->location_id,
                            $line_qty
                        );
                    }
                }

                //Map sell lines with purchase lines
                $business = ['id' => $business_id,
                            'accounting_method' => $request->session()->get('business.accounting_method'),
                            'location_id' => $production_sell->location_id
                        ];
                $this->transactionUtil->mapPurchaseSell($business, $production_sell->sell_lines, 'production_purchase');
            }

            DB::commit();
            
            $output = ['success' => 1,
                            'msg' => __('lang_v1.added_success')
                        ];
        } catch (Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect()->action('\Modules\Manufacturing\Http\Controllers\ProductionController@index')->with('status', $output);
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module')) || !auth()->user()->can('manufacturing.access_production')) {
            abort(403, 'Unauthorized action.');
        }

        $production_purchase = Transaction::where('business_id', $business_id)
                                    ->where('type', 'production_purchase')
                                    ->with(['purchase_lines', 'purchase_lines.variations', 'purchase_lines.variations.product_variation', 'purchase_lines.variations.product',
                                        'purchase_lines.sub_unit', 'purchase_lines.variations.product.unit'])
                                    ->findOrFail($id);

        $production_sell = Transaction::where('business_id', $business_id)
                                    ->where('type', 'production_sell')
                                    ->where('mfg_parent_production_purchase_id', $production_purchase->id)
                                    ->with(['sell_lines', 'sell_lines.variations', 'sell_lines.variations.product_variation', 'sell_lines.variations.product', 'sell_lines.sub_unit'])
                                    ->first();

        $purchase_line = $production_purchase->purchase_lines[0];
        $base_unit_multiplier = !empty($purchase_line->sub_unit) ? $purchase_line->sub_unit->base_unit_multiplier : 1;
        $quantity = $purchase_line->quantity / $base_unit_multiplier;
        $quantity_wasted = 0;
        $unit_name = !empty($purchase_line->sub_unit) ?  $purchase_line->sub_unit->short_name : $purchase_line->variations->product->unit->short_name;
        if (!empty($production_purchase->mfg_wasted_units)) {
            $quantity_wasted = $production_purchase->mfg_wasted_units;
            $quantity += $quantity_wasted;
        }

        $actual_quantity = $quantity * $base_unit_multiplier;

        $ingredients = [];
        $total_ingredients_price = 0;
        //Format sell lines
        foreach ($production_sell->sell_lines as $sell_line) {
            $variation = $sell_line->variations;
            $sell_line_qty = empty($sell_line->sub_unit) ? $sell_line->quantity : $sell_line->quantity / $sell_line->sub_unit->base_unit_multiplier;
            $unit = empty($sell_line->sub_unit) ? $variation->product->unit->short_name : $sell_line->sub_unit->short_name;

            $line_total_price = $variation->dpp_inc_tax * $sell_line->quantity;
            $total_ingredients_price += $line_total_price;

            $waste_percent = !empty($sell_line->mfg_waste_percent) ? $sell_line->mfg_waste_percent : 0;
            $wasted_qty = $this->moduleUtil->calc_percentage($sell_line_qty, $waste_percent);
            $final_quantity = $sell_line_qty - $wasted_qty;

            $ingredients[] = [
                'dpp_inc_tax' => $variation->dpp_inc_tax,
                'quantity' => $sell_line_qty,
                'full_name' => $variation->full_name,
                'id' => $variation->id,
                'unit' => $unit,
                'allow_decimal' => $variation->product->unit->allow_decimal,
                'variation' => $variation,
                'enable_stock' => $variation->product->enable_stock,
                'total_price' => $line_total_price,
                'waste_percent' =>  $waste_percent,
                'final_quantity' => $final_quantity
           ];
        }

        $total_production_cost = 0;
        if (!empty($production_purchase->mfg_production_cost)) {
            $total_production_cost = $this->transactionUtil->calc_percentage($total_ingredients_price, $production_purchase->mfg_production_cost);
        }

        return view('manufacturing::production.show')->with(compact('production_purchase', 'production_sell', 'purchase_line', 'ingredients', 'unit_name', 'quantity', 'quantity_wasted', 'actual_quantity', 'total_production_cost'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'manufacturing_module')) || !auth()->user()->can('manufacturing.access_production')) {
            abort(403, 'Unauthorized action.');
        }

        $production_purchase = Transaction::where('business_id', $business_id)
                                    ->where('type', 'production_purchase')
                                    ->with(['purchase_lines', 'purchase_lines.variations', 'purchase_lines.variations.product_variation', 'purchase_lines.variations.product'])
                                    ->findOrFail($id);

        //Finalized production should not be editable
        if ($production_purchase->mfg_is_final == 1) {
            $output = ['success' => 0,
                        'msg' => __('messages.something_went_wrong')
                    ];
            return redirect()->action('\Modules\Manufacturing\Http\Controllers\ProductionController@index')->with('status', $output);
        }

        $production_sell = Transaction::where('business_id', $business_id)
                                    ->where('type', 'production_sell')
                                    ->where('mfg_parent_production_purchase_id', $production_purchase->id)
                                    ->with(['sell_lines', 'sell_lines.variations', 'sell_lines.variations.product_variation', 'sell_lines.variations.product', 'sell_lines.variations.product.unit'])
                                    ->first();
        $purchase_line = $production_purchase->purcha