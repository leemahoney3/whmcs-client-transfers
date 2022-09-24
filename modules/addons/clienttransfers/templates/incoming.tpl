{include file='./partials/messages.tpl'}

<br />

<div class="card">
    <div class="card-body">
        <div class="card-title">
            Incoming Transfer Requests ({$incomingRequests.count})
        </div>
        <p>View and decide on your incoming transfers below.</p>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Service/Domain</th>
                        <th scope="col">Requested By</th>
                        <th scope="col">Requested Date/Time</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {if $incomingRequests.count == 0}
                        <tr>
                            <td colspan="6"><p class="text-center">No records found</p></td>
                        </tr>
                    {else}
                        {foreach $incomingRequests.data as $request}
                            <tr>
                                <td class="align-text-bottom">{$request->id}</td>
                                <td class="align-middle">{if $request->type eq 'domain'}<b>Domain</b> - {$request->domain->domain}{elseif $request->type eq 'service'}<b>Service</b> - {$request->service->product->name} ({$request->service->domain}){/if}</td>
                                <td class="align-text-bottom">{$request->losing_client_email}</td>
                                <td class="align-text-bottom">{$request->requestedAt()}</td>
                                <td class="align-text-bottom"><a href="index.php?m=clienttransfers&action=accept&id={$request->id}" class="btn btn-success">Accept</a> <a href="index.php?m=clienttransfers&action=deny&id={$request->id}" class="btn btn-danger">Deny</a></td>
                            </tr>
                        {/foreach}
                    {/if}
                    </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{$incomingRequests.links}}
    </div>
</div>

<br />

<div class="card">
    <div class="card-body">
        <div class="card-title">
            Request History
        </div>
        <p>Shows past requests, does not include requests that have been cancelled.</p>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Service/Domain</th>
                        <th scope="col">Requested By</th>
                        <th scope="col">Result</th>
                        <th scope="col">Requested Date/Time</th>
                        <th scope="col">Completion Date/Time</th>
                    </tr>
                </thead>
                <tbody>
                    {if $previousRequests.count == 0}
                        <tr>
                            <td colspan="6"><p class="text-center">No records found</p></td>
                        </tr>
                    {else}
                        {foreach $previousRequests.data as $request}
                            <tr>
                                <td class="align-text-bottom">{$request->id}</td>
                                <td class="align-middle">{if $request->type eq 'domain'}<b>Domain</b> - {$request->domain->domain}{elseif $request->type eq 'service'}<b>Service</b> - {$request->service->product->name} ({$request->service->domain}){/if}</td>
                                <td class="align-text-bottom">{$request->losing_client_email}</td>
                                <td class="align-text-bottom">{$request->getStatus()}</td>
                                <td class="align-text-bottom">{$request->requestedAt()}</td>
                                <td class="align-text-bottom">{$request->completedAt()}</td>
                            </tr>
                        {/foreach}
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{$previousRequests.links}}
    </div>  
</div>