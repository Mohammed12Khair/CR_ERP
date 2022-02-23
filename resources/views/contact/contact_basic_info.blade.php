<!-- <strong>{{ $contact->name }}</strong><br><br> -->
<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                Button with data-targetsdsd
            </button>
            

<h3 class="profile-username">
    <i class="fas fa-user-tie"></i>
    {{ $contact->name }}
    <small>
        @if($contact->type == 'both')
            {{__('role.customer')}} & {{__('role.supplier')}}
        @elseif(($contact->type != 'lead'))
            {{__('role.'.$contact->type)}}
        @endif
    </small>
</h3> 


<strong><i class="fa fa-map-marker  "></i> @lang('business.address')</strong>
<span class="text-muted">
    {!! $contact->contact_address !!}
</span>
@if($contact->supplier_business_name)
    <strong><i class="fa fa-briefcase  "></i> 
    @lang('business.business_name')</strong>
    <span class="text-muted">
        {{ $contact->supplier_business_name }}
    </span>
@endif

<strong><i class="fa fa-mobile  "></i> @lang('contact.mobile')</strong>
<span class="text-muted">
    {{ $contact->mobile }}
</span>
@if($contact->landline)
    <strong><i class="fa fa-phone  "></i> @lang('contact.landline')</strong>
    <span class="text-muted">
        {{ $contact->landline }}
    </span>
@endif
@if($contact->alternate_number)
    <strong><i class="fa fa-phone  "></i> @lang('contact.alternate_contact_number')</strong>
    <span class="text-muted">
        {{ $contact->alternate_number }}
    </span>
@endif
@if($contact->dob)
    <strong><i class="fa fa-calendar  "></i> @lang('lang_v1.dob')</strong>
    <span class="text-muted">
        {{ @format_date($contact->dob) }}
    </span>
@endif
