<?php

namespace App\Http\Controllers;

use App\Contact;
use Illuminate\Http\Request;

use App\RefundTable;

class RefundController extends Controller
{
    //
    public function index(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        $refund = RefundTable::where('business_id', $business_id)->where('contact_id', $id)
            ->whereIn('status', [0, 1])->get();
        return view('refund.index')->with('refund', $refund);
    }

    public function updateBalanceAdd(Request $request, $id)
    {

        try {
            $business_id = $request->session()->get('user.business_id');

            error_log("business id = " . $business_id);

            $refund = RefundTable::where('business_id', $business_id)->where('id', $id)->first();

            error_log("refund = " . $refund->refund_total);

            $contact = Contact::where('id', $refund->contact_id)->where('business_id', $business_id)->first();

            error_log("contact = " . $contact->balance);

            $current_balance = $refund->refund_total + $contact->balance;

            error_log("current_balance = " . $current_balance);

            Contact::where('id', $refund->contact_id)->update([
                "balance" => $current_balance
            ]);

            error_log("current_balance = " . $current_balance);

            RefundTable::where('id', $id)->update([
                "status" => 1
            ]);

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

        return redirect('refund/' . $contact->id)->with('status', $output);
    }
    public function updateBalanceRemove(Request $request, $id)
    {

        try {
            $business_id = $request->session()->get('user.business_id');

            error_log("business id = " . $business_id);

            $refund = RefundTable::where('business_id', $business_id)->where('id', $id)
                ->where('status', 1)->first();

            error_log("refund = " . $refund->refund_total);

            $contact = Contact::where('id', $refund->contact_id)->where('business_id', $business_id)->first();

            error_log("contact = " . $contact->balance);

            $current_balance = $contact->balance - $refund->refund_total;

            error_log("current_balance = " . $current_balance);

            if ($current_balance < 0) {
                $output = [
                    'success' => 0,
                    'msg' => __('messages.refund_error')
                    // 'msg' => $e->getMessage()
                ];

                return redirect('refund/' . $contact->id)->with('status', $output);
            }

            Contact::where('id', $refund->contact_id)->update([
                "balance" => $current_balance
            ]);

            error_log("current_balance = " . $current_balance);

            RefundTable::where('id', $id)->update([
                "status" => 0
            ]);

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

        return redirect('refund/' . $contact->id)->with('status', $output);
    }




}
