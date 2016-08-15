<link href="{$currentTemplateDir}css/easyTable.css"  rel="stylesheet">
<div class="panel panel-default">
	<div class="panel-body">
		<table id="table">
			<thead>
				<tr>
					<th class="text-left">Abonnent</th>
					<th class="text-left">Kundengruppe</th>
					<th class="text-left">E-Mail Adresse</th>
					<th class="text-left">Eingetragen am</th>
					<th class="text-left">Liste</th>
					<th class="text-left">Synchronisiert am</th>
					<th class="text-left">Letzte Synchronisierung</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$oNewsletterEmpfaenger_arr item="oNewsletterEmpfaenger"}
					<tr class="tab_bg1">
						<td class="text-left">{$oNewsletterEmpfaenger->cAnrede} {$oNewsletterEmpfaenger->cVorname} {$oNewsletterEmpfaenger->cNachname}</td>	
						<td class="text-left">{$oNewsletterEmpfaenger->cKundengruppe}</td>
						<td class="text-left">{$oNewsletterEmpfaenger->cEmail}</td>
						<td class="text-left">{$oNewsletterEmpfaenger->dEingetragen}</td>
						<td class="text-left">{$oNewsletterEmpfaenger->cList}</td>
						<td class="text-left">{$oNewsletterEmpfaenger->dSync}</td>
						<td class="text-left">{$oNewsletterEmpfaenger->dLastSync}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
<script type="text/javascript" src="{$currentTemplateDir}js/easyTable.js"></script>
<script>
	$("#table").easyTable();
</script>