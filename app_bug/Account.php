<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils\Util;
use DB;
use App\BusinessLocation;

class Account extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'account_details' => 'array',
    ];


    public static function forDropdown_cheque($business_id, $prepend_none, $closed = false, $show_balance = false)
    {

        $cheques_accounts = AccountType::where('business_id', $business_id)
            ->where('cheque', 1)->get();
        $active_ = [];
        foreach ($cheques_accounts as  $cheques_account) {
            array_push($active_, $cheques_account->id);
        }
        // $query = Account::where('business_id', $business_id)->whereIn('account_type_id', $active_);

        // shoe allowed accounts only Jan 04
        $userid = request()->session()->get('user.id');
        $allowed_accounts = Activeaccounts::where('userid', $userid)->get();
        $allowed_accounts_data = [];
        foreach ($allowed_accounts as $allowed_account) {
            array_push($allowed_accounts_data, $allowed_account->account_id);
        }
        // $query = Account::where('business_id', $business_id);
        $query = Account::where('business_id', $business_id)->whereIn('id', $allowed_accounts_data)
            ->whereIn('account_type_id', $active_);

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

        $can_access_account = auth()->user()->can('account.access');
        if ($can_access_account && $show_balance) {
            // $query->leftjoin('account_transactions as AT', function ($join) {
            //     $join->on('AT.account_id', '=', 'accounts.id');
            //     $join->whereNull('AT.deleted_at');
            // })
            $query->select(
                'accounts.name',
                'accounts.id',
                DB::raw("(SELECT SUM( IF(account_transactions.type='credit', amount, -1*amount) ) as balance from account_transactions where account_transactions.account_id = accounts.id AND deleted_at is NULL) as balance")
            );
        }

        if (!$closed) {
            $query->where('is_closed', 0);
        }

        $accounts = $query->get();

        $dropdown = [];
        if ($prepend_none) {
            $dropdown[''] = __('lang_v1.none');
        }

        $commonUtil = new Util;
        foreach ($accounts as $account) {
            $name = $account->name;

            if ($can_access_account && $show_balance) {
                $name .= ' (' . __('lang_v1.balance') . ': ' . $commonUtil->num_f($account->balance) . ')';
            }

            $dropdown[$account->id] = $name;
        }

        return $dropdown;
    }
    public static function forDropdown_cheque_not_cheques($business_id, $prepend_none, $closed = false, $show_balance = false)
    {

        $cheques_accounts = AccountType::where('business_id', $business_id)
            ->where('cheque', 1)->get();

        $active_ = [];
        foreach ($cheques_accounts as  $cheques_account) {
            array_push($active_, $cheques_account->id);
        }
        // $query = Account::where('business_id', $business_id)->whereIn('account_type_id', $active_);

        // shoe allowed accounts only Jan 04
        $userid = request()->session()->get('user.id');
        $allowed_accounts = Activeaccounts::where('userid', $userid)->get();
        $allowed_accounts_data = [];
        foreach ($allowed_accounts as $allowed_account) {
            array_push($allowed_accounts_data, $allowed_account->account_id);
        }
        // $query = Account::where('business_id', $business_id);
        $query = Account::where('business_id', $business_id)->whereNotIn('id', $allowed_accounts_data)
            ->whereIn('account_type_id', $active_);

        $permitted_locations = auth()->user()->permitted_locations();
        $account_ids = [];
        // if ($permitted_locations != 'all') {
        //     $locations = BusinessLocation::where('business_id', $business_id)
        //         ->whereIn('id', $permitted_locations)
        //         ->get();

        //     foreach ($locations as $location) {
        //         if (!empty($location->default_payment_accounts)) {
        //             $default_payment_accounts = json_decode($location->default_payment_accounts, true);
        //             foreach ($default_payment_accounts as $key => $account) {
        //                 if (!empty($account['is_enabled']) && !empty($account['account'])) {
        //                     $account_ids[] = $account['account'];
        //                 }
        //             }
        //         }
        //     }
        //     $account_ids = array_unique($account_ids);
        // }

        if ($permitted_locations != 'all') {
            // $query->whereIn('accounts.id', $account_ids);
        }

        $can_access_account = auth()->user()->can('account.access');
        if ($can_access_account && $show_balance) {
            // $query->leftjoin('account_transactions as AT', function ($join) {
            //     $join->on('AT.account_id', '=', 'accounts.id');
            //     $join->whereNull('AT.deleted_at');
            // })
            $query->select(
                'accounts.name',
                'accounts.id',
                DB::raw("(SELECT SUM( IF(account_transactions.type='credit', amount, -1*amount) ) as balance from account_transactions where account_transactions.account_id = accounts.id AND deleted_at is NULL) as balance")
            );
        }

        if (!$closed) {
            $query->where('is_closed', 0);
        }

        $accounts = $query->get();

        $dropdown = [];
        if ($prepend_none) {
            $dropdown[''] = __('lang_v1.none');
        }

        $commonUtil = new Util;
        foreach ($accounts as $account) {
            $name = $account->name;

            if ($can_access_account && $show_balance) {
                $name .= ' (' . __('lang_v1.balance') . ': ' . $commonUtil->num_f($account->balance) . ')';
            }

            $dropdown[$account->id] = $name;
        }

        return $dropdown;
    }

    public static function forDropdown($business_id, $prepend_none, $closed = false, $show_balance = false)
    {
        // shoe allowed accounts only Jan 04
        $userid = request()->session()->get('user.id');
        $allowed_accounts = Activeaccounts::where('userid', $userid)->get();
        $allowed_accounts_data = [];
        foreach ($allowed_accounts as $allowed_account) {
            array_push($allowed_accounts_data, $allowed_account->account_id);
        }
        // $query = Account::where('business_id', $business_id);

        // Get accounts Id not in Cheques
        $acount_Types = AccountType::where('business_id', $business_id)->where('cheque', 1)->get();
        $acount_Type_id = [];
        foreach ($acount_Types as $acount_Type) {
            array_push($acount_Type_id, $acount_Type->id);
        }
        // commect

        $query = Account::where('business_id', $business_id)->whereIn('id', $allowed_accounts_data);
        // $query = Account::where('business_id', $business_id);


        $permitted_locations = auth()->user()->permitted_locations();
        $account_ids = [];
        // if ($permitted_locations != 'all') {
        //     $locations = BusinessLocation::where('business_id', $business_id)
        //         ->whereIn('id', $permitted_locations)
        //         ->get();

        //     foreach ($locations as $location) {
        //         if (!empty($location->default_payment_accounts)) {
        //             $default_payment_accounts = json_decode($location->default_payment_accounts, true);
        //             foreach ($default_payment_accounts as $key => $account) {
        //                 if (!empty($account['is_enabled']) && !empty($account['account'])) {
        //                     $account_ids[] = $account['account'];

        //                 }
        //             }
        //         }
        //     }

        //     $account_ids = array_unique($account_ids);
        // }


        // Commented for Zainco 30-Mar
        if ($permitted_locations != 'all') {
            // $query->whereIn('accounts.id', $account_ids);
        }

        $can_access_account = auth()->user()->can('account.access');
        if ($can_access_account && $show_balance) {
            // $query->leftjoin('account_transactions as AT', function ($join) {
            //     $join->on('AT.account_id', '=', 'accounts.id');
            //     $join->whereNull('AT.deleted_at');
            // })
            $query->select(
                'accounts.name',
                'accounts.id',
                DB::raw("(SELECT SUM( IF(account_transactions.type='credit', amount, -1*amount) ) as balance from account_transactions where account_transactions.account_id = accounts.id AND deleted_at is NULL) as balance")
            );
        }

        if (!$closed) {
            $query->where('is_closed', 0);
        }

        $accounts = $query->get();

        $dropdown = [];
        if ($prepend_none) {
            $dropdown[''] = __('lang_v1.none');
        }

        $commonUtil = new Util;
        foreach ($accounts as $account) {
            $name = $account->name;

            if ($can_access_account && $show_balance) {
                $name .= ' (' . __('lang_v1.balance') . ': ' . $commonUtil->num_f($account->balance) . ')';
            }

            $dropdown[$account->id] = $name;
        }

        return $dropdown;
    }

    /**
     * Scope a query to only include not closed accounts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotClosed($query)
    {
        return $query->where('is_closed', 0);
    }

    /**
     * Scope a query to only include non capital accounts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeNotCapital($query)
    // {
    //     return $query->where(function ($q) {
    //         $q->where('account_type', '!=', 'capital');
    //         $q->orWhereNull('account_type');
    //     });
    // }

    public static function accountTypes()
    {
        return [
            '' => __('account.not_applicable'),
            'saving_current' => __('account.saving_current'),
            'capital' => __('account.capital')
        ];
    }

    public function account_type()
    {
        return $this->belongsTo(\App\AccountType::class, 'account_type_id');
    }
}
