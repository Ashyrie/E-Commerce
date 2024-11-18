<?php
session_name('client_session');
session_start();
$pageTitle = 'Shop';
include './init.php';

$stmt = $con->prepare("
    SELECT 
        p.id, 
        p.name_product, 
        p.description_product, 
        p.price_product, 
        p.currency, 
        p.img_product, 
        p.stock_product, 
        p.created_at 
    FROM 
        products p
    ORDER BY 
        p.created_at DESC
");
$stmt->execute();
$ListProducts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/main.css"> 
</head>
<body>

<div class="product-list my-3">
  <div class="container">
    <h1><?php echo $lang['Last Products'] ?></h1>
    <div class="row g-3">
      <?php foreach ($ListProducts as $product) : ?>
        <div class="col-md-3">
          <div class="card">
            <img class="card-img-top" src="<?php echo $dirs . $product['img_product'] ?>" alt="<?php echo $product['name_product'] ?>">
            <div class="card-body">
              <a href="product.php?id=<?php echo $product['id'] ?>">
                <h2 class="card-title"><?php echo $product['name_product'] ?></h2>
              </a>
              <p class="card-text"><?php echo $product['price_product'] . ' ' . $product['currency'] ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
</body>

<footer>
<?php include $tpl . 'footer.php'; ?>
</footer>
</html>
