<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name('admin_session');
    session_start();
}
?>

<nav class="navbar navbar-expand-lg bg-body-tertiary text-uppercase sticky-top" style="background-color: rgba(255, 255, 255, 0.95);">
  <div class="container-fluid px-3">
    <a class="navbar-brand h1" href="./dashboard.php">
      <img src="assets/img/deltech2.png" alt="Deltech" style="height: 40px;">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#MyNavbar" aria-controls="MyNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="MyNavbar">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item">
          <a class="nav-link nav-hover" href="./dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-hover" href="edit-customers.php">Customers</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-hover" href="edit-orders.php">Orders</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-hover" href="./edit-products.php">Products</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-hover" href="contacts.php">Contacts</a>
        </li>
        <li class="nav-item dropdown">
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
            <li><a class="dropdown-item" href="./edit-profile.php">Profile</a></li>
            <li><a class="dropdown-item" href="../">Visit Store</a></li>
            <li><a class="dropdown-item" href="./admin_chat_list.php">Customer Inquiries</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="./logout.php">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

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

.user-avatar:hover {
    background-color: #e9ecef;
    transform: translateY(-2px);
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

.dropdown-item:hover {
    background-color: rgba(0, 123, 255, 0.1);
    color: #007bff;
}
</style>
