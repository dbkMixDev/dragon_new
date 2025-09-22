<!DOCTYPE html>
<html lang="id">
  
    <?php
    include('include/config.php');
      // WIB
    $dateTime = date('d M Y H:i:s');

    $inv = $_GET['inv']; // Example invoice number
   
    
    // Prepare the SQL query
    $sql = "SELECT * FROM tb_finnal_trans WHERE  invoice = '$inv'";
    
    // Execute the query
    $result = mysqli_query($conn, $sql);
    
    // Check for errors
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
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
      padding: 20px;
      background-color: #f0f0f0;
    }

    #struk {
      width: 58mm;
      max-width: 58mm; /* Pastikan lebar tidak lebih dari 58mm */
      padding: 10px;
      background: white;
      font-size: 12px;
      color: #000;
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
    <div style="width: 100%; height: 70px; overflow: hidden; position: relative;">
  <img src="assets/images/logostruk.png" alt="Logo Strip"
       style="width: 100%; position: absolute; top: 50%; left: 0; transform: translateY(-50%);">
</div>



      <h3>Zian PlayStation</h3>
      <p>
      Jl. Tusam Raya No.L27, Pedalangan, Kec. Banyumanik<br />
        Kota Semarang<br />
        087875015802
      </p>
      <hr />
      <p><?=$dateTime?><br/>PlayStation No.<?=($row['no_ps'] == 0 ? "GUEST" : $row['no_ps'])?></p>
    </center>

    <p>TransID:<?=$inv?></p>
    <table>
<?php
 $sql_details = "SELECT * FROM tb_trans WHERE payment = '$inv'";
 $result_details = mysqli_query($conn, $sql_details);

 // Check if query is successful
 if (!$result_details) {
     die("Query failed: " . mysqli_error($conn));
 }
 // Loop through each transaction detail
 
 while ($detail = mysqli_fetch_array($result_details, MYSQLI_ASSOC)) {
    $extra = ($detail['extra'] == 1) ? "Ext" : "Reg";
    echo "<tr><td>1x($extra) {$detail['durasi']} Min</td><td style='text-align: right;'>Rp". number_format($detail['harga'], 0, ',', '.') . "</td></tr>";
}

?>
<?php
 $sql_details2 = "SELECT 
   
    SUM(t.qty) AS qty, 
    sum(t.total_harga) as harga, 
    t.date_time, 
    t.no_ps,
    c.harga as priceitem,
    c.nama AS item_name, 
    c.id as id_canteen
FROM 
    tb_trans_canteen t
JOIN 
    tb_canteen c ON t.id_canteen = c.id
WHERE 
 t.payment = '$inv'
GROUP BY 
    c.id
ORDER BY 
    t.id
   ";
 $result_details2 = mysqli_query($conn, $sql_details2);

 // Check if query is successful
 if (!$result_details2) {
     die("Query failed: " . mysqli_error($conn));
 }
 // Loop through each transaction detail
 
 while ($detail2 = mysqli_fetch_array($result_details2, MYSQLI_ASSOC)) {
   
    echo "<tr>
    <td>{$detail2['qty']}x({$detail2['item_name']})</td>
    <td style='text-align: right;'>Rp". number_format($detail2['harga'], 0, ',', '.') . "</td>
  </tr>";
  
}

?>
    </table>

    <hr />
    <table>
    <tr><td>Total</td><td style="text-align: right;">Rp<?=number_format($row['total'], 0, ',', '.')?></td></tr>
    <tr>
  <td>Bayar (<?= $row['method']; ?>)</td>
  <td style="text-align: right;">
  Rp<?= $row['method'] == "cash" 
    ? number_format(floatval($row['bayar']), 0, ',', '.') 
    : number_format(floatval($row['total']), 0, ',', '.'); ?>

</td>
</tr>
<?php if ($row['method'] == "cash"): ?>
<tr>

  <td>Kembali</td>
  <td style="text-align: right;">Rp<?= number_format(floatval($row['kembali']), 0, ',', '.'); ?></td>
</tr>
<?php endif; ?>



    </table>
    <center><p>Di sini kita lawan, di luar tetap kawan. Enjoy the game, bro!</p></center>
  </div>


  <script>
     const invoiceNumber = "<?= $inv ?>"; // Ambil dari PHP
  
     window.onload = function () {
        setTimeout(() => {
    const element = document.getElementById("struk");
    const preview = document.getElementById("preview");

    if (!element) {
      console.error("Element #struk tidak ditemukan!");
      return;
    }

    html2canvas(element, { scale: 4 }).then(canvas => {
      // Bersihkan preview dulu (jika ada)
      if (preview) {
        preview.innerHTML = "";
        preview.appendChild(canvas); // Preview
      }

      // Auto-download gambar
      const link = document.createElement("a");
      link.href = canvas.toDataURL("image/jpeg", 1);
      link.download = `struk-${invoiceNumber}.jpg`;
      document.body.appendChild(link); // Penting di beberapa browser
      link.click();
      document.body.removeChild(link);
    }).catch(error => {
      console.error("Gagal generate gambar:", error);
    });
}, 500); // delay 0.5 detik
};
  </script>


  </script>

</body>
</html>
