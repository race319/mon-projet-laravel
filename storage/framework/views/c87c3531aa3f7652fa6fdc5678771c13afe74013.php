<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Admin</title>

    <link rel="stylesheet" href="<?php echo e(asset('css/admin.css')); ?>">
</head>
<body>

<div class="admin-container">

  
    <div class="admin-header">
        <h2>Espace Administrateur</h2>
        <p>Bienvenue <?php echo e(auth()->user()->name); ?></p>
    </div>

    
    <div class="admin-cards">

        <div class="admin-card">
            <h3>Séances</h3>
            <p>Gestion des séances via fichiers CSV</p>
            <a href="<?php echo e(route('seances.csv.page')); ?>" class="admin-btn btn-blue">
                Gérer
            </a>
        </div>

        <div class="admin-card">
            <h3>Vœux Enseignement</h3>
            <p>Gestion des vœux d’enseignement</p>
            <a href="<?php echo e(route('voeux_enseignement.csv.page')); ?>" class="admin-btn btn-green">
                Gérer
            </a>
        </div>

        <div class="admin-card">
            <h3>Vœux Examen</h3>
            <p>Gestion des vœux d’examen</p>
            <a href="<?php echo e(route('voeux_examen.csv.page')); ?>" class="admin-btn btn-orange">
                Gérer
            </a>
        </div>
        <div class="admin-card">
    <h3>Absences</h3>
    <p>Gestion des absences des étudiants</p>
    <a href="<?php echo e(route('absences.csv.page')); ?>" class="admin-btn btn-blue">
        Gérer
    </a>
</div>

<div class="admin-card">
    <h3>Enseignants</h3>
    <p>Gestion des charges des enseignants</p>
    <a href="<?php echo e(route('enseignants.csv.page')); ?>" class="admin-btn btn-blue">
        Gérer
    </a>
</div>

<div class="admin-card">
    <h3>Enseignements</h3>
    <p>Gestion des enseignements des enseignants</p>
    <a href="<?php echo e(route('enseignements.csv.page')); ?>" class="admin-btn btn-blue">
        Gérer
    </a>
</div>
<div class="admin-card">
    <h3>Groupes</h3>
    <p>Gestion des groupes d’étudiants</p>
    <a href="<?php echo e(route('groupes.csv.page')); ?>" class="admin-btn btn-blue">
        Gérer
    </a>
</div>

<div class="admin-card">
    <h3>Inscriptions</h3>
    <p>Gestion des inscriptions des étudiants</p>
    <a href="<?php echo e(route('inscriptions.csv.page')); ?>" class="admin-btn btn-blue">
        Gérer
    </a>
</div>



<div class="admin-card">
    <h3>Horaires</h3>
    <p>Gestion des horaires des séances</p>
    <a href="<?php echo e(route('horaires.csv.page')); ?>" class="admin-btn btn-blue">
        Gérer
    </a>
</div>


<div class="admin-card">
    <h3>Matières</h3>
    <p>Gestion des matières </p>
    <a href="<?php echo e(route('matieres.csv.page')); ?>" class="admin-btn btn-blue">
        Gérer
    </a>
</div>

<div class="admin-card">
    <h3>Salles</h3>
    <p>Gestion des salles</p>
    <a href="<?php echo e(route('salles.csv.page')); ?>" class="admin-btn btn-blue">
        Gérer
    </a>
</div>

<div class="admin-card">
    <h3>Créneaux</h3>
    <p>Gestion des créneaux des séances</p>
    <a href="<?php echo e(route('creneaux.csv.page')); ?>" class="admin-btn btn-blue">
        Gérer
    </a>
</div>

<div class="admin-card">
    <h3>Utilisateurs</h3>
    <p>Gestion des utilisateurs (enseignants, étudiants, admins)</p>
    <a href="<?php echo e(route('users.csv.page')); ?>" class="admin-btn btn-blue">
        Gérer
    </a>
</div>

<div class="admin-card">
    <h3>Groupes – Matières</h3>
    <p>Gestion des relations entre groupes et matières</p>
    <a href="<?php echo e(route('groupematiere.csv.page')); ?>" class="admin-btn btn-blue">
        Gérer
    </a>
</div>











        


    </div>

    <!-- Logout -->
    <div class="logout">
        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit">Déconnexion</button>
        </form>
    </div>

</div>

</body>
</html>
<?php /**PATH C:\Users\pc\eduapp\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>