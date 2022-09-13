<div id="{{ $uqid = empty($uniqid) ? uniqid() : $uniqid }}" class="widget-options" data-run="[run_name]">

    @include('pages.interface.settings.above')

    <div class="modal fade" id="{{ "modal-$uqid" }}" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-lg"></div>
        <!-- /.modal-dialog -->
    </div>
</div>
