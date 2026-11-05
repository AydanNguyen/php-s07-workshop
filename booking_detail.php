<?php
require __DIR__ . '/lib/bootstrap.php';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') { http_response_code(405); exit('Method not allowed'); }
$ref = $_GET['ref'] ?? '';
$ref = is_string($ref) ? trim($ref) : '';
$index = find_booking_index($_SESSION['bookings'], $ref);
$booking = $index === null ? null : $_SESSION['bookings'][$index];
$workshop = $booking === null ? null : booking_workshop($booking, $workshops);
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
if ($booking === null) http_response_code(404);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Booking Details</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= e($theme) ?>">
<main class="wrap">
<header>
<h1>Booking Details</h1>
<nav>
<a href="index.php">Catalog</a>
<a href="dashboard.php">My bookings</a>
</nav>
</header>
<?php if ($flash): ?>
<p class="notice">
<?= e($flash) ?>
</p>
<?php endif; ?>
<?php if ($booking === null): ?>
<div class="panel">
<h2>Booking not found</h2>
<p>No booking in your current session matches this reference.</p>
<a class="btn" href="dashboard.php">Back to My Bookings</a>
</div>
<?php else: ?>
<div class="panel">
<dl class="detail">
<dt>Booking Reference</dt>
<dd><span class="ref-badge"><?= e($booking['ref']) ?></span></dd>
<dt>Attendee</dt>
<dd><?= e($booking['full_name']) ?></dd>
<dt>Email</dt>
<dd><?= e($booking['email']) ?></dd>
<dt>Workshop</dt>
<dd><?= e($booking['workshop']) ?></dd>
<dt>Quantity</dt>
<dd><?= e($booking['quantity']) ?> ticket<?= (int)$booking['quantity'] === 1 ? '' : 's' ?></dd>
<dt>Unit Fee</dt>
<dd><?= $workshop ? number_format($workshop['fee']) . ' VND' : 'N/A' ?></dd>
<dt>Total Amount</dt>
<dd><strong><?= number_format($booking['amount']) ?> VND</strong></dd>
</dl>
<div class="actions bordered">
<a class="btn btn-warning" href="booking_edit.php?ref=<?= urlencode($booking['ref']) ?>">Edit This Booking</a>
<a class="btn btn-secondary" href="dashboard.php">Back to My Bookings</a>
</div>
</div>
<p class="muted">Booking information is read from the current PHP session.</p>
<?php endif; ?>
</main>
</body>
</html>
