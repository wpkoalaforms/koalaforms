import { getInputBlockOptions } from '../../blockHelper';

export const initializeEditors = (modalType, formSettings, handleMetaChange, initializedEditors, setInitializedEditors) => {
   

    (function() {
        let fieldOptions = getInputBlockOptions();
        if(formSettings.unique_id)
            fieldOptions = [{text: 'Unique ID', value: 'UNIQUE_ID'}, ...fieldOptions];

        
        tinymce.PluginManager.add('koalaforms_fields_button', function(editor, url) {
            editor.addButton('koalaforms_fields_button', {
                text: 'Form Fields',
                icon: false,
                type: 'listbox',
                onselect: function (e) {
                    editor.insertContent('{{' + this.value() + '}}');
                },
                values: fieldOptions
            });
        });
    })();

    setTimeout(() => {
        if (window.wp && window.wp.editor && window.wp.editor.initialize) {
            if (modalType === 'post_submission') {
                if (document.getElementById('success-message-editor')) {
                    window.wp.editor.initialize('success-message-editor', {
                        tinymce: {
                            wpautop: true,
                            plugins: 'lists link image paste tabfocus wordpress wpautoresize wpeditimage wpgallery wplink wpdialogs wptextpattern wpview',
                            toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr | alignleft aligncenter alignright | link unlink | wp_adv',
                            toolbar2: 'formatselect forecolor backcolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',
                            setup: function (editor) {
                                editor.on('change keyup', function () {
                                    handleMetaChange('success_message', editor.getContent());
                                });
                            }
                        },
                        quicktags: true
                    });
                }
            }

            if (modalType === 'notification_settings') {
                const autoReplyId = 'auto-reply-editor';
                const adminReplyId = 'admin-email-editor';

                if (document.getElementById(autoReplyId) && !initializedEditors[autoReplyId]) {
                    setInitializedEditors(prev => ({ ...prev, [autoReplyId]: true }));
                    window.wp.editor.initialize(autoReplyId, {
                        tinymce: {
                            wpautop: true,
                            plugins: 'lists link image paste tabfocus wordpress wpautoresize wpeditimage wpgallery wplink wpdialogs wptextpattern wpview koalaforms_fields_button',
                            toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr | alignleft aligncenter alignright | link unlink | wp_adv | koalaforms_fields_button',
                            toolbar2: 'formatselect forecolor backcolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',
                            setup: function (editor) {
                                editor.on('init', () => {
                                    editor.setContent(formSettings.auto_reply_body || '');
                                    editor.show();
                                    editor.theme.resizeTo(null, 250);
                                    if (window.switchEditors) {
                                        window.switchEditors.go(autoReplyId, 'tinymce');
                                    }
                                });
                                editor.on('change keyup', () => {
                                    handleMetaChange('auto_reply_body', editor.getContent());
                                });
                            }
                        },
                        quicktags: {
                            buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close'
                        }
                    });
                }

                if (document.getElementById(adminReplyId) && !initializedEditors[adminReplyId]) {
                    setInitializedEditors(prev => ({ ...prev, [adminReplyId]: true }));
                    window.wp.editor.initialize(adminReplyId, {
                        tinymce: {
                            wpautop: true,
                            plugins: 'lists link image paste tabfocus wordpress wpautoresize wpeditimage wpgallery wplink wpdialogs wptextpattern wpview koalaforms_fields_button',
                            toolbar1: 'bold italic underline strikethrough | bullist numlist | blockquote hr | alignleft aligncenter alignright | link unlink | wp_adv | koalaforms_fields_button',
                            toolbar2: 'formatselect forecolor backcolor | pastetext removeformat charmap | outdent indent | undo redo | wp_help',
                            setup: function (editor) {
                                editor.on('init', () => {
                                    editor.setContent(formSettings.admin_email_body || '');
                                    editor.show();
                                    editor.theme.resizeTo(null, 250);
                                });
                                editor.on('change keyup', () => {
                                    handleMetaChange('admin_email_body', editor.getContent());
                                });
                            }
                        },
                        quicktags: {
                            buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close'
                        }
                    });
                }
            }
        }
    }, 1000);
};

export const cleanupEditors = (modalType) => {
    if (window.wp && window.wp.editor && window.wp.editor.remove) {
        if (modalType === 'post_submission') {
            window.wp.editor.remove('success-message-editor');
        }
        if (modalType === 'notification_settings') {
            window.wp.editor.remove('auto-reply-editor');
            window.wp.editor.remove('admin-email-editor');
        }
    }
};