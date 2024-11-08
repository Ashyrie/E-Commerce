<?php
ob_start(); // Start output buffering
session_start();
$pageTitle = 'Product';
include './init.php';
include 'Parsedown.php';
$Parsedown = new Parsedown();
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Updated SQL query to include the currency directly
$DetailsProducts = $con->prepare("
    SELECT 
        p.*, 
        c.currency 
    FROM 
        products p
    JOIN 
        currencies c ON p.currency = c.currency 
    WHERE 
        p.id = ?
");
$DetailsProducts->execute(array($id));
$product = $DetailsProducts->fetch(PDO::FETCH_ASSOC);

if (!$product) {
?>
  <div class="container">
    <div class="alert alert-warning text-center mt-5" role="alert">
      Product not found
    </div>
  </div>
<?php
  header('Refresh: 6; url=index.php');
} else {
  $test = $product['description_product'];
  $description = $Parsedown->text($test);
  
  // Determine if the user is logged in
  $isLoggedIn = isset($_SESSION['customer_id']);
?>
  <div class="product-list">
    <div class="container">
      <a class="btn btn-light my-2" href="./index.php"><i class="fa fa-backward" aria-hidden="true"></i>&nbsp;Back</a>
      <div class="row g-3">
        <div class="col-md-4">
          <img class="img-dProduct" src="<?php echo htmlspecialchars($dirs . $product['img_product']); ?>" alt="<?php echo htmlspecialchars($product['name_product']); ?>" />
        </div>
        <div class="col-md-8">
          <?php if (isset($_SESSION['message'])) : ?>
            <div id="message">
              <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); endif; ?>
          <h1><?php echo htmlspecialchars($product['name_product']); ?></h1>
          <h2>Price: <?php echo number_format($product['price_product'], 2) . ' ' . htmlspecialchars($product['currency']); ?></h2>
          <p class="fw-bold">
            <?php if ($product['stock_product'] > 0) : ?>
              <span class="text-success">The product is available in stock.</span>
            <?php else : ?>
              <span class="text-danger">The product is not available in stock.</span>
            <?php endif; ?>
          </p>
          <div class="desc_product border-top border-bottom border-dark py-1 my-1">
            <span class="text-secondary">Description :</span>
            <?php echo $description; ?>
          </div>
          <form action="./cart.php?do=add-cart" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>" />
            <div class="input-group mb-3">
              <span class="input-group-text" id="quantity">Quantity</span>
              <input type="number" class="form-control" name="quantity" aria-label="quantity" aria-describedby="quantity" value="1" min="1" max="<?php echo htmlspecialchars($product['stock_product']); ?>">
              <button class="btn btn-primary" type="submit" name="add_to_cart" <?php if (!$isLoggedIn || $product['stock_product'] < 1) : ?>disabled<?php endif; ?>>
                <i class="fa-solid fa-cart-plus"></i>&nbsp;Reserve
              </button>
            </div>
          </form>
          <?php
          // Display error message if user is not logged in
          if (!$isLoggedIn) {
              echo '<div class="alert alert-danger">You need to log in to reserve products.</div>';
          }
          ?>
        </div>
      </div>
    </div>
  </div>
<?php
}
include $tpl . 'footer.php'; 
ob_end_flush(); // End output buffering
?>
