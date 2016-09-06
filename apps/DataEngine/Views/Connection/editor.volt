<h3>Discovery</h3>

{{ form('DataEngine/Connection/editor', 'method': 'post') }}
	<div class="row">
		<div class="col-sm-5">

			{{ form.render('id', ['style': 'width: 100%', 'id': 'connectionid']) }}
			

			{{ form.render('type', ['style': 'width: 100%', 'id': 'connectiontype']) }}

			<input type="text" id="connectionname" name="name" value="{{ values['name'] }}" title="Name" style="width: 100%; background: url('/images/icons/book.png') 3px 3px no-repeat; padding-left: 25px;" /><br />
			<input type="text" id="connectionusername" name="username" value="{{ values['username'] }}" title="Username" style="width: 100%; background: url('/images/icons/user.png') 3px 3px no-repeat; padding-left: 25px;" /><br />
			<input type="text" id="connectionpassword" name="password" value="{{ values['password'] }}" title="Password" style="width: 100%; background: url('/images/icons/key.png') 3px 3px no-repeat; padding-left: 25px;" /><br />
			<input type="text" id="connectionhost" name="host" value="{{ values['host'] }}" title="Hostname" style="width: 100%; background: url('/images/icons/server.png') 3px 3px no-repeat; padding-left: 25px;" /><br />
			<input type="text" id="connectionschema" name="schema" value="{{ values['schema'] }}" title="Schema" style="width: 100%; background: url('/images/icons/database.png') 3px 3px no-repeat; padding-left: 25px;" /><br />
			<input type="text" id="connectionextra" name="extra" value="{{ values['extra'] }}" title="Extra data" style="width: 100%; background: url('/images/icons/basket.png') 3px 3px no-repeat; padding-left: 25px;" /><br />
		</div>
		<div class="col-sm-2">
			<input type="submit" name="op" id="btnConnectionTest" value="Test" class="btn btn-block" />
			<input type="submit" name="op" id="btnConnectionSave" value="Save" class="btn btn-block" />
			<input type="submit" name="op" id="btnConnectionDiscover" value="Discover" class="btn btn-block" />
			<input type="submit" name="op" id="btnConnectionDelete" value="Delete" class="btn btn-block" />
		</div>
		<div class="col-sm-5" id="changes">
			{{ results }}
		</div>
	</div>
{{ end_form() }}