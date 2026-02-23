<style>
    /* Le conteneur qui couvre l'écran (Overlay) */
    #loader-overlay {
        display: flex;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #fff;
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
    function showLoader() {
        document.getElementById('loader-overlay').style.display = 'flex';
    }

    function hideLoader() {
        document.getElementById('loader-overlay').style.display = 'none';
    }


    window.addEventListener('pageshow', function() {
        hideLoader();
    });


    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        

        if (link && link.href && !link.hasAttribute('download') && link.target !== '_blank') {
            if (link.href.contains(window.location.hostname)) {
                showLoader();
            }
        }
    });

    document.addEventListener('submit', function(e) {
        if (!e.target.hasAttribute('data-no-loader')) {
            showLoader();
        }
    });
</script>
