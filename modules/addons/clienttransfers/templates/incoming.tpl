{include file='./partials/messages.tpl'}

<h3>Incoming Transfer Requests ({$incomingRequestsCount})</h3>
<p>View and decide on your incoming transfers below.</p>

<br />

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
        {if !$incomingRequests}
            <tr>
                <td colspan="6"><p class="text-center">No incoming transfer requests.</p></td>
            </tr>
        {else}
            {foreach $incomingRequests as $request}
                <tr>
                    <td class="align-text-bottom">{$request.id}</td>
                    <td class="align-text-bottom">{if $request.type eq 'domain'}<b>Domain</b> - {$request.domain}{elseif $request.type eq 'service'}<b>Service</b> - {$request.service}{/if}</td>
                    <td class="align-text-bottom">{$request.requested_by}</td>
                    <td class="align-text-bottom">{$request.requested_date}</td>
                    <td class="align-text-bottom"><a href="index.php?m=clienttransfers&action=accept&id={$request.id}" class="btn btn-success">Accept</a> <a href="index.php?m=clienttransfers&action=deny&id={$request.id}" class="btn btn-danger">Deny</a></td>
                </tr>
            {/foreach}
        {/if}
        </tbody>
</table>

<br />

<h3>Request History</h3>
<p>Shows past requests, does not include requests that have been cancelled.</p>
<br />
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
        {if !$previousRequests}
            <tr>
                <td colspan="6"><p class="text-center">No previous transfer requests.</p></td>
            </tr>
        {else}
            {foreach $previousRequests as $request}
                <tr>
                    <td class="align-text-bottom">{$request.id}</td>
                    <td class="align-text-bottom">{if $request.type eq 'domain'}<b>Domain</b> - {$request.domain}{elseif $request.type eq 'service'}<b>Service</b> - {$request.service}{/if}</td>
                    <td class="align-text-bottom">{$request.requested_by}</td>
                    <td class="align-text-bottom">{$request.result}</td>
                    <td class="align-text-bottom">{$request.requested_date}</td>
                    <td class="align-text-bottom">{$request.completed_date}</td>
                </tr>
            {/foreach}
        {/if}
    </tbody>
</table>