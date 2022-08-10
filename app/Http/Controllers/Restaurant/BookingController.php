<?php

namespace App\Http\Controllers\Restaurant;

use App\BusinessLocation;
use App\Contact;
use App\CustomerGroup;
use App\Restaurant\Booking;
use App\Transaction;
use App\User;
use App\Utils\Util;
use DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\RestaurantUtil;

class BookingController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $commonUtil;
    protected $restUtil;

    public function __construct(Util $commonUtil, RestaurantUtil $restUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->restUtil = $restUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('crud_all_bookings') && !auth()->user()->can('crud_own_bookings')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        $user_id = request()->has('user_id') ? request()->user_id : null;
        if (!auth()->user()->hasPermissionTo('crud_all_bookings') && !$this->restUtil->is_admin(auth()->user(), $business_id)) {
            $user_id = request()->session()->get('user.id');
        }
        if (request()->ajax()) {
            $filters = [
                'start_date' => request()->start,
                'end_date' => request()->end,
                'user_id' => $user_id,
                'location_id' => !empty(request()->location_id) ? request()->location_id : null,
                'business_id' => $business_id
            ];

            $events = $this->restUtil->getBookingsForCalendar($filters);

            return $events;
        }

        $business_locations = BusinessLocation::forDropdown($business_id);

        $customers =  Contact::customersDropdown($business_id, false);

        $correspondents = User::forDropdown($business_id, false);

        $types = Contact::getContactTypes();
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $booking_statuses = [
            // 'waiting' => __('lang_v1.waiting_1'),
            // 'booked' => __('lang_v1.booked'),
            // 'completed' => __('lang_v1.completed'),
            // 'cancelled' => __('lang_v1.cancelled'),
        ];

        if (auth()->user()->can('booking.waiting_1')) {
            $booking_statuses['waiting'] = __('lang_v1.waiting_1');
        }
        if (auth()->user()->can('booking.booked')) {
            $booking_statuses['booked'] = __('lang_v1.booked');
        }
        if (auth()->user()->can('booking.completed')) {
            $booking_statuses['completed'] = __('lang_v1.completed');
        }
        if (auth()->user()->can('booking.cancelled')) {
            $booking_statuses['cancelled'] = __('lang_v1.cancelled');
        }



        return view('restaurant.booking.index', compact('business_locations', 'customers', 'correspondents', 'types', 'customer_groups', 'booking_statuses'));
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('crud_all_bookings') && !auth()->user()->can('crud_own_bookings')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            if ($request->ajax()) {
                $business_id = request()->session()->get('user.business_id');
                $user_id = request()->session()->get('user.id');

                $input = $request->input();
                $input['booking_status'] = "waiting";
                $booking_start = $this->commonUtil->uf_date($input['booking_start'], true);
                $booking_end = $this->commonUtil->uf_date($input['booking_end'], true);
                $date_range = [$booking_start, $booking_end];

                //Check if booking is available for the required input
                $query = Booking::where('business_id', $business_id)
                    ->where('location_id', $input['location_id'])
                    ->where('contact_id', $input['contact_id'])
                    ->where(function ($q) use ($date_range) {
                        $q->whereBetween('booking_start', $date_range)
                            ->orWhereBetween('booking_end', $date_range);
                    });

                if (isset($input['res_table_id'])) {
                    $query->where('table_id', $input['res_table_id']);
                }

                $existing_booking = $query->first();
                if (empty($existing_booking)) {
                    $input['business_id'] = $business_id;
                    $input['created_by'] = $user_id;
                    $input['booking_start'] = $booking_start;
                    $input['booking_end'] = $booking_end;

                    $booking = Booking::createBooking($input);

                    $output = [
                        'success' => 1,
                        'msg' => trans("lang_v1.added_success"),
                    ];

                    //Send notification to customer
                    if (isset($input['send_notification']) && $input['send_notification'] == 1) {
                        $output['send_notification'] = 1;
                        $output['notification_url'] = action('NotificationController@getTemplate', ["transaction_id" => $booking->id, "template_for" => "new_booking"]);
                    }
                } else {
                    $time_range = $this->commonUtil->format_date($existing_booking->booking_start, true) . ' ~ ' .
                        $this->commonUtil->format_date($existing_booking->booking_end, true);

                    $output = [
                        'success' => 0,
                        'msg' => trans(
                            "restaurant.booking_not_available",
                            [
                                'customer_name' => $existing_booking->customer->name,
                                'booking_time_range' => $time_range
                            ]
                        )
                    ];
                }
            } else {
                die(__("messages.something_went_wrong"));
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __("messages.something_went_wrong")
            ];
        }
        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $booking = Booking::where('business_id', $business_id)
                ->where('id', $id)
                ->with(['table', 'customer', 'correspondent', 'waiter', 'location'])
                ->first();
            if (!empty($booking)) {
                $booking_start = $this->commonUtil->format_date($booking->booking_start, true);
                $booking_end = $this->commonUtil->format_date($booking->booking_end, true);

                if ($booking->booking_invoice == 0) {
                    $booking_statuses = [
                        // 'waiting' => __('lang_v1.waiting_1'),
                        // 'booked' => __('lang_v1.booked'),
                        // 'completed' => __('lang_v1.completed'),
                        // 'cancelled' => __('lang_v1.cancelled'),
                    ];
            
                    if (auth()->user()->can('booking.waiting_1')) {
                        $booking_statuses['waiting'] = __('lang_v1.waiting_1');
                    }
                    if (auth()->user()->can('booking.booked')) {
                        $booking_statuses['booked'] = __('lang_v1.booked');
                    }
                    if (auth()->user()->can('booking.completed')) {
                        $booking_statuses['completed'] = __('lang_v1.completed');
                    }
                    if (auth()->user()->can('booking.cancelled')) {
                        $booking_statuses['cancelled'] = __('lang_v1.cancelled');
                    }
                } else {
                    $booking_statuses = [
                        // 'waiting' => __('lang_v1.waiting_1'),
                        // 'booked' => __('lang_v1.booked'),
                        // 'completed' => __('lang_v1.completed'),
                        // 'cancelled' => __('lang_v1.cancelled'),
                    ];
            
                    if (auth()->user()->can('booking.waiting_1')) {
                        $booking_statuses['waiting'] = __('lang_v1.waiting_1');
                    }
                    if (auth()->user()->can('booking.booked')) {
                        $booking_statuses['booked'] = __('lang_v1.booked');
                    }
                    if (auth()->user()->can('booking.completed')) {
                        $booking_statuses['completed'] = __('lang_v1.completed');
                    }
                    if (auth()->user()->can('booking.cancelled')) {
                        $booking_statuses['cancelled'] = __('lang_v1.cancelled');
                    }
                }

                return view('restaurant.booking.show', compact('booking', 'booking_start', 'booking_end', 'booking_statuses'));
            }
        }
    }



    public function linkInvoice($id)
    {
        if (!auth()->user()->can('crud_all_bookings') && !auth()->user()->can('crud_own_bookings')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->has('user_id') ? request()->user_id : null;
        if (!auth()->user()->hasPermissionTo('crud_all_bookings') && !$this->restUtil->is_admin(auth()->user(), $business_id)) {
            $user_id = request()->session()->get('user.id');
        }


        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $today = \Carbon::now()->format('Y-m-d');
        $transaction = Transaction::where('id', $id)->first();
        $query = Booking::where('business_id', $business_id)
            ->where('contact_id', $transaction->contact_id)->orderBy('id', 'DESC')->get();

        // return $query;
        // ->where('booking_status', 'booked')
        // ->whereDate('booking_start', $today)
        // ->with(['table', 'customer', 'correspondent', 'waiter', 'location']);

        if (request()->ajax()) {
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $customers =  Contact::customersDropdown($business_id, false);
        $correspondents = User::forDropdown($business_id, false);
        $types = Contact::getContactTypes();
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $booking_statuses = [
            'waiting' => __('lang_v1.waiting_1'),
            'booked' => __('lang_v1.booked'),
            'completed' => __('lang_v1.completed'),
            'cancelled' => __('lang_v1.cancelled'),
        ];

        return view('restaurant.booking.index_booking_link')->with('query', $query)
            ->with('id', $id);

        // return "Test " . $id;
    }
    public function linkInvoiceGrand($id, $Bookingid, $action)
    {

        try {
            if ($action == 'grand') {
                $values = array('booking_status' =>  'booked', 'booking_invoice' =>  $id);
                Booking::where('id', $Bookingid)->update($values);
                $output = [
                    'success' => True,
                    'msg' => ''
                ];
            }
            if ($action == 'delete') {
                $values = array('booking_status' =>  'waiting', 'booking_invoice' =>  0);
                Booking::where('id', $Bookingid)->update($values);
                $output = [
                    'success' => True,
                    'msg' => ''
                ];
            }
        } catch (Exception $e) {
            $output = [
                'success' => False,
                'msg' => ''
            ];
        }
        return redirect()->back()->with('status', $output);
        // if (!auth()->user()->can('crud_all_bookings') && !auth()->user()->can('crud_own_bookings')) {
        //     abort(403, 'Unauthorized action.');
        // }
        // $business_id = request()->session()->get('user.business_id');
        // $user_id = request()->has('user_id') ? request()->user_id : null;
        // if (!auth()->user()->hasPermissionTo('crud_all_bookings') && !$this->restUtil->is_admin(auth()->user(), $business_id)) {
        //     $user_id = request()->session()->get('user.id');
        // }


        // $business_id = request()->session()->get('user.business_id');
        // $user_id = request()->session()->get('user.id');
        // $today = \Carbon::now()->format('Y-m-d');
        // $query = Booking::where('business_id', $business_id)->get();

        // return $query;
        // ->where('booking_status', 'booked')
        // ->whereDate('booking_start', $today)
        // ->with(['table', 'customer', 'correspondent', 'waiter', 'location']);

        // if (request()->ajax()) {
        // }

        // $business_locations = BusinessLocation::forDropdown($business_id);
        // $customers =  Contact::customersDropdown($business_id, false);
        // $correspondents = User::forDropdown($business_id, false);
        // $types = Contact::getContactTypes();
        // $customer_groups = CustomerGroup::forDropdown($business_id);

        // $booking_statuses = [
        //     'waiting' => __('lang_v1.waiting_1'),
        //     'booked' => __('lang_v1.booked'),
        //     'completed' => __('lang_v1.completed'),
        //     'cancelled' => __('lang_v1.cancelled'),
        // ];

        // return view('restaurant.booking.index_booking_link')->with('query',$query)
        // ->with('id',$id);

        // return "Test " . $id;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('crud_all_bookings') && !auth()->user()->can('crud_own_bookings')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = $request->session()->get('user.business_id');
            $booking = Booking::where('business_id', $business_id)
                ->find($id);
            if (!empty($booking)) {
                $booking->booking_status = $request->booking_status;
                $booking->save();
            }

            $output = [
                'success' => 1,
                'msg' => trans("lang_v1.updated_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __("messages.something_went_wrong")
            ];
        }
        return $output;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('crud_all_bookings') && !auth()->user()->can('crud_own_bookings')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = request()->session()->get('user.business_id');
            $booking_check = Booking::where('business_id', $business_id)
                ->where('id', $id)
                ->where('booking_invoice', 0);

            if ($booking_check->count() == 0) {
                $output = [
                    'success' => 0,
                    'msg' => __("messages.something_went_wrong") . " الحجز مرتبط بفاتورة بيع  "
                ];
            } else {
                $booking = Booking::where('business_id', $business_id)
                    ->where('id', $id)
                    ->where('booking_invoice', 0)
                    ->delete();
                $output = [
                    'success' => 1,
                    'msg' => trans("lang_v1.deleted_success")
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __("messages.something_went_wrong")
            ];
        }
        return $output;
    }

    /**
     * Retrieves todays bookings
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function getTodaysBookings()
    {
        if (!auth()->user()->can('crud_all_bookings') && !auth()->user()->can('crud_own_bookings')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');
            $today = \Carbon::now()->format('Y-m-d');
            $query = Booking::where('business_id', $business_id)
                ->where('booking_status', 'booked')
                ->whereDate('booking_start', $today)
                ->with(['table', 'customer', 'correspondent', 'waiter', 'location']);

            if (!empty(request()->location_id)) {
                $query->where('location_id', request()->location_id);
            }

            if (!auth()->user()->hasPermissionTo('crud_all_bookings') && !$this->commonUtil->is_admin(auth()->user(), $business_id)) {


                $query->where(function ($query) use ($user_id) {
                    $query->where('created_by', $user_id)
                        ->orWhere('correspondent_id', $user_id)
                        ->orWhere('waiter_id', $user_id);
                });

                //$query->where('created_by', $user_id);
            }

            return Datatables::of($query)
                ->editColumn('table', function ($row) {
                    return !empty($row->table->name) ? $row->table->name : '--';
                })
                ->editColumn('customer', function ($row) {
                    return !empty($row->customer->name) ? $row->customer->name : '--';
                })
                ->editColumn('correspondent', function ($row) {
                    return !empty($row->correspondent->user_full_name) ? $row->correspondent->user_full_name : '--';
                })
                ->editColumn('waiter', function ($row) {
                    return !empty($row->waiter->user_full_name) ? $row->waiter->user_full_name : '--';
                })
                ->editColumn('location', function ($row) {
                    return !empty($row->location->name) ? $row->location->name : '--';
                })
                ->editColumn('booking_start', function ($row) {
                    return $this->commonUtil->format_date($row->booking_start, true);
                })
                ->editColumn('booking_end', function ($row) {
                    return $this->commonUtil->format_date($row->booking_end, true);
                })
                ->removeColumn('id')
                ->make(true);
        }
    }
    /**
     * Retrieves todays bookings
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function getAllBookings()
    {
        if (!auth()->user()->can('crud_all_bookings') && !auth()->user()->can('crud_own_bookings')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');
            $today = \Carbon::now()->format('Y-m-d');
            $query = Booking::where('business_id', $business_id)
                // ->where('booking_status', 'booked')
                // ->whereDate('booking_start', $today)
                ->with(['table', 'customer', 'correspondent', 'waiter', 'location']);

            if (!empty(request()->location_id)) {
                $query->where('location_id', request()->location_id);
            }

            if (!auth()->user()->hasPermissionTo('crud_all_bookings') && !$this->commonUtil->is_admin(auth()->user(), $business_id)) {


                $query->where(function ($query) use ($user_id) {
                    $query->where('created_by', $user_id)
                        ->orWhere('correspondent_id', $user_id)
                        ->orWhere('waiter_id', $user_id);
                });

                //$query->where('created_by', $user_id);
            }

            return Datatables::of($query)
                ->editColumn('table', function ($row) {
                    return !empty($row->table->name) ? $row->table->name : '--';
                })
                ->editColumn('booking_status', function ($row) {
                    return $row->booking_status;
                })
                ->editColumn('customer', function ($row) {
                    return !empty($row->customer->name) ? $row->customer->name : '--';
                })
                ->editColumn('correspondent', function ($row) {
                    return !empty($row->correspondent->user_full_name) ? $row->correspondent->user_full_name : '--';
                })
                ->editColumn('waiter', function ($row) {
                    return !empty($row->waiter->user_full_name) ? $row->waiter->user_full_name : '--';
                })
                ->editColumn('location', function ($row) {
                    return !empty($row->location->name) ? $row->location->name : '--';
                })
                ->editColumn('booking_start', function ($row) {
                    return $this->commonUtil->format_date($row->booking_start, true);
                })
                ->editColumn('booking_invoice', function ($row) {
                    if ($row->booking_invoice == null) {
                        $html = '<a href=>booking</a>';
                        return $html;
                    } else {
                        return $row->booking_invoice;
                    }
                })
                ->editColumn('booking_end', function ($row) {
                    return $this->commonUtil->format_date($row->booking_end, true);
                })
                ->removeColumn('id')
                ->rawColumns(['booking_invoice'])
                ->make(true);
        }
    }
}
