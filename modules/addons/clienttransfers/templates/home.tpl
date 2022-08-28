{include file='./partials/messages.tpl'}

<div class="pull-right">
    {if $allowClientInitiateTransfers}
        <a href="index.php?m=clienttransfers&action=init" class="btn btn-success">Start new Transfer</a>
    {/if}
    <a href="index.php?m=clienttransfers&action=incoming" class="btn btn-primary">Incoming Transfer Requests ({$incomingRequestsCount})</a>
    </div>
    
    <br />

    {if $showPendingTransfers}
        <h3><b>Pending Transfers</b></h3>
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
                    <td colspan="6"><p class="text-center">No pending transfers</p></td>
                </tr>
                {else}
                    {foreach $pendingTransfers as $transfer}
                        <tr>
                            <td class="align-middle">{$transfer.id}</td>
                            <td class="align-middle">{if $transfer.type eq 'domain'}<b>Domain</b> - {$transfer.domain}{elseif $transfer.type eq 'service'}<b>Service</b> - {$transfer.service}{/if}</td>
                            <td class="align-middle">{$transfer.target_account}</td>
                            <td class="align-middle">{$transfer.requested}</td>
                            <td class="align-middle"><a href="index.php?m=clienttransfers&action=cancel&id={$transfer.id}" class="btn btn-danger">Cancel</a></td>
                        </tr>
                    {/foreach}
                {/if}
            </tbody>
        </table>
    {/if}
    
    <br />
    
    {if $showPreviousTransfers}
        <h3><b>Previous Transfers</b></h3>
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
                    <td colspan="6"><p class="text-center">No previous transfers</p></td>
                </tr>
                {else}
                    {foreach $previousTransfers as $transfer}
                        <tr>
                            <td class="align-middle">{$transfer.id}</td>
                            <td class="align-middle">{if $transfer.type eq 'domain'}<b>Domain</b> - {$transfer.domain}{elseif $transfer.type eq 'service'}<b>Service</b> - {$transfer.service}{/if}</td>
                            <td class="align-middle">{$transfer.target_account}</td>
                            <td class="align-middle">{$transfer.status}</td>
                            <td class="align-middle">{$transfer.requested}</td>
                            <td class="align-middle">{$transfer.completed}</td>
                        </tr>
                    {/foreach}
                {/if}
            </tbody>
        </table>
    {/if}