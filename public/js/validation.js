// JavaScript validation for client and contact forms
document.addEventListener('DOMContentLoaded', function() {
    var clientForm = document.getElementById('clientForm');
    if (clientForm) {
        clientForm.addEventListener('submit', function(e) {
            var name = clientForm.querySelector('input[name="name"]');
            if (!name.value.trim()) {
                alert('Name is required.');
                name.focus();
                e.preventDefault();
            }
        });
    }
    var contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            var name = contactForm.querySelector('input[name="name"]');
            var surname = contactForm.querySelector('input[name="surname"]');
            var email = contactForm.querySelector('input[name="email"]');
            if (!name.value.trim() || !surname.value.trim() || !email.value.trim()) {
                alert('All fields are required.');
                e.preventDefault();
                return;
            }
            var emailPattern = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
            if (!emailPattern.test(email.value.trim())) {
                alert('Invalid email address.');
                email.focus();
                e.preventDefault();
            }
        });
    }
    document.querySelectorAll('.ajax-unlink').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to unlink?')) { return; }
            var clientId = this.dataset.clientId;
            var contactId = this.dataset.contactId;
            fetch('/ajax_link.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=unlink&client_id=' + encodeURIComponent(clientId) + '&contact_id=' + encodeURIComponent(contactId)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.closest('tr').remove();
                } else {
                    alert('Failed to unlink.');
                }
            });
        });
    });
});
