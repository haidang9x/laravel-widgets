<div id="{{ $uqid = empty($uniqid)?uniqid():$uniqid }}" class="widget-options">
    
   @include('pages.interface.settings.above')
   
    <form class="content-form">
        <div class="modal fade" id="{{ "modal-$uqid" }}" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ $item->title??ucwords(str_replace('_', ' ', $item->name)) }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>One fine body…</p>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save To Publish</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
    </form>
</div>
@section('widgets_js')
@parent
<script data-type="{{ !empty($draggabled)?'javascript':'babel' }}">
    (function () {
        var uqid = '{{ $uqid }}';
        var widgetJson = {!! json_encode($item) !!};
        function settingSave(that) {
            saveSettingOptions(widgetJson, that, '[run_name]');
            return false;
        }
        $('#' + uqid).find('.setting-form :input').on('change', function () {
            var that = $(this).parents('form').eq(0);
            settingSave(that);
        });
        $('#' + uqid).find('.setting-form').on('submit', function(){
            settingSave(this);
            return false;
        });
        $('#' + uqid).find('.content-form').on('submit', function () {
            setItemPathIndex(widgetJson, $(this));
            var enableGlobal = $('#'+uqid).find('input[name="global"]').is(':checked')?1:0;
            settings_queue['content' + uqid] = {
                type: 'content',
                config: widgetJson,
                post_data: $(this).serialize(),
                run_name: '[run_name]',
                global: enableGlobal
            };
            $('#{{ "modal-$uqid" }}').modal('hide');
            return false;
        });
    })();
</script>
@stop
@if(!empty($draggabled))
    @yield('widgets_js')
@endif
