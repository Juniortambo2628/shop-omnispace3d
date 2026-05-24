<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'OmniShop'); ?></title>
    <meta name="description" content="Official exhibitor services catalog for <?php echo htmlspecialchars($event['name'] ?? 'Solar and Storage Live 2026'); ?>. Order stand furniture, audiovisual equipment, and booth services online.">
    <meta name="keywords" content="exhibitor services, furniture rental, event services, <?php echo htmlspecialchars($event['short_name'] ?? 'SSL 2026'); ?>, OmniSpace 3D">
    <link rel="icon" type="image/png" href="/static/images/square-logos/solar-and-storage-live.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ── SHARED CSS RESET & DESIGN TOKENS ── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Montserrat', Arial, sans-serif; background: #f7f8fa; color: #333; }
        a { color: #0A9696; text-decoration: none; }

        /* Shared Header */
        .header { background: linear-gradient(135deg, #0A9696 0%, #088080 100%); color: #fff; padding: 14px 28px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 12px rgba(0,0,0,0.12); position: sticky; top: 0; z-index: 100; }
        .header img { height: 36px; }
        .header h1 { font-size: 20px; font-weight: 700; }
        .header a { color: #fff; font-size: 13px; opacity: 0.9; text-decoration: none; }
        .header a:hover { opacity: 1; text-decoration: underline; }

        /* Shared Footer */
        .footer { background: #1a1a2e; color: #fff; text-align: center; padding: 32px 20px; margin-top: 40px; }
        .footer img { height: 32px; margin-bottom: 12px; opacity: 0.9; }
        .footer p { font-size: 12px; opacity: 0.7; margin: 4px 0; }
        .footer .tagline { font-style: italic; margin-top: 12px; opacity: 0.5; font-size: 11px; }

        /* Shared UI Components */
        .section { background: #fff; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); padding: 28px; margin-bottom: 24px; }
        .section h2 { font-size: 18px; font-weight: 700; margin-bottom: 20px; color: #222; padding-bottom: 12px; border-bottom: 2px solid #D6F0EF; }
        .btn { padding: 12px 28px; border-radius: 8px; font-size: 14px; font-weight: 600; font-family: inherit; cursor: pointer; text-decoration: none; display: inline-block; border: none; transition: all 0.2s; }
        .btn-primary { background: #0A9696; color: #fff; }
        .btn-primary:hover { background: #088080; }
        .btn-outline { background: #fff; color: #0A9696; border: 2px solid #0A9696; }
        .btn-outline:hover { background: #D6F0EF; }

        /* Shared Toast */
        .toast { position: fixed; bottom: 30px; right: 30px; background: #0A9696; color: #fff; padding: 14px 24px; border-radius: 8px; font-size: 13px; font-weight: 600; box-shadow: 0 4px 16px rgba(0,0,0,0.2); transform: translateY(100px); opacity: 0; transition: all 0.3s; z-index: 300; }
        .toast.show { transform: translateY(0); opacity: 1; }

        /* Skeleton Loading */
        .skeleton {
            background-color: #f6f7f8 !important;
            background-image: linear-gradient(90deg, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%) !important;
            background-repeat: no-repeat !important;
            background-size: 200% 100% !important;
            display: inline-block;
            animation: skeleton-shimmer 1.5s infinite linear;
        }
        @keyframes skeleton-shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* Mobile base */
        @media (max-width: 768px) {
            .header { padding: 10px 14px; }
            .header h1 { font-size: 16px; }
        }
    </style>
