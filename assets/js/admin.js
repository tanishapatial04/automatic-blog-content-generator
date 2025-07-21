jQuery(document).ready(function($) {
    $('#abcg-generate-content').on('click', function(e) {
        e.preventDefault();

        var title = $('#title').val(); // Get post title
        var status = $('#abcg-status');

        if (title === '') {
            alert('Please enter a title first!');
            return;
        }

        status.text('Generating content...');

        $.ajax({
            url: abcg_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'abcg_generate_blog_content',
                title: title,
                nonce: abcg_ajax.nonce
            },
            success: function(response) {
console.log(response);
                if (response.success) {
                    // Insert into WordPress editor
                    if (typeof tinymce !== 'undefined') {
                        var editor = tinymce.get('content');
                        if (editor) {
                            editor.setContent(response.data);
                        } else {
                            $('#content').val(response.data);
                        }
                    }
                    status.text('Content generated!');
                } else {
                    status.text('Failed to generate content.');
                }
            },
            error: function() {
                status.text('AJAX error.');
            }
        });
    });
});
