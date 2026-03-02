<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>

    <link rel="stylesheet" href="<?php echo e(asset('css/login.css')); ?>">
</head>
<body>

<div class="login-container">

    
    <img src="<?php echo e(asset('images/logo.png')); ?>" class="logo" alt="Logo">

    
    <p class="subtitle">Bienvenue dans FSEG Dashboard</p>

    <?php if($errors->any()): ?>
        <p class="error"><?php echo e($errors->first()); ?></p>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('login.web')); ?>">
        <?php echo csrf_field(); ?>

        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>

        <button type="submit">Se connecter</button>
    </form>

</div>

</body>
</html>
<?php /**PATH C:\Users\pc\eduapp\resources\views/auth/login.blade.php ENDPATH**/ ?>