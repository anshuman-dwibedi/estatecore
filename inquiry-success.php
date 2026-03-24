<?php
/**
 * Public — Inquiry Submitted Confirmation Page
 */
require_once __DIR__ . '/core/bootstrap.php';

$propertyName = htmlspecialchars($_GET['property'] ?? 'the property');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inquiry Sent — EstateCore</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../core/ui/devcore.css">
<style>
  :root { --dc-accent: #e8a838; --dc-accent-2: #f0c060; --dc-accent-glow: rgba(232,168,56,0.2); }
  .success-wrap {
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    padding: 24px;
  }
  .success-card {
    max-width: 520px; width: 100%;
    text-align: center;
  }
  .check-circle {
    width: 80px; height: 80px;
    background: rgba(34,211,160,0.12);
    border: 2px solid var(--dc-success);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.2rem;
    margin: 0 auto 24px;
    animation: dc-pulse 2s ease infinite;
  }
  .step-row {
    display: flex; align-items: flex-start; gap: 14px;
    text-align: left;
    padding: 14px 0;
    border-bottom: 1px solid var(--dc-border);
  }
  .step-row:last-child { border-bottom: none; }
  .step-icon {
    width: 36px; height: 36px;
    background: var(--dc-accent-glow);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
  }
</style>
</head>
<body>

<nav class="dc-nav">
  <div class="dc-nav__brand"><i class="dc-icon dc-icon-home"></i> Estate<span>Core</span></div>
  <div class="dc-nav__links">
    <a href="index.php" class="dc-nav__link">← All Listings</a>
  </div>
</nav>

<div class="success-wrap">
  <div class="success-card dc-animate-fade-up">

    <div class="check-circle"><i class="dc-icon dc-icon-check"></i></div>

    <h1 class="dc-h2" style="margin-bottom:8px">Inquiry Sent!</h1>
    <p class="dc-body" style="color:var(--dc-text-2);max-width:380px;margin:0 auto 28px">
      Your inquiry about <strong style="color:var(--dc-text)"><?= $propertyName ?></strong> has been received. An agent will be in touch soon.
    </p>

    <div class="dc-card-solid" style="text-align:left;margin-bottom:28px">
      <div class="dc-label" style="margin-bottom:12px">What Happens Next</div>
      <div class="step-row">
        <div class="step-icon"><i class="dc-icon dc-icon-inbox"></i></div>
        <div>
          <div style="font-weight:600;font-size:0.9rem">Inquiry Received</div>
          <p class="dc-caption" style="color:var(--dc-text-3);margin-top:2px">Your message is now in our system and has been flagged as new for our agents.</p>
        </div>
      </div>
      <div class="step-row">
        <div class="step-icon"><i class="dc-icon dc-icon-smartphone"></i></div>
        <div>
          <div style="font-weight:600;font-size:0.9rem">Agent Contact</div>
          <p class="dc-caption" style="color:var(--dc-text-3);margin-top:2px">An agent will review your inquiry and contact you by email or phone within 24 hours.</p>
        </div>
      </div>
      <div class="step-row">
        <div class="step-icon"><i class="dc-icon dc-icon-home"></i></div>
        <div>
          <div style="font-weight:600;font-size:0.9rem">Schedule a Viewing</div>
          <p class="dc-caption" style="color:var(--dc-text-3);margin-top:2px">We'll arrange a convenient time for you to visit the property in person or virtually.</p>
        </div>
      </div>
    </div>

    <div class="dc-flex" style="gap:12px;justify-content:center;flex-wrap:wrap">
      <a href="index.php" class="dc-btn dc-btn-primary" style="background:var(--dc-accent);border-color:var(--dc-accent)">
        Browse More Listings
      </a>
      <a href="javascript:history.back()" class="dc-btn dc-btn-ghost">
        ← Back to Property
      </a>
    </div>

    <p class="dc-caption" style="margin-top:24px;color:var(--dc-text-3)">
      EstateCore · Real-time property listings
    </p>

  </div>
</div>

<script src="../../core/ui/devcore.js"></script>
<script>
  // Auto-redirect to listings after 15 seconds
  setTimeout(() => {
    Toast.info('Redirecting to listings in 5 seconds…', 4500);
    setTimeout(() => window.location.href = 'index.php', 5000);
  }, 10000);
</script>
</body>
</html>
