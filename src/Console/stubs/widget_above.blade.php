    <form class="setting-form mb-2">
        <div class="form-group row">
            <label class="col-2">Style:</label>
            <select name="style" class="form-control col-10">
                @if (!empty($item->path_dir))
                    {{-- filesStyle --}}
                    @foreach ($filesStyle as $file)
                        @php($fileName = basename($file, '.blade.php'))
                        <option value="{{ $fileName }}" {{ $item->style == $fileName ? 'selected' : '' }}>
                            {{ $fileName }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input id="{{ "label-$uqid" }}" name="global" type="checkbox" class="custom-control-input"
                            {{ !empty($item->global) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="{{ "label-$uqid" }}">Use global widget</label>
                    </div>
                </div>
            </div>

            <div class="col-6">
                {{-- <button class="btn btn-info" type="submit">
                        Save To Publish
                    </button> --}}
                <button type="button" class="btn btn-default open-modal" data-toggle="modal" data-target="#{{ "modal-$uqid" }}">
                    Content Editor
                </button>
            </div>
        </div>
    </form>
