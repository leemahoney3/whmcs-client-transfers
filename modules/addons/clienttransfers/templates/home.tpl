{include file='./partials/messages.tpl'}

<div class="float-right">
    {if $allowClientInitiateTransfers}
        <a href="index.php?m=clienttransfers&action=init" class="btn btn-success">Start new Transfer</a>
    {/if}
    <a href="index.php?m=clienttransfers&action=incoming" class="btn btn-primary">Incoming Transfer Requests ({$incomingRequestsCount})</a>
</div>
    
<div class="clearfix"></div>

<br />

{if $showPendingTransfers}
    <div class="card">
        <div class="card-body">
            <div class="card-title">
                <h3>Pending Transfers</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Service/Domain</th>
                            <th scope="col">Target Account</th>
                            <th scope="col">Requested Date/Time</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {if !$pendingTransfers}
                        <tr>
                            <td colspan="6"><p class="text-center">No records found</p></td>
                        </tr>
                        {else}
                            {foreach $pendingTransfers as $transfer}
                                <tr>
                                    <td class="align-middle">{$transfer->id}</td>
                                    <td class="align-middle">{if $transfer->type eq 'domain'}<b>Domain</b> - {$transfer->domain->domain}{elseif $transfer->type eq 'service'}<b>Service</b> - {$transfer->service->product->name} ({$transfer->service->domain}){/if}</td>
                                    <td class="align-middle">{$transfer->gaining_client_email}</td>
                                    <td class="align-middle">{$transfer->requestedAt()}</td>
                                    <td class="align-middle"><a href="index.php?m=clienttransfers&action=cancel&id={$transfer->id}" class="btn btn-danger">Cancel</a></td>
                                </tr>
                            {/foreach}
                        {/if}
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{$pendingTransfersLinks}}
        </div>
    </div>        
{/if}

<br />

{if $showPreviousTransfers}
    <div class="card">
        <div class="card-body">
            <div class="card-title">
                <h3>Previous Transfers</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Service/Domain</th>
                            <th scope="col">Target Account</th>
                            <th scope="col">Result</th>
                            <th scope="col">Requested Date/Time</th>
                            <th scope="col">Completion Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        {if !$previousTransfers}
                        <tr>
                            <td colspan="6"><p class="text-center">No records found</p></td>
                        </tr>
                        {else}
                            {foreach $previousTransfers as $transfer}
                                <tr>
                                    <td class="align-middle">{$transfer->id}</td>
                                    <td class="align-middle">{if $transfer->type eq 'domain'}<b>Domain</b> - {$transfer->domain->domain}{elseif $transfer->type eq 'service'}<b>Service</b> - {$transfer->service->product->name} ({$transfer->service->domain}){/if}</td>
                                    <td class="align-middle">{$transfer->gaining_client_email}</td>
                                    <td class="align-middle">{$transfer->getStatus()}</td>
                                    <td class="align-middle">{$transfer->requestedAt()}</td>
                                    <td class="align-middle">{$transfer->completedAt()}</td>
                                </tr>
                            {/foreach}
                        {/if}
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{$previousTransfersLinks}}
        </div>
    </div>
{/if}