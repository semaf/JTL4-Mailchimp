<div id="settings">
	<table>
		 <tbody>
		 	<tr>
				  <th class="tleft">Abonnent</th>
				  <th class="tleft">Kundengruppe</th>
				  <th class="tleft">E-Mail Adresse</th>
				  <th class="tcenter">Eingetragen am</th>
				  <th class="tcenter">Liste</th>
				  <th class="tcenter">Synchronisiert am</th>
				  <th class="tcenter">Letzte Synchronisierung</th>
			 </tr>
			 {foreach from=$oNewsletterEmpfaenger_arr item="oNewsletterEmpfaenger"}
 			 <tr class="tab_bg1">
				  <td class="tleft">
				  	{$oNewsletterEmpfaenger->cAnrede} {$oNewsletterEmpfaenger->cVorname} {$oNewsletterEmpfaenger->cNachname}
				  </td>	
				  <td class="tleft">{$oNewsletterEmpfaenger->cKundengruppe}</td>
				  <td class="tleft">{$oNewsletterEmpfaenger->cEmail}</td>
				  <td class="tcenter">{$oNewsletterEmpfaenger->dEingetragen|date_format:"%d.%m.%Y %R"}</td>
				  <td class="tcenter">{$oNewsletterEmpfaenger->cList}</td>
				  <td class="tcenter">{$oNewsletterEmpfaenger->dSync|date_format:"%d.%m.%Y %R"}</td>
				  <td class="tcenter">{$oNewsletterEmpfaenger->dLastSync|date_format:"%d.%m.%Y %R"}</td>
			 </tr>
			 {/foreach}
		</tbody>
	</table>
</div>