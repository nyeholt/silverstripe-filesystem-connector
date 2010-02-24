<style type="text/css">
.fileTable { width: 70%; border: none; border-collapse: collapse; }
.fileTable th { text-align: center; font-weight: bold; padding: 1em; }
.fileTable td { padding: 1em; }
</style>

<h2>$Title</h2>
$Title has $Children.Count child items
<% if Children %>
<table class="fileTable">
	<thead>
		<tr>
			<th></th>
			<th>Name</th>
			<th>Size</th>
		</tr>
	</thead>
	<tbody>
	<% control Children %>
		<tr>
			<td><img src="<% if Type == file %>jsparty/tree/images/page-file.gif<% else %>jsparty/tree/images/page-closedfolder.gif<% end_if %>" /></td>
			<td><a href="<% if Type == file %>$DownloadLink<% else %>$Link<% end_if %>">$Name</a></td>
			<td><% if Type == file %>$Size<% end_if %></td>
		</tr>
	<% end_control %>
	</tbody>
</table>

<% end_if %>