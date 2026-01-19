<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Horaires</title>

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>

<div class="admin-container">

    <!-- Header -->
    <div class="page-header">
        <h2>Gestion des Horaires (CSV)</h2>
        <p>Importer et exporter les horaires via fichier CSV</p>
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Actions -->
    <div class="action-card">

        <!-- Download -->
        <div class="action-block">
            <h4>Téléchargement</h4>
            <p>Exporter tous les horaires en fichier CSV</p>
            <a href="{{ route('horaires.csv.download') }}" class="admin-btn btn-blue">
                Télécharger CSV
            </a>
        </div>

        <!-- Upload -->
        <div class="action-block">
            <h4>Importation</h4>
            <p>Importer ou mettre à jour les horaires depuis un CSV</p>

            <form method="POST" action="{{ route('horaires.csv.upload') }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="csv_file" class="file-input" required>
                <button type="submit" class="admin-btn btn-green">
                    Uploader CSV
                </button>
            </form>
        </div>

    </div>

    <!-- Retour -->
    <div class="logout">
        <a href="{{ route('admin.dashboard') }}" class="admin-btn btn-blue">
            Retour Admin
        </a>
    </div>

</div>

</body>
</html>
