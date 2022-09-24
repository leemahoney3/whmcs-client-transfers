<link rel="stylesheet" href="{$systemURL}/modules/addons/clienttransfers/assets/css/app.min.css">

<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{$moduleLink}&page={PageHelper::getPageInfo('dashboard', 'slug')}">Client Transfers</a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            

            <ul class="nav navbar-nav">

                {foreach $allNavPages as $page}

                    <li {if $currentPage eq $page.slug || $page.dropdown}class="{if $currentPage eq $page.slug}active{/if} {if $page.dropdown}dropdown{/if}"{/if}>
                        
                        <a href="{$moduleLink}&page={$page.slug}" {if $page.dropdown}data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"{/if}>
                            {{$page.icon}} {{$page.name}}
                            {if $page.dropdown}<span class="caret"></span>{/if}
                        </a>
                        
                        {if $page.dropdown}
                            <ul class="dropdown-menu">
                                {foreach $page.links as $link => $slug}
                                    <li><li><a href="{$moduleLink}&page={$page.slug}{$slug}">{$link}</a></li></li>
                                {/foreach}
                            </ul>
                        {/if}

                    </li>
                
                
                {/foreach}

            </ul>

            <ul class="nav navbar-nav navbar-right">
                <li><a href="https://leemahoney.tech/" class="lmtech-logo">LMTech</a></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div>
</nav>

{block name="content"}{/block}

{block name="scripts"}{/block}
<script type="text/javascript">

    $(document).ready(function () {

        $('#contentarea').find('h1').first().remove();

    });
</script>