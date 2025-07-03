<?php include __DIR__ . '/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clients</title>
    <link rel="stylesheet" href="/basic_data_capturing_app/public/css/style.css">
</head>
<body>
    <h1>Clients</h1>
    <a href="?controller=client&action=create">Add New Client</a>
    <table>
        <thead>
            <tr>
                <th style="text-align:left;">Name</th>
                <th style="text-align:left;">Client code</th>
                <th style="text-align:center;">No. of linked contacts</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clients)): ?>
                <tr><td colspan="3" style="text-align:center;">No client(s) found.</td></tr>
            <?php else: ?>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td style="text-align:left;"><a href="?controller=client&action=edit&id=<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></a></td>
                        <td style="text-align:left;"><?= htmlspecialchars($client['client_code']) ?></td>
                        <td style="text-align:center;"><?= (int)$client['contact_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
