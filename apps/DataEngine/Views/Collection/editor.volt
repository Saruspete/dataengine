<h3>DataEngine Collection Editor</h3>

<!-- using Jquery Multi-Select http://crlcu.github.io/multiselect/ -->

<form action="{{ url('DataEngine/Collection/editor') }}" method="POST">
	<div class="row">
		<div class="col-sm-5">
			<select id="fields" class="form-control" multiple="multiple" size="40"></select>
		</div>

		<div class="col-sm-2">
			<button type="button" id="fields_rightSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-right"></i></button>
			<button type="button" id="fields_leftSelected" class="btn btn-block"><i class="glyphicon glyphicon-chevron-left"></i></button>
		</div>

		<div class="col-sm-5">
			<select name="collection" id="collection" style="width:100%"></select>
			<select id="fields_to" class="form-control"  multiple="multiple" size="40" name="fields[]"></select>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-5"></div>
		<div class="col-sm-2">
			<input type="submit" name="Save" value="Save" class="btn btn-block" />
		</div>
		<div class="col-sm-5"></div>
	</div>
</form>