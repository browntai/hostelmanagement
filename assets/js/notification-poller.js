/**
 * Notification and Counter Poller
 * Handles real-time updates for the dashboard
 */

function updateSystemCounts() {
    // Determine relative path to API
    var path = window.location.pathname;
    var isSubDir = path.includes('/client/') || path.includes('/landlord/') || path.includes('/admin/');
    var apiUrl = (isSubDir ? '../' : '') + 'api/get-system-counts.php';

    $.ajax({
        url: apiUrl,
        method: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.error) return;

            // Update Notification Bell
            const bellBadge = $('.notify-no, .nav-link .badge-notify'); // Adjust based on actual UI classes
            if (data.unread_notifications > 0) {
                if (bellBadge.length) {
                    bellBadge.text(data.unread_notifications).show();
                } else {
                    // If no badge exists, we might need to append one to the bell icon
                    $('.fa-bell').parent().append('<span class="badge badge-pill badge-danger badge-notify" style="position: absolute; top: 5px; right: 5px; font-size: 10px;">' + data.unread_notifications + '</span>');
                }
            } else {
                bellBadge.hide();
            }

            // Update Sidebar Badges
            updateBadge('#sidebar-messages-count', data.unread_messages);
            updateBadge('#sidebar-bookings-count', data.pending_bookings);
            updateBadge('#sidebar-clients-count', data.total_clients);
            updateBadge('#sidebar-notifications-count', data.unread_notifications);

            // For Dashboard Cards (if they exist on the page)
            updateText('#dash-pending-bookings', data.pending_bookings);
            updateText('#dash-unread-messages', data.unread_messages);
            updateText('#dash-total-clients', data.total_clients);
            updateText('#dash-total-rooms', data.total_rooms);
            updateText('#dash-occupied-rooms', data.occupied_rooms);

            // Trigger Toasts for New Notifications
            if (data.unread_list && data.unread_list.length > 0) {
                let toastedIds = JSON.parse(sessionStorage.getItem('toastedIds') || '[]');
                let isInitialLoad = !sessionStorage.getItem('toastedIds');

                // Reverse to show oldest first if multiple new ones
                data.unread_list.reverse().forEach(notif => {
                    if (!toastedIds.includes(notif.id)) {
                        // Only show toast if it's not the very first load of the page 
                        // (to avoid spamming all existing unread on refresh)
                        if (!isInitialLoad) {
                            showSystemToast(notif.title, notif.message);
                        }
                        toastedIds.push(notif.id);
                    }
                });

                // Keep only the last 50 IDs to avoid sessionStorage bloat
                if (toastedIds.length > 50) toastedIds = toastedIds.slice(-50);
                sessionStorage.setItem('toastedIds', JSON.stringify(toastedIds));
            }
        },
        error: function (err) {
            console.error('Failed to fetch counts:', err);
        }
    });
}

function showSystemToast(title, message) {
    if (typeof Toastify === 'function') {
        Toastify({
            text: title + ": " + message,
            duration: 5000,
            close: true,
            gravity: "top",
            position: "center",
            style: { background: "linear-gradient(to center, #36D1DC, #5B86E5)" },
            stopOnFocus: true
        }).showToast();
    }
}

function updateBadge(selector, count) {
    const el = $(selector);
    if (el.length) {
        // Smart Suppression Logic
        const storageKey = 'suppressed_' + selector;
        let suppressed = parseInt(localStorage.getItem(storageKey)) || 0;

        // If count has dropped (items processed), update the suppression baseline
        // This ensures that if it goes from 5 -> 0 -> 1, the new 1 will be shown (as 1 > 0)
        // rather than hidden (as 1 < 5)
        if (count < suppressed) {
            suppressed = count;
            localStorage.setItem(storageKey, suppressed);
        }

        if (count > 0 && count > suppressed) {
            el.text(count).show();
        } else {
            el.hide();
        }
    }
}

function updateText(selector, text) {
    const el = $(selector);
    if (el.length) {
        el.text(text);
    }
}

// Global function to be called by onclick events
window.suppressBadge = function (selector) {
    const el = $(selector);
    if (el.length) {
        let count = parseInt(el.text()) || 0;
        localStorage.setItem('suppressed_' + selector, count);
        el.hide();
    }
};

// Start polling
$(document).ready(function () {
    updateSystemCounts(); // Initial load
    setInterval(updateSystemCounts, 15000); // Every 15 seconds
});
