<?php
ob_start();
session_name('admin_session');
session_start();
$pageTitle = 'Product';
include './init.php';

if (isset($_SESSION['username'])) {
    $do = isset($_GET['do']) ? $_GET['do'] : 'dashboard';

    if ($do === 'dashboard') {
        $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
        $orderBy = (isset($_GET['order']) && $_GET['order'] === 'price') ? 'price_product DESC' : 'created_at DESC';

        // Prepare the SQL query with search functionality
        $ListProducts = $con->prepare("SELECT * FROM `products` WHERE `name_product` LIKE ? ORDER BY $orderBy");
        $ListProducts->execute(['%' . $searchTerm . '%']);
        $products = $ListProducts->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="products">
            <div class="container">
                <h1>Products&nbsp;<a class="btn btn-outline-primary" href="./edit-products.php?do=add-new">Add New</a></h1>

                <form action="./edit-products.php?do=dashboard" method="get" class="mb-3">
                    <input type="hidden" name="do" value="dashboard">
                    <div class="input-group">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search by product name" class="form-control" />
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <?php if (isset($_SESSION['message'])) : ?>
                        <div id="message" class="alert alert-success">
                            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                        </div>
                    <?php endif; ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>
                                    <a href="./edit-products.php?do=dashboard&search=<?php echo urlencode($searchTerm); ?>&order=price" class="text-decoration-none">Price</a>
                                </th>
                                <th>Stock</th>
                                <th>Date</th>
                                <th>Controller</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product) : ?>
                                <tr>
                                    <td>
                                        <?php
                                        $imagePath = '../uploads/' . $product['img_product'];
                                        $defaultImage = '../path/to/default/image.jpg';
                                        $imgSrc = file_exists($imagePath) ? $imagePath : $defaultImage;
                                        ?>
                                        <img class="img-avatar-product rounded-circle" src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($product['name_product']); ?>">
                                        <?php echo htmlspecialchars($product['name_product']); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $currency = !empty($product['currency']) ? $product['currency'] : 'PHP'; 
                                        echo htmlspecialchars($product['price_product']) . ' ' . htmlspecialchars($currency); 
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['stock_product']); ?></td>
                                    <td><?php echo htmlspecialchars($product['created_at']); ?></td>
                                    <td>
                                        <form action="./edit-products.php?do=action" method="post">
                                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                            <div class="d-grid gap-2 d-md-block">
                                                <button type="submit" class="btn btn-success" name="btn_edit"><i class="fa-solid fa-pen-to-square"></i>&nbsp;Edit</button>
                                                <a href="../product.php?id=<?php echo htmlspecialchars($product['id']); ?>" target="_blank" class="btn btn-info"><i class="fa-solid fa-eye"></i>&nbsp;View</a>
                                                <button type="submit" class="btn btn-danger" name="btn_delete"><i class="fa-solid fa-trash"></i>&nbsp;Delete</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    } elseif ($do === 'add-new') {
        ?>
        <div class="add-new">
            <div class="container">
                <a class="btn btn-light my-2" href="./edit-products.php"><i class="fa fa-backward" aria-hidden="true"></i>&nbsp;Back</a>
                <div class="col-md-6 mx-auto">
                    <h1>Add new product</h1>
                    <form action="./edit-products.php?do=create-true" method="post" class="py-3" enctype="multipart/form-data">
                        <?php if (isset($_SESSION['message'])) : ?>
                            <div id="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                        <?php endif; ?>
                        <div class="form-group mb-3">
                            <span class="label">Name</span>
                            <input class="form-control" name="name_product" required />
                        </div>
                        <div class="form-group mb-3">
                            <span class="label">Price</span>
                            <input class="form-control" name="price_product" required />
                        </div>
                        <div class="form-group mb-3">
                            <span class="label">Description</span>
                            <textarea name="description_product" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <span class="label">Stock</span>
                            <input class="form-control" type="number" name="stock_product" required />
                        </div>
                        <div class="form-group mb-3">
                            <span class="label">Currency</span>
                            <select name="currency" class="form-control" required>
                                <?php
                                $currencies = $con->query("SELECT * FROM `currencies`")->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($currencies as $currency) {
                                    echo '<option value="' . htmlspecialchars($currency['currency']) . '">' . htmlspecialchars($currency['currency']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <span class="label">Image</span>
                            <input class="form-control" type="file" name="img_product" required />
                        </div>
                        <button type="submit" class="btn btn-primary" name="create_true">Publish</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    } elseif ($do === 'create-true') {
        if (isset($_POST['create_true'])) {
            $name_product = $_POST['name_product'];
            $description_product = $_POST['description_product'];
            $price_product = $_POST['price_product'];
            $stock_product = $_POST['stock_product'];
            $currency = $_POST['currency'];

            // Check if currency exists
            $currencyCheck = $con->prepare("SELECT * FROM `currencies` WHERE `currency` = ?");
            $currencyCheck->execute([$currency]);
            if (!$currencyCheck->fetch()) {
                show_message('Selected currency is not valid.', 'danger');
                header('location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }

            if (!empty($_FILES['img_product']['name'])) {
                $upload_dir = '../uploads/';
                $file_ext = pathinfo($_FILES['img_product']['name'], PATHINFO_EXTENSION);
                $img_product = uniqid() . '.' . $file_ext;
                $img_product_path = $upload_dir . $img_product;
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array(strtolower($file_ext), $allowed_types)) {
                    show_message('Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.', 'danger');
                    header('location: ' . $_SERVER['HTTP_REFERER']);
                    exit();
                }

                $max_file_size = 5 * 1024 * 1024; // 5MB
                if ($_FILES['img_product']['size'] > $max_file_size) {
                    show_message('File size exceeds the allowed limit (5MB).', 'danger');
                    header('location: ' . $_SERVER['HTTP_REFERER']);
                    exit();
                }

                if (!move_uploaded_file($_FILES['img_product']['tmp_name'], $img_product_path)) {
                    show_message('Failed to upload image!', 'danger');
                    header('location: ' . $_SERVER['HTTP_REFERER']);
                    exit();
                }

                $stmt = $con->prepare("INSERT INTO `products`(`name_product`, `description_product`, `price_product`, `img_product`, `stock_product`, `currency`) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name_product, $description_product, $price_product, $img_product, $stock_product, $currency]);
                show_message('Product added successfully', 'success');
                header('location: edit-products.php');
                exit();
            } else {
                show_message('No image selected!', 'danger');
                header('location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }
        } else {
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    } elseif ($do === 'action') {
        if (isset($_POST['btn_edit'])) {
            $id = $_POST['id'];
            $edit = productInfo($con, $id);
            ?>
            <div class="add-new">
                <div class="container">
                    <a class="btn btn-light my-2" href="./edit-products.php"><i class="fa fa-backward" aria-hidden="true"></i>&nbsp;Back</a>
                    <div class="row">
                        <div class="col-md-4">
                            <?php
                            $imagePath = '../uploads/' . $edit['img_product'];
                            $defaultImage = '../path/to/default/image.jpg';
                            $imgSrc = file_exists($imagePath) ? $imagePath : $defaultImage;
                            ?>
                            <img class="img-fluid" src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($edit['name_product']); ?>">
                        </div>
                        <div class="col-md-8">
                            <h1>Edit product: <?php echo htmlspecialchars($edit['name_product']); ?></h1>
                            <form action="./edit-products.php?do=update-true" method="post" class="py-3" enctype="multipart/form-data">
                                <?php if (isset($_SESSION['message'])) : ?>
                                    <div id="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                                <?php endif; ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit['id']); ?>">
                                <div class="form-group mb-3">
                                    <span class="label">Name</span>
                                    <input class="form-control" name="name_product" value="<?php echo htmlspecialchars($edit['name_product']); ?>" required />
                                </div>
                                <div class="form-group mb-3">
                                    <span class="label">Price</span>
                                    <input class="form-control" name="price_product" value="<?php echo htmlspecialchars($edit['price_product']); ?>" required />
                                </div>
                                <div class="form-group mb-3">
                                    <span class="label">Description</span>
                                    <textarea name="description_product" class="form-control" rows="9" required><?php echo htmlspecialchars($edit['description_product']); ?></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <span class="label">Stock</span>
                                    <input class="form-control" type="number" name="stock_product" value="<?php echo htmlspecialchars($edit['stock_product']); ?>" required />
                                </div>
                                <div class="form-group mb-3">
                                    <span class="label">Currency</span>
                                    <select name="currency" class="form-control" required>
                                        <?php
                                        $currencies = $con->query("SELECT * FROM `currencies`")->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($currencies as $currency) {
                                            $selected = ($currency['currency'] === $edit['currency']) ? 'selected' : '';
                                            echo '<option value="' . htmlspecialchars($currency['currency']) . '" ' . $selected . '>' . htmlspecialchars($currency['currency']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <span class="label">Image</span>
                                    <input class="form-control" type="file" name="img_product" />
                                </div>
                                <button type="submit" class="btn btn-primary" name="updated">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        } elseif (isset($_POST['btn_delete'])) {
            $id = $_POST['id'];
            $stmt = $con->prepare("DELETE FROM products WHERE `id` = ?");
            $stmt->execute([$id]);
            show_message('Product deleted successfully', 'success');
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            header('location: edit-products.php');
            exit();
        }
    } elseif ($do === 'update-true') {
        if (isset($_POST['updated'])) {
            $id = $_POST['id'];
            $name_product = $_POST['name_product'];
            $description_product = $_POST['description_product'];
            $price_product = $_POST['price_product'];
            $stock_product = $_POST['stock_product'];
            $currency = $_POST['currency'];

            // Check if currency exists
            $currencyCheck = $con->prepare("SELECT * FROM `currencies` WHERE `currency` = ?");
            $currencyCheck->execute([$currency]);
            if (!$currencyCheck->fetch()) {
                show_message('Selected currency is not valid.', 'danger');
                header('location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }

            if (!empty($_FILES['img_product']['name'])) {
                $upload_dir = '../uploads/';
                $file_ext = pathinfo($_FILES['img_product']['name'], PATHINFO_EXTENSION);
                $img_product = uniqid() . '.' . $file_ext;
                $img_product_path = $upload_dir . $img_product;
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array(strtolower($file_ext), $allowed_types)) {
                    show_message('Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.', 'danger');
                    header('location: ' . $_SERVER['HTTP_REFERER']);
                    exit();
                }

                $max_file_size = 5 * 1024 * 1024; // 5MB
                if ($_FILES['img_product']['size'] > $max_file_size) {
                    show_message('File size exceeds the allowed limit (5MB).', 'danger');
                    header('location: ' . $_SERVER['HTTP_REFERER']);
                    exit();
                }

                if (!move_uploaded_file($_FILES['img_product']['tmp_name'], $img_product_path)) {
                    show_message('Failed to upload image!', 'danger');
                    header('location: ' . $_SERVER['HTTP_REFERER']);
                    exit();
                }

                $stmt = $con->prepare("UPDATE `products` SET `name_product`= ?, `description_product`= ?, `price_product`= ?, `img_product`= ?, `stock_product`= ?, `currency`= ? WHERE `id`= ?");
                $stmt->execute([$name_product, $description_product, $price_product, $img_product, $stock_product, $currency, $id]);
            } else {
                $stmt = $con->prepare("UPDATE `products` SET `name_product`= ?, `description_product`= ?, `price_product`= ?, `stock_product`= ?, `currency`= ? WHERE `id`= ?");
                $stmt->execute([$name_product, $description_product, $price_product, $stock_product, $currency, $id]);
            }

            show_message('Product updated successfully', 'success');
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    } else {
        // Handle other cases
    }
} else {
    header('location: index.php');
    exit();
}

include $tpl . 'footer.php';
ob_end_flush();