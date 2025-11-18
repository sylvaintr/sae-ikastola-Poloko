<style>
    /* Le conteneur qui couvre l'écran (Overlay) */
    #loader-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9999;
        justify-content: center;
        align-items: center;

    }

    /* L'animation du spinner */
    .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #f29201;
        /* Couleur bleue */
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<div id="loader-overlay">
    <div class="spinner"></div>
</div>


<script>
    // Fonction pour afficher le loader
    function showLoader() {
        document.getElementById('loader-overlay').style.display = 'flex';
    }

    // Fonction pour masquer le loader
    function hideLoader() {
        document.getElementById('loader-overlay').style.display = 'none';
    }

    // Exemple d'utilisation : afficher le loader lors du chargement de la page
    window.addEventListener('pageshow', function() {
        hideLoader();
    });

    window.addEventListener('beforeunload', function() {
        showLoader();
    });
</script>
