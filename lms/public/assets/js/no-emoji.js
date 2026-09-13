/**
 * Block emoji input/paste on all text fields site-wide.
 */
(function () {
    'use strict';

    var EMOJI_RE = /(?:\p{Extended_Pictographic}|\uFE0F|\u200D|[\u{1F1E6}-\u{1F1FF}])/gu;

    function isTextControl(el) {
        if (!el || el.disabled || el.readOnly) {
            return false;
        }
        if (el.isContentEditable) {
            return true;
        }
        if (el.tagName === 'TEXTAREA') {
            return true;
        }
        if (el.tagName !== 'INPUT') {
            return false;
        }
        var type = (el.getAttribute('type') || 'text').toLowerCase();
        return ['text', 'search', 'email', 'tel', 'url', 'password', 'number', ''].indexOf(type) !== -1
            || !el.getAttribute('type');
    }

    function stripEmojis(value) {
        return String(value || '').replace(EMOJI_RE, '');
    }

    function hasEmoji(value) {
        EMOJI_RE.lastIndex = 0;
        return EMOJI_RE.test(String(value || ''));
    }

    function cleanInput(el) {
        if (!isTextControl(el) || el.isContentEditable) {
            return;
        }
        var before = el.value;
        var after = stripEmojis(before);
        if (before === after) {
            return;
        }
        var start = el.selectionStart;
        var end = el.selectionEnd;
        el.value = after;
        if (typeof start === 'number' && typeof end === 'number') {
            var diff = before.length - after.length;
            el.setSelectionRange(Math.max(0, start - diff), Math.max(0, end - diff));
        }
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function onBeforeInput(e) {
        if (!isTextControl(e.target)) {
            return;
        }
        if (e.data && hasEmoji(e.data)) {
            e.preventDefault();
        }
    }

    function onPaste(e) {
        if (!isTextControl(e.target)) {
            return;
        }
        var text = (e.clipboardData || window.clipboardData).getData('text');
        if (!hasEmoji(text)) {
            return;
        }
        e.preventDefault();
        var cleaned = stripEmojis(text);
        var el = e.target;
        if (el.isContentEditable) {
            document.execCommand('insertText', false, cleaned);
            return;
        }
        var start = el.selectionStart || 0;
        var end = el.selectionEnd || 0;
        var value = el.value || '';
        el.value = value.slice(0, start) + cleaned + value.slice(end);
        var caret = start + cleaned.length;
        el.setSelectionRange(caret, caret);
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function onInput(e) {
        cleanInput(e.target);
    }

    function bind(root) {
        root.addEventListener('beforeinput', onBeforeInput, true);
        root.addEventListener('paste', onPaste, true);
        root.addEventListener('input', onInput, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            bind(document);
        });
    } else {
        bind(document);
    }
})();
