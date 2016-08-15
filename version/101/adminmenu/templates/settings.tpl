<div class="panel panel-default">
	<div class="panel-body">
	    <form name="mailchimp_settings" class="form" method="POST" enctype="multipart/form-data">
	        <div class="form-group">
	            <label>API Key</label>
	            <div class="for">
	                <input type="text" class="form-control" name="settings[api_key]" value="{$cEinstellungen_arr.api_key}" />
	                <p title="Der API Key wird benötigt damit der JTL-Shop3 mit MailChimp kommunizieren kann. Den API Key erhalten Sie von MailChimp." ref="1" class="help"></p>
	            </div>
	        </div>
	        <div class="form-group">
	            <label>Willkommensnachricht nach Import versenden</label>
	            <div class="for">
	                <select name="settings[send_welcome]" class="form-control" >
	                    <option value="0" {if $cEinstellungen_arr.send_welcome == "0"}selected="selected"{/if}>Nein</option>
	                    <option value="1" {if $cEinstellungen_arr.send_welcome == "1"}selected="selected"{/if}>Ja</option>
	                </select>
	                <p title="Nach dem der Benutzer importiert wurde versendet MailChimp eine E-Mail mit den eingetragenen Daten und einem Link um den Newsletter abzubestellen." ref="2" class="help"></p>
	        </div>
	        <div class="form-group">
	            <label>Newsletterabmeldung</label>
	                <select name="settings[delete_member]" class="form-control">
	                    <option value="0" {if $cEinstellungen_arr.delete_member == "0"}selected="selected"{/if}>Status "unsubscribed"</option>
	                    <option value="1" {if $cEinstellungen_arr.delete_member == "1"}selected="selected"{/if}>Empfänger löschen</option>
	                </select>
	                <p title="Wenn ein Empfänger sich abmelden möchte bzw. von Ihnen abgemeldet wird." ref="2" class="help"></p>
	            </div>
	        <div class="form-group">
	            <label>Liste für Empfänger ohne Kundenkonto</label>
	                <select name="settings[list_no_account]" class="form-control" >
	                    <option value="">Bitte wählen Sie eine Liste aus</option>
	                    {foreach from=$oLists_arr.data item=xList_arr}
	                        <option value="{$xList_arr.id}" {if $xList_arr.id == $cEinstellungen_arr.list_no_account}selected="selected"{/if}>{$xList_arr.name|utf8_decode}</option>
	                    {/foreach}
	                </select>
	                <p title="Welcher Liste sollen Empfänger zugewiesen werden die kein Kundenkonto besitzten ?" ref="3" class="help"></p>
	        </div>
	        {foreach from=$oKundengruppen_arr item="oKundengruppe"}
	            {assign var='nSettingRef' value=$nSettingRef+1}
	            {assign var="kKundengruppe" value=$oKundengruppe->kKundengruppe}
	            {assign var="cKey" value="list_customer_group_$kKundengruppe"}
	            <div class="form-group">
	                <label>Liste für Empfänger der Kundengruppe "{$oKundengruppe->cName}"</label>
	                <select name="settings[list_customer_group_{$oKundengruppe->kKundengruppe}]" class="form-control" >
	                        <option value="">Bitte wählen Sie eine Liste aus</option>
	                        {foreach from=$oLists_arr.data item=xList_arr}
	                            <option value="{$xList_arr.id}" {if $xList_arr.id == $cEinstellungen_arr[$cKey]}selected="selected"{/if}>{$xList_arr.name|utf8_decode}</option>
	                        {/foreach}
	                    </select>
	                    <p title="Welcher Liste sollen Empfänger zugewiesen werden die der Kundengruppe '{$oKundengruppe->cName}' zugewiesen sind ?" ref="{$nSettingRef}" class="help"></p>
	            </div>
	        {/foreach}
	        <div class="save_wrapper">
	            <input type="submit" class="btn btn-success" value="Speichern">
	        </div>
	    </form>
	</div>
</div>