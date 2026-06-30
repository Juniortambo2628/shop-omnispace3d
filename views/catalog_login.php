<?php $page_title = htmlspecialchars($event['short_name']) . ' — Catalog Access'; ?>
<?php include __DIR__ . '/storefront/_head.php'; ?>
    <style>
        body {
            background: linear-gradient(135deg, var(--brand-teal) 0%, #066666 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }

        /* Top panel — event branding */
        .card-top {
            background: #f8fefe;
            border-bottom: 1px solid #e0f5f5;
            padding: 40px 40px 32px;
            text-align: center;
        }
        /* Logo row — matches catalog header (OmniSpace + event, side by side) */
        .logo-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .logo-row .logo-omnispace {
            height: 64px;
            width: auto;
            max-width: 260px;
            object-fit: contain;
        }
        .logo-row .logo-event {
            height: 72px;
            width: auto;
            max-width: 280px;
            object-fit: contain;
        }
        .event-name {
            font-size: 13px;
            font-weight: 600;
            color: #6E6E6E;
            letter-spacing: 0.3px;
            margin-bottom: 4px;
        }
        .event-dates {
            font-size: 12px;
            color: #9ca3af;
        }

        /* Bottom panel — login form */
        .card-body {
            padding: 32px 40px 36px;
        }
        .lock-icon {
            font-size: 28px;
            text-align: center;
            margin-bottom: 10px;
        }
        .card-body h2 {
            font-size: 17px;
            font-weight: 700;
            color: #1a1a1a;
            text-align: center;
            margin-bottom: 6px;
        }
        .card-body p {
            font-size: 13px;
            color: #6E6E6E;
            text-align: center;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .input-wrap {
            position: relative;
            margin-bottom: 20px;
        }
        .input-wrap input {
            width: 100%;
            padding: 13px 16px 13px 44px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            color: #333;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff;
        }
        .input-wrap input:focus {
            outline: none;
            border-color: var(--brand-teal);
            box-shadow: 0 0 0 3px rgba(10,150,150,0.12);
        }
        .input-wrap .icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #aaa;
            pointer-events: none;
        }
        .toggle-pw {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: #aaa;
            cursor: pointer;
            user-select: none;
        }
        .toggle-pw:hover { color: var(--brand-teal); }

        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 11px 14px;
            font-size: 12px;
            color: #dc2626;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--brand-teal), var(--brand-teal-dark));
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            letter-spacing: 0.3px;
            transition: opacity 0.2s, transform 0.1s;
        }
        .btn:hover { opacity: 0.92; transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }

        .help-text {
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
            margin-top: 18px;
            line-height: 1.5;
        }
        .help-text a {
            color: var(--brand-teal);
            text-decoration: none;
        }
        .help-text a:hover { text-decoration: underline; }

        /* OmniSpace footer */
        .footer {
            margin-top: 28px;
            text-align: center;
        }
        .footer img {
            height: 28px;
            opacity: 0.85;
            filter: brightness(0) invert(1);
        }
        .footer p {
            font-size: 11px;
            color: rgba(255,255,255,0.65);
            margin-top: 6px;
        }
    </style>
</head>
<body>

<div class="card">
    <!-- Event branding -->
    <div class="card-top">
        <div class="logo-row">
            <img class="logo-omnispace" src="/static/images/omnispace-logo.jpg" alt="OmniSpace 3D Events">
            <img class="logo-event" src="<?php echo htmlspecialchars($event['logo']); ?>" alt="<?php echo htmlspecialchars($event['short_name']); ?>">
        </div>
        <div class="event-name"><?php echo htmlspecialchars($event["name"]); ?></div>
        <div class="event-dates"><?php echo htmlspecialchars($event["dates"]); ?> &nbsp;·&nbsp; <?php echo htmlspecialchars(explode(',', $event["venue"])[0]); ?></div>
    </div>

    <!-- Login form -->
    <div class="card-body">
        <div class="lock-icon">🔒</div>
        <h2>Catalog Access</h2>
        <p>This catalog is available to registered exhibitors only.<br>Please enter your access password to continue.</p>

        <?php if ($error): ?>
        <div class="error-box">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/<?php echo htmlspecialchars($event_slug); ?>/login">
            <label for="password">Access Password</label>
            <div class="input-wrap">
                <span class="icon">🔑</span>
                <input type="password" id="password" name="password"
                       placeholder="Enter your access password"
                       autofocus autocomplete="off" required>
                <span class="toggle-pw" onclick="togglePw()" title="Show/hide password">👁</span>
            </div>
            <button type="submit" class="btn">Access Catalog →</button>
        </form>

        <div class="help-text">
            Don't have a password? Contact us at
            <a href="mailto:<?php echo htmlspecialchars($event['contact_email']); ?>"><?php echo htmlspecialchars($event["contact_email"]); ?></a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/storefront/_footer.php'; ?>

<script>
function togglePw() {
    var inp = document.getElementById('password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
