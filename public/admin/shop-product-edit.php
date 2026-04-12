<?php
// public/admin/shop-product-edit.php – create or edit a shop product
require_once __DIR__ . '/../init.php';
Auth::requireAdmin();

$productModel = new ShopProductModel();
$id      = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = $id ? $productModel->findById($id) : null;
$isEdit  = (bool) $product;
$errors  = [];

$defaults = [
    'name'         => '',
    'description'  => '',
    'price_cents'  => 0,
    'is_available' => true,
];
$values = $isEdit ? array_merge($defaults, $product) : $defaults;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::verifyCsrf();

    $values['name']         = trim($_POST['name']        ?? '');
    $values['description']  = trim($_POST['description'] ?? '');
    $values['price_cents']  = (int) round((float) str_replace(',', '.', $_POST['price_euros'] ?? '0') * 100);
    $values['is_available'] = isset($_POST['is_available']);

    if ($values['name'] === '') {
        $errors[] = 'Le nom du produit est obligatoire.';
    }
    if ($values['price_cents'] < 0) {
        $errors[] = 'Le prix ne peut pas être négatif.';
    }

    // Handle photo upload
    $newPhotoFilename = null;
    if (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erreur lors du téléchargement de la photo (code ' . $_FILES['photo']['error'] . ').';
        } else {
            $file    = $_FILES['photo'];
            $maxSize = 8 * 1024 * 1024;

            if ($file['size'] > $maxSize) {
                $errors[] = 'La photo est trop volumineuse (8 Mo maximum).';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($file['tmp_name']);
                $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

                if ($mime === false || !array_key_exists($mime, $allowedMimes)) {
                    $errors[] = 'Type de fichier non autorisé. Seuls JPEG, PNG et WebP sont acceptés.';
                } else {
                    $uploadDir = ROOT_DIR . '/public/uploads/shop/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $ext = $allowedMimes[$mime];
                    do {
                        $filename    = bin2hex(random_bytes(16)) . '.' . $ext;
                        $destination = $uploadDir . $filename;
                    } while (file_exists($destination));

                    if (!move_uploaded_file($file['tmp_name'], $destination)) {
                        $errors[] = 'Impossible de sauvegarder la photo. Vérifiez les permissions.';
                    } else {
                        $newPhotoFilename = $filename;
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            $productModel->update($id, $values);
            if ($newPhotoFilename !== null) {
                // Delete old photo file if any
                $oldPhoto = $product['photo_filename'] ?? null;
                if ($oldPhoto) {
                    $oldPath = ROOT_DIR . '/public/uploads/shop/' . $oldPhoto;
                    if (is_file($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $productModel->updatePhoto($id, $newPhotoFilename);
            }
            flash('success', 'Produit modifié avec succès.');
        } else {
            $newId = $productModel->create(array_merge($values, ['photo_filename' => $newPhotoFilename]));
            flash('success', 'Produit créé avec succès.');
        }
        header('Location: ' . APP_BASE_URL . '/admin/shop-products.php');
        exit;
    }
}

$pageTitle = $isEdit ? 'Modifier le produit' : 'Nouveau produit';
include ROOT_DIR . '/templates/header.php';
?>
<div class="container">
    <?php include ROOT_DIR . '/templates/flash.php'; ?>

    <h1 class="page-title"><?= $isEdit ? '✏️ Modifier le produit' : '➕ Nouveau produit' ?></h1>

    <?php if ($errors): ?>
        <div class="flash flash--error">
            <ul style="margin:0;padding-left:1.2rem">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data" style="max-width:600px">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">

        <div class="form-group">
            <label for="name">Nom du produit *</label>
            <input type="text" id="name" name="name" required value="<?= e($values['name']) ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?= e($values['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="price_euros">Prix (€) *</label>
            <input type="number" id="price_euros" name="price_euros" required min="0" step="0.01"
                   value="<?= number_format((int) $values['price_cents'] / 100, 2, '.', '') ?>">
        </div>

        <div class="form-group form-group--checkbox">
            <input type="checkbox" id="is_available" name="is_available" value="1"
                   <?php
                   $avail = $values['is_available'];
                   if ($avail === true || $avail === 't' || $avail === 1 || $avail === '1') echo 'checked';
                   ?>>
            <label for="is_available">✅ Produit disponible à la vente</label>
        </div>

        <div class="form-group">
            <label for="photo">Photo du produit (JPEG, PNG ou WebP, 8 Mo max)</label>
            <?php if ($isEdit && !empty($product['photo_filename'])): ?>
                <div style="margin-bottom:.5rem">
                    <img src="<?= APP_BASE_URL ?>/uploads/shop/<?= e($product['photo_filename']) ?>"
                         alt="<?= e($product['name']) ?>"
                         style="width:120px;height:120px;object-fit:cover;border-radius:8px">
                    <p style="font-size:.85rem;color:var(--color-muted);margin-top:.25rem">
                        Photo actuelle. Sélectionnez un nouveau fichier pour la remplacer.
                    </p>
                </div>
            <?php endif; ?>
            <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
        </div>

        <div style="display:flex;gap:1rem;margin-top:1.5rem">
            <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Enregistrer' : 'Créer le produit' ?></button>
            <a href="<?= APP_BASE_URL ?>/admin/shop-products.php" class="btn btn--secondary">Annuler</a>
        </div>
    </form>
</div>
<?php include ROOT_DIR . '/templates/footer.php'; ?>
