<?php
// session_start(); // Removed to avoid duplicate session_start warnings
?>
<nav class="navbar">
    <ul>
        <?php if (!(basename($_SERVER['PHP_SELF']) === 'dashboard.php')): ?>
            <li><a href="/basic_data_capturing_app/dashboard.php">Dashboard</a></li>
        <?php endif; ?>
        <li><a href="/basic_data_capturing_app/index.php?controller=client&action=create">Add New Client</a></li>
        <li><a href="/basic_data_capturing_app/index.php?controller=contact&action=create">Add New Contact</a></li>
        <li><a href="/basic_data_capturing_app/index.php?controller=client&action=index">Client Dashboard</a></li>
        <li><a href="/basic_data_capturing_app/index.php?controller=contact&action=index">Contact Dashboard</a></li>
        <?php if (!isset($_SESSION['user_id'])): ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/basic_data_capturing_app/public/LogoutController.php">Logout</a></li>
        <?php endif; ?>
        <li style="flex:1;"></li>
        <li>
            <form id="searchForm" style="display:inline;position:relative;">
                <input type="text" id="searchInput" placeholder="Search clients or contacts..." style="padding-left:24px;padding-right:65px;height:28px;box-sizing:border-box;">
                <select id="searchType" style="position:absolute;right:2px;top:50%;transform:translateY(-50%);height:24px;width:60px;font-size:11px;padding:0 2px;border-radius:3px;">
                    <option value="all">All</option>
                    <option value="clients">Clients</option>
                    <option value="contacts">Contacts</option>
                    <option value="client_code">Code</option>
                    <option value="email">Email</option>
                </select>
                <button type="submit" id="searchBtn" style="position:absolute;left:0;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;line-height:1;">
                    <span style="font-size:14px;">🔍</span>
                </button>
                <div id="searchResults" style="position:absolute;top:38px;left:0;width:300px;z-index:1000;background:#fff;border:1px solid #ccc;display:none;"></div>
            </form>
        </li>
    </ul>
</nav>
<script>
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
const searchType = document.getElementById('searchType');
let searchTimeout;
searchInput.addEventListener('input', doInstantSearch);
searchType.addEventListener('change', doInstantSearch);
function doInstantSearch() {
    clearTimeout(searchTimeout);
    const query = searchInput.value.trim();
    const type = searchType.value;
    if (!query) {
        searchResults.style.display = 'none';
        searchResults.innerHTML = '';
        return;
    }
    searchTimeout = setTimeout(() => {
        fetch(`/basic_data_capturing_app/public/ajax_search.php?q=${encodeURIComponent(query)}&type=${encodeURIComponent(type)}`)
            .then(res => res.json())
            .then(data => {
                let html = '';
                if ((type === 'all' || type === 'clients') && data.clients.length) {
                    html += '<div style="padding:4px 8px;font-weight:bold;background:#f7f7f7;">Clients</div>';
                    html += '<ul style="margin:0;padding:0;list-style:none;">';
                    data.clients.forEach(c => {
                        html += `<li style=\"padding:4px 8px;\"><a href='/basic_data_capturing_app/index.php?controller=client&action=edit&id=${c.id}'>${c.name} (${c.client_code})</a></li>`;
                    });
                    html += '</ul>';
                }
                if ((type === 'all' || type === 'contacts') && data.contacts.length) {
                    html += '<div style="padding:4px 8px;font-weight:bold;background:#f7f7f7;">Contacts</div>';
                    html += '<ul style="margin:0;padding:0;list-style:none;">';
                    data.contacts.forEach(c => {
                        html += `<li style=\"padding:4px 8px;\"><a href='/basic_data_capturing_app/index.php?controller=contact&action=edit&id=${c.id}'>${c.surname} ${c.name} (${c.email})</a></li>`;
                    });
                    html += '</ul>';
                }
                if (!html) html = '<div style="padding:8px;">No results found.</div>';
                searchResults.innerHTML = html;
                searchResults.style.display = 'block';
            });
    }, 250);
}
document.addEventListener('click', function(e) {
    if (!searchResults.contains(e.target) && e.target !== searchInput && e.target !== searchType) {
        searchResults.style.display = 'none';
    }
});
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var query = searchInput.value.trim();
    var type = searchType.value;
    if (!query) return;
    window.location.href = '/basic_data_capturing_app/index.php?controller=search&action=results&query=' + encodeURIComponent(query) + '&type=' + encodeURIComponent(type);
});
</script>
<style>
.navbar {
    background: #FFFFFF;
    border-bottom: 2px solid #808080;
    margin-bottom: 20px;
}
.navbar ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
}
.navbar li {
    margin: 0 10px;
}
.navbar a {
    text-decoration: none;
    color: #1a59fb;
    font-weight: bold;
    padding: 10px 15px;
    display: block;
    border-radius: 4px;
    transition: background 0.2s, color 0.2s;
}
.navbar a:hover {
    background: #1a59fb;
    color: #FFFFFF;
}
</style>
