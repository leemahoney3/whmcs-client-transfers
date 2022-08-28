{extends file='admin/layouts/app.tpl'}

{block "content"}
<div class="ct-container">
    <div style="display: flex; justify-content: space-between;">
        <h2 class="ct-heading">{$currentPageName}</h2>
        <a href="" class="btn btn-purple">New Transfer</a>
    </div>
    

    <div class="ct-statboxes">
        <div class="ct-statbox">
            <div class="ct-num">{count($transfers.pendingTransfers)}</div>
            <div class="ct-desc">Transfers Pending</div>
        </div>
        <div class="ct-statbox">
            <div class="ct-num">{count($transfers.completedTransfers)}</div>
            <div class="ct-desc">Transfers Completed</div>
        </div>
        <div class="ct-statbox">
            <div class="ct-num">{count($transfers.deniedTransfers)}</div>
            <div class="ct-desc">Transfers Denied</div>
        </div>
        <div class="ct-statbox">
            <div class="ct-num">{count($transfers.cancelledTransfers)}</div>
            <div class="ct-desc">Transfers Cancelled</div>
        </div>
        <div class="ct-statbox">
            <div class="ct-num">{count($transfers.servicesTransferred)}</div>
            <div class="ct-desc">Services Transferred</div>
        </div>
        <div class="ct-statbox">
            <div class="ct-num">{count($transfers.domainsTransferred)}</div>
            <div class="ct-desc">Domains Transferred</div>
        </div>
    </div>

    <br /><br />
    <h2 class="ct-heading">View Transfers</h2>
    <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#transfers-pending" aria-controls="home" role="tab" data-toggle="tab">Pending</a></li>
    <li role="presentation"><a href="#transfers-completed" aria-controls="profile" role="tab" data-toggle="tab">Completed</a></li>
    <li role="presentation"><a href="#transfers-denied" aria-controls="messages" role="tab" data-toggle="tab">Denied</a></li>
    <li role="presentation"><a href="#transfers-cancelled" aria-controls="settings" role="tab" data-toggle="tab">Cancelled</a></li>
  </ul>

  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="transfers-pending">
        <table class="table table-striped" id="pendingTransfersTable">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Type</th>
                    <th scope="col">Service/Domain</th>
                    <th scope="col">Requestor</th>
                    <th scope="col">Recipient</th>
                    <th scope="col">Requested Date/Time</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                {if count($transfers.pendingTransfers) eq 0}
                    <tr>
                        <td colspan="6"><p class="text-center" style="padding-top: 5px;">No pending transfers.</p></td>
                    </tr>
                {else}
                    {foreach $transfers.pendingTransfers as $pendingTransfer}
                        <tr>
                            <td>{$pendingTransfer.id}</td>
                            <td>{$pendingTransfer.type}</td>
                            <td>{$pendingTransfer.service_domain}</td>
                            <td>{$pendingTransfer.losing_client->firstname} {$pendingTransfer.losing_client->lastname} <br /><small>({$pendingTransfer.losing_client->email})</td>
                            <td>{$pendingTransfer.gaining_client->firstname} {$pendingTransfer.gaining_client->lastname} <br /><small>({$pendingTransfer.gaining_client->email})</td>
                            <td>{$pendingTransfer.requested_at}</td>
                            <td><a href="" class="btn btn-success">Accept</a> <a href="" class="btn btn-danger">Deny</a> <a href="" class="btn btn-default">Cancel</a></td>
                        </tr>
                    {/foreach}
                {/if}
            </tbody>
        </table>
    </div>
    <div role="tabpanel" class="tab-pane" id="transfers-completed">
        <table class="table table-striped" id="completedTransfersTable">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Type</th>
                    <th scope="col">Service/Domain</th>
                    <th scope="col">Requestor</th>
                    <th scope="col">Recipient</th>
                    <th scope="col">Requested Date/Time</th>
                    <th scope="col">Completed Date/Time</th>
                </tr>
            </thead>
            <tbody>
                {if count($transfers.completedTransfers) eq 0}
                    <tr>
                        <td colspan="7"><p class="text-center" style="padding-top: 5px;">No completed transfers.</p></td>
                    </tr>
                {else}
                    {foreach $transfers.completedTransfers as $completedTransfer}
                        <tr>
                            <td>{$completedTransfer.id}</td>
                            <td>{$completedTransfer.type}</td>
                            <td>{$completedTransfer.service_domain}</td>
                            <td>{$completedTransfer.losing_client->firstname} {$completedTransfer.losing_client->lastname} <br /><small>({$completedTransfer.losing_client->email})</td>
                            <td>{$completedTransfer.gaining_client->firstname} {$completedTransfer.gaining_client->lastname} <br /><small>({$completedTransfer.gaining_client->email})</td>
                            <td>{$completedTransfer.requested_at}</td>
                            <td>{$completedTransfer.completed_at}</td>
                        </tr>
                    {/foreach}
                {/if}
            </tbody>
        </table>
    </div>
    <div role="tabpanel" class="tab-pane" id="transfers-denied">
        <table class="table table-striped" id="deniedTransfersTable">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Type</th>
                    <th scope="col">Service/Domain</th>
                    <th scope="col">Requestor</th>
                    <th scope="col">Recipient</th>
                    <th scope="col">Requested Date/Time</th>
                    <th scope="col">Denied Date/Time</th>
                </tr>
            </thead>
            <tbody>
                {if count($transfers.deniedTransfers) eq 0}
                    <tr>
                        <td colspan="7"><p class="text-center" style="padding-top: 5px;">No denied transfers.</p></td>
                    </tr>
                {else}
                    {foreach $transfers.deniedTransfers as $deniedTransfer}
                        <tr>
                            <td>{$deniedTransfer.id}</td>
                            <td>{$deniedTransfer.type}</td>
                            <td>{$deniedTransfer.service_domain}</td>
                            <td>{$deniedTransfer.losing_client->firstname} {$deniedTransfer.losing_client->lastname} <br /><small>({$deniedTransfer.losing_client->email})</td>
                            <td>{$deniedTransfer.gaining_client->firstname} {$deniedTransfer.gaining_client->lastname} <br /><small>({$deniedTransfer.gaining_client->email})</td>
                            <td>{$deniedTransfer.requested_at}</td>
                            <td>{$deniedTransfer.completed_at}</td>
                        </tr>
                    {/foreach}
                {/if}
            </tbody>
        </table>
    </div>
    <div role="tabpanel" class="tab-pane" id="transfers-cancelled">
        <table class="table table-striped" id="cancelledTransfersTable">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Type</th>
                    <th scope="col">Service/Domain</th>
                    <th scope="col">Requestor</th>
                    <th scope="col">Recipient</th>
                    <th scope="col">Requested Date/Time</th>
                    <th scope="col">Cancelled Date/Time</th>
                </tr>
            </thead>
            <tbody>
                {if count($transfers.cancelledTransfers) eq 0}
                    <tr>
                        <td colspan="7"><p class="text-center" style="padding-top: 5px;">No cancelled transfers.</p></td>
                    </tr>
                {else}
                    {foreach $transfers.cancelledTransfers as $cancelledTransfer}
                        <tr>
                            <td>{$cancelledTransfer.id}</td>
                            <td>{$cancelledTransfer.type}</td>
                            <td>{$cancelledTransfer.service_domain}</td>
                            <td>{$cancelledTransfer.losing_client->firstname} {$cancelledTransfer.losing_client->lastname} <br /><small>({$cancelledTransfer.losing_client->email})</td>
                            <td>{$cancelledTransfer.gaining_client->firstname} {$cancelledTransfer.gaining_client->lastname} <br /><small>({$cancelledTransfer.gaining_client->email})</td>
                            <td>{$cancelledTransfer.requested_at}</td>
                            <td>{$cancelledTransfer.completed_at}</td>
                        </tr>
                    {/foreach}
                {/if}
            </tbody>
        </table>
    </div>
  </div>
</div>
{/block}

{block "scripts"}
    <script src="{$systemURL}/modules/addons/clienttransfers/assets/js/datatables.min.js"></script>
    <script src="{$systemURL}/modules/addons/clienttransfers/assets/js/datatables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {

            {if count($transfers.pendingTransfers) != 0}$('#pendingTransfersTable').DataTable();{/if}
            {if count($transfers.completedTransfers) != 0}$('#completedTransfersTable').DataTable();{/if}
            {if count($transfers.deniedTransfers) != 0}$('#deniedTransfersTable').DataTable();{/if}
            {if count($transfers.cancelledTransfers) != 0}$('#cancelledTransfersTable').DataTable();{/if}

        });
    </script>
{/block}