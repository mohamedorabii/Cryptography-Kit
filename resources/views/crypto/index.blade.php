<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cryptography Kit</title>

    {{-- Google Fonts: JetBrains Mono for monospace result text, Inter for UI --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        /* ============================================================
           CSS VARIABLES — Purple theme palette
           ============================================================ */
        :root {
            --bg-primary:    #0e0b1a;   /* Deep dark purple background */
            --bg-card:       #16102a;   /* Card / panel background */
            --bg-input:      #0e0b1a;   /* Input field background */
            --bg-result:     #0e0b1a;   /* Result box background */
            --border:        #2e2450;   /* Subtle purple border */
            --border-focus:  #a855f7;   /* Purple focus ring */
            --teal:          #c084fc;   /* Primary purple accent */
            --teal-dark:     #a855f7;   /* Darker purple for hover */
            --blue:          #7c3aed;
            --blue-hover:    #6d28d9;
            --btn-encrypt:   #9333ea;
            --btn-decrypt:   #7c3aed;
            --text-primary:  #ede9fe;   /* Soft lavender text */
            --text-secondary:#9ca3af;   /* Muted text */
            --text-result:   #e879f9;   /* Bright fuchsia result text */
            --success:       #a78bfa;
            --error:         #f87171;
        }

        /* ============================================================
           RESET & BASE
           ============================================================ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        /* ============================================================
           MAIN APP CONTAINER  (mimics the window chrome in screenshot)
           ============================================================ */
        .app-window {
            width: 100%;
            max-width: 720px;
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 24px 48px rgba(0,0,0,.6);
        }

        /* Window title bar (lock icon + title + window buttons) */
        .title-bar {
            background: #130f23;
            border-bottom: 1px solid var(--border);
            padding: 0 16px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .title-bar-left {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .title-bar-left svg { color: var(--teal); }

        /* Window control buttons (−  □  ×) */
        .window-controls {
            display: flex;
            gap: 8px;
        }

        .win-btn {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: none;
            cursor: default;
        }

        .win-btn.minimize { background: #f59e0b; }
        .win-btn.maximize { background: #10b981; }
        .win-btn.close    { background: #ef4444; }

        /* ============================================================
           HEADER — Shield icon + title
           ============================================================ */
        .app-header {
            padding: 28px 32px 24px;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        /* Teal shield SVG icon */
        .shield-icon {
            width: 64px;
            height: 64px;
            flex-shrink: 0;
        }

        .header-text h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.5px;
        }

        .header-text p {
            font-size: 14px;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        /* ============================================================
           MAIN FORM AREA
           ============================================================ */
        .form-area {
            padding: 0 32px 32px;
        }

        /* Form row: label + input stacked */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        /* Shared input + select styling */
        .form-control {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            padding: 12px 16px;
            outline: none;
            transition: border-color .2s;
        }

        .form-control:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(56,139,253,.15);
        }

        /* Algorithm dropdown — custom arrow */
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23c084fc' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 40px;
            cursor: pointer;
            max-width: 360px;     /* matches screenshot: half-width dropdown */
        }

        select.form-control option {
            background: var(--bg-card);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 64px;
            max-height: 160px;
        }

        /* Key hint — shown below the key input, describes expected format */
        .key-hint {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* ============================================================
           ALGORITHM INFO BADGE
           Shows expected key type for selected cipher
           ============================================================ */
        .algo-info {
            background: rgba(45,212,191,.08);
            border: 1px solid rgba(45,212,191,.2);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: var(--teal);
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        /* ============================================================
           ACTION BUTTONS — Encrypt (green) + Decrypt (blue)
           ============================================================ */
        .btn-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: transform .1s, opacity .2s;
        }

        .btn:active { transform: scale(0.98); }

        .btn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .btn-encrypt {
            background: linear-gradient(135deg, #9333ea, #7c3aed);
            color: #fff;
        }

        .btn-encrypt:hover:not(:disabled) {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
        }

        .btn-decrypt {
            background: linear-gradient(135deg, #6d28d9, #5b21b6);
            color: #fff;
        }

        .btn-decrypt:hover:not(:disabled) {
            background: linear-gradient(135deg, #5b21b6, #4c1d95);
        }

        /* Spinner icon inside button while loading */
        .btn .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
            display: none;
        }

        .btn.loading .spinner { display: block; }
        .btn.loading .btn-label { display: none; }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Divider line between buttons and result */
        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin-bottom: 24px;
        }

        /* ============================================================
           RESULT BOX
           ============================================================ */
        .result-section { display: none; }

        .result-section.visible { display: block; }

        .result-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--teal);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .result-box {
            background: var(--bg-result);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px 50px 16px 16px;
            min-height: 56px;
            position: relative;
        }

        .result-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 18px;
            font-weight: 700;
            color: var(--text-result);
            letter-spacing: 1px;
            word-break: break-all;
            line-height: 1.5;
        }

        .result-text.error {
            color: var(--error);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 500;
        }

        /* Copy button inside result box */
        .copy-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-secondary);
            padding: 4px;
            border-radius: 4px;
            transition: color .2s, background .2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .copy-btn:hover {
            color: var(--teal);
            background: rgba(192,132,252,.1);
        }

        /* Copy feedback tooltip */
        .copy-tooltip {
            position: absolute;
            right: 10px;
            top: -28px;
            background: var(--teal);
            color: #0d1117;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 4px;
            pointer-events: none;
            opacity: 0;
            transition: opacity .2s;
        }

        .copy-tooltip.show { opacity: 1; }

        /* ============================================================
           RESPONSIVE — Narrow screens
           ============================================================ */
        @media (max-width: 500px) {
            .app-header     { padding: 20px 20px 16px; }
            .form-area      { padding: 0 20px 24px; }
            .btn-row        { grid-template-columns: 1fr; }
            select.form-control { max-width: 100%; }
        }
    </style>
</head>
<body>

{{-- ============================================================
     APP WINDOW
     ============================================================ --}}
<div class="app-window">

    {{-- Window Title Bar --}}
    <div class="title-bar">
        <div class="title-bar-left">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            Cryptography Kit
        </div>
        <div class="window-controls">
            <span class="win-btn minimize"></span>
            <span class="win-btn maximize"></span>
            <span class="win-btn close"></span>
        </div>
    </div>

    {{-- Header: Shield + Title --}}
    <div class="app-header">
        {{-- Teal Shield SVG Icon --}}
        <svg class="shield-icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M32 4L8 14V34C8 48.5 18.7 57.9 32 60C45.3 57.9 56 48.5 56 34V14L32 4Z"
                  fill="none" stroke="#c084fc" stroke-width="3" stroke-linejoin="round"/>
            <rect x="24" y="28" width="16" height="14" rx="2" fill="none" stroke="#c084fc" stroke-width="2.5"/>
            <circle cx="32" cy="35" r="2.5" fill="#c084fc"/>
            <path d="M28 28V24a4 4 0 0 1 8 0v4" stroke="#c084fc" stroke-width="2.5" stroke-linecap="round"/>
        </svg>

        <div class="header-text">
            <h1>Cryptography Kit</h1>
            <p>Encrypt and Decrypt messages using classic ciphers</p>
        </div>
    </div>

    {{-- Form Area --}}
    <div class="form-area">

        {{-- Algorithm Selection --}}
        <div class="form-group">
            <label for="algorithm">Algorithm:</label>
            <select id="algorithm" class="form-control" onchange="updateAlgoInfo()">
                <option value="vigenere">Vigenere</option>
                <option value="caesar">Caesar</option>
                <option value="playfair">Playfair</option>
                <option value="autokey">Autokey</option>
                <option value="vernam">Vernam</option>
                <option value="railfence">Rail Fence</option>
                <option value="rowtransposition">Row Transposition</option>
            </select>
        </div>

        {{-- Algorithm Info (key type hint) --}}
        <div class="algo-info" id="algoInfo">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span id="algoInfoText">Key: alphabetic word or phrase (e.g. KEY). The key repeats to match text length.</span>
        </div>

        {{-- Plain / Cipher Text Input --}}
        <div class="form-group">
            <label for="textInput" id="textLabel">Plain Text:</label>
            <textarea id="textInput" class="form-control" rows="2" placeholder="Enter your text here..."></textarea>
        </div>

        {{-- Key Input --}}
        <div class="form-group">
            <label for="keyInput">Key:</label>
            <input id="keyInput" type="text" class="form-control" placeholder="Enter key...">
        </div>

        {{-- Encrypt / Decrypt Buttons --}}
        <div class="btn-row">
            <button class="btn btn-encrypt" id="encryptBtn" onclick="processText('encrypt')">
                <div class="spinner"></div>
                <span class="btn-label" style="display:flex;align-items:center;gap:8px">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Encrypt
                </span>
            </button>

            <button class="btn btn-decrypt" id="decryptBtn" onclick="processText('decrypt')">
                <div class="spinner"></div>
                <span class="btn-label" style="display:flex;align-items:center;gap:8px">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                        <path d="M7 11V7a5 5 0 0 1 9.9-1"/>
                    </svg>
                    Decrypt
                </span>
            </button>
        </div>

        <hr class="divider">

        {{-- Result Output --}}
        <div class="result-section" id="resultSection">
            <div class="result-label">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Result:
            </div>
            <div class="result-box">
                <div class="result-text" id="resultText"></div>
                <button class="copy-btn" onclick="copyResult()" title="Copy to clipboard">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    <span class="copy-tooltip" id="copyTooltip">Copied!</span>
                </button>
            </div>
        </div>

    </div>{{-- /.form-area --}}
</div>{{-- /.app-window --}}


<script>
// ============================================================
// Algorithm descriptions & key hints
// ============================================================
const algoInfo = {
    caesar:           'Key: a number representing the shift (e.g. 3). Each letter shifts by that amount in the alphabet.',
    playfair:         'Key: a word/phrase (alphabetic only). Used to build a 5×5 matrix. J is treated as I.',
    vigenere:         'Key: an alphabetic word or phrase (e.g. KEY). The key repeats to match the plaintext length.',
    autokey:          'Key: an alphabetic word. The plaintext itself extends the key — no repetition needed.',
    vernam:           'Key: an alphabetic word. Each letter is XOR-ed with the corresponding key letter.',
    railfence:        'Key: a number ≥ 2 representing the number of rails (rows) in the zigzag pattern.',
    rowtransposition: 'Key: an alphabetic word. Column order is determined by the alphabetical order of key letters.',
};

/**
 * Update the info badge when the algorithm changes
 */
function updateAlgoInfo() {
    const algo = document.getElementById('algorithm').value;
    document.getElementById('algoInfoText').textContent = algoInfo[algo] || '';
}

// Run once on load so the initial algorithm shows its hint
updateAlgoInfo();

// ============================================================
// Main process function — sends AJAX request to Laravel backend
// ============================================================

/**
 * Send text + key + algorithm to the backend and display the result.
 * @param {string} operation - 'encrypt' or 'decrypt'
 */
async function processText(operation) {
    const algorithm = document.getElementById('algorithm').value;
    const text      = document.getElementById('textInput').value.trim();
    const key       = document.getElementById('keyInput').value.trim();

    // Basic client-side validation
    if (!text) {
        showResult('Please enter some text.', true);
        return;
    }
    if (!key) {
        showResult('Please enter a key.', true);
        return;
    }

    // Show loading state on the clicked button
    const btn = document.getElementById(operation === 'encrypt' ? 'encryptBtn' : 'decryptBtn');
    btn.classList.add('loading');
    btn.disabled = true;

    try {
        // POST to the Laravel process route with CSRF token
        const response = await fetch('{{ route("crypto.process") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ algorithm, text, key, operation }),
        });

        const data = await response.json();

        if (data.success) {
            showResult(data.result, false);
        } else {
            // Show validation or logic errors
            const msg = data.errors
                ? Object.values(data.errors).flat().join(' ')
                : (data.error || 'An unexpected error occurred.');
            showResult(msg, true);
        }

    } catch (err) {
        showResult('Network error. Please try again.', true);
    } finally {
        btn.classList.remove('loading');
        btn.disabled = false;
    }
}

/**
 * Display the result (or error) in the result section.
 * @param {string}  text    The output text to display
 * @param {boolean} isError Whether this is an error message
 */
function showResult(text, isError = false) {
    const section    = document.getElementById('resultSection');
    const resultEl   = document.getElementById('resultText');

    section.classList.add('visible');
    resultEl.textContent = text;

    if (isError) {
        resultEl.classList.add('error');
    } else {
        resultEl.classList.remove('error');
    }

    // Scroll the result into view smoothly
    section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Copy the result text to the clipboard
 */
function copyResult() {
    const text    = document.getElementById('resultText').textContent;
    const tooltip = document.getElementById('copyTooltip');

    navigator.clipboard.writeText(text).then(() => {
        tooltip.classList.add('show');
        setTimeout(() => tooltip.classList.remove('show'), 1800);
    }).catch(() => {
        // Fallback for older browsers
        const ta    = document.createElement('textarea');
        ta.value    = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        tooltip.classList.add('show');
        setTimeout(() => tooltip.classList.remove('show'), 1800);
    });
}

// Allow pressing Enter in key field to trigger encrypt
document.getElementById('keyInput').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') processText('encrypt');
});
</script>

</body>
</html>

