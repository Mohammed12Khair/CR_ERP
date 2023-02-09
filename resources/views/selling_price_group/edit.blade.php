<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('SellingPriceGroupController@update', [$spg->id]), 'method' => 'put', 'id' => 'selling_price_group_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'lang_v1.edit_selling_price_group' )</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __( 'lang_v1.name' ) . ':*') !!}
        {!! Form::text('name', $spg->name, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.name' ) ]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('rate', __( 'lang_v1.rate' ) . ':') !!}
        {!! Form::number('rate', $spg->rate, ['class' => 'form-control','placeholder' => __( 'lang_v1.description' ), 'rows' => 3,'step'=>'any']); !!}
      </div>

      <div class="form-group">
        {!! Form::label('currencie', __( 'lang_v1.currencie' ) . ':') !!}
        <select class="form-control" name="currencie">
          <?php
          $currnec = App\Currency::all();
          echo "<option value='" . $spg->currencie . "'>" . App\Currency::where('id', $spg->currencie)->first()->code . "</option>";
          foreach ($currnec as $currne) {
            echo "<option value='" . $currne->id . "'>" . $currne->code . " " . $currne->symbol . "</option>";
          }
          ?>
        </select>

      </div>

      <div class="form-group">
        {!! Form::label('description', __( 'lang_v1.description' ) . ':') !!}
        {!! Form::textarea('description', $spg->description, ['class' => 'form-control','placeholder' => __( 'lang_v1.description' ), 'rows' => 3]); !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->