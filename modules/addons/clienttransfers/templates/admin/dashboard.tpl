{extends file='admin/layouts/app.tpl'}

{block "content"}

    <div class="row stat-labels margin-top-xl">
        <div class="col-md-2">
            <a href="{$moduleLink}&page={PageHelper::getPageInfo('transfers', 'slug')}&type=completed">
                <div class="panel panel-success">
                    <div class="panel-body text-center">
                        <h1>{$transfers.completedTransfers->count()}</h1>
                        Completed Transfers
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-2">
            <a href="{$moduleLink}&page={PageHelper::getPageInfo('transfers', 'slug')}&type=pending">
                <div class="panel panel-warning">
                    <div class="panel-body text-center">
                        <h1>{$transfers.pendingTransfers->count()}</h1>
                        Pending Transfers
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-2">
            <a href="{$moduleLink}&page={PageHelper::getPageInfo('transfers', 'slug')}&type=denied">
                <div class="panel panel-danger">
                    <div class="panel-body text-center">
                        <h1>{$transfers.deniedTransfers->count()}</h1>
                        Denied Transfers
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-2">
            <a href="{$moduleLink}&page={PageHelper::getPageInfo('transfers', 'slug')}&type=cancelled">
                <div class="panel panel-info">
                    <div class="panel-body text-center">
                        <h1>{$transfers.cancelledTransfers->count()}</h1>
                        Cancelled Transfers
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-2">
            <a href="{$moduleLink}&page={PageHelper::getPageInfo('transfers', 'slug')}&type=completed&filter=service">
                <div class="panel panel-default">
                    <div class="panel-body text-center">
                        <h1>{$transfers.servicesTransferred->count()}</h1>
                        Services Transferred
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-2">
            <a href="{$moduleLink}&page={PageHelper::getPageInfo('transfers', 'slug')}&type=completed&filter=domain">
                <div class="panel panel-default">
                    <div class="panel-body text-center">
                        <h1>{$transfers.domainsTransferred->count()}</h1>
                        Domains Transferred
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row margin-top-xl">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Latest Completed Transfers</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Service/Domain</th>
                                    <th scope="col">Requestor</th>
                                    <th scope="col">Recipient</th>
                                    <th scope="col">Requested Date/Time</th>
                                    <th scope="col">Completed Date/Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                {if $transfers.completedTransfers->count() eq 0}
                                    <tr>
                                        <td colspan="7"><p class="text-center" style="padding-top: 5px;">No records found</p></td>
                                    </tr>
                                {else}
                                    {foreach $transfers.completedTransfers->limit(5)->get() as $transfer}
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
                                            <td>{$transfer->completedAt()}</td>
                                        </tr>
                                    {/foreach}
                                {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel-footer text-right"><a href="{$moduleLink}&page={PageHelper::getPageInfo('transfers', 'slug')}&type=completed">View All <i class="fas fa-caret-right"></i></a></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Latest Pending Transfers</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Service/Domain</th>
                                    <th scope="col">Requestor</th>
                                    <th scope="col">Recipient</th>
                                    <th scope="col">Requested Date/Time</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {if $transfers.pendingTransfers->count() eq 0}
                                    <tr>
                                        <td colspan="7"><p class="text-center" style="padding-top: 5px;">No records found</p></td>
                                    </tr>
                                {else}
                                    {foreach $transfers.pendingTransfers->limit(5)->get() as $transfer}
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
                                            <td>{$transfer->pendingAdminLinks()}</td>
                                        </tr>
                                    {/foreach}
                                {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel-footer text-right"><a href="{$moduleLink}&page={PageHelper::getPageInfo('transfers', 'slug')}&type=pending">View All <i class="fas fa-caret-right"></i></a></div>
            </div>
        </div>
    </div>
    <div class="row margin-top-xl">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Latest Denied Transfers</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Service/Domain</th>
                                    <th scope="col">Requestor</th>
                                    <th scope="col">Recipient</th>
                                    <th scope="col">Requested Date/Time</th>
                                    <th scope="col">Denied Date/Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                {if $transfers.deniedTransfers->count() eq 0}
                                    <tr>
                                        <td colspan="7"><p class="text-center" style="padding-top: 5px;">No records found</p></td>
                                    </tr>
                                {else}
                                    {foreach $transfers.deniedTransfers->limit(5)->get() as $transfer}
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
                                            <td>{$transfer->completedAt()}</td>
                                        </tr>
                                    {/foreach}
                                {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel-footer text-right"><a href="{$moduleLink}&page={PageHelper::getPageInfo('transfers', 'slug')}&type=denied">View All <i class="fas fa-caret-right"></i></a></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Latest Cancelled Transfers</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Service/Domain</th>
                                    <th scope="col">Requestor</th>
                                    <th scope="col">Recipient</th>
                                    <th scope="col">Requested Date/Time</th>
                                    <th scope="col">Cancelled Date/Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                {if $transfers.cancelledTransfers->count() eq 0}
                                    <tr>
                                        <td colspan="7"><p class="text-center" style="padding-top: 5px;">No records found</p></td>
                                    </tr>
                                {else}
                                    {foreach $transfers.cancelledTransfers->limit(5)->get() as $transfer}
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
                                            <td>{$transfer->completedAt()}</td>
                                        </tr>
                                    {/foreach}
                                {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="panel-footer text-right"><a href="{$moduleLink}&page={PageHelper::getPageInfo('transfers', 'slug')}&type=cancelled">View All <i class="fas fa-caret-right"></i></a></div>
            </div>
        </div>
    </div>
{/block}