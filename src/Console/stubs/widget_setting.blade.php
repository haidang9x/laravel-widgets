<div id="{{ $uqid = uniqid() }}">
    <form class="setting-form mb-2">
        <div class="form-group">
            <div class="custom-control custom-switch">
                <input id="{{ "label-$uqid" }}" name="global" type="checkbox"
                       class="custom-control-input" {{ !empty($item->global)?'checked':'' }}>
                <label class="custom-control-label" for="{{ "label-$uqid" }}">Use global widget</label>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-2">Style:</label>
            <select name="style" class="form-control col-10">
                @if(!empty($item->path_dir))
                    @php($filesStyle = \File::glob(base_path($item->path_dir . '/*.blade.php')))
                    @foreach($filesStyle as $file)
                        @php($fileName = basename($file, '.blade.php'))
                        <option
                                value="{{ $fileName }}"{{ $item->style==$fileName?'selected':''}}>{{ $fileName }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <button class="btn btn-info" type="submit">
            Save To Publish
        </button>
        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#{{ "modal-$uqid" }}">
            Content Editor
        </button>
    </form>
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
<script type="text/babel">
    (function () {
        var uqid = '{{ $uqid }}';
        var widgetJson = {!! json_encode($item) !!};
        function settingSave(that) {
            setItemPathIndex(widgetJson, $(that));
            settings_queue['set' + uqid] = {
                type: 'setting',
                config: widgetJson,
                post_data: $(that).serialize(),
                run_name: 'FaqsFromBing'
            };
            var btnSubmit = $(that).find('button[type="submit"]');
            if(btnSubmit.text() != 'Saved!') {
                setTimeout(function (t) {
                    btnSubmit.text(t);
                }, 2000, btnSubmit.text());
                btnSubmit.text('Saved!');
            }
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
