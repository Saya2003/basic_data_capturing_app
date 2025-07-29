document.addEventListener('DOMContentLoaded', function() {
    const editButton = document.getElementById('editButton');
    const deleteActions = document.getElementById('deleteActions');
    const deleteColumns = document.querySelectorAll('.delete-column');
    const clientsTable = document.getElementById('clientsTable');
    const deleteButton = document.querySelector('.delete-button');
    const cancelButton = document.querySelector('.cancel-edit');

    let isEditMode = false;

    function initializeState() {
        console.log('Initializing client list state...');
        deleteColumns.forEach(col => col.style.display = 'none'); // Hide the entire column

        // Ensure checkboxes are hidden, unchecked, and disabled initially
        document.querySelectorAll('.delete-checkbox').forEach(checkbox => {
             checkbox.checked = false;
             checkbox.disabled = true; // Disabled from the start
             checkbox.style.opacity = '0'; // Hidden visually
             checkbox.style.pointerEvents = 'none'; // Not clickable
             checkbox.style.transition = 'opacity 0.3s ease'; // Smooth transition
        });

        if (deleteActions) {
            deleteActions.style.display = 'none';
        }
        if (deleteButton) {
            deleteButton.disabled = true;
        }
        if (editButton) {
            editButton.textContent = 'Delete Clients';
            editButton.classList.remove('active');
        }
        console.log('Client list state initialized.');
    }

    function toggleEditMode() {
        isEditMode = !isEditMode;
        console.log('Toggling edit mode. New state:', isEditMode);

        // Toggle visibility of delete columns (the <td> elements)
        deleteColumns.forEach(col => {
            col.style.display = isEditMode ? 'table-cell' : 'none';
        });

        const currentCheckboxes = document.querySelectorAll('.delete-checkbox');
        currentCheckboxes.forEach(checkbox => {
            if (isEditMode) {
                // When entering edit mode: enable, make visible, make clickable
                checkbox.disabled = false;
                checkbox.style.opacity = '1';
                checkbox.style.pointerEvents = 'auto';
            } else {
                // When exiting edit mode: uncheck, disable, hide, make unclickable
                checkbox.checked = false;
                checkbox.disabled = true;
                checkbox.style.opacity = '0';
                checkbox.style.pointerEvents = 'none';
            }
        });

        if (deleteActions) {
            deleteActions.style.display = isEditMode ? 'block' : 'none';
        }

        if (editButton) {
            editButton.textContent = isEditMode ? 'Cancel' : 'Delete Clients';
            editButton.classList.toggle('active');
        }

        // The delete button should be disabled unless in edit mode AND at least one checkbox is checked
        if (deleteButton) {
            const atLeastOneChecked = Array.from(currentCheckboxes).some(cb => cb.checked);
            deleteButton.disabled = !(isEditMode && atLeastOneChecked);
        }
        console.log('Edit mode toggled. Current delete button disabled:', deleteButton ? deleteButton.disabled : 'N/A');
    }

    // --- Setup Event Handlers Function (No changes needed here from previous version) ---
    function setupEventHandlers() {
        if (editButton) {
            editButton.addEventListener('click', function() {
                console.log('Edit button clicked.');
                // Prompt only when *entering* delete mode for the first time
                if (!isEditMode && !sessionStorage.getItem('hasShownDeletePrompt')) {
                    alert('Select the clients you want to delete by checking the boxes.');
                    sessionStorage.setItem('hasShownDeletePrompt', 'true');
                }
                toggleEditMode();
            });
        }

        if (cancelButton) {
            cancelButton.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Cancel button clicked.');
                toggleEditMode();
            });
        }

        if (clientsTable) {
            clientsTable.addEventListener('change', function(event) {
                if (event.target.classList.contains('delete-checkbox')) {
                    console.log('Checkbox changed via delegation:', event.target.checked, 'Client ID:', event.target.dataset.clientId);
                    if (deleteButton) {
                        const currentCheckboxes = document.querySelectorAll('.delete-checkbox');
                        const atLeastOneChecked = Array.from(currentCheckboxes).some(cb => cb.checked);
                        deleteButton.disabled = !(isEditMode && atLeastOneChecked); // Only enable if in edit mode AND checked
                        console.log('Delete button disabled status after checkbox change:', deleteButton.disabled);
                    }
                }
            });
        }

        if (deleteButton) {
            deleteButton.addEventListener('click', function() {
                console.log('Delete Selected button clicked.');
                const selectedCheckboxes = document.querySelectorAll('.delete-checkbox:checked');

                if (selectedCheckboxes.length === 0) {
                    alert('Please select the clients you want to delete.');
                    return;
                }

                const selectedCount = selectedCheckboxes.length;
                const confirmMessage = selectedCount === 1
                    ? 'Are you sure you want to delete this client?'
                    : `Are you sure you want to delete these ${selectedCount} clients?`;

                if (!confirm(confirmMessage + ' This action cannot be undone.')) {
                    return;
                }

                const clientIds = Array.from(selectedCheckboxes).map(cb => cb.dataset.clientId);
                console.log('Client IDs to delete:', clientIds);

                fetch('/basic_data_capturing_app/index.php?controller=client&action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ clientIds: clientIds })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().catch(() => {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        console.log('Deletion successful on server. Removing rows...');
                        selectedCheckboxes.forEach(checkbox => {
                            const row = checkbox.closest('tr');
                            if (row) {
                                row.remove();
                            }
                        });

                        const message = selectedCount === 1
                            ? 'Client has been deleted successfully.'
                            : `${selectedCount} clients have been deleted successfully.`;
                        alert(message);

                        toggleEditMode(); // Reset the UI after deletion

                        if (document.querySelectorAll('tbody tr').length === 0) {
                            const tbody = document.querySelector('tbody');
                            if (tbody) {
                                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No client(s) found.</td></tr>';
                            }
                        }
                    } else {
                        alert(data.error || 'Failed to delete clients. Please try again.');
                        console.error('Server reported error:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Fetch error during client deletion:', error);
                    alert('An error occurred while deleting clients. Please try again.');
                });
            });
        }
    }

    initializeState();
    setupEventHandlers();
});