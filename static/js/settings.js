/* settings.js — rich text editor helpers for admin settings page */

function rtCmd(cmd, btn) {
    document.execCommand(cmd, false, null);
    btn.classList.toggle('active');
}

function rtInsertVar(btn) {
    var v = btn.getAttribute('data-var');
    var editor = btn.closest('.rt-editor-wrap').querySelector('.rt-content');
    editor.focus();
    document.execCommand('insertText', false, v);
}

function syncRtEditors() {
    document.getElementById('tpl_new_order').value = document.getElementById('editor_new_order').innerHTML;
    document.getElementById('tpl_availability').value = document.getElementById('editor_availability').innerHTML;
    document.getElementById('tpl_payment').value = document.getElementById('editor_payment').innerHTML;
}
