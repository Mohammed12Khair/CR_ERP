<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('SellingPriceGroupController@store'), 'method' => 'post', 'id' => 'selling_price_group_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'lang_v1.add_selling_price_group' )</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __( 'lang_v1.name' ) . ':*') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.name' ) ]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('rate', __( 'lang_v1.rate' ) . ':') !!}
        {!! Form::number('rate', 1, ['class' => 'form-control','placeholder' => __( 'lang_v1.description' ), 'rows' => 3,'step'=>'any']); !!}
      </div>

      <div class="form-group">
        {!! Form::label('currencie', __( 'lang_v1.currencie' ) . ':') !!}
        <select class="form-control"  name="currencie">
          <?php
          $currnec = App\Currency::all();
          foreach ($currnec as $currne) {
            echo "<option value='" . $currne->id . "'>" . $currne->code . " " . $currne->symbol . "</option>";
          }
          ?>
        </select>

      </div>

      <div class="form-group">
        {!! Form::label('description', __( 'lang_v1.description' ) . ':') !!}
        {!! Form::textarea('description', null, ['class' => 'form-control','placeholder' => __( 'lang_v1.description' ), 'rows' => 3]); !!}
      </div>


    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->