<?php include __DIR__ . '/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Form</title>
    <link rel="stylesheet" href="/basic_data_capturing_app/public/css/style.css">
    <script src="/js/validation.js"></script>
</head>
<body>
    <h1><?= isset($contact) ? 'Edit Contact' : 'Add New Contact' ?></h1>
    <form method="post" id="contactForm" action="">
        <div class="tabs">
            <button type="button" class="tab-btn active" onclick="showTab('general')">General</button>
            <?php if (isset($contact)): ?>
            <button type="button" class="tab-btn" onclick="showTab('clients')">Client(s)</button>
            <?php endif; ?>
        </div>
        <div id="tab-general" class="tab-content active">
            <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <div class="form-group">
                <label>Name: <input type="text" name="name" id="contactName" value="<?= isset($contact) ? htmlspecialchars($contact['name']) : '' ?>" required></label>
                <div id="nameError" class="error-message"></div>
            </div>
            <div class="form-group">
                <label>Surname: <input type="text" name="surname" id="contactSurname" value="<?= isset($contact) ? htmlspecialchars($contact['surname']) : '' ?>" required></label>
                <div id="surnameError" class="error-message"></div>
            </div>
            <div class="form-group">
                <label>Email: <input type="email" name="email" id="contactEmail" value="<?= isset($contact) ? htmlspecialchars($contact['email']) : '' ?>" required></label>
                <div id="emailError" class="error-message"></div>
            </div>
            <button type="submit">Save</button>
        </div>
        <?php if (isset($contact)): ?>
        <div id="tab-clients" class="tab-content">
            <h2>Client(s)</h2>
            <?php if (empty($clients)): ?>
                <p>No clients found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th style="text-align:left;">Client name</th>
                            <th style="text-align:left;">Client code</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td style="text-align:left;">
                                    <?= htmlspecialchars($client['name']) ?>
                                </td>
                                <td style="text-align:left;">
                                    <?= htmlspecialchars($client['client_code']) ?>
                                </td>
                                <td><a href="#" class="ajax-unlink-client" data-client-id="<?= $client['id'] ?>" data-contact-id="<?= $contact['id'] ?>">Unlink</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <h3>Link a new client</h3>
            <div style="margin-top:10px;">
                <select id="link_client_id" name="link_client_id" required>
                    <option value="">-- Select Client --</option>
                    <?php foreach ($allClients as $c): ?>
                        <?php
                        $alreadyLinked = false;
                        foreach ($clients as $linked) {
                            if ($linked['id'] == $c['id']) { $alreadyLinked = true; break; }
                        }
                        ?>
                        <?php if (!$alreadyLinked): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['name'] . ' (' . $c['client_code'] . ')') ?>
                        </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <button id="linkClientBtn" type="button">Link Client</button>
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

    // AJAX unlink client
    document.querySelectorAll('.ajax-unlink-client').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to unlink this client?')) return;
            var clientId = this.getAttribute('data-client-id');
            var contactId = this.getAttribute('data-contact-id');
            fetch('public/ajax_link_contact.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=unlink&client_id=' + encodeURIComponent(clientId) + '&contact_id=' + encodeURIComponent(contactId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.closest('tr').remove();
                    if (document.querySelectorAll('#tab-clients tbody tr').length === 0) {
                        document.querySelector('#tab-clients tbody').innerHTML = '';
                        document.querySelector('#tab-clients').insertAdjacentHTML('afterbegin', '<p>No clients found.</p>');
                    }
                } else {
                    alert('Failed to unlink client.');
                }
            });
        });
    });

    // AJAX link client (no nested form)
    var linkClientBtn = document.getElementById('linkClientBtn');
    if (linkClientBtn) {
        linkClientBtn.addEventListener('click', function(e) {
            var select = document.getElementById('link_client_id');
            var clientId = select.value;
            var contactId = <?= isset($contact) ? (int)$contact['id'] : 0 ?>;
            if (!clientId || !contactId) {
                alert('Invalid client or contact selection.');
                return;
            }
            if (!confirm('Are you sure you want to link this client to the contact?')) return;
            fetch('public/ajax_link_contact.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=link&client_id=' + encodeURIComponent(clientId) + '&contact_id=' + encodeURIComponent(contactId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else if (data.error) {
                    alert(data.error);
                } else {
                    alert('Failed to link client.');
                }
            });
        });
    }
    </script>
</body>
</html>
