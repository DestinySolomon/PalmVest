<?php
$stakes = $conn->query("
    SELECT *, DATEDIFF(matures_at, NOW()) AS days_left
    FROM oil_stakes
    WHERE user_id = $user_id
    ORDER BY id DESC
");
?>

<table class="table table-dark table-hover">
    <thead>
        <tr>
            <th>Qty</th>
            <th>Buy Price</th>
            <th>Days Left</th>
        </tr>
    </thead>

    <tbody>
    <?php while ($s = $stakes->fetch_assoc()): ?>
        <tr>
            <td><?= $s['quantity'] ?></td>
            <td>₦<?= number_format($s['purchase_price']) ?></td>
            <td>
                <?php if ($s['days_left'] <= 0): ?>
                    <span class="text-success">Matured</span>
                <?php else: ?>
                    <?= $s['days_left'] ?> days left
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
