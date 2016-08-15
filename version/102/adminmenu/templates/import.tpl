<script type="text/javascript" src="{$currentTemplateDir}js/jquery.progressbar.js"></script>
<script type="text/javascript">
    var url = "{$cAdminmenuPfadURL}doImport.php";
    var tpl = "{$URL_SHOP}/{$PFAD_ADMIN}templates/bootstrap/gfx/jquery";

    {literal}      
    $(function() {
        $('#exportall').click(function() {
           $('.extract_async').trigger('click');
           return false;
        });
    });

    function init_import()
    {
     $.getJSON(url, {doImport: 1}, function(cb) {
        do_import(cb);
     });
     return false;
    }

    function do_import(cb)
    {
     if (typeof cb != 'object')
     {
        error_import();
     }
     else if (cb.bFinished)
     {
        finish_import(cb);
     }
     else
     {
        show_import_info(cb);
        $.getJSON(cb.cURL, {doImport: 1}, function(cb) {
           do_import(cb);
        });
     }
    }

    function error_import(cb)
    {
        alert('Es ist ein Fehler aufgetreten');
    }

    function show_import_info(cb)
    {
         var elem = '#progress';
         $(elem).find('span').fadeIn();
         $(elem).find('span').progressBar(cb.nCurrent, {
            max: cb.nMax, 
            textFormat: 'fraction',
            steps : cb.bFirst ? 0 : 20,
            stepDuration : cb.bFirst ? 0 : 20,
            boxImage: tpl + '/progressbar.gif',
            barImage: { 0:  tpl + '/progressbg_red.gif', 30: tpl + '/progressbg_orange.gif', 50: tpl + '/progressbg_yellow.gif', 70: tpl + '/progressbg_green.gif' }
         });
    }

    function finish_import(cb)
    {
     var elem = '#progress';
     $(elem).find('span').fadeOut(250, function() {
        var text = $(elem).find('p').html();
        $(elem).find('p').html(text).fadeIn(1000);
     });
    }
    {/literal}
</script>
<div class="panel panel-default">
	<div class="panel-body">
		<table class="table">
			<p style="color:red;">Hinweis: Diese Funktion sollten Sie nur initial nach der Installation des Plugins verwenden. Dabei werden alle Empfänger nach MailChimp übertragen und vorhandene überschrieben!</p>
			<thead>
				<tr>
					<th class="text-left">Empfänger</th>
					<th class="text-left">Synchronisierte Empfänger</th>
					<th class="text-left">Fortschritt</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="text-left">{$nEmpfaengerCount}</td>	
					<td class="text-left">{$nSyncCount}</td>
					<td class="text-left"><span id="progress"><span></span></span></td>
				</tr>
			</tbody>
		</table>
    </div>
    <div class="panel-footer">
        <form method="post">
            <input type="submit" class="btn btn-primary" name="prepareImport" value="Vorbereiten">
            <input type="submit" class="btn btn-success" name="doImport" value="Starten" onclick="return init_import();">
        </form>
    </div>
</div>