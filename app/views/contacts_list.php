<?php include __DIR__ . '/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contacts</title>
    <link rel="stylesheet" href="/basic_data_capturing_app/public/css/style.css">
</head>
<body>
    <h1>Contacts</h1>
    <a href="?controller=contact&action=create">Add New Contact</a>
    <table>
        <thead>
            <tr>
                <th style="text-align:left;">Name</th>
                <th style="text-align:left;">Surname</th>
                <th style="text-align:left;">Email address</th>
                <th style="text-align:center;">No. of linked clients</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($contacts)): ?>
                <tr><td colspan="4" style="text-align:center;">No contact(s) found.</td></tr>
            <?php else: ?>
                <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td style="text-align:left;"><a href="?controller=contact&action=edit&id=<?= $contact['id'] ?>"><?= htmlspecialchars($contact['name']) ?></a></td>
                        <td style="text-align:left;"><?= htmlspecialchars($contact['surname']) ?></td>
                        <td style="text-align:left;"><?= htmlspecialchars($contact['email']) ?></td>
                        <td style="text-align:center;"><?= (int)$contact['client_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
