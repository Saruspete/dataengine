<h3>DataEngine Mapping Editor</h3>

<!-- using Jquery Multi-Select http://crlcu.github.io/multiselect/ -->

<form action="{{ url('DataEngine/Collection/mapping') }}" method="POST">
	<div class="row">
		<div class="col-sm-2">
			
		</div>

		<div class="col-sm-10">
			<table>
				<tr>
					<td>
						Src <select name="collectionSrc" id="collectionSrc"></select>
					</td>
					<td>
						<b>Transformation</b>
					</td>
					<td>
						Dst <select name="collectionDst" id="collectionDst"></select>
					</td>
				</tr>
				{% for fieldDst in fieldsDst %}
				<tr>
					<td>
						<select name="fieldSrc[{{ fieldDst.getId() }}]">
						{% for fieldSrc in fieldsSrc %}
						<option value="{{ field.id }}">{{ field.name }} ({{ field.path }})</option>
						{% endfor %}
						</select>
					</td>
					<td>
					<select name="fieldTransform[{{ fieldDst.getId() }}]">
						<option value="">(none)</option>
						<option value="fixed">Fixed Value</option>
					</select>
					</td>
					<td>{{ fieldDst.name }}</td>
				</tr>
				{% endfor %}
			</table>
		</div>
	</div>
</form>