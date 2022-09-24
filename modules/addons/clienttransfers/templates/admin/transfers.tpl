{extends file='admin/layouts/app.tpl'}

{block "content"}
    
    <div class="row">
        <div class="col-md-12">
            <h2>{$transferData.type|ucfirst} Transfers</h2>
        </div>
    </div>
    <div class="row margin-top-xl">
        <div class="col-md-6">
            <form class="form-inline">
                <div class="form-group">
                    <label for="filter">Filter By</label>
                    <select name="filter" class="form-control" id="filter">
                        <option value="all"{($transferData.filter == ' all') ? 'selected' : ''}>All Transfer Types</option>
                        <option value="service"{($transferData.filter == 'service') ? ' selected' : ''}>Services Only</option>
                        <option value="domain"{($transferData.filter == 'domain') ? ' selected' : ''}>Domains Only</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="col-md-6 text-right">
            <form class="form-inline">
                <div class="form-group">
                    <label for="searchString">Search Transfers</label>
                    
                    <!-- Losing Client -->
                    <select name="lclient" id="lclient" class="form-control" data-live-search="true">
                        <option value="0">Losing Client</option>
                        {foreach $transferData.losingClients as $client}
                            <option value="{$client->id}" {($transferData.losingClient == $client->id) ? 'selected' : ''}>{$client->firstname} {$client->lastname}</option>
                        {/foreach}
                    </select>

                    <!-- Receiving Client -->
                    <select name="rclient" id="rclient" class="form-control" data-live-search="true">
                        <option value="0">Receiving Client</option>
                        {foreach $transferData.receivingClients as $client}
                            <option value="{$client->id}" {($transferData.recievingClient == $client->id) ? 'selected' : ''}>{$client->firstname} {$client->lastname}</option>
                        {/foreach}
                    </select>
                    <!-- Domain -->
                </div>
                <button type="submit" class="btn btn-default" id="searchBtn">Go</button>
            </form>
        </div>
    </div>

    <div class="row margin-top-xl">
        <div class="col-md-12">
    
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Service/Domain</th>
                            <th scope="col">Requestor</th>
                            <th scope="col">Recipient</th>
                            <th scope="col">Requested Date/Time</th>
                            {if $transferData.type eq 'completed'}<th scope="col">Completed Date/Time</th>{/if}
                            {if $transferData.type eq 'pending'}<th scope="col">Actions</th>{/if}
                            {if $transferData.type eq 'denied'}<th scope="col">Denied Date/Time</th>{/if}
                            {if $transferData.type eq 'cancelled'}<th scope="col">Cancelled Date/Time</th>{/if}
                        </tr>
                    </thead>
                    <tbody>
                        {if count($transferData.transfers) eq 0}
                            <tr>
                                <td colspan="7"><p class="text-center" style="padding-top: 5px;">No records found</p></td>
                            </tr>
                        {else}
                            {foreach $transferData.transfers as $transfer}
                                    <tr>
                                        <td>{$transfer->id}</td>
                                        <td>
                                        {if $transfer->type eq 'service'}
                                            <a href="clientsservices.php?userid={$transfer->service->userid}&id={$transfer->service->id}">{$transfer->service->product->name} - {$transfer->service->domain}</a>
                                        {else}
                                            <a href="clientsdomains.php?userid={$transfer->domain->userid}&id={$transfer->domain->id}">Domain - {$transfer->domain->domain}</a>
                                        {/if}
                                        </td>
                                        <td><a href="clientssummary.php?userid={$transfer->losingClient->id}">{$transfer->losingClient->firstname} {$transfer->losingClient->lastname} <br /><small>({$transfer->losingClient->email})</a></td>
                                        <td><a href="clientssummary.php?userid={$transfer->gainingClient->id}">{$transfer->gainingClient->firstname} {$transfer->gainingClient->lastname} <br /><small>({$transfer->gainingClient->email})</a></td>
                                        <td>{$transfer->requestedAt()}</td>
                                        {if $transferData.type eq 'completed' or $transferData.type eq 'denied' or $transferData.type eq 'cancelled'}<td>{$transfer->completedAt()}</td>{/if}
                                        {if $transferData.type eq 'pending'}<td>{$transfer->pendingAdminLinks()}</td>{/if}
                                    </tr>
                            {/foreach}
                        {/if}
                </table>
            </div>

        </div>
    </div>

    {$transferData.transfersLinks}

{/block}

{block "scripts"}
    <script src="{$systemURL}/modules/addons/clienttransfers/assets/js/bootstrap-select.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {

            $('#lclient').selectpicker();
            $('#rclient').selectpicker();
            $('#filter').selectpicker();

            $('#filter').on('change', function (e) {

                let url = window.location.href;
                url = removeParam('filter', url);
                
                window.location.href = url + '&filter=' + $(this).val();

            });
            
            $('#searchBtn').on('click', function (e) {

                e.preventDefault();

                let newUrl = '';
                let lclient = $('#lclient').val();
                let rclient = $('#rclient').val();

                if (lclient != 0) {
                    newUrl += '&lclient=' + lclient;
                }

                if (rclient != 0) {
                    newUrl += '&rclient=' + rclient;
                }

                let url = window.location.href; 
                url = removeParam('lclient', url);
                url = removeParam('rclient', url);

                window.location.href = url + newUrl;

            });

        });

        // Credit: https://stackoverflow.com/questions/16941104/remove-a-parameter-to-the-url-with-javascript
        function removeParam(key, sourceURL) {
    
            var rtn = sourceURL.split("?")[0],
                param,
                params_arr = [],
                queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
            
            if (queryString !== "") {
                
                params_arr = queryString.split("&");
                
                for (var i = params_arr.length - 1; i >= 0; i -= 1) {
                    param = params_arr[i].split("=")[0];
                    
                    if (param === key) {
                        params_arr.splice(i, 1);
                    }
                }
                
                if (params_arr.length) rtn = rtn + "?" + params_arr.join("&");
            }
            
            return rtn;
        }

    </script>
{/block}