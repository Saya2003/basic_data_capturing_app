<?php include __DIR__ . '/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Form</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/validation.js"></script>
</head>
<body>
    <h1><?= isset($client) ? 'Edit Client' : 'Add New Client' ?></h1>
    <form method="post" id="clientForm" action="">
        <div class="tabs">
            <button type="button" class="tab-btn active" onclick="showTab('general')">General</button>
            <?php if (isset($client)): ?>
            <button type="button" class="tab-btn" onclick="showTab('contacts')">Contact(s)</button>
            <?php endif; ?>
        </div>
        <div id="tab-general" class="tab-content active">
            <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <label>Name: <input type="text" name="name" value="<?= isset($client) ? htmlspecialchars($client['name']) : '' ?>" required></label><br>
            <?php if (isset($client)): ?>
                <label>Client code: <input type="text" value="<?= htmlspecialchars($client['client_code']) ?>" readonly></label><br>
            <?php endif; ?>
            <button type="submit">Save</button>
        </div>
        <?php if (isset($client)): ?>
        <div id="tab-contacts" class="tab-content">
            <h2>Contact(s)</h2>
            <?php if (empty($contacts)): ?>
                <p>No contacts found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th style="text-align:left;">Contact Full Name</th>
                            <th style="text-align:left;">Email</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact): ?>
                            <tr>
                                <td style="text-align:left;">
                                    <?= htmlspecialchars($contact['surname'] . ' ' . $contact['name']) ?>
                                </td>
                                <td style="text-align:left;">
                                    <?= htmlspecialchars($contact['email']) ?>
                                </td>
                                <td><a href="?controller=client&action=edit&id=<?= $client['id'] ?>&unlink_contact_id=<?= $contact['id'] ?>">Unlink</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <h3>Link a new contact</h3>
            <div style="margin-top:10px;">
                <select id="link_contact_id" name="link_contact_id" required>
                    <option value="">-- Select Contact --</option>
                    <?php foreach ($allContacts as $c): ?>
                        <?php
                        $alreadyLinked = false;
                        foreach ($contacts as $linked) {
                            if ($linked['id'] == $c['id']) { $alreadyLinked = true; break; }
                        }
                        ?>
                        <?php if (!$alreadyLinked): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['surname'] . ' ' . $c['name'] . ' (' . $c['email'] . ')') ?>
                        </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <button id="linkContactBtn" type="button">Link Contact</button>
            </div>
        </div>
        <?php endif; ?>
    </form>
    <script>
    function showTab(tab) {
        document.querySelectorAll('.tab-content').forEach(e => e.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(e => e.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        event.target.classList.add('active');
    }

    // AJAX unlink
    document.querySelectorAll('.ajax-unlink').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to unlink this contact?')) return;
            var clientId = this.getAttribute('data-client-id');
            var contactId = this.getAttribute('data-contact-id');
            fetch('public/ajax_link.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=unlink&client_id=' + encodeURIComponent(clientId) + '&contact_id=' + encodeURIComponent(contactId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row from the table
                    this.closest('tr').remove();
                    // If no more rows, show the no contacts message
                    if (document.querySelectorAll('#tab-contacts tbody tr').length === 0) {
                        document.querySelector('#tab-contacts tbody').innerHTML = '';
                        document.querySelector('#tab-contacts').insertAdjacentHTML('afterbegin', '<p>No contacts found.</p>');
                    }
                } else {
                    alert('Failed to unlink contact.');
                }
            });
        });
    });

    // AJAX link (no nested form)
    var linkContactBtn = document.getElementById('linkContactBtn');
    if (linkContactBtn) {
        linkContactBtn.addEventListener('click', function(e) {
            var select = document.getElementById('link_contact_id');
            var contactId = select.value;
            var clientId = <?= isset($client) ? (int)$client['id'] : 0 ?>;
            var contactText = select.options[select.selectedIndex].text;
            if (!contactId || !clientId) {
                alert('Invalid client or contact selection.');
                return;
            }
            fetch('public/ajax_link.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=link&client_id=' + encodeURIComponent(clientId) + '&contact_id=' + encodeURIComponent(contactId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the contact from the dropdown
                    select.remove(select.selectedIndex);
                    // Add the new contact to the table
                    var tbody = document.querySelector('#tab-contacts tbody');
                    if (!tbody) {
                        // If table doesn't exist, create it
                        var tableHtml = '<table><thead><tr><th style="text-align:left;">Contact Full Name</th><th style="text-align:left;">Email</th><th></th></tr></thead><tbody></tbody></table>';
                        document.querySelector('#tab-contacts').insertAdjacentHTML('afterbegin', tableHtml);
                        tbody = document.querySelector('#tab-contacts tbody');
                        // Remove "No contacts found." message if present
                        var noContacts = document.querySelector('#tab-contacts p');
                        if (noContacts) noContacts.remove();
                    }
                    // Parse contactText to get name and email
                    var match = contactText.match(/^(.*?) \((.*?)\)$/);
                    var fullName = match ? match[1] : contactText;
                    var email = match ? match[2] : '';
                    var newRow = document.createElement('tr');
                    newRow.innerHTML = '<td style="text-align:left;">' + fullName + '</td>' +
                        '<td style="text-align:left;">' + email + '</td>' +
                        '<td><a href="?controller=client&action=edit&id=' + clientId + '&unlink_contact_id=' + contactId + '">Unlink</a></td>';
                    tbody.appendChild(newRow);
                } else if (data.error) {
                    alert(data.error);
                } else {
                    alert('Failed to link contact.');
                }
            });
        });
    }
    </script>
</body>
</html>
