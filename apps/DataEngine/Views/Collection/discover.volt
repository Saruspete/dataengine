<h3>Discovery</h3>


<form action="{{ url('DataEngine/Collection/discover') }}" method="post">
	<div class="row">
		<div class="col-sm-5">
			<select name="connectionId" id="connectionId" style="width: 100%">
				<option value="0">(New Connection)</option>
			{% for conn in connections %}
				<option value="{{ conn.id }}">{{ conn.name }} ({{conn.type}}://{{conn.username}}@{{conn.hostname}})</option>
			{% endfor %}
			</select><br />

			<select name="Type" id="connectionType" value="" style="width: 100%">
			{% for type in conntypes %}
				<option value="{{type}}">{{type}}</option>
			{% endfor %}
			</select>
			<input type="text" id="connectionName" name="Name" value="{{ values['Name'] }}" title="Name" style="width: 100%" /><br />
			<input type="text" id="connectionUser" name="User" value="{{ values['User'] }}" title="Username" style="width: 100%" /><br />
			<input type="text" id="connectionPass" name="Pass" value="{{ values['Pass'] }}" title="Password" style="width: 100%" /><br />
			<input type="text" id="connectionHost" name="Host" value="{{ values['Host'] }}" title="Hostname" style="width: 100%" /><br />
			<input type="text" id="connectionSchema" name="Schema" value="{{ values['Schema'] }}" title="Schema" style="width: 100%" /><br />
			<input type="text" id="connectionExtra" name="Extra" value="{{ values['Extra'] }}" title="Extra data" style="width: 100%" /><br />
		</div>
		<div class="col-sm-2">
			<input type="submit" name="op" id="btnConnectionTest" value="Test" class="btn btn-block" />
			<input type="submit" name="op" id="btnConnectionDiscover" value="Discover" class="btn btn-block" />
			<input type="submit" name="op" id="btnConnectionSave" value="Save" class="btn btn-block" />
		</div>
		<div class="col-sm-5" id="changes">
			{{ results }}
		</div>
	</div>
</form>