<?php

require_once './include/config.php'; // koneksi database
require_once './include/crypto.php'; // jika password ingin di-enkripsi, tapi gunakan password_hash lebih baik
$userid = $_SESSION['user_id'];
$r = $con->query("SELECT * FROM userx WHERE username = '$userid'");
foreach ($r as $rr) {
        $merchand = $rr['merchand'];
        $level = $rr['level'];
        $license = $rr['license'];
        $cabang = $rr['cabang'];
        $logox = $rr['logox'];
         $timezone = $rr['timezone'];
        

}



date_default_timezone_set($timezone);
$now = date('Y-m-d H:i:s'); // Contoh: 2025-06-14 08:15:00

  

?>

  <div class="navbar-header">
                    <div class="d-flex">
                        <!-- LOGO -->
                        <div class="navbar-brand-box">
                            <a href="index.php" class="logo logo-dark">
                                <span class="logo-sm">
                                    <img src="assets/images/logo.svg" alt="" height="30">
                                </span>
                                <span class="logo-lg">
                                    <img src="assets/images/logo-dark.png" alt="" height="20">
                                </span>
                            </a>

                            <a href="index.php" class="logo logo-light">
                                <span class="logo-sm">
                                    <img src="assets/images/logo-light.svg" alt="" height="35">
                                </span>
                                <span class="logo-lg">
                                    <img src="assets/images/logo-light.png" alt="" height="30">
                                </span>
                            </a>
                        </div>

                        <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                            <i class="fa fa-fw fa-bars"></i>
                        </button>

                        <!-- App Search-->
                        <div class="d-flex align-items-center ms-3 d-none d-lg-flex">
                            <span id="current-date" class="me-2"></span>
                            <span id="current-time"> </span>  
                        </div>
                        <script>
                            function updateDateTime() {
                                const now = new Date();
                                const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
                                document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
                                // 24-hour format
                                const hours = now.getHours().toString().padStart(2, '0');
                                const minutes = now.getMinutes().toString().padStart(2, '0');
                                const seconds = now.getSeconds().toString().padStart(2, '0');
                                document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds}`;
                            }
                            setInterval(updateDateTime, 1000);
                            updateDateTime();
                        </script>

                        
                    </div>

                    <div class="d-flex">

                        

                        
                        <div class="dropdown d-lg-inline-block ms-1">
                            <button type="button" class="btn header-item noti-icon waves-effect"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-customize"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
   
        <div class="row g-0">
            <?php
            // Pastikan koneksi database $con dan $username tersedia
            $username = $_SESSION['username'] ?? null;

            $sql = $username 
                ? "SELECT * FROM playstations WHERE userx = '" . mysqli_real_escape_string($con, $username) . "' ORDER BY no_ps ASC" 
                : "SELECT * FROM playstations ORDER BY no_ps ASC";

            $result = $con->query($sql);
            if (!$result) {
                die("Query gagal: " . $con->error);
            }

            $count = 0;

            while ($row = $result->fetch_assoc()):
                // Data playstation
                $no_ps     = $row['no_ps'];
                $type_ps   = $row['type_ps'];
                $status    = ucfirst($row['status']);
                $duration  = $row['duration'] ?? '-';
                $startTime = (!empty($row['start_time']) && $row['start_time'] !== '0000-00-00 00:00:00') ? $row['start_time'] : '-';
                $endTime   = (!empty($row['end_time']) && $row['end_time'] !== '0000-00-00 00:00:00') ? $row['end_time'] : '-';
$remainingSeconds = 0;
if ($endTime !== '-' && $endTime !== '0000-00-00 00:00:00') {
    $endTimestamp = strtotime($endTime);
    $remainingSeconds = max(0, $endTimestamp - time());
}
               if ($status === 'Available') {
    $badgeColor = 'success';
} elseif ($status === 'Paused') {
    $badgeColor = 'warning';
} else {
    $badgeColor = 'danger';
}


                // Ambil daftar harga
                $priceList = [];
                $queryPrice = "SELECT duration, price FROM tb_pricelist WHERE type_ps = '$type_ps' ORDER BY duration+0 ASC";
                $resultPrice = mysqli_query($con, $queryPrice);
                if ($resultPrice && mysqli_num_rows($resultPrice) > 0) {
                    $priceList = mysqli_fetch_all($resultPrice, MYSQLI_ASSOC);
                }

                // Buka baris baru setiap 3 kolom
                if ($count > 0 && $count % 5 == 0) {
                    echo '</div><div class="row g-0">';
                }
            ?>
                <div class="col text-center p-0 m-0">
    <a class="dropdown-icon-item" href="index.php?q=<?=encrypt('rentals')?>&scr=<?=base64url_encode('rentals')?>">
        <div>
            <strong class="badge bg-<?= $badgeColor ?>">#<?= $no_ps ?></strong>
        </div>
    
        
        <!-- Countdown -->
        <div>
        <?php
$isOpen = strtolower($duration) === 'open';
$startISO = $isOpen && $startTime !== '-' ? date('c', strtotime($startTime)) : '';
$endISO = (!$isOpen && $endTime !== '-') ? date('c', strtotime($endTime)) : '';
?>
<small id="timer-<?= $no_ps ?>"
       class="count-timer"
       data-mode="<?= $isOpen ? 'countup' : 'countdown' ?>"
       data-start="<?= $startISO ?>"
       data-end="<?= $endISO ?>"
       data-status="<?= strtolower($status) ?>"
       data-type-ps="<?= $type_ps ?>">
</small>

        </div>
    </a>
</div>
            <?php
                $count++;
            endwhile;

            if ($count === 0) {
                echo '<div class="col text-center p-3"><em>Tidak ada data unit PlayStation</em></div>';
            } else {
                echo '</div>'; // Tutup row terakhir
            }
            ?>
        </div>
    </div>
<!-- Updated JavaScript Timer (replace the existing script in your main file) -->


                   

                        <div class="dropdown d-none d-lg-inline-block ms-1">
                            <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="fullscreen">
                                <i class="bx bx-fullscreen"></i>
                            </button>
                        </div>

                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bx bx-bell bx-tada"></i>
                               <span class="badge bg-danger rounded-pill" id="notification-count">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" id="notification-list"
                                aria-labelledby="page-header-notifications-dropdown">
                                
                                
                                
                            </div>
                        </div>

                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img class="rounded-circle header-profile-user" src="<?=$logox?>"
                                    alt="Header Avatar">
                                <span class="d-none d-xl-inline-block ms-1" key="t-henry"><?=$userid?></span>
                                <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <!-- item-->
                                <!-- <a class="dropdown-item" href="#"><i class="bx bx-user font-size-16 align-middle me-1"></i> <span key="t-profile">Profile</span></a> -->
                                <!-- <a class="dropdown-item" href="#"><i class="bx bx-wallet font-size-16 align-middle me-1"></i> <span key="t-my-wallet">My Wallet</span></a> -->
                              <?php if ($_SESSION['level'] === 'admin'): ?>
    <a href="./index.php?q=<?= encrypt('general') ?>&scr=<?= base64url_encode('general') ?>" 
       class="dropdown-item d-block waves-effect <?= $currentPage === 'general' ? 'active' : '' ?>">
        <i class="bx bx-wrench font-size-16 align-middle me-1"></i> 
        <span key="t-settings">Settings</span>
    </a>
<?php endif; ?>

                                <!-- <a class="dropdown-item" href="#"><i class="bx bx-lock-open font-size-16 align-middle me-1"></i> <span key="t-lock-screen">Lock screen</span></a> -->
                                <div class="dropdown-divider"></div>
                              <a class="dropdown-item text-danger" href="./controller/logoutx.php">
    <i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> 
    <span key="t-logout">Logout</span>
</a>

                            </div>
                        </div>

                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn header-item noti-icon right-bar-toggle waves-effect">
                                <i class="bx bx-cog bx-spin"></i>
                            </button>
                        </div>

                    </div>
                </div>

<script>
// Enhanced JavaScript Timer with Pause Support
document.addEventListener('DOMContentLoaded', function () {
    const timers = document.querySelectorAll('.count-timer');
    const activeTimers = new Map();
    const notificationFlags = new Map();
    const pausedTimerData = new Map(); // Store paused timer data
    let audioContext = null;
    let isAudioInitialized = false;

    // Initialize audio context after user interaction
    function initializeAudio() {
        if (!isAudioInitialized) {
            try {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
                if (audioContext.state === 'suspended') {
                    audioContext.resume();
                }
                isAudioInitialized = true;
                console.log('Audio initialized successfully');
            } catch (error) {
                console.log('Audio initialization failed:', error);
            }
        }
    }

    // Add event listeners for user interaction to initialize audio
    ['click', 'touchstart', 'keydown'].forEach(event => {
        document.addEventListener(event, initializeAudio, { once: true });
    });

    function speakNotification(message) {
        if ('speechSynthesis' in window) {
            speechSynthesis.cancel();
            setTimeout(() => {
                const utterance = new SpeechSynthesisUtterance(message);
                utterance.lang = 'id-ID';
                utterance.rate = 1.0;
                utterance.volume = 1.0;

                const speechData = {
                    message: message,
                    startTime: Date.now(),
                    duration: Math.ceil(message.length * 0.1 * 1000)
                };

                try {
                    sessionStorage.setItem('currentSpeech', JSON.stringify(speechData));
                } catch(e) {
                    console.log('SessionStorage not available');
                }

                utterance.onstart = function() {
                    console.log('Speech started');
                };

                utterance.onend = function() {
                    console.log('Speech ended');
                    try {
                        sessionStorage.removeItem('currentSpeech');
                    } catch(e) {
                        console.log('SessionStorage not available');
                    }
                };

                utterance.onerror = function(event) {
                    console.log('Speech synthesis error:', event);
                    try {
                        sessionStorage.removeItem('currentSpeech');
                    } catch(e) {
                        console.log('SessionStorage not available');
                    }
                };

                speechSynthesis.speak(utterance);
            }, 100);
        }
    }

    function resumeSpeechAfterReload() {
        try {
            const speechData = sessionStorage.getItem('currentSpeech');
            if (speechData) {
                const data = JSON.parse(speechData);
                const elapsed = Date.now() - data.startTime;
                
                if (elapsed < data.duration) {
                    console.log('Resuming speech after reload...');
                    speakNotification(data.message);
                } else {
                    sessionStorage.removeItem('currentSpeech');
                }
            }
        } catch(e) {
            console.log('Could not resume speech:', e);
        }
    }

    resumeSpeechAfterReload();

    function playNotificationSound() {
        if (!isAudioInitialized || !audioContext) {
            console.log('Audio not initialized, trying to play without beep');
            return;
        }

        try {
            if (audioContext.state === 'suspended') {
                audioContext.resume();
            }

            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (error) {
            console.log('Beep sound failed:', error);
        }
    }

    timers.forEach(function (el) {
        const mode = el.dataset.mode;
        const startTime = el.dataset.start ? new Date(el.dataset.start) : null;
        const endTime = el.dataset.end ? new Date(el.dataset.end) : null;
        const status = el.dataset.status; // Get the status from data attribute
        const psNumber = el.id.replace('timer-', '');
        const typePs = el.dataset.typePs;

        // Initialize notification flag for this timer
        notificationFlags.set(psNumber, false);

        function pad(n) {
            return n.toString().padStart(2, '0');
        }

        function update() {
            // Check if status is paused - if so, stop the timer
            if (status === 'paused') {
                console.log(`Timer for PS #${psNumber} is paused`);
                
                // Store current timer data for potential resume
                pausedTimerData.set(psNumber, {
                    mode: mode,
                    startTime: startTime,
                    endTime: endTime,
                    lastUpdate: new Date()
                });
                
                // Display "PAUSED" text
                el.textContent = 'PAUSED';
                el.style.color = '#ffc107'; // Warning color for paused state
                el.style.fontWeight = 'bold';
                
                // Clear any active timer
                if (activeTimers.has(psNumber)) {
                    clearTimeout(activeTimers.get(psNumber));
                    activeTimers.delete(psNumber);
                }
                
                return; // Exit the update function
            }

            let seconds = 0;
            let shouldContinue = true;

            if (mode === 'countup' && startTime) {
                const now = new Date();
                seconds = Math.floor((now - startTime) / 1000);
            }

            if (mode === 'countdown' && endTime) {
                const now = new Date();
                seconds = Math.floor((endTime - now) / 1000);
                
                // Check for 5-minute warning (300 seconds = 5 minutes)
                if (seconds === 300 && !notificationFlags.get(psNumber)) {
                    notificationFlags.set(psNumber, true);
                    
                    if (!isAudioInitialized) {
                        initializeAudio();
                    }
                    
                    playNotificationSound();
                    
                    const message = `Pelanggan nomor ${psNumber}, waktu sewa anda akan berakhir dalam 5 menit lagi. Anda bisa lakukan penambahan extra time di kasir.`;
                    speakNotification(message);
                    
                    showNotification(`PlayStation #${psNumber}: 5 minutes remaining!`, 'warning');
                    
                    el.style.color = '#ffc107';
                    el.style.fontWeight = 'bold';
                    el.style.animation = 'blink 1s linear infinite';
                }
                
                // Check for 5-second warning
                if (seconds === 5 && !notificationFlags.get(psNumber + '_final')) {
                    notificationFlags.set(psNumber + '_final', true);
                    
                    if (!isAudioInitialized) {
                        initializeAudio();
                    }
                    
                    playNotificationSound();
                    
                    const message = `Pelanggan nomor ${psNumber}, waktu sewa anda telah berakhir.`;
                    speakNotification(message);
                    
                    showNotification(`PlayStation #${psNumber}: Time expired!`, 'danger');
                }
                
                if (seconds <= 0) {
                    seconds = 0;
                    shouldContinue = false;
                    executeAutoStop(psNumber, typePs, el);
                }
            }

            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;
            el.textContent = `${pad(h)}:${pad(m)}:${pad(s)}`;

            // Continue timer if needed and status is not paused
            if (shouldContinue && status !== 'paused') {
                const timerId = setTimeout(update, 1000);
                activeTimers.set(psNumber, timerId);
            } else {
                // Clean up timer
                activeTimers.delete(psNumber);
                if (!shouldContinue) {
                    notificationFlags.delete(psNumber);
                    pausedTimerData.delete(psNumber);
                }
            }
        }

        // Start the timer only if not paused
        if (status !== 'paused') {
            update();
        } else {
            // If paused, just show PAUSED text
            el.textContent = 'PAUSED';
            el.style.color = '#ffc107';
            el.style.fontWeight = 'bold';
        }
    });

    function executeAutoStop(psNumber, typePs, timerElement) {
        timerElement.textContent = 'Stopping...';
        timerElement.style.color = '#ffc107';
        
        fetch('controller/ajax_free_ps.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=free_ps&no_ps=${encodeURIComponent(psNumber)}&type_ps=${encodeURIComponent(typePs)}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log(`PS #${psNumber} automatically stopped successfully`);
                
                timerElement.textContent = 'STOPPED';
                timerElement.style.color = '#28a745';
                
                showNotification(`PlayStation #${psNumber} has been automatically stopped`, 'success');
                
                setTimeout(() => {
                    location.reload();
                }, 2000);
                
            } else {
                console.error('Error stopping PS:', data.message);
                timerElement.textContent = 'ERROR';
                timerElement.style.color = '#dc3545';
                
                showNotification(`Failed to stop PlayStation #${psNumber}: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            console.error('AJAX Error:', error);
            timerElement.textContent = 'ERROR';
            timerElement.style.color = '#dc3545';
            
            showNotification(`Network error while stopping PlayStation #${psNumber}`, 'error');
        });
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info'} alert-dismissible fade show`;
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    // Function to resume paused timers (call this when status changes from paused to active)
    window.resumePausedTimer = function(psNumber) {
        const timerElement = document.getElementById(`timer-${psNumber}`);
        if (timerElement && pausedTimerData.has(psNumber)) {
            const pausedData = pausedTimerData.get(psNumber);
            console.log(`Resuming timer for PS #${psNumber}`);
            
            // Update the status in the element
            timerElement.dataset.status = 'active'; // or whatever the active status should be
            
            // Restart the timer logic
            // You would need to re-initialize the timer here
            location.reload(); // Simple solution: reload the page to restart all timers
        }
    };

    // Clean up timers when page unloads
    window.addEventListener('beforeunload', function() {
        activeTimers.forEach(timerId => clearTimeout(timerId));
        activeTimers.clear();
        notificationFlags.clear();
        pausedTimerData.clear();
        
        try {
            const speechData = sessionStorage.getItem('currentSpeech');
            if (!speechData && 'speechSynthesis' in window) {
                speechSynthesis.cancel();
            }
        } catch(e) {
            console.log('Could not check speech status:', e);
        }
        
        if (audioContext && audioContext.state !== 'closed') {
            audioContext.close();
        }
    });

    // Handle page visibility change
    document.addEventListener('visibilitychange', function() {
        if (document.hidden && 'speechSynthesis' in window) {
            console.log('Page hidden, but keeping speech active');
        } else if (!document.hidden && 'speechSynthesis' in window) {
            if (speechSynthesis.paused) {
                speechSynthesis.resume();
            }
        }
    });

    window.addEventListener('blur', function() {
        // Don't do anything - let speech continue
    });

    window.addEventListener('focus', function() {
        if ('speechSynthesis' in window && speechSynthesis.paused) {
            speechSynthesis.resume();
        }
    });
});

// CSS for blinking animation and paused state
const style = document.createElement('style');
style.textContent = `
    @keyframes blink {
        0%, 50% { opacity: 1; }
        51%, 100% { opacity: 0.3; }
    }
    
    .timer-paused {
        color: #ffc107 !important;
        font-weight: bold !important;
        background-color: rgba(255, 193, 7, 0.1);
        padding: 2px 6px;
        border-radius: 3px;
    }
`;
document.head.appendChild(style);

// Function to test audio notification
function testAudioNotification(psNumber = '1') {
    const message = `Pelanggan nomor ${psNumber}, waktu sewa anda akan berakhir dalam 5 menit lagi. Anda bisa lakukan penambahan extra time di kasir.`;
    
    if ('speechSynthesis' in window) {
        speechSynthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(message);
        utterance.lang = 'id-ID';
        utterance.rate = 1.0;
        utterance.volume = 1.0;
        
        const speechData = {
            message: message,
            startTime: Date.now(),
            duration: Math.ceil(message.length * 0.1 * 1000)
        };
        
        try {
            sessionStorage.setItem('currentSpeech', JSON.stringify(speechData));
        } catch(e) {
            console.log('SessionStorage not available');
        }
        
        utterance.onend = function() {
            try {
                sessionStorage.removeItem('currentSpeech');
            } catch(e) {
                console.log('SessionStorage not available');
            }
        };
        
        speechSynthesis.speak(utterance);
        console.log('Test speech started - try reloading the page now!');
    }
}

// Function to manually clear stuck speech
function clearSpeech() {
    if ('speechSynthesis' in window) {
        speechSynthesis.cancel();
    }
    try {
        sessionStorage.removeItem('currentSpeech');
        console.log('Speech cleared');
    } catch(e) {
        console.log('Could not clear speech storage');
    }
}

// Function to update PS status dynamically (if needed)
function updatePSStatus(psNumber, newStatus) {
    const timerElement = document.getElementById(`timer-${psNumber}`);
    if (timerElement) {
        timerElement.dataset.status = newStatus.toLowerCase();
        
        if (newStatus.toLowerCase() === 'paused') {
            // Timer will be paused on next update cycle
            console.log(`PS #${psNumber} status changed to paused`);
        } else {
            // Resume timer - reload page for simplicity
            location.reload();
        }
    }
}
</script>