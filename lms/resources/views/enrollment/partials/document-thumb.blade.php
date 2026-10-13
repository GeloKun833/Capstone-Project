@include('enrollment.partials.file-card-assets')
@php
    $canPreview = $document
        && ($document->submission_method ?? null) !== 'in_person'
        && $document->file_path;
    $previewUrl = $canPreview ? route('enrollment.portal.preview-document', $document->id) : null;
    $ext = strtoupper(pathinfo((string) ($document->file_name ?? ''), PATHINFO_EXTENSION));
    if ($ext === 'JPEG') {
        $ext = 'JPG';
    }
    $kind = $document && $document->isPdf() ? 'pdf' : (($document && $document->isImage()) ? 'image' : 'file');
    if ($kind === 'pdf') {
        $ext = 'PDF';
    }
@endphp
@if(! $previewUrl)
    <span class="text-muted">—</span>
@else
    <a class="enr-file-card" href="{{ $previewUrl }}" target="_blank" rel="noopener"
       data-enr-file data-kind="{{ $kind }}" data-src="{{ $previewUrl }}" title="{{ $document->file_name }}">
        <span class="enr-file-card__meta">
            <span class="enr-file-card__name">{{ $document->file_name }}</span>
            <span class="enr-file-card__type">{{ $ext ?: 'FILE' }}</span>
        </span>
        <span class="enr-file-card__preview"></span>
    </a>
@endif
