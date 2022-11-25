<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('ExpenseCategoryController@store'), 'method' => 'post', 'id' => 'expense_category_add_form' ]) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'expense.add_expense_category' )</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __( 'expense.category_name' ) . ':*') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'expense.category_name' )]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('code', __( 'expense.category_code' ) . ':') !!}
        {!! Form::text('code', null, ['class' => 'form-control', 'placeholder' => __( 'expense.category_code' )]); !!}
      </div>


      <div class="form-group">
        {!! Form::label('expense', __( 'expense.expense_shipt' ) . ':*') !!}
        <?php

        $SomeRandomData = 0;

        ?>
        <select class="form-control" name="transfer">
          <option value="0" <?php if ($SomeRandomData == 0) {
                              echo 'selected';
                            } ?>>@lang( 'expense.expense_shipt_no' )</option>
          <option value="1" <?php if ($SomeRandomData == 1) {
                              echo 'selected';
                            } ?>>@lang( 'expense.expense_shipt_yes' )</option>
        </select>

      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->