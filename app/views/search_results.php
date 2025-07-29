<?php include __DIR__ . '/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results</title>
    <link rel="stylesheet" href="/basic_data_capturing_app/public/css/style.css">
</head>
<body>
    <h1>Search Results for "<?= htmlspecialchars($query) ?>"</h1>
    <h2>Clients</h2>
    <?php if (empty($clients)): ?>
        <p>No clients found.</p>
    <?php else: ?>
        <div class="search-results">
            <?php foreach ($clients as $client): ?>
            <div class="result-card">
                <div class="result-field">
                    <span class="field-label">Name:</span>
                    <span class="field-value"><?= htmlspecialchars($client['name']) ?></span>
                </div>
                <div class="result-field">
                    <span class="field-label">Client code:</span>
                    <span class="field-value"><?= htmlspecialchars($client['client_code']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <h2>Contacts</h2>
    <?php if (empty($contacts)): ?>
        <p>No contacts found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Surname</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $contact): ?>
                <tr>
                    <td><?= htmlspecialchars($contact['name']) ?></td>
                    <td><?= htmlspecialchars($contact['surname']) ?></td>
                    <td><?= htmlspecialchars($contact['email']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
