<div id="settings">
    <form name="mailchimp_settings" method="POST" enctype="multipart/form-data">
        <div class="item">
            <div class="name">
                <label>API Key</label>
            </div>
            <div class="for">
                <input type="text" name="settings[api_key]" value="{$cEinstellungen_arr.api_key}" />
                <div title="Der API Key wird benötigt damit der JTL-Shop3 mit MailChimp kommunizieren kann. Den API Key erhalten Sie von MailChimp." ref="1" class="help"></div>
            </div>
        </div>
        <div class="item">
            <div class="name">
                <label>Willkommensnachricht nach Import versenden</label>
            </div>
            <div class="for">
                <select name="settings[send_welcome]">
                    <option value="0" {if $cEinstellungen_arr.send_welcome == "0"}selected="selected"{/if}>Nein</option>
                    <option value="1" {if $cEinstellungen_arr.send_welcome == "1"}selected="selected"{/if}>Ja</option>
                </select>
                <div title="Nach dem der Benutzer importiert wurde versendet MailChimp eine E-Mail mit den eingetragenen Daten und einem Link um den Newsletter abzubestellen." ref="2" class="help"></div>
            </div>
        </div>
        <div class="item">
            <div class="name">
                <label>Liste für Empfänger ohne Kundenkonto</label>
            </div>
            <div class="for">
                <select name="settings[list_no_account]">
                    <option value="">Bitte wählen Sie eine Liste aus</option>
                    {foreach from=$oLists_arr.data item=xList_arr}
                        <option value="{$xList_arr.id}" {if $xList_arr.id == $cEinstellungen_arr.list_no_account}selected="selected"{/if}>{$xList_arr.name|utf8_decode}</option>
                    {/foreach}
                </select>
                <div title="Welcher Liste sollen Empfänger zugewiesen werden die kein Kundenkonto besitzten ?" ref="3" class="help"></div>
            </div>
        </div>
        {foreach from=$oKundengruppen_arr item="oKundengruppe"}
            {assign var='nSettingRef' value=$nSettingRef+1}
            {assign var="kKundengruppe" value=$oKundengruppe->kKundengruppe}
            {assign var="cKey" value="list_customer_group_$kKundengruppe"}
            <div class="item">
                <div class="name">
                    <label>Liste für Empfänger der Kundengruppe "{$oKundengruppe->cName}"</label>
                </div>
                <div class="for">
                    <select name="settings[list_customer_group_{$oKundengruppe->kKundengruppe}]">
                        <option value="">Bitte wählen Sie eine Liste aus</option>
                        {foreach from=$oLists_arr.data item=xList_arr}
                            <option value="{$xList_arr.id}" {if $xList_arr.id == $cEinstellungen_arr[$cKey]}selected="selected"{/if}>{$xList_arr.name|utf8_decode}</option>
                        {/foreach}
                    </select>
                    <div title="Welcher Liste sollen Empfänger zugewiesen werden die der Kundengruppe '{$oKundengruppe->cName}' zugewiesen sind ?" ref="{$nSettingRef}" class="help"></div>
                </div>
            </div>
        {/foreach}
        <div class="save_wrapper">
            <input type="submit" class="button orange" value="Speichern">
        </div>
    </form>
</div>