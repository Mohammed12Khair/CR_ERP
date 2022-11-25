<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Contact;
use App\System;
use App\User;
use App\Utils\ModuleUtil;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Spatie\Activitylog\Models\Activity;
use App\Account;
use App\Activeaccounts;
use App\Category;
use App\category_view;
use App\ExpenseCategory;

class ManageUserController extends Controller
{


    public function AccountManageAdd(Request $request)
    {
        error_log($request->input('userid'));
        error_log($request->input('account_id'));
        $business_id = request()->session()->get('user.business_id');
        $ActiveAccounts = new Activeaccounts();
        $ActiveAccounts->userid = $request->input('userid');
        $ActiveAccounts->account_id = $request->input('account_id');
        $ActiveAccounts->business_id = $business_id;
        $ActiveAccounts->save();
        return redirect()->back();
    }

    public function AccountManageDelete(Request $request)
    {
        Activeaccounts::where('userid', $request->input('userid'))
            ->where('account_id', $request->input('account_id'))->delete();

        return redirect()->back();
    }

    public function AccountManage($id)
    {

        if (!auth()->user()->can('users_accounts_admin')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        error_log('Bus' . $business_id);
        error_log('user' . $id);
        $All_Accounts = Account::where('business_id', $business_id)->where('is_closed', 0)->get();
        $ActiveAccounts = Activeaccounts::where('business_id', $business_id)
            ->where('userid', $id)->get();

        $active_accounts_data = [];
        foreach ($ActiveAccounts as $ActiveAccount) {
            error_log($ActiveAccount->account_id);
            array_push($active_accounts_data, $ActiveAccount->account_id);
        }

        foreach ($All_Accounts as $All_Account) {
            error_log($All_Account->id);
            if (in_array($All_Account->id, $active_accounts_data)) {
                error_log("In array");
            } else {
                error_log("Not In array");
            }
        }
        return view('manage_user.account')
            ->with('id', $id)
            ->with('All_Accounts', $All_Accounts)
            ->with('active_accounts_data', $active_accounts_data);
    }
    public function ExpensManageAdd(Request $request)
    {

        // return $request->input('account_id') . request()->session()->get('user.business_id') . $request->input('userid');
        $business_id = request()->session()->get('user.business_id');
        DB::statement("INSERT INTO activeexpensis (expens_id,userid,business_id) VALUES
        (:expens_id,:userid,:business_id)", ["expens_id" => $request->input('account_id'), "userid" => $request->input('userid'), "business_id" => $business_id]);
        return redirect()->back();
    }

    public function ExpensManageDelete(Request $request)
    {
        DB::statement(
            "DELETE FROM activeexpensis where userid=:userid and expens_id=:expens_id",
            ["userid" => $request->input('userid'), "expens_id" => $request->input('account_id')]
        );

        return redirect()->back();
    }

    public function ExpensManage($id)
    {

        if (!auth()->user()->can('users_accounts_admin')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $All_Accounts = ExpenseCategory::where('business_id', $business_id)->where('deleted_at', NULL)->get();
        $ActiveAccounts = DB::SELECT("SELECT * FROM activeexpensis WHERE business_id=:business_id AND userid=:userid", ['business_id' => $business_id, 'userid' => $id]);

        $active_accounts_data = [];
        foreach ($ActiveAccounts as $ActiveAccount) {
            // error_log($ActiveAccount->expens_id);
            array_push($active_accounts_data, $ActiveAccount->expens_id);
        }

        foreach ($All_Accounts as $All_Account) {
            error_log($All_Account->id);
            if (in_array($All_Account->id, $active_accounts_data)) {
                error_log("In array");
            } else {
                error_log("Not In array");
            }
        }
        return view('manage_user.expens')
            ->with('id', $id)
            ->with('All_Accounts', $All_Accounts)
            ->with('active_accounts_data', $active_accounts_data);
    }

    public function CategoryManagerAdd(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $Category_data = new category_view();
        $Category_data->user_id = $request->input('user_id');
        $Category_data->category_id = $request->input('category_id');
        $Category_data->business_id = $business_id;
        $Category_data->save();
        return redirect()->back();
    }


    public function CategoryManagerDelete(Request $request)
    {
        category_view::where('user_id', $request->input('user_id'))
            ->where('category_id', $request->input('category_id'))->delete();

        return redirect()->back();
    }

    public function CategoryManager($id)
    {

        if (!auth()->user()->can('users_accounts_admin')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $categories = Category::where('business_id', $business_id)->where('deleted_at', '=', null)->get();

        $Active_categories = category_view::where('business_id', $business_id)
            ->where('user_id', $id)->get();

        $user_active_categories = [];
        foreach ($Active_categories as $Active_categorie) {
            array_push($user_active_categories, $Active_categorie->category_id);
        }

        return view('manage_user.category')
            ->with('id', $id)
            ->with('categories', $categories)
            ->with('user_active_categories', $user_active_categories);
    }
    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('user.view') && !auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $users = User::where('business_id', $business_id)
                ->user()
                ->where('is_cmmsn_agnt', 0)
                ->select([
                    'id', 'username',
                    DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"), 'email', 'allow_login'
                ]);

            return Datatables::of($users)
                ->editColumn('username', '{{$username}} @if(empty($allow_login)) <span class="label bg-gray">@lang("lang_v1.login_not_allowed")</span>@endif')
                ->addColumn(
                    'role',
                    function ($row) {
                        $role_name = $this->moduleUtil->getUserRoleName($row->id);
                        return $role_name;
                    }
                )
                ->addColumn(
                    'action',
                    '@can("user.update")
                        <a href="{{action(\'ManageUserController@edit\', [$id])}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a>
                        &nbsp;
                    @endcan
                    @can("user.view")
                    <a href="{{action(\'ManageUserController@show\', [$id])}}" class="btn btn-xs btn-info"><i class="fa fa-eye"></i> @lang("messages.view")</a>
                    &nbsp;
                    @endcan
                    @can("user.delete")
                        <button data-href="{{action(\'ManageUserController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_user_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan
                    @can("users_accounts_admin")
                    <a href="{{action(\'ManageUserController@AccountManage\', [$id])}}" class="btn btn-xs btn-success"><i class="glyphicon glyphicon-usd"></i> @lang("lang_v1.payment_accounts")</a>
                @endcan
                    @can("users_accounts_admin")
                    <a href="{{action(\'ManageUserController@CategoryManager\', [$id])}}" class="btn btn-xs btn-warning"><i class="glyphicon glyphicon-check"></i> @lang("lang_v1.category")</a>
                @endcan
                @can("users_accounts_admin")
                <a href="{{action(\'ManageUserController@ExpensManage\', [$id])}}" class="btn btn-xs btn-warning"><i class="glyphicon glyphicon-check"></i> @lang("lang_v1.Expens")</a>
            @endcan'
                )
                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->rawColumns(['action', 'username'])
                ->make(true);
        }

        return view('manage_user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('users', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('users', $business_id, action('ManageUserController@index'));
        }

        $roles  = $this->getRolesArray($business_id);
        $username_ext = $this->moduleUtil->getUsernameExtension();
        $locations = BusinessLocation::where('business_id', $business_id)
            ->Active()
            ->get();

        //Get user form part from modules
        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.create']);

        return view('manage_user.create')
            ->with(compact('roles', 'username_ext', 'locations', 'form_partials'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {

            if (!empty($request->input('dob'))) {
                $request['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }

            $request['cmmsn_percent'] = !empty($request->input('cmmsn_percent')) ? $this->moduleUtil->num_uf($request->input('cmmsn_percent')) : 0;

            $request['max_sales_discount_percent'] = !is_null($request->input('max_sales_discount_percent')) ? $this->moduleUtil->num_uf($request->input('max_sales_discount_percent')) : null;

            $user = $this->moduleUtil->createUser($request);

            $output = [
                'success' => 1,
                'msg' => __("user.user_added")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return redirect('users')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('user.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $user = User::where('business_id', $business_id)
            ->with(['contactAccess'])
            ->find($id);

        //Get user view part from modules
        $view_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.show', 'user' => $user]);

        $users = User::forDropdown($business_id, false);

        $activities = Activity::forSubject($user)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        return view('manage_user.show')->with(compact('user', 'view_partials', 'users', 'activities'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user = User::where('business_id', $business_id)
            ->with(['contactAccess'])
            ->findOrFail($id);

        $roles = $this->getRolesArray($business_id);

        $contact_access = $user->contactAccess->pluck('name', 'id')->toArray();

        if ($user->status == 'active') {
            $is_checked_checkbox = true;
        } else {
            $is_checked_checkbox = false;
        }

        $locations = BusinessLocation::where('business_id', $business_id)
            ->get();

        $permitted_locations = $user->permitted_locations();
        $username_ext = $this->moduleUtil->getUsernameExtension();

        //Get user form part from modules
        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'manage_user.edit', 'user' => $user]);

        return view('manage_user.edit')
            ->with(compact('roles', 'user', 'contact_access', 'is_checked_checkbox', 'locations', 'permitted_locations', 'form_partials', 'username_ext'));
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
        if (!auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $user_data = $request->only([
                'surname', 'first_name', 'last_name', 'email', 'selected_contacts', 'marital_status',
                'blood_group', 'contact_number', 'fb_link', 'twitter_link', 'social_media_1',
                'social_media_2', 'permanent_address', 'current_address',
                'guardian_name', 'custom_field_1', 'custom_field_2',
                'custom_field_3', 'custom_field_4', 'id_proof_name', 'id_proof_number', 'cmmsn_percent', 'gender', 'max_sales_discount_percent', 'family_number', 'alt_number'
            ]);

            $user_data['status'] = !empty($request->input('is_active')) ? 'active' : 'inactive';
            $business_id = request()->session()->get('user.business_id');

            if (!isset($user_data['selected_contacts'])) {
                $user_data['selected_contacts'] = 0;
            }

            if (empty($request->input('allow_login'))) {
                $user_data['username'] = null;
                $user_data['password'] = null;
                $user_data['allow_login'] = 0;
            } else {
                $user_data['allow_login'] = 1;
            }

            if (!empty($request->input('password'))) {
                $user_data['password'] = $user_data['allow_login'] == 1 ? Hash::make($request->input('password')) : null;
            }

            //Sales commission percentage
            $user_data['cmmsn_percent'] = !empty($user_data['cmmsn_percent']) ? $this->moduleUtil->num_uf($user_data['cmmsn_percent']) : 0;

            $user_data['max_sales_discount_percent'] = !is_null($user_data['max_sales_discount_percent']) ? $this->moduleUtil->num_uf($user_data['max_sales_discount_percent']) : null;

            if (!empty($request->input('dob'))) {
                $user_data['dob'] = $this->moduleUtil->uf_date($request->input('dob'));
            }

            if (!empty($request->input('bank_details'))) {
                $user_data['bank_details'] = json_encode($request->input('bank_details'));
            }

            DB::beginTransaction();

            if ($user_data['allow_login'] && $request->has('username')) {
                $user_data['username'] = $request->input('username');
                $ref_count = $this->moduleUtil->setAndGetReferenceCount('username');
                if (blank($user_data['username'])) {
                    $user_data['username'] = $this->moduleUtil->generateReferenceNumber('username', $ref_count);
                }

                $username_ext = $this->moduleUtil->getUsernameExtension();
                if (!empty($username_ext)) {
                    $user_data['username'] .= $username_ext;
                }
            }

            $user = User::where('business_id', $business_id)
                ->findOrFail($id);

            $user->update($user_data);
            $role_id = $request->input('role');
            $user_role = $user->roles->first();
            $previous_role = !empty($user_role->id) ? $user_role->id : 0;
            if ($previous_role != $role_id) {
                $is_admin = $this->moduleUtil->is_admin($user);
                $all_admins = $this->getAdmins();
                //If only one admin then can not change role
                if ($is_admin && count($all_admins) <= 1) {
                    throw new \Exception(__('lang_v1.cannot_change_role'));
                }
                if (!empty($previous_role)) {
                    $user->removeRole($user_role->name);
                }

                $role = Role::findOrFail($role_id);
                $user->assignRole($role->name);
            }

            //Grant Location permissions
            $this->moduleUtil->giveLocationPermissions($user, $request);

            //Assign selected contacts
            if ($user_data['selected_contacts'] == 1) {
                $contact_ids = $request->get('selected_contact_ids');
            } else {
                $contact_ids = [];
            }
            $user->contactAccess()->sync($contact_ids);

            //Update module fields for user
            $this->moduleUtil->getModuleData('afterModelSaved', ['event' => 'user_saved', 'model_instance' => $user]);

            $this->moduleUtil->activityLog($user, 'edited', null, ['name' => $user->user_full_name]);

            $output = [
                'success' => 1,
                'msg' => __("user.user_update_success")
            ];

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();

            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage()
            ];
        }

        return redirect('users')->with('status', $output);
    }

    private function getAdmins()
    {
        $business_id = request()->session()->get('user.business_id');
        $admins = User::role('Admin#' . $business_id)->get();

        return $admins;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('user.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $user = User::where('business_id', $business_id)
                    ->findOrFail($id);

                $this->moduleUtil->activityLog($user, 'deleted', null, ['name' => $user->user_full_name, 'id' => $user->id]);

                $user->delete();
                $output = [
                    'success' => true,
                    'msg' => __("user.user_delete_success")
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
     * Retrives roles array (Hides admin role from non admin users)
     *
     * @param  int  $business_id
     * @return array $roles
     */
    private function getRolesArray($business_id)
    {
        $roles_array = Role::where('business_id', $business_id)->get()->pluck('name', 'id');
        $roles = [];

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        foreach ($roles_array as $key => $value) {
            if (!$is_admin && $value == 'Admin#' . $business_id) {
                continue;
            }
            $roles[$key] = str_replace('#' . $business_id, '', $value);
        }
        return $roles;
    }
}
