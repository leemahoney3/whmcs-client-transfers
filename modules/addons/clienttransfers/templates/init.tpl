{include file='./partials/messages.tpl'}

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">

    <h3>Initiate a new Transfer</h3>
    <p>Please fill out the details below to begin the transfer.</p>
    <br />
    <form action="{$smarty.server.REQUEST_URI}" method="post">
        <div class="form-group">
            <label for="transfertype">Service/Domain</label>
            <select name="servicedomain" id="servicedomain" class="form-control" data-live-search="true">
                <optgroup label="Services">
                    {foreach $services as $service}
                        <option value="s_{$service->id}" {if $type and $type eq 'service' and $id}selected{/if}>{$service->name} - {$service->domain}</option>
                    {/foreach}
                </optgroup>
                <optgroup label="Domains">
                    {foreach $domains as $domain}
                        <option value="d_{$domain->id}" {if $type and $type eq 'domain' and $id}selected{/if}>{$domain->domain}</option>
                    {/foreach}
                </optgroup>
            </select>
        </div>
        <div class="form-group">
            <label for="recipientemail">Gaining Client's Email Address</label>
            <input type="text" name="recipientemail" id="recipientemail" placeholder="e.g. johndoe@domain.com" class="form-control">
        </div>
        <button type="submit" name="init_submit" class="btn btn-primary">Initiate Transfer</button>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
    <script type="text/javascript">
        $(function() {
	        $('#servicedomain').selectpicker();
        });
    </script>