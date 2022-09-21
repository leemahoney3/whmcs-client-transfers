{if $currentPage != 'home'}
    <a href="index.php?m=clienttransfers" class="btn btn-default float-right">Dashboard</a>
    <div class="clearfix"></div>
    <br />
{/if}


{if $message and $message.type eq 'error'}
    <div class="alert alert-danger alert-dismissible show" role="alert">
        {$message.content}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
{/if}

{if $message and $message.type eq 'success'}
    <div class="alert alert-success alert-dismissible show" role="alert">
        {$message.content}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
{/if}

<!-- Good job, WHMCS. -->
<style>
    .label {
        border-radius: 0.25em !important;
    }

    .bs-placeholder {
        border: 1px solid #ccc;
    }
</style>