<!DOCTYPE html>
<html lang="id">
     <?php
    include('../include/config.php');
    session_start();
    $username = $_SESSION['username'];
      // WIB
    $dateTime = date('d M Y H:i:s');
 $r = $con->query("SELECT * 
FROM userx 
WHERE username ='$username'");

                                        foreach ($r as $rr) {
                                            $merchand = $rr['merchand'];
                                             $address = $rr['address'];
                                             $logox = $rr['logox'];
                                        }

    $inv = $_GET['inv']; // Example invoice number
   
    
    // Prepare the SQL query
   $sql = "
SELECT 
    tf.*, 
    COALESCE(t.id_ps, tfnb.id_ps) as id_ps,
    CASE 
        WHEN t.id_ps IS NOT NULL THEN 'tb_trans'
        WHEN tfnb.id_ps IS NOT NULL THEN 'tb_trans_fnb'
        ELSE 'no_ps_data'
    END as source_table
FROM tb_trans_final tf
LEFT JOIN tb_trans t ON tf.invoice = t.inv
LEFT JOIN tb_trans_fnb tfnb ON tf.invoice = tfnb.inv
WHERE tf.invoice = '$inv'
LIMIT 1
";

    
    // Execute the query
    $result = mysqli_query($con, $sql);
    
    // Check for errors
    if (!$result) {
        die("Query failed: " . mysqli_error($con));
    }
    
    // Fetch the first row as an array
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    ?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Struk Billing</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <style>
    /* Base Responsive Styles */
    * {
      box-sizing: border-box;
    }
    
    body {
      margin: 0;
      padding: 10px;
      background: #f0f0f0;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    /* Desktop Styles */
    #struk {
      width: 80mm;
      max-width: 80mm;
      background: linear-gradient(135deg, #4A90E2, #357ABD);
      position: relative;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 8px 32px rgba(0,0,0,0.2);
      margin: 0;
      padding: 0;
    }
    
    /* Mobile Styles */
    @media screen and (max-width: 768px) {
      body {
        padding: 5px;
        align-items: flex-start;
        padding-top: 20px;
      }
      
      #struk {
        width: 95vw;
        max-width: 350px;
        min-width: 280px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
      }
      
      /* Scale font sizes for mobile */
      .header-title {
        font-size: 18px !important;
      }
      
      .merchant-name {
        font-size: 13px !important;
      }
      
      .detail-text {
        font-size: 10px !important;
      }
      
      .item-text {
        font-size: 9px !important;
      }
      
      .total-text {
        font-size: 11px !important;
      }
      
      .final-total {
        font-size: 14px !important;
      }
    }
    
    /* Very small mobile */
    @media screen and (max-width: 320px) {
      #struk {
        width: 98vw;
        max-width: 300px;
      }
      
      .header-title {
        font-size: 16px !important;
      }
      
      .detail-text, .item-text {
        font-size: 8px !important;
      }
    }
    
    /* Landscape mobile */
    @media screen and (max-height: 500px) and (orientation: landscape) {
      body {
        align-items: center;
        padding: 10px;
      }
      
      #struk {
        width: 60vw;
        max-width: 300px;
      }
    }
    
    /* Touch improvements for mobile */
    @media (hover: none) and (pointer: coarse) {
      #struk {
        cursor: pointer;
        transition: transform 0.2s ease;
      }
      
      #struk:active {
        transform: scale(0.98);
      }
      
      /* Add visual feedback for mobile */
      .mobile-hint {
        display: block !important;
      }
    }
    
    /* Improve readability on small screens */
    @media screen and (max-width: 400px) {
      .success-icon {
        width: 50px !important;
        height: 50px !important;
      }
      
      .success-icon span {
        font-size: 24px !important;
      }
      
      /* Better spacing for mobile */
      .content-section {
        padding: 20px 15px 15px 15px !important;
      }
      
      .header-section {
        padding: 25px 15px 35px 15px !important;
      }
    }
    
    /* Mobile hint styles */
    .mobile-hint {
      display: none;
      background: rgba(74, 144, 226, 0.1);
      padding: 8px;
      border-radius: 6px;
      border: 1px solid rgba(74, 144, 226, 0.2);
      text-align: center;
      margin-top: 10px;
    }
    
    @media screen and (max-width: 768px) {
      .mobile-hint {
        display: block;
      }
    }
  </style>
</head>

<body style="margin: 0; padding: 20px; background: linear-gradient(135deg, #4A90E2, #357ABD); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center;">

<div id="struk" style="
  width: 58mm;
  max-width: 58mm;
  background: linear-gradient(135deg, #4A90E2, #357ABD);
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0,0,0,0.2);
">

  <!-- Header Blue Section with Icon -->
  <div class="header-section" style="
    background: linear-gradient(135deg, #4A90E2, #357ABD);
    padding: 30px 20px 40px 20px;
    text-align: center;
    position: relative;
  ">
    
    <!-- Success Icon -->
    <div class="success-icon" style="
      width: 60px;
      height: 60px;
      background: rgba(255,255,255,0.2);
      border: 3px solid rgba(255,255,255,0.8);
      border-radius: 50%;
      margin: 0 auto 20px auto;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(10px);
    ">
      <span style="
        color: white;
        font-size: 28px;
        font-weight: bold;
        line-height: 1;
      ">✓</span>
    </div>

    <!-- Title -->
    <h1 class="header-title" style="
      color: white;
      font-size: 15px;
      font-weight: 600;
      margin: 0;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    ">Transaksi Berhasil</h1>

  </div>

  <!-- White Content Area with Torn Edge Effect -->
  <div class="content-section" style="
    background: white;
    position: relative;
    margin-top: -20px;
    padding: 25px 20px 20px 20px;
    clip-path: polygon(
      0% 20px,
      5% 10px,
      10% 25px,
      15% 5px,
      20% 20px,
      25% 8px,
      30% 22px,
      35% 12px,
      40% 18px,
      45% 6px,
      50% 24px,
      55% 14px,
      60% 20px,
      65% 4px,
      70% 16px,
      75% 10px,
      80% 22px,
      85% 8px,
      90% 18px,
      95% 12px,
      100% 20px,
      100% 100%,
      0% 100%
    );
  ">

    <!-- Transaction Details -->
    <div style="margin-bottom: 20px;">
      
      <!-- Date -->
      <div style="display: flex; justify-content: space-between; margin-bottom: 5px;margin-top:10px">
        <span class="detail-text" style="color: #666; font-size: 11px; font-weight: 500;">Tanggal</span>
        <span class="detail-text" style="color: #333; font-size: 11px; font-weight: 600;"><?=$dateTime?></span>
      </div>

      <!-- Transaction ID -->
      <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
        <span class="detail-text" style="color: #666; font-size: 11px; font-weight: 500;">Trans ID</span>
        <span class="detail-text" style="color: #333; font-size: 11px; font-weight: 600;"><?=$inv?></span>
      </div>

      <!-- PlayStation -->
      <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
        <span class="detail-text" style="color: #666; font-size: 11px; font-weight: 500;">PlayStation</span>
        <span class="detail-text" style="color: #333; font-size: 11px; font-weight: 600;">No.<?=($row['id_ps'] == 0 ? "GUEST" : $row['id_ps'])?></span>
      </div>

    </div>

    <!-- Separator Line -->
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">

    <!-- Merchant Info -->
    <div style="text-align: center; margin-bottom: 20px;">
      
    

      <!-- Merchant Name -->
      <h3 class="merchant-name" style="
        margin: 0 0 5px 0;
        font-size: 14px;
        font-weight: 700;
        color: #333;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      "><?=$merchand?></h3>

      <!-- Address -->
      <p class="detail-text" style="
        margin: 0;
        font-size: 10px;
        color: #666;
        line-height: 1.4;
      "><?=$address?></p>

    </div>

    <!-- Items Section -->
    <div style="margin-bottom: 20px;">
      <h4 style="
        margin: 0 0 10px 0;
        font-size: 12px;
        font-weight: 600;
        color: #333;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
      ">Detail Transaksi</h4>

      <table style="width: 100%; border-collapse: collapse;">
<?php
 $sql_details = "SELECT 
    CONCAT('1x(', IF(t.extra = 1, 'Ext', 'Reg'), ') ', t.durasi, ' Min') AS details,
    t.harga AS harga
FROM tb_trans t
WHERE t.inv = '$inv'

UNION ALL

SELECT 
    CONCAT(f.qty, 'x(', c.nama, ')') AS details,
    f.total AS harga
FROM tb_trans_fnb f
JOIN tb_fnb c ON c.id = f.id_fnb
WHERE f.inv = '$inv';
";

 $result_details = mysqli_query($con, $sql_details);

 if (!$result_details) {
     die("Query failed: " . mysqli_error($con));
 }
 
 while ($detail = mysqli_fetch_array($result_details, MYSQLI_ASSOC)) {
   echo "<tr>
     <td class='item-text' style='
       padding: 4px 0;
       font-size: 10px;
       color: #666;
       vertical-align: top;
     '>" . (strlen($detail['details']) > 18 ? substr($detail['details'], 0, 18) . ".." : $detail['details']) . "</td>
     <td class='item-text' style='
       text-align: right;
       padding: 4px 0;
       font-size: 10px;
       color: #333;
       font-weight: 600;
       vertical-align: top;
     '>Rp" . number_format($detail['harga'], 0, ',', '.') . "</td>
   </tr>";
 }
?>
      </table>
    </div>

    <!-- Separator -->
    <hr style="border: none; border-top: 1px solid #eee; margin: 15px 0;">

    <!-- Totals Section -->
    <div style="margin-bottom: 15px;">
      
      <!-- Subtotal -->
      <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
        <span class="total-text" style="color: #666; font-size: 11px; font-weight: 500;">Total</span>
        <span class="total-text" style="color: #333; font-size: 11px; font-weight: 600;">Rp<?=number_format($row['grand_total']+$row['promo'], 0, ',', '.')?></span>
      </div>

      <!-- Discount (if any) -->
      <?php if ($row['promo'] > 0): ?>
      <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
        <span class="total-text" style="color: #e74c3c; font-size: 11px; font-weight: 500;">Diskon</span>
        <span class="total-text" style="color: #e74c3c; font-size: 11px; font-weight: 600;">-Rp<?=number_format($row['promo'], 0, ',', '.')?></span>
      </div>
      <?php endif; ?>

      <!-- Payment Method -->
      <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
        <span class="total-text" style="color: #666; font-size: 11px; font-weight: 500;">Metode Bayar</span>
        <span class="total-text" style="color: #333; font-size: 11px; font-weight: 600;"><?= ($row['metode_pembayaran'] === 'debit') ? 'Transfer' : ucfirst($row['metode_pembayaran']); ?></span>
      </div>

      <!-- Payment Amount -->
      <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
        <span class="total-text" style="color: #27ae60; font-size: 11px; font-weight: 500;">Dibayar</span>
        <span class="total-text" style="color: #27ae60; font-size: 11px; font-weight: 600;">Rp<?= $row['metode_pembayaran'] == "cash" 
          ? number_format(floatval($row['bayar']), 0, ',', '.') 
          : number_format(floatval($row['grand_total']), 0, ',', '.'); ?></span>
      </div>

      <!-- Change (if cash) -->
      <?php if ($row['metode_pembayaran'] == "cash"): ?>
      <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
        <span class="total-text" style="color: #3498db; font-size: 11px; font-weight: 500;">Kembalian</span>
        <span class="total-text" style="color: #3498db; font-size: 11px; font-weight: 600;">Rp<?= number_format(floatval($row['kembali']), 0, ',', '.'); ?></span>
      </div>
      <?php endif; ?>

    </div>

    <!-- Final Total -->
    <div style="
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid #e9ecef;
    ">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <span class="total-text" style="color: #333; font-size: 12px; font-weight: 600;">Total Bayar</span>
        <span class="final-total" style="color: #4A90E2; font-size: 16px; font-weight: 700;">Rp<?=number_format($row['grand_total'], 0, ',', '.')?></span>
      </div>
    </div>

    <!-- Footer Message -->
    <div style="text-align: center; margin-bottom: 15px;">
      <p class="detail-text" style="
        margin: 0 0 5px 0;
        font-size: 10px;
        color: #666;
        line-height: 1.4;
        font-weight: 500;
      ">Pilih Billing Cerdas<br>Owner Pasti Puas!</p>
      <p class="item-text" style="
        margin: 0;
        font-size: 9px;
        color: #999;
        font-style: italic;
      ">dragonplay.id</p>
    </div>


  </div>

</div>

<script>
const invoiceNumber = "<?= $inv ?>";

function isMobile() {
  return window.innerWidth <= 768 || /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

function downloadStruk() {
  console.log("Memulai download struk...");
  
  const element = document.getElementById("struk");
  if (!element) {
    alert("Element struk tidak ditemukan!");
    return;
  }

  // Sementara hilangkan box-shadow untuk hasil yang lebih clean
  const originalBoxShadow = element.style.boxShadow;
  element.style.boxShadow = 'none';
  
  // Determine canvas size based on device
  const scale = isMobile() ? 3 : 4; // Lower scale for mobile to save memory
  const rect = element.getBoundingClientRect();
  
  // Konfigurasi html2canvas yang optimal untuk semua device
  const options = {
    scale: scale,
    backgroundColor: 'transparent',
    useCORS: true,
    allowTaint: false,
    logging: false,
    x: 0,
    y: 0,
    width: element.offsetWidth,
    height: element.offsetHeight,
    scrollX: 0,
    scrollY: 0,
    windowWidth: window.innerWidth,
    windowHeight: window.innerHeight
  };

  html2canvas(element, options)
    .then(function(canvas) {
      console.log("Canvas berhasil dibuat");

      // Buat canvas final dengan ukuran yang tepat
      const finalCanvas = document.createElement('canvas');
      const ctx = finalCanvas.getContext('2d');
      
      // For mobile, use viewport-based width, for desktop use 80mm
      let targetWidth;
      if (isMobile()) {
        targetWidth = canvas.width; // Use actual rendered width
      } else {
        const mmToPx = 3.7795275591;
        targetWidth = 80 * mmToPx * scale;
      }
      
      const targetHeight = canvas.height;
      
      finalCanvas.width = Math.max(targetWidth, canvas.width);
      finalCanvas.height = targetHeight;
      
      // Background
      ctx.fillStyle = '#f0f0f0';
      ctx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
      
      // Center the content
      const offsetX = (finalCanvas.width - canvas.width) / 2;
      const offsetY = 0;
      
      // Draw struk di center
      ctx.drawImage(canvas, offsetX, offsetY);
      
      // Kembalikan box-shadow
      element.style.boxShadow = originalBoxShadow;
      
      // Convert to blob dan download
      finalCanvas.toBlob(function(blob) {
        if (!blob) {
          alert("Gagal membuat file gambar");
          return;
        }

        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `struk-${invoiceNumber}.png`;
        
        document.body.appendChild(a);
        a.click();
        
        // Cleanup
        setTimeout(() => {
          document.body.removeChild(a);
          window.URL.revokeObjectURL(url);
        }, 100);
        
        console.log("Download berhasil: struk-" + invoiceNumber + ".png");
        
        // Show success feedback on mobile
        if (isMobile()) {
          const hint = document.querySelector('.mobile-hint');
          if (hint) {
            const originalText = hint.innerHTML;
            hint.innerHTML = '<span style="color: #27ae60; font-size: 9px; font-weight: 500;">✓ Download berhasil!</span>';
            setTimeout(() => {
              hint.innerHTML = originalText;
            }, 2000);
          }
        }
        
      }, 'image/png', 0.95); // Slightly compressed for mobile
    })
    .catch(function(error) {
      console.error("Error saat membuat canvas:", error);
      // Kembalikan box-shadow jika error
      element.style.boxShadow = originalBoxShadow;
      alert("Gagal membuat gambar struk: " + error.message);
    });
}

// Auto download after page load
window.addEventListener('load', function() {
  console.log("Halaman loaded, menunggu 3 detik...");
  setTimeout(downloadStruk, 3000);
});

// Enhanced touch and click handling
document.addEventListener('DOMContentLoaded', function() {
  const struk = document.getElementById("struk");
  if (struk) {
    // For mobile, use single tap
    if (isMobile()) {
      struk.addEventListener('touchend', function(e) {
        e.preventDefault();
        console.log("Touch detected, downloading...");
        downloadStruk();
      });
    } else {
      // For desktop, use double click
      struk.addEventListener('dblclick', function() {
        console.log("Double click detected, downloading...");
        downloadStruk();
      });
    }
  }
});

// Handle orientation change on mobile
if (isMobile()) {
  window.addEventListener('orientationchange', function() {
    setTimeout(() => {
      // Recalculate sizes after orientation change
      const struk = document.getElementById("struk");
      if (struk) {
        struk.style.width = window.innerWidth <= 768 ? '95vw' : '80mm';
      }
    }, 100);
  });
}
</script>

</body>
</html>