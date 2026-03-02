<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Salles</title>

    <link rel="stylesheet" href="<?php echo e(asset('css/admin.css')); ?>">
</head>
<body>

<div class="admin-container">

    <!-- Header -->
    <div class="page-header">
        <h2>Gestion des Salles (CSV)</h2>
        <p>Importer et exporter les salles via fichier CSV</p>
    </div>

    <?php if(session('success')): ?>
        <div class="alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="action-card">

        <!-- Download -->
        <div class="action-block">
            <h4>Téléchargement</h4>
            <p>Exporter toutes les salles en fichier CSV</p>
            <a href="<?php echo e(route('salles.csv.download')); ?>" class="admin-btn btn-blue">
                Télécharger CSV
            </a>
        </div>

        <!-- Upload -->
        <div class="action-block">
            <h4>Importation</h4>
            <p>Importer ou mettre à jour les salles depuis un CSV</p>

            <form method="POST" action="<?php echo e(route('salles.csv.upload')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="file" name="csv_file" class="file-input" required>
                <button type="submit" class="admin-btn btn-green">
                    Uploader CSV
                </button>
            </form>
        </div>

    </div>

    <!-- Retour -->
    <div class="logout">
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="admin-btn btn-blue">
            Retour Admin
        </a>
    </div>

</div>

</body>
</html>
<?php /**PATH C:\Users\pc\eduapp\resources\views/admin/salles_csv.blade.php ENDPATH**/ ?>