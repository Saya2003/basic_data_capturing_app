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
    <div style="margin: 20px 0; display: flex; gap: 10px;">
        <button onclick="window.location.href='?controller=client&action=create'" type="button" class="primary-button">Add New Client</button>
        <button id="editButton" type="button" class="secondary-button">Delete Clients</button>
    </div>
    <table id="clientsTable">
        <thead>
            <tr>
                <th style="width: 40px;" class="delete-column">&nbsp;</th>
                <th style="text-align:left;">Name</th>
                <th style="text-align:left;">Client code</th>
                <th style="text-align:center;">No. of linked contacts</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clients)): ?>
                <tr><td colspan="4" style="text-align:center;">No client(s) found.</td></tr>
            <?php else: ?>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td class="delete-column" style="text-align: center;">
                            <input type="checkbox" class="delete-checkbox" data-client-id="<?= $client['id'] ?>">
                        </td>
                        <td style="text-align:left;"><a href="?controller=client&action=edit&id=<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></a></td>
                        <td style="text-align:left;"><?= htmlspecialchars($client['client_code']) ?></td>
                        <td style="text-align:center;"><?= (int)$client['contact_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <div id="deleteActions" style="margin-top: 20px; display: none;">
        <button type="button" class="delete-button">Delete Selected</button>
        <button type="button" class="secondary-button cancel-edit">Cancel</button>
    </div>
    <script src="/basic_data_capturing_app/public/js/client-list.js"></script>
</body>
</html>
