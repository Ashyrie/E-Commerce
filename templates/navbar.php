<?php
if (session_status() == PHP_SESSION_NONE) {
    session_name('client_session');
    session_start();
}

$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$isLoggedIn = isset($_SESSION['customer_id']);
?>
<nav class="navbar navbar-expand-lg bg-body-tertiary text-uppercase sticky-top" style="background-color: rgba(255, 255, 255, 0.95);">
  <div class="container-fluid px-3">
    <a class="navbar-brand h1" href="./homepage.php">
      <img src="assets/img/deltech2.png" alt="<?php echo $lang['Deltech']; ?>" style="height: 40px;">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#MyNavbar" aria-controls="MyNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="MyNavbar">
      <form class="d-flex btn-search mx-lg-auto my-2 my-lg-0" role="search" action="search.php" method="GET">
        <div class="input-group">
          <input class="form-control" type="search" name="q" placeholder="<?php echo $lang['Search'] ?>" aria-label="Search" required="required" />
          <button class="btn btn-outline-primary" type="submit"><?php echo $lang['Search'] ?></button>
        </div>
      </form>
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item">
          <a class="nav-link nav-hover" href="./homepage.php">HOME</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-hover" href="./index.php">PRODUCTS</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-hover" href="./services.php">SERVICES</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-hover" href="./about.php">ABOUT US</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-hover" href="./contact.php">CONTACT US</a>
        </li>
        <li class="nav-item">
          <a class="btn btn-light position-relative cart-btn" href="<?php echo $isLoggedIn ? 'cart.php' : '#'; ?>" <?php if (!$isLoggedIn) echo 'onclick="return false;"'; ?>>
            <i class="fas fa-shopping-cart"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark">
              <?php echo $isLoggedIn ? $cartCount : '0'; ?>
              <span class="visually-hidden">unread messages</span>
            </span>
          </a>
        </li>
        <li class="nav-item dropdown">
          <?php if ($isLoggedIn): ?>
            <a class="nav-link dropdown-toggle profile-icon" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end animate slideIn" aria-labelledby="userDropdown">
              <li class="dropdown-header">
                <span class="fw-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="update-info.php"><i class="fas fa-user-edit me-2"></i>Update Info</a></li>
              <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
          <?php else: ?>
            <a class="nav-link nav-hover" href="login.php"><?php echo $lang['Login']; ?></a>
          <?php endif; ?>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle menu-icon" href="#" id="menuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-bars"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end animate slideIn" aria-labelledby="menuDropdown">
            <li><a class="dropdown-item" href="./faq.php">FAQ</a></li>
            <li><a class="dropdown-item" href="./tracking.php">TRACKING</a></li>
            <li><a class="dropdown-item" href="./chat_list.php">YOUR INQUIRIES</a></li>
            <li><hr class="dropdown-divider"></li>
            
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Logout Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Any item unplaced for reservation will be removed. Are you sure you want to log out?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <form method="POST" action="logout.php">
                    <button type="submit" class="btn btn-primary">Yes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
/* Navbar Styles */
.navbar {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.navbar .nav-link {
    color: #333;
    font-weight: 500;
    padding: 0.5rem 1rem;
    margin: 0 0.2rem;
    border-radius: 4px;
    transition: all 0.3s ease;
}

/* Profile Icon Styles */
.profile-icon, .menu-icon {
    padding: 0.5rem !important;
}

.profile-icon::after, .menu-icon::after {
    display: none !important; 
}

.user-avatar {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.user-avatar i {
    font-size: 1.5rem;
    color: #333; 
}

.menu-icon i {
    font-size: 1.2rem;
    color: #333;
}

.user-avatar:hover {
    background-color: #e9ecef;
    transform: translateY(-2px);
}

.dropdown-header {
    padding: 0.5rem 1rem;
    color: #6c757d;
    background-color: #f8f9fa;
}

/* Enhanced Hover Effect */
.nav-hover {
    position: relative;
}

.nav-hover::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: 0;
    left: 50%;
    background-color: #007bff;
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.nav-hover:hover::after {
    width: 100%;
}

.nav-hover:hover {
    color: #007bff !important;
    background-color: rgba(0, 123, 255, 0.1);
}

/* Search Bar Styles */
.btn-search {
    width: 100%;
    max-width: 400px;
}

@media (max-width: 991.98px) {
    .btn-search {
        max-width: 100%;
        margin: 1rem 0;
    }
}

/* Cart Button Styles */
.cart-btn {
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.cart-btn:hover {
    background-color: #007bff;
    color: white;
}

/* Dropdown Animation */
.animate {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}

.slideIn {
    animation-name: slideIn;
}

@keyframes slideIn {
    0% {
        transform: translateY(1rem);
        opacity: 0;
    }
    100% {
        transform: translateY(0rem);
        opacity: 1;
    }
}

/* Dropdown Styles */
.dropdown-menu {
    border: none;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.dropdown-item {
    padding: 0.7rem 1.5rem;
    transition: all 0.3s ease;
}

.dropdown-item i {
    width: 20px;
    text-align: center;
}

.dropdown-item:hover {
    background-color: rgba(0, 123, 255, 0.1);
    color: #007bff;
}

/* Modal Button Styles */
.modal-footer .btn {
    color: #ffffff;
    transition: all 0.3s ease;
}

.modal-footer .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.modal-footer .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
    transform: translateY(-1px);
}

.modal-footer .btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.modal-footer .btn-primary:hover {
    background-color: #0056b3;
    border-color: #004085;
    transform: translateY(-1px);
}

/* Search button styles */
.btn-search .btn-outline-primary {
    color: #007bff;
    border-color: #007bff;
    transition: all 0.3s ease;
}

.btn-search .btn-outline-primary:hover {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
    transform: translateY(-1px);
}

/* Responsive Adjustments */
@media (max-width: 991.98px) {
    .navbar-nav {
        padding: 1rem 0;
    }
    
    .nav-item {
        margin: 0.2rem 0;
    }
    
    .nav-hover::after {
        display: none;
    }
}
</style>