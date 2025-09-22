<?php
$enccurrentPage = $_GET['scr'] ?? 'home';
$currentPage = base64url_decode($enccurrentPage);
// echo "Current Page: $currentPage"; // Debugging line, remove in production

// Asumsikan level user disimpan dalam session atau variabel
// Sesuaikan dengan sistem authentication Anda
$userLevel = $_SESSION['level'] ?? 'user'; // atau ambil dari database/session
?>

<div id="sidebar-menu">
    <ul class="metismenu list-unstyled" id="side-menu">
        <li class="menu-title" key="t-menu">Dashboard</li>

        <li class="<?= in_array($currentPage, ['home', 'rentals']) ? 'mm-active' : '' ?>">
            <a href="javascript:void(0);" class="has-arrow waves-effect" aria-expanded="false">
                <i class="bx bx-home"></i>
                <span key="t-dashboards">Dashboards</span>
            </a>
            <ul class="sub-menu" aria-expanded="false" style="display:none;">
                <li><a href="./index.php?q=<?=encrypt('home')?>&scr=<?=base64url_encode('home')?>" class="<?= $currentPage === 'home' ? 'active' : '' ?>" key="t-default">Home</a></li>
                <li><a href="./index.php?q=<?=encrypt('rentals')?>&scr=<?=base64url_encode('rentals')?>" class="<?= $currentPage === 'rentals' ? 'active' : '' ?>" key="t-saas">Rentals</a></li>
            </ul>
        </li>

        <li class="menu-title" key="t-apps">Apps</li>

        <?php if ($userLevel === 'admin'): ?>
        <!-- Menu Master - hanya tampil untuk admin -->
        <li class="<?= in_array($currentPage, ['unit_rentals', 'pricelist', 'product', 'customer']) ? 'mm-active' : '' ?>">
            <a href="javascript:void(0);" class="has-arrow waves-effect" aria-expanded="false">
                <i class="bx bx-cog"></i>
                <span key="t-dashboards">Master</span>
            </a>
            <ul class="sub-menu" aria-expanded="false" style="display:none;">
                <li><a href="./index.php?q=<?=encrypt('unit_rentals')?>&scr=<?=base64url_encode('unit_rentals')?>" class="<?= $currentPage === 'unit_rentals' ? 'active' : '' ?>" key="t-tui-calendar">Unit Rentals</a></li>
                <li><a href="./index.php?q=<?=encrypt('category')?>&scr=<?=base64url_encode('category')?>" class="<?= $currentPage === 'category' ? 'active' : '' ?>" key="t-full-calendar">Category</a></li>
                <li><a href="./index.php?q=<?=encrypt('pricelist')?>&scr=<?=base64url_encode('pricelist')?>" class="<?= $currentPage === 'pricelist' ? 'active' : '' ?>" key="t-full-calendar">Pricelist</a></li>
                <li><a href="./index.php?q=<?=encrypt('product')?>&scr=<?=base64url_encode('product')?>" class="<?= $currentPage === 'product' ? 'active' : '' ?>" key="t-full-calendar">Product</a></li>
                <li><a href="./index.php?q=<?=encrypt('promo')?>&scr=<?=base64url_encode('promo')?>" class="<?= $currentPage === 'promo' ? 'active' : '' ?>" key="t-full-calendar">Promo</a></li>
            </ul>
        </li>
        <?php endif; ?>

        <!-- Fixed: Added active class for single menu items -->
        <li class="<?= $currentPage === 'transactions' ? 'mm-active' : '' ?>">
            <a href="./index.php?q=<?=encrypt('transactions')?>&scr=<?=base64url_encode('transactions')?>" class="waves-effect <?= $currentPage === 'transactions' ? 'active' : '' ?>">
                <i class="bx bx-receipt"></i>
                <span key="t-chat">Transactions</span>
            </a>
        </li>
           <?php if ($userLevel === 'admin'): ?>
          <!-- Fixed: Added active class for single menu items -->
        <li class="<?= $currentPage === 'trash' ? 'mm-active' : '' ?>">
            <a href="./index.php?q=<?=encrypt('trash')?>&scr=<?=base64url_encode('trash')?>" class="waves-effect <?= $currentPage === 'trash' ? 'active' : '' ?>">
                <i class="bx bx-trash"></i>
                <span key="t-chat">Trash List</span>
            </a>
        </li>
<?php endif; ?>
        <!-- <li class="<?= $currentPage === 'closing' ? 'mm-active' : '' ?>">
            <a href="./index.php?q=<?=encrypt('closing')?>&scr=<?=base64url_encode('closing')?>" class="waves-effect <?= $currentPage === 'closing' ? 'active' : '' ?>">
                <i class="bx bx-log-out"></i>
                <span key="t-chat">Closing Shift</span>
            </a>
        </li> -->

        <li class="<?= in_array($currentPage, ['summary_report', 'table_report', 'cashflow']) ? 'mm-active' : '' ?>">
            <a href="javascript:void(0);" class="has-arrow waves-effect" aria-expanded="false">
                <i class="bx bx-bar-chart"></i>
                <span key="t-ecommerce">Report</span>
            </a>
            <ul class="sub-menu" aria-expanded="false" style="display:none;">
                <li><a href="./index.php?q=<?=encrypt('summary')?>&scr=<?=base64url_encode('summary')?>" class="<?= $currentPage === 'summary' ? 'active' : '' ?>" key="t-products">Summary Report</a></li>
                
                <li><a href="./index.php?q=<?=encrypt('cashflow')?>&scr=<?=base64url_encode('cashflow')?>" class="<?= $currentPage === 'cashflow' ? 'active' : '' ?>" key="t-orders">Cash Flow Report</a></li>
            </ul>
        </li>

        <?php if ($userLevel === 'admin'): ?>
        <!-- Section Setting - hanya tampil untuk admin -->
        <li class="menu-title" key="t-pages">Setting</li>

        <!-- Fixed: Added mm-active class for single menu items -->
        <li class="<?= $currentPage === 'general' ? 'mm-active' : '' ?>">
            <a href="./index.php?q=<?=encrypt('general')?>&scr=<?=base64url_encode('general')?>" class="waves-effect <?= $currentPage === 'general' ? 'active' : '' ?>">
                <i class="bx bx-wrench"></i>
                <span key="t-chat">General</span>
            </a>
        </li>

       

        <!-- Fixed: Added mm-active class for single menu items -->
        <li class="<?= $currentPage === 'portal' ? 'mm-active' : '' ?>">
            <a href="./index.php?q=<?=encrypt('portal')?>&scr=<?=base64url_encode('portal')?>" class="waves-effect <?= $currentPage === 'portal' ? 'active' : '' ?>">
                <i class="bx bx-wrench"></i>
                <span key="t-chat">Portal & Booking</span>
            </a>
        </li>


        <!-- <li class="<?= $currentPage === 'rental_config' ? 'mm-active' : '' ?>">
            <a href="./index.php?q=<?=encrypt('rental_config')?>&scr=<?=base64url_encode('rental_config')?>" class="waves-effect <?= $currentPage === 'rental_config' ? 'active' : '' ?>">
                <i class="bx bx-cog"></i>
                <span key="t-chat">Rental Config</span>
            </a>
        </li> -->
        <?php endif; ?>

        <li class="menu-title" key="t-components">Community</li>

        <li class="<?= $currentPage === 'forum' ? 'mm-active' : '' ?>">
            <a href="./index.php?q=<?=encrypt('forum')?>&scr=<?=base64url_encode('forum')?>" class="waves-effect <?= $currentPage === 'forum' ? 'active' : '' ?>">
                <i class="bx bx-group"></i>
                <span key="t-chat">Forum</span>
            </a>
        </li>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const submenuHeaders = document.querySelectorAll('#side-menu a.has-arrow');
    const currentPage = '<?= $currentPage ?>';

    // Handler untuk menu yang memiliki submenu
    submenuHeaders.forEach(header => {
        header.addEventListener('click', function(e) {
            e.preventDefault();

            const parentLi = this.parentElement;
            const subMenu = parentLi.querySelector('.sub-menu');

            if (!subMenu) return;

            const isVisible = subMenu.style.display === 'block';

            // Tutup semua submenu lain dulu
            document.querySelectorAll('#side-menu .sub-menu').forEach(menu => {
                menu.style.display = 'none';
                menu.parentElement.classList.remove('mm-active');
                menu.parentElement.querySelector('a.has-arrow').setAttribute('aria-expanded', 'false');
            });

            // Hapus active state dari menu tanpa submenu
            document.querySelectorAll('#side-menu li:not(.menu-title)').forEach(li => {
                if (!li.querySelector('.sub-menu')) {
                    li.classList.remove('mm-active');
                }
            });

            if (!isVisible) {
                subMenu.style.display = 'block';
                parentLi.classList.add('mm-active');
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // Handler untuk menu tanpa submenu
    const singleMenuLinks = document.querySelectorAll('#side-menu li:not(.menu-title) > a:not(.has-arrow)');
    singleMenuLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Hapus active state dari semua menu
            document.querySelectorAll('#side-menu li:not(.menu-title)').forEach(li => {
                li.classList.remove('mm-active');
            });

            // Tutup semua submenu
            document.querySelectorAll('#side-menu .sub-menu').forEach(menu => {
                menu.style.display = 'none';
                menu.parentElement.classList.remove('mm-active');
                const arrow = menu.parentElement.querySelector('a.has-arrow');
                if (arrow) {
                    arrow.setAttribute('aria-expanded', 'false');
                }
            });

            // Aktifkan menu yang diklik
            this.parentElement.classList.add('mm-active');
        });
    });

    // Buka submenu otomatis sesuai currentPage saat halaman dimuat
    submenuHeaders.forEach(header => {
        const parentLi = header.parentElement;
        const subMenu = parentLi.querySelector('.sub-menu');
        if (!subMenu) return;

        const links = subMenu.querySelectorAll('a');
        links.forEach(link => {
            const url = new URL(link.href, window.location.origin);
            const decop = url.searchParams.get('scr');
            const pageParam = atob(decop); // Decode base64 URL
            if (pageParam === currentPage) {
                subMenu.style.display = 'block';
                parentLi.classList.add('mm-active');
                header.setAttribute('aria-expanded', 'true');
            }
        });
    });
});
</script>