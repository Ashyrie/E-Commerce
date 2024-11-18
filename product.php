<?php
ob_start(); // Start output buffering
session_name('client_session');
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
          <img class="img-dProduct" src="<?php echo htmlspecialchars($dirs . $product['img_product']); ?>" alt="<?php echo htmlspecialchars($product['name_product']); ?>" style="max-width: 100%; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);"/>
        </div>
        <div class="col-md-8">
          <?php if (isset($_SESSION['message'])) : ?>
            <div id="message">
              <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); endif; ?>
          <h1 style="font-size: 2rem; font-weight: 600; color: #333;"><?php echo htmlspecialchars($product['name_product']); ?></h1>
          <h2 style="font-size: 1.6rem; color: #007bff; margin-top: 10px;"><?php echo number_format($product['price_product'], 2) . ' ' . htmlspecialchars($product['currency']); ?></h2>
          <p class="fw-bold" style="font-size: 1.1rem; margin-top: 15px;">
            <?php if ($product['stock_product'] > 0) : ?>
              <span class="text-success">The product is available in stock.</span>
            <?php else : ?>
              <span class="text-danger">The product is not available in stock.</span>
            <?php endif; ?>
          </p>
          <div class="desc_product border-top border-bottom border-dark py-1 my-1">
            <span class="text-secondary" style="font-weight: 500; font-size: 1.1rem;">Description :</span>
            <div style="font-size: 1rem; line-height: 1.5; margin-top: 15px;"><?php echo $description; ?></div>
          </div>
          <form action="./cart.php?do=add-cart" method="post">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>" />
    <div class="input-group mb-3" style="max-width: 700px; margin-top: 20px;">
        <span class="input-group-text" id="quantity" style="font-size: 1.1rem; font-weight: 500; background-color: #f8f9fa; border-radius: 5px;">Quantity</span>
        <input type="number" class="form-control" name="quantity" aria-label="quantity" aria-describedby="quantity" value="1" min="1" max="<?php echo htmlspecialchars($product['stock_product']); ?>" style="text-align: center; padding: 5px 10px; font-size: 1rem; width: 70px"/>

        <!-- Reserve Button -->
        <button class="btn btn-primary" type="submit" name="add_to_cart" <?php if (!$isLoggedIn || $product['stock_product'] < 1) : ?>disabled<?php endif; ?> style="padding: 10px 20px; font-size: 1.1rem; border-radius: 5px; background-color: #007bff; color: white; border: none; cursor: pointer; margin-left: 10px;">
            <i class="fa-solid fa-cart-plus"></i>&nbsp;Reserve
        </button>

        <!-- Inquire Button -->
        <a href="chat.php?product_id=<?php echo htmlspecialchars($product['id']); ?>&customer_id=<?php echo $_SESSION['customer_id']; ?>" class="btn btn-secondary" style="padding: 10px 20px; font-size: 1.1rem; border-radius: 5px; background-color: #6c757d; color: white; border: none; cursor: pointer; margin-left: 10px;">
    <i class="fa fa-comment" aria-hidden="true"></i>&nbsp;Inquire
</a>
    </div>
</form>
          <?php
          // Display error message if user is not logged in
          if (!$isLoggedIn) {
              echo '<div class="alert alert-danger" style="font-size: 1.1rem; margin-top: 20px;">You need to log in to reserve products.</div>';
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
