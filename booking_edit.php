<?php
require __DIR__ . '/lib/bootstrap.php';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET' && $method !== 'POST') { http_response_code(405); exit('Method not allowed'); }
if ($method === 'POST' && !csrf_valid($_POST)) { http_response_code(403); exit('Invalid CSRF token'); }

$source = $method === 'POST' ? $_POST : $_GET;
$ref = is_string($source['ref'] ?? null) ? trim($source['ref']) : '';
$index = find_booking_index($_SESSION['bookings'], $ref);
$booking = $index === null ? null : $_SESSION['bookings'][$index];
$workshop = $booking === null ? null : booking_workshop($booking, $workshops);

if ($method === 'POST' && $booking !== null && $workshop !== null) {
    $field = static fn(string $k): string => is_string($_POST[$k] ?? null) ? $_POST[$k] : '';
    $result = validate_booking([
        'full_name' => $field('full_name'),
        'email' => $field('email'),
        'quantity' => $field('quantity'),
        'workshop_id' => $workshop['id'],
    ], $workshops);
    if ($result['errors']) {
        $_SESSION['edit_form'] = [
            'ref' => $ref,
            'errors' => $result['errors'],
            'old' => [
                'full_name' => $result['data']['full_name'],
                'email' => $result['data']['email'],
                'quantity' => trim($field('quantity')),
            ],
        ];
        redirect_to('booking_edit.php?ref=' . urlencode($ref));
    }
    $d = $result['data'];
    $_SESSION['bookings'][$index] = array_merge($booking, [
        'full_name' => $d['full_name'],
        'email' => $d['email'],
        'quantity' => $d['quantity'],
        'workshop_id' => $workshop['id'],
        'workshop' => $workshop['title'],
        'amount' => $workshop['fee'] * $d['quantity'],
    ]);
    unset($_SESSION['edit_form']);
    $_SESSION['flash'] = 'Booking ' . $ref . ' updated successfully.';
    redirect_to('booking_detail.php?ref=' . urlencode($ref));
}

$errors = [];
$old = [];
$form = $_SESSION['edit_form'] ?? null;
unset($_SESSION['edit_form']);
if (is_array($form) && ($form['ref'] ?? null) === $ref) {
    $errors = $form['errors'];
    $old = $form['old'];
}
if ($booking === null || $workshop === null) http_response_code(404);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Booking</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= e($theme) ?>">
<main class="wrap">
<header>
<h1>Edit Booking</h1>
<nav>
<a href="index.php">Catalog</a>
<a href="dashboard.php">My bookings</a>
</nav>
</header>
<?php if ($booking === null || $workshop === null): ?>
<div class="panel">
<h2>Booking not found</h2>
<p>No booking in your current session matches this reference. Nothing was changed.</p>
<a class="btn" href="dashboard.php">Back to My Bookings</a>
</div>
<?php else: ?>
<form method="post" action="booking_edit.php" class="panel form-stack wide" novalidate>
<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
<input type="hidden" name="ref" value="<?= e($booking['ref']) ?>">
<label>Booking Reference <input value="<?= e($booking['ref']) ?>" readonly>
</label>
<label>Workshop <input value="<?= e($workshop['title']) ?>" readonly>
</label>
<label>Full Name <input name="full_name" value="<?= e($old['full_name'] ?? $booking['full_name']) ?>" placeholder="Nguyen Van A">
</label>
<small class="error">
<?= e($errors['full_name'] ?? '') ?>
</small>
<label>Email <input name="email" value="<?= e($old['email'] ?? $booking['email']) ?>" placeholder="user@example.com">
</label>
<small class="error">
<?= e($errors['email'] ?? '') ?>
</small>
<label>Tickets (1–4) <input name="quantity" type="number" min="1" max="4" value="<?= e($old['quantity'] ?? $booking['quantity']) ?>">
</label>
<small class="error">
<?= e($errors['quantity'] ?? '') ?>
</small>
<div class="bordered">
<p><strong>Current total: <?= number_format($booking['amount']) ?> VND</strong></p>
<p class="muted">Unit fee: <?= number_format($workshop['fee']) ?> VND – Total will be recalculated after saving.</p>
</div>
<div class="actions">
<button>Save Changes</button>
<a class="btn btn-secondary" href="booking_detail.php?ref=<?= urlencode($booking['ref']) ?>">Cancel</a>
</div>
</form>
<p class="muted"><?= $errors ? 'Invalid data is not saved to the Session.' : 'The server validates name, email and ticket quantity, verifies the CSRF token and updates the correct booking in $_SESSION.' ?></p>
<?php endif; ?>
</main>
</body>
</html>
