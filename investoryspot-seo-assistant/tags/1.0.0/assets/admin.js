jQuery(function ($) {

    var investoryspot = {
        nonce: investoryspot_seo.nonce,
        post_id: investoryspot_seo.post_id,
        loading: false
    };

    function showLoading() {
        $('.investoryspot-seo-loading').show();
        investoryspot.loading = true;
    }

    function hideLoading() {
        $('.investoryspot-seo-loading').hide();
        investoryspot.loading = false;
    }

    function updateScore(value) {
        var circle = $('.investoryspot-seo-score-circle');
        circle.removeClass('score-good score-ok score-bad');
        $('.investoryspot-seo-score-value').text(value);

        if (value >= 80) {
            circle.addClass('score-good');
        } else if (value >= 50) {
            circle.addClass('score-ok');
        } else {
            circle.addClass('score-bad');
        }
    }

    function updateField(field, value) {
        $('#' + field).val(value).trigger('input');
    }

    function updateCounter(field) {
        var len = $('#' + field).val().length;
        $('#' + field.replace('investoryspot_seo_', '') + '-count').text(len);
    }

    $('#investoryspot_seo_title').on('input', function () {
        updateCounter('investoryspot_seo_title');
    });

    $('#investoryspot_seo_description').on('input', function () {
        updateCounter('investoryspot_seo_description');
    });

    $(document).on('click', '.investoryspot-generate-title-btn', function () {
        if (investoryspot.loading) return;
        showLoading();

        $.post(investoryspot_seo.ajax_url, {
            action: 'investoryspot_generate_title',
            post_id: investoryspot.post_id || $(this).data('post-id'),
            nonce: investoryspot.nonce
        }, function (res) {
            hideLoading();
            if (res.success) {
                updateField('investoryspot_seo_title', res.data.title);
            } else {
                alert(res.data || 'Failed to generate title');
            }
        }).fail(function () {
            hideLoading();
            alert('AJAX error. Please try again.');
        });
    });

    $(document).on('click', '.investoryspot-generate-desc-btn', function () {
        if (investoryspot.loading) return;
        showLoading();

        $.post(investoryspot_seo.ajax_url, {
            action: 'investoryspot_generate_description',
            post_id: investoryspot.post_id || $(this).data('post-id'),
            nonce: investoryspot.nonce
        }, function (res) {
            hideLoading();
            if (res.success) {
                updateField('investoryspot_seo_description', res.data.description);
            } else {
                alert(res.data || 'Failed to generate description');
            }
        }).fail(function () {
            hideLoading();
            alert('AJAX error. Please try again.');
        });
    });

    $(document).on('click', '.investoryspot-generate-all-btn', function () {
        if (investoryspot.loading) return;
        showLoading();

        $.post(investoryspot_seo.ajax_url, {
            action: 'investoryspot_generate_all',
            post_id: investoryspot.post_id || $(this).data('post-id'),
            nonce: investoryspot.nonce
        }, function (res) {
            hideLoading();
            if (res.success) {
                if (res.data.title) {
                    updateField('investoryspot_seo_title', res.data.title);
                }
                if (res.data.description) {
                    updateField('investoryspot_seo_description', res.data.description);
                }
                if (res.data.errors && res.data.errors.length) {
                    alert('Some errors occurred:\n' + res.data.errors.join('\n'));
                }
            } else {
                alert(res.data || 'Failed to generate');
            }
        }).fail(function () {
            hideLoading();
            alert('AJAX error. Please try again.');
        });
    });

    $(document).on('click', '.investoryspot-analyze-btn', function () {
        if (investoryspot.loading) return;
        var btn = $(this);
        btn.text('Analyzing...').prop('disabled', true);
        showLoading();

        $.post(investoryspot_seo.ajax_url, {
            action: 'investoryspot_analyze',
            post_id: investoryspot.post_id || $(this).data('post-id'),
            nonce: investoryspot.nonce
        }, function (res) {
            hideLoading();
            btn.text('Analyze Now').prop('disabled', false);

            if (res.success) {
                updateScore(res.data.score);
                renderAnalysis(res.data);
            } else {
                alert(res.data || 'Failed to analyze');
            }
        }).fail(function () {
            hideLoading();
            btn.text('Analyze Now').prop('disabled', false);
            alert('AJAX error. Please try again.');
        });
    });

    $(document).on('click', '.investoryspot-suggest-kw-btn', function () {
        if (investoryspot.loading) return;
        showLoading();

        $.post(investoryspot_seo.ajax_url, {
            action: 'investoryspot_suggest_keyphrases',
            post_id: investoryspot.post_id || $(this).data('post-id'),
            nonce: investoryspot.nonce
        }, function (res) {
            hideLoading();
            if (res.success) {
                $('#investoryspot_seo_keyphrase').val(res.data.keyphrases);
            } else {
                alert(res.data || 'Failed to suggest keyphrases');
            }
        }).fail(function () {
            hideLoading();
            alert('AJAX error. Please try again.');
        });
    });

    function renderAnalysis(data) {
        var html = '<div class="investoryspot-analysis-result">';

        if (data.checks && data.checks.length) {
            html += '<h4>SEO Checklist</h4><ul>';
            $.each(data.checks, function (i, check) {
                var icon = check.pass ? '&#10003;' : '&#10007;';
                var cls = check.pass ? 'investoryspot-check-pass' : 'investoryspot-check-fail';
                html += '<li class="' + cls + '"><strong>' + icon + ' ' + check.name + ':</strong> ' + check.note + '</li>';
            });
            html += '</ul>';
        }

        if (data.ai_analysis && data.ai_analysis.score) {
            var ai = data.ai_analysis;

            if (ai.issues && ai.issues.length) {
                html += '<h4>AI Issues Found</h4><ul>';
                $.each(ai.issues, function (i, issue) {
                    html += '<li>' + issue + '</li>';
                });
                html += '</ul>';
            }

            if (ai.suggestions && ai.suggestions.length) {
                html += '<h4>AI Suggestions</h4><ul>';
                $.each(ai.suggestions, function (i, suggestion) {
                    html += '<li>' + suggestion + '</li>';
                });
                html += '</ul>';
            }

            if (ai.readability) {
                html += '<p><strong>Readability:</strong> ' + ai.readability + '</p>';
            }
            if (ai.keyword_density) {
                html += '<p><strong>Keyword density:</strong> ' + ai.keyword_density + '</p>';
            }
        }

        html += '</div>';
        $('#investoryspot-seo-results').html(html);
    }
});
