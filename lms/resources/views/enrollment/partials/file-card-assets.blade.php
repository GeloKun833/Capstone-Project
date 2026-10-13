@once
<style>
.enr-file-card {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    width: 280px;
    max-width: 100%;
    padding: 8px 10px 8px 12px;
    border-radius: 12px;
    background: #f3f4f6;
    color: #111827;
    text-decoration: none;
}
.enr-file-card__remove {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 22px;
    height: 22px;
    border: 0;
    border-radius: 50%;
    background: #111827;
    color: #fff;
    font-size: 14px;
    line-height: 1;
    cursor: pointer;
}
.enr-file-card { cursor: pointer; }
.enr-file-card:hover { color: #111827; }
.enr-file-card__meta { min-width: 0; flex: 1; }
.enr-file-card__name {
    display: block;
    font-weight: 700;
    font-size: 14px;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-decoration: underline;
}
.enr-file-card__type {
    display: block;
    margin-top: 4px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}
.enr-file-card__preview {
    width: 96px;
    height: 72px;
    flex: none;
    border-radius: 4px;
    overflow: hidden;
    background: #fff;
    border: 1px solid #e5e7eb;
}
.enr-file-card__preview img,
.enr-file-card__preview canvas {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: top center;
}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
(function () {
    function kindOf(name, mime) {
        var type = (mime || '').toLowerCase();
        var ext = (name || '').split('.').pop().toLowerCase();
        if (type.indexOf('pdf') !== -1 || ext === 'pdf') return 'pdf';
        if (type.indexOf('image/') === 0 || ['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(ext) !== -1) return 'image';
        return 'file';
    }

    function labelOf(name, kind) {
        if (kind === 'pdf') return 'PDF';
        var ext = (name || '').split('.').pop().toUpperCase();
        return ext === 'JPEG' ? 'JPG' : (ext || 'FILE');
    }

    function ensurePdf() {
        if (!window.pdfjsLib) return null;
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        return window.pdfjsLib;
    }

    async function paintPreview(box, source, kind) {
        var frame = box.querySelector('.enr-file-card__preview');
        if (!frame) return;
        if (kind === 'image') {
            var img = document.createElement('img');
            img.alt = '';
            img.src = typeof source === 'string' ? source : URL.createObjectURL(source);
            frame.appendChild(img);
            return;
        }
        if (kind !== 'pdf') return;
        var pdfjs = ensurePdf();
        if (!pdfjs) return;
        try {
            var doc = typeof source === 'string'
                ? await pdfjs.getDocument({ url: source, withCredentials: true }).promise
                : await pdfjs.getDocument({ data: await source.arrayBuffer() }).promise;
            var page = await doc.getPage(1);
            var base = page.getViewport({ scale: 1 });
            var canvas = document.createElement('canvas');
            var viewport = page.getViewport({ scale: 192 / base.width });
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            await page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise;
            frame.appendChild(canvas);
        } catch (e) {}
    }

    window.paintEnrollmentFileCard = function (box, file) {
        if (box.dataset.objectUrl) {
            URL.revokeObjectURL(box.dataset.objectUrl);
            delete box.dataset.objectUrl;
        }
        if (!file) {
            box.innerHTML = '';
            return;
        }
        var kind = kindOf(file.name, file.type);
        var url = URL.createObjectURL(file);
        box.dataset.objectUrl = url;
        box.innerHTML = '<a class="enr-file-card" target="_blank" rel="noopener">'
            + '<span class="enr-file-card__meta"><span class="enr-file-card__name"></span><span class="enr-file-card__type"></span></span>'
            + '<span class="enr-file-card__preview"></span>'
            + '<button type="button" class="enr-file-card__remove" aria-label="Remove file">&times;</button></a>';
        var link = box.querySelector('.enr-file-card');
        link.href = url;
        box.querySelector('.enr-file-card__name').textContent = file.name;
        box.querySelector('.enr-file-card__type').textContent = labelOf(file.name, kind);
        box.querySelector('.enr-file-card__remove').addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            var input = box.previousElementSibling;
            if (input && input.type === 'file') {
                input.value = '';
                input.dispatchEvent(new Event('change', { bubbles: true }));
            } else {
                window.paintEnrollmentFileCard(box, null);
            }
        });
        paintPreview(box, file, kind);
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-enr-file]').forEach(function (card) {
            paintPreview(card, card.getAttribute('data-src'), card.getAttribute('data-kind'));
        });
    });
})();
</script>
@endonce
