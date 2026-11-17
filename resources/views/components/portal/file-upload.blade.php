@props(['id' => 'ticket-attachments', 'demo' => null])
<div class="file-upload" data-file-upload>
    <label class="create-field-label" for="{{ $id }}">Unggah Lampiran / Screenshot Error</label>
    <div class="upload-dropzone" data-upload-dropzone>
        <input type="file" id="{{ $id }}" name="attachments[]" accept=".png,.jpg,.jpeg,.pdf,.log,image/png,image/jpeg,application/pdf,text/plain" multiple class="sr-only" aria-describedby="{{ $id }}-help {{ $id }}-errors" data-upload-input>
        <label for="{{ $id }}"><span class="upload-cloud"><x-portal.icon name="cloud-upload" :size="22" /></span><span class="upload-instruction">Tarik & Lepas tangkapan layar atau <span>Pilih Berkas</span></span><span id="{{ $id }}-help" class="upload-help">PNG, JPG, PDF, atau LOG · Maks. 5 MB/file, 5 file.</span></label>
    </div>
    <ul id="{{ $id }}-errors" class="upload-errors" data-upload-errors role="alert" hidden></ul>
    <div class="upload-file-list" data-upload-list aria-label="Lampiran terpilih">
        @if($demo)
            <div class="upload-file-row"><span class="upload-file-icon"><x-portal.icon name="image" :size="19" /></span><button type="button" class="upload-file-info" data-demo-preview><span>{{ $demo['name'] }}</span><small>1.2 MB · Contoh lampiran, belum diunggah</small></button><button type="button" class="upload-remove" data-demo-remove aria-label="Hapus contoh lampiran"><x-portal.icon name="x" :size="14" /></button></div>
        @endif
    </div>
    <template data-upload-template><div class="upload-file-row"><span class="upload-file-icon"><x-portal.icon name="file" :size="19" /><img data-file-thumbnail alt="" hidden></span><button type="button" class="upload-file-info" data-file-preview><span data-file-name></span><small data-file-meta></small></button><button type="button" class="upload-remove" data-file-remove><x-portal.icon name="x" :size="14" /></button></div></template>
    <span class="sr-only" data-upload-status role="status" aria-live="polite"></span>
</div>
