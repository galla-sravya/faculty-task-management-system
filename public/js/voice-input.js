/**
 * ═══════════════════════════════════════════════════════════════
 *  VOICE INPUT — Speech-to-Text for all textareas
 * ═══════════════════════════════════════════════════════════════
 *
 *  Auto-discovers every <textarea> on the page and injects a
 *  microphone button. Uses the Web Speech API (SpeechRecognition)
 *  to convert voice into text in real time.
 *
 *  Features:
 *  - Lazy initialisation (only on click)
 *  - Continuous recognition with interim results
 *  - Inserts text at cursor position, preserves existing content
 *  - Re-scans when Bootstrap modals open
 *  - Graceful fallback for unsupported browsers
 *  - Error handling with friendly messages
 *  - Keyboard accessible (Enter / Space to toggle)
 */
(function () {
    'use strict';

    // ── Feature Detection ─────────────────────────────────────
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const isSupported = !!SpeechRecognition;

    // Track which textareas have already been enhanced
    const enhanced = new WeakSet();

    // Track active recognition instances so we can stop them cleanly
    const activeRecognitions = new Map();

    // ── Enhance a single textarea ─────────────────────────────
    function enhanceTextarea(textarea) {
        if (enhanced.has(textarea)) return;
        enhanced.add(textarea);

        // Skip if textarea is inside a non-visible container (will be rescanned on modal show)
        // But still mark as enhanced so we don't double-wrap

        // Don't wrap if already wrapped (defensive)
        if (textarea.parentElement && textarea.parentElement.classList.contains('voice-input-wrapper')) return;

        // ── Create wrapper ────────────────────────────────────
        const wrapper = document.createElement('div');
        wrapper.className = 'voice-input-wrapper';
        textarea.parentNode.insertBefore(wrapper, textarea);
        wrapper.appendChild(textarea);

        // ── Create mic button ─────────────────────────────────
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'voice-input-btn';
        btn.setAttribute('aria-label', 'Start Voice Input');
        btn.setAttribute('title', 'Start Voice Input');
        btn.setAttribute('tabindex', '0');
        btn.innerHTML = '<i class="bi bi-mic"></i>';

        if (!isSupported) {
            btn.classList.add('unsupported');
            btn.setAttribute('aria-label', 'Voice input is not supported in this browser');
            btn.setAttribute('title', 'Voice input is not supported in this browser');
            btn.innerHTML = '<i class="bi bi-mic-mute"></i>';
            wrapper.appendChild(btn);

            // Show unsupported message on hover/focus
            btn.addEventListener('mouseenter', function () {
                showError(wrapper, 'Voice input is not supported in this browser.');
            });
            btn.addEventListener('mouseleave', function () {
                clearError(wrapper);
            });
            btn.addEventListener('focus', function () {
                showError(wrapper, 'Voice input is not supported in this browser.');
            });
            btn.addEventListener('blur', function () {
                clearError(wrapper);
            });
            return;
        }

        wrapper.appendChild(btn);

        // ── Click handler — Toggle listening ──────────────────
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (activeRecognitions.has(textarea)) {
                stopListening(textarea, wrapper, btn);
            } else {
                startListening(textarea, wrapper, btn);
            }
        });
    }

    // ── Start Listening ───────────────────────────────────────
    function startListening(textarea, wrapper, btn) {
        // Stop any other active recognitions first
        activeRecognitions.forEach(function (rec, ta) {
            const w = ta.closest('.voice-input-wrapper');
            const b = w ? w.querySelector('.voice-input-btn') : null;
            stopListening(ta, w, b);
        });

        clearError(wrapper);

        const recognition = new SpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = navigator.language || 'en-US';

        // Track the text that was confirmed so far in this session
        let sessionText = '';

        recognition.onresult = function (event) {
            let interim = '';
            let finalTranscript = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    finalTranscript += transcript;
                } else {
                    interim += transcript;
                }
            }

            if (finalTranscript) {
                // Insert final text at cursor position
                insertTextAtCursor(textarea, finalTranscript);
                sessionText += finalTranscript;
            }
        };

        recognition.onerror = function (event) {
            let message = '';
            switch (event.error) {
                case 'not-allowed':
                    message = 'Microphone permission denied. Please allow microphone access in your browser settings.';
                    break;
                case 'no-speech':
                    message = 'No speech detected. Please try again.';
                    break;
                case 'audio-capture':
                    message = 'No microphone found. Please connect a microphone and try again.';
                    break;
                case 'network':
                    message = 'Network error. Speech recognition requires an internet connection.';
                    break;
                case 'aborted':
                    // User-initiated stop — no error needed
                    return;
                default:
                    message = 'Speech recognition error: ' + event.error;
            }

            showError(wrapper, message);
            stopListening(textarea, wrapper, btn);
        };

        recognition.onend = function () {
            // If we didn't intentionally stop, it may have auto-ended (e.g. silence)
            // Clean up the UI in any case
            if (activeRecognitions.has(textarea)) {
                stopListening(textarea, wrapper, btn);
            }
        };

        try {
            recognition.start();
            activeRecognitions.set(textarea, recognition);

            // Update button state
            btn.classList.add('listening');
            btn.innerHTML = '<i class="bi bi-mic-fill"></i>';
            btn.setAttribute('aria-label', 'Stop Voice Input');
            btn.setAttribute('title', 'Stop Voice Input');

            // Show listening indicator
            showListeningIndicator(wrapper);

        } catch (err) {
            showError(wrapper, 'Could not start voice input. Please try again.');
        }
    }

    // ── Stop Listening ────────────────────────────────────────
    function stopListening(textarea, wrapper, btn) {
        const recognition = activeRecognitions.get(textarea);
        if (recognition) {
            try {
                recognition.abort();
            } catch (e) {
                // Ignore — already stopped
            }
            activeRecognitions.delete(textarea);
        }

        if (btn) {
            btn.classList.remove('listening');
            btn.innerHTML = '<i class="bi bi-mic"></i>';
            btn.setAttribute('aria-label', 'Start Voice Input');
            btn.setAttribute('title', 'Start Voice Input');
        }

        if (wrapper) {
            removeListeningIndicator(wrapper);
        }
    }

    // ── Insert text at cursor position ────────────────────────
    function insertTextAtCursor(textarea, text) {
        // Focus the textarea to ensure we can get selection
        textarea.focus();

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const before = textarea.value.substring(0, start);
        const after = textarea.value.substring(end);

        // Add a space before if there's already text and the last char isn't whitespace
        let prefix = '';
        if (before.length > 0 && !/\s$/.test(before)) {
            prefix = ' ';
        }

        textarea.value = before + prefix + text + after;

        // Move cursor to end of inserted text
        const newPos = start + prefix.length + text.length;
        textarea.selectionStart = newPos;
        textarea.selectionEnd = newPos;

        // Trigger input event so any JS watchers / validation pick up the change
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        textarea.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // ── Listening Indicator ───────────────────────────────────
    function showListeningIndicator(wrapper) {
        removeListeningIndicator(wrapper);
        const indicator = document.createElement('span');
        indicator.className = 'voice-listening-indicator';
        indicator.textContent = 'Listening…';
        wrapper.appendChild(indicator);
    }

    function removeListeningIndicator(wrapper) {
        const existing = wrapper.querySelector('.voice-listening-indicator');
        if (existing) existing.remove();
    }

    // ── Error Display ─────────────────────────────────────────
    function showError(wrapper, message) {
        clearError(wrapper);
        const el = document.createElement('div');
        el.className = 'voice-input-error';
        el.textContent = message;
        wrapper.appendChild(el);

        // Auto-dismiss after 5 seconds
        setTimeout(function () {
            if (el.parentNode) el.remove();
        }, 5000);
    }

    function clearError(wrapper) {
        const existing = wrapper.querySelector('.voice-input-error');
        if (existing) existing.remove();
    }

    // ── Scan & Enhance all textareas ──────────────────────────
    function scanAndEnhance(root) {
        const textareas = (root || document).querySelectorAll('textarea');
        textareas.forEach(enhanceTextarea);
    }

    // ── Initialise on DOMContentLoaded ────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        scanAndEnhance();

        // Re-scan when Bootstrap modals are shown (textareas inside modals
        // may not have been visible during initial scan)
        document.addEventListener('shown.bs.modal', function (event) {
            if (event.target) {
                scanAndEnhance(event.target);
            }
        });
    });

    // ── Stop all recognition when page unloads ────────────────
    window.addEventListener('beforeunload', function () {
        activeRecognitions.forEach(function (rec) {
            try { rec.abort(); } catch (e) { /* noop */ }
        });
        activeRecognitions.clear();
    });

})();
