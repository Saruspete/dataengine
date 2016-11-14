<h3>DataEngine Collection Editor</h3>

<!-- using Jquery Multi-Select http://crlcu.github.io/multiselect/ -->

<form action="{{ url('DataEngine/Collection/editor') }}" method="POST">
	<div class="row">
		<div class="col-sm-4">
			<select id="fields" class="form-control" multiple="multiple" size="30"></select>
		</div>

		<div class="col-sm-3">
		<!--
			<fieldset>
				<legend>Create new field</legend>
				<input type="text" id="field_new_name" title="name" style="width: 100%; background: url('/images/icons/application.png') 3px 3px no-repeat; padding-left: 25px;" />
				<select id="field_new_type" title="type">
					<select name="int">INT</select>
					<select name="varchar">VARCHAR</select>
				</select>
				<button type="button">Add</button>
			</fieldset>
			<br />
		-->
			<fieldset>
				<legend>Add field</legend>
				<input type="text" id="fields_rename" class="btn btn-block" />
				<button type="button" id="fields_rightSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-right"></i></button>
				<button type="button" id="fields_leftSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-left"></i></button>
			</fieldset>
			<br />
			<fieldset>
				<legend>Edit Collection</legend>
				{{ form.render('phPrim', ['style': 'width: 100%', 'id': 'phPrim']) }}
				<br />
				<input type="submit" name="op" value="Save" class="btn btn-block" />
				<input type="submit" name="op" value="Create storage" class="btn btn-block" />
				<input type="submit" name="op" value="Delete" class="btn btn-block" />
			</fieldset>
		</div>

		<div class="col-sm-5">
			<select name="collection" id="collection" style="width:100%"></select>
			<select id="fields_to" class="form-control"  multiple="multiple" size="30" name="fields[]"></select>
		</div>
	</div>
</form>