$(document).ready(function () {
    // ✅ Load notifikasi saat halaman pertama kali dimuat
    loadNotifications();

    // Delegasi klik untuk notifikasi individual
    $(document).on('click', '.notification-item', function () {
        const id = $(this).data('id');

        $.ajax({
            url: 'controller/mark_notification_read.php',
            method: 'POST',
            data: { id: id },
            success: function (response) {
                const res = JSON.parse(response);
                if (res.status === 'success') {
                    loadNotifications(); // reload daftar & count
                }
            }
        });
    });

    // Load ulang saat dropdown dibuka (opsional)
    $('#page-header-notifications-dropdown').on('click', function () {
        loadNotifications();
    });

    function loadNotifications() {
        $.ajax({
            url: 'controller/ajax_notification.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                let unreadCount = 0;
                let html = `
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0">Notifications</h6>
                            </div>
                            <div class="col-auto">
                              
                            </div>
                        </div>
                    </div>
                    <div data-simplebar style="max-height: 230px;">`;

                data.forEach(item => {
                    if (item.is_read == 0) unreadCount++;

                    html += `
<a href="javascript:void(0);" class="text-reset notification-item ${item.is_read == 0 ? 'fw-bold' : ''}" data-id="${item.id}">
    <div class="d-flex">
        ${
            item.image_url
                ? `<img src="${item.image_url}" class="me-3 rounded-circle avatar-xs" alt="user-pic">`
                : `<div class="avatar-xs me-3">
                    <span class="avatar-title bg-primary rounded-circle font-size-16">
                        <i class="bx ${item.icon ?? 'bx-bell'}"></i>
                    </span>
                </div>`
        }
        <div class="flex-grow-1">
            <h6 class="mb-1">${item.title}</h6>
            <div class="font-size-12 text-muted">
                <p class="mb-1">${item.message}</p>
                <p class="mb-0"><i class="mdi mdi-clock-outline"></i> ${timeAgo(item.created_at)}</p>
            </div>
        </div>
    </div>
</a>`;
                });

                html += `</div>
                    <div class="p-2 border-top d-grid">
                       
                    </div>`;

                $('#notification-list').html(html);
                $('#notification-count').text(unreadCount);
            },
            error: function () {
                console.error('Gagal mengambil notifikasi.');
            }
        });
    }

    function timeAgo(datetime) {
        const now = new Date();
        const then = new Date(datetime);
        const diff = Math.floor((now - then) / 60000); // dalam menit

        if (diff < 60) return `${diff} min ago`;
        const hours = Math.floor(diff / 60);
        return `${hours} hours ago`;
    }
});
