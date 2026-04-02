(function () {
    var btn    = document.getElementById('mfvv-fetch-thumb');
    var status = document.getElementById('mfvv-fetch-status');

    if (!btn) return;

    btn.addEventListener('click', function () {
        var url = document.getElementById('mfvv_vimeo_url').value.trim();

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
                    status.textContent = res.data;
                    status.style.color = '#00a32a';
                    // Refresh the featured image metabox
                    if (wp && wp.data && wp.data.dispatch) {
                        wp.data.dispatch('core/editor').editPost({ featured_media: 0 });
                        wp.data.dispatch('core').invalidateResolution('getEntityRecord', ['postType', 'mfvv_video', mfvvAdmin.postId]);
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
