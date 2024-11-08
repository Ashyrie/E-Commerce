<?php
function show_message($message, $type = 'success') {
    if ($type == 'success') {
        $_SESSION['message'] = '<div class="alert alert-success">' . $message . '</div>';
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger">' . $message . '</div>';
    }
}

function userInfo($con, $id) {
    $stmt = $con->prepare("SELECT `id`, `username`, `password`, `fullname`, `email`, `biographical`, `phone`, `created` FROM `admin` WHERE `id` = ? LIMIT 1");
    $stmt->execute(array($id));
    return $stmt->fetch();
}

function generateOrderNumber($con) {
    $orderNumber = '#' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $query = $con->prepare("SELECT COUNT(*) FROM orders WHERE orders_number = ?");
    $query->execute([$orderNumber]);
    $count = $query->fetchColumn();
    if ($count > 0) {
        return generateOrderNumber($con);
    }
    return $orderNumber;
}

function getTitle() {
    global $pageTitle;
    if (isset($pageTitle)) {
        echo $pageTitle;
    } else {
        echo 'Default';
    }
}

function pageActive($currentPage, $pageName) {
    return $currentPage == $pageName ? 'active' : '';
}

function displayFAQ($faqItems) {
    ?>
    <div class="accordion" id="faqAccordion">
        <?php foreach ($faqItems as $index => $item) : ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="faqHeading<?php echo $index ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse<?php echo $index ?>" aria-expanded="false" aria-controls="faqCollapse<?php echo $index ?>">
                        <?php echo $item['question'] ?>
                    </button>
                </h2>
                <div id="faqCollapse<?php echo $index ?>" class="accordion-collapse collapse" aria-labelledby="faqHeading<?php echo $index ?>" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <p><?php echo $item['answer'] ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
    <?php
}

function generatePDF($fullName, $phone, $email, $note_customer, $orderDetails, $orders_number) {
  require('./FPDF/fpdf.php'); // Include FPDF library

  $pdf = new FPDF();
  $pdf->AddPage();
  
  // Title
  $pdf->SetFont('Arial', 'B', 16);
  $pdf->Cell(0, 10, 'Receipt for Order Number: ' . htmlspecialchars($orders_number), 0, 1, 'C');

  // Customer details
  $pdf->SetFont('Arial', '', 12);
  $pdf->Cell(0, 10, 'Customer Name: ' . htmlspecialchars($fullName), 0, 1);
  $pdf->Cell(0, 10, 'Phone: ' . htmlspecialchars($phone), 0, 1);
  $pdf->Cell(0, 10, 'Email: ' . htmlspecialchars($email), 0, 1);
  $pdf->Cell(0, 10, 'Note: ' . nl2br(htmlspecialchars($note_customer)), 0, 1);
  $pdf->Ln(10); // Add a line break

  // Order details header
  $pdf->SetFont('Arial', 'B', 12);
  $pdf->Cell(60, 10, 'Product', 1);
  $pdf->Cell(30, 10, 'Quantity', 1);
  $pdf->Cell(40, 10, 'Price', 1);
  $pdf->Cell(40, 10, 'Total', 1);
  $pdf->Ln();

  // Order details
  $pdf->SetFont('Arial', '', 12);
  foreach ($orderDetails as $item) {
      $total = $item['product_quantity'] * $item['product_price'];
      $pdf->Cell(60, 10, htmlspecialchars($item['product_name']), 1);
      $pdf->Cell(30, 10, htmlspecialchars($item['product_quantity']), 1);
      $pdf->Cell(40, 10, htmlspecialchars(number_format($item['product_price'], 2)), 1);
      $pdf->Cell(40, 10, htmlspecialchars(number_format($total, 2)), 1);
      $pdf->Ln();
  }

  // Output the PDF
  $pdf->Output('D', 'receipt_' . $orders_number . '.pdf'); // Download the PDF
}



?>
