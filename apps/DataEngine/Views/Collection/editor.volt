<h3>DataEngine Collection Editor</h3>

<!-- using Jquery Multi-Select http://crlcu.github.io/multiselect/ -->

<form action="{{ url('DataEngine/Collection/editor') }}" method="POST">
	<div class="row">
		<div class="col-sm-4">
			<select id="fields" class="form-control" multiple="multiple" size="30"></select>
		</div>

		<div class="col-sm-3">
			<fieldset>
				<legend>Add new</legend>
				<input type="text" id="field_name" />
				<button type="button">Add new</button>
			</fieldset>
			<br />
			<fieldset>
				<legend>Add existing</legend>
				<button type="button" id="fields_rightSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-right"></i></button>
				<button type="button" id="fields_leftSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-left"></i></button>
			</fieldset>
			<br />
			<button type="button" id="fields_setPrimary" class="btn btn-block">Set as primary</button>
			<br />
			<input type="submit" name="op" value="Save" class="btn btn-block" />
			<input type="submit" name="op" value="Create storage" class="btn btn-block" />
		</div>

		<div class="col-sm-5">
			<select name="collection" id="collection" style="width:100%"></select>
			<select id="fields_to" class="form-control"  multiple="multiple" size="30" name="fields[]"></select>
		</div>
	</div>
</form>