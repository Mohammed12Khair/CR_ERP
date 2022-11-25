<?php

namespace App\Http\Controllers;

use App\ExpenseCategory;
use DB;
use Illuminate\Http\Request;
use PHPUnit\Framework\Constraint\Count;
use Yajra\DataTables\Facades\DataTables;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!(auth()->user()->can('expense.add') || auth()->user()->can('expense.edit'))) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $userid = request()->session()->get('user.id');


            // Filter expens khair
            $ActiveAccounts = DB::SELECT("SELECT * FROM activeexpensis WHERE business_id=:business_id AND userid=:userid", ['business_id' => $business_id, 'userid' => $userid]);

            $active_accounts_data = [];
            foreach ($ActiveAccounts as $ActiveAccount) {
                // error_log($ActiveAccount->expens_id);
                array_push($active_accounts_data, $ActiveAccount->expens_id);
            }
            // $expens_id=DB::

            $expense_category = ExpenseCategory::where('business_id', $business_id)
            ->whereIn('id',$active_accounts_data)
                ->select(['name', 'code', 'id']);

            return Datatables::of($expense_category)
                ->addColumn(
                    'action',
                    function ($row) {
                        $returnHtml = "";
                        if (auth()->user()->can('expense.catgory_edit')) {
                            $returnHtml .= '<button data-href="' . action('ExpenseCategoryController@edit', [$row->id]) . '" class="btn btn-xs btn-primary btn-modal" data-container=".expense_category_modal"><i class="glyphicon glyphicon-edit"></i>' .  __("messages.edit") . '</button>
                            &nbsp;';
                        }

                        if (auth()->user()->can('expense.catgory_delete')) {
                            $returnHtml .= '  <button data-href="' . action('ExpenseCategoryController@destroy', [$row->id]) . '" class="btn btn-xs btn-danger delete_expense_category"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</button>';
                        }


                        return $returnHtml;
                    }
                    // '<button data-href="{{action(\'ExpenseCategoryController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".expense_category_modal"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>
                    //     &nbsp;
                    //     <button data-href="{{action(\'ExpenseCategoryController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_expense_category"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>'
                )
                // ->addColumn(
                //     'action',
                //     '<button data-href="{{action(\'ExpenseCategoryController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".expense_category_modal"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>
                //         &nbsp;
                //         <button data-href="{{action(\'ExpenseCategoryController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_expense_category"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>'
                // )
                ->removeColumn('id')
                ->rawColumns([2])
                ->make(false);
        }

        return view('expense_category.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!(auth()->user()->can('expense.add') || auth()->user()->can('expense.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        return view('expense_category.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!(auth()->user()->can('expense.add') || auth()->user()->can('expense.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'code', 'transfer']);
            $input['business_id'] = $request->session()->get('user.business_id');

            ExpenseCategory::create($input);
            $output = [
                'success' => true,
                'msg' => __("expense.added_success")
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

    /**
     * Display the specified resource.
     *
     * @param  \App\ExpenseCategory  $expenseCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ExpenseCategory $expenseCategory)
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
        if (!(auth()->user()->can('expense.add') || auth()->user()->can('expense.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $expense_category = ExpenseCategory::where('business_id', $business_id)->find($id);

            return view('expense_category.edit')
                ->with(compact('expense_category'));
        }
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
        if (!(auth()->user()->can('expense.add') || auth()->user()->can('expense.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'code', 'transfer']);
                $business_id = $request->session()->get('user.business_id');

                $expense_category = ExpenseCategory::where('business_id', $business_id)->findOrFail($id);
                $expense_category->name = $input['name'];
                $expense_category->code = $input['code'];
                $expense_category->transfer = $input['transfer'];
                $expense_category->save();

                $output = [
                    'success' => true,
                    'msg' => __("expense.updated_success")
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!(auth()->user()->can('expense.add') || auth()->user()->can('expense.edit'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $expense_category = ExpenseCategory::where('business_id', $business_id)->findOrFail($id);
                $expense_category->delete();

                $output = [
                    'success' => true,
                    'msg' => __("expense.deleted_success")
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
}
