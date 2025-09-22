<!DOCTYPE html>
<html lang="id">
  
    <?php
    include('../include/config.php');
    session_start();
    $username = $_SESSION['username'];
      // WIB
  
    


 $r = $con->query("SELECT * 
FROM userx 
WHERE username ='$username'");

                                        foreach ($r as $rr) {
                                            $merchand = $rr['merchand'];
                                             $address = $rr['address'];
                                              $logox = $rr['logox'];
                                              $timezone = $rr['timezone'];
                                        }
                                        
date_default_timezone_set($timezone);
  $dateTime = date('d M Y H:i:s');

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
  <title>Struk Biling</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>


  <style>
    body {
      font-family: monospace;
      margin: 0;
      width: 58mm;
     
      background: white;
    }

    #struk {
      width: 58mm;
      max-width: 58mm; /* Pastikan lebar tidak lebih dari 58mm */
      padding: 20px;
    
      font-size: 15px;
      color: #000;
    }

    .logo-container {
      width: 100%; 
      height: 50px; 
      overflow: hidden; 
      position: relative;
      /* margin-bottom: 10px; */
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .logo-container img {
      max-width: 100%;
      max-height: 50px;
      width: auto;
      height: auto;
      object-fit: contain;
    }

    hr {
      border: none;
      border-top: 1px dashed #000;
      margin: 5px 0;
    }

    table {
      width: 100%;
    }

    td {
      vertical-align: top;
    }

    #buttons {
      margin-top: 20px;
    }

    button, a {
      padding: 8px 12px;
      background-color: #333;
      color: white;
      text-decoration: none;
      border: none;
      border-radius: 4px;
      margin-right: 10px;
      cursor: pointer;
    }

    button:hover, a:hover {
      background-color: #555;
    }
  </style>
</head>
<body>

  <div id="struk">
    <center>
      <!-- Logo Section -->
     
      <div class="logo-container">
        <img src="../<?=$logox?>" alt="Logo" onerror="this.style.display='block'">
      </div>

      <h2><?=$merchand?></h2>
      <p>
      <?=$address?>
      </p>
      <hr />
      <p><?=$dateTime?><br/>PlayStation No.<?=($row['id_ps'] == 0 ? "GUEST" : $row['id_ps'])?></p>
    </center>

    <p>TransID:<?=$inv?></p>
    <table>
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

 // Check if query is successful
 if (!$result_details) {
     die("Query failed: " . mysqli_error($con));
 }
 // Loop through each transaction detail
 
 while ($detail = mysqli_fetch_array($result_details, MYSQLI_ASSOC)) {
  
   echo "<tr><td>" . (strlen($detail['details']) > 16 ? substr($detail['details'], 0, 16) . ".." : $detail['details']) . "</td>
      <td style='text-align: right;'>Rp" . number_format($detail['harga'], 0, ',', '.') . "</td></tr>";

}

?>

    </table>

    <hr />
    <table>
    <tr><td>Total</td><td style="text-align: right;">Rp<?=number_format($row['grand_total']+$row['promo'], 0, ',', '.')?></td></tr>
    <?php if ($row['promo']>0){?>
                <tr><td>Disc</td><td style="text-align: right;">(Rp<?=number_format($row['promo'], 0, ',', '.')?>)</td></tr>
    <?php } ?>
    <tr>
  <td>Bayar (<?= ( $row['metode_pembayaran'] === 'debit') ? 'Transfer' :  $row['metode_pembayaran']; ?>)</td>
  <td style="text-align: right;">
  Rp<?= $row['metode_pembayaran'] == "cash" 
    ? number_format(floatval($row['bayar']), 0, ',', '.') 
    : number_format(floatval($row['grand_total']), 0, ',', '.'); ?>

</td>
</tr>
<?php if ($row['metode_pembayaran'] == "cash"): ?>
<tr>

  <td>Kembali</td>
  <td style="text-align: right;">Rp<?= number_format(floatval($row['kembali']), 0, ',', '.'); ?></td>
</tr>
<?php endif; ?>



    </table>
  <center>
  <p>Pilih Billing Cerdas<br>Owner Pasti Puas!.</p>
  <p style="font-size: 12px; color: gray;">dragonplay.id</p>
</center>

  </div>

  <script>
     const invoiceNumber = "<?= $inv ?>"; // Ambil dari PHP
     
     window.onload = function () {
        setTimeout(() => {
            // Menampilkan fungsi print setelah 1.5 detik
            window.print();

            // Menutup jendela setelah 2 detik
            setTimeout(function(){
                window.close();
            }, 2000); // Waktu tunggu 2 detik setelah print
        }, 1500); // delay 1.5 detik sebelum print
    };
</script>


</body>
</html>