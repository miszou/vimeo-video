(function () {
    var btn = document.getElementById('mfvv-fetch-thumb');
    var status = document.getElementById('mfvv-fetch-status');

    if (!btn) return;

    btn.addEventListener('click', function () {
        var url = document.getElementById('mfvv_vimeo_url_input').value.trim();

        if (!url) {
            status.textContent = 'Enter a Vimeo URL first.';
            status.style.color = '#d63638';
            return;
        }

        btn.disabled = true;
        status.textContent = 'Fetching...';
        status.style.color = '';

        var data = new FormData();
        data.append('action', 'mfvv_fetch_thumbnail');
        data.append('nonce', mfvvAdmin.nonce);
        data.append('post_id', mfvvAdmin.postId);
        data.append('vimeo_url', url);

        fetch(mfvvAdmin.ajaxUrl, { method: 'POST', body: data })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    var attachmentId = res.data && res.data.attachment_id ? parseInt(res.data.attachment_id, 10) : 0;

                    status.textContent = (res.data && res.data.message) || 'Thumbnail updated.';
                    status.style.color = '#00a32a';

                    // Keep the block editor state in sync. Setting this to 0 removes the
                    // featured image on the next save, so only write the new attachment ID.
                    if (attachmentId && typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
                        wp.data.dispatch('core/editor').editPost({ featured_media: attachmentId });
                        wp.data.dispatch('core').invalidateResolution('getEntityRecord', ['postType', 'mfvv_video', mfvvAdmin.postId]);
                        wp.data.dispatch('core').invalidateResolution('getMedia', [attachmentId]);
                    }
                } else {
                    status.textContent = res.data || 'Error fetching thumbnail.';
                    status.style.color = '#d63638';
                }
            })
            .catch(function () {
                status.textContent = 'Request failed.';
                status.style.color = '#d63638';
            })
            .finally(function () {
                btn.disabled = false;
            });
    });
})();
