<?php
include 'init.php';
if (isset($_GET['q'])) {
  $searchQuery = strtolower($_GET['q']);
  // Updated query to include image and currency
  $searchResults = $con->prepare("
    SELECT 
      id, 
      name_product, 
      description_product, 
      price_product, 
      currency, 
      img_product 
    FROM products 
    WHERE LOWER(name_product) LIKE :search
  ");
  $searchResults->bindValue(':search', '%' . $searchQuery . '%');
  $searchResults->execute();
  $products = $searchResults->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
  <div class="product-list my-3">
    <div class="container">
      <?php if (count($products) > 0) : ?>
        <h1>Search Results</h1>
        <div class="row g-3">
          <?php foreach ($products as $product) : ?>
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
      <?php else : ?>
        <div class="alert alert-warning text-center mt-5" role="alert">
          The item you searched for is not available. Please <a href="contact.php">contact us</a> for further assistance.
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
<footer>
  <?php include $tpl . 'footer.php'; ?>
</footer>
</html>
<?php
} else {
  header('Location:./index.php');
}
?>