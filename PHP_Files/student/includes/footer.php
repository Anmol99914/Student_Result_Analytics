<?php
// File: PHP_Files/student/includes/footer.php
?>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Student Common JS -->
    <script src="../js/common.js"></script>
    
    <!-- Page-specific JS -->
    <?php if(isset($page_js)): ?>
        <script src="../js/<?php echo $page_js; ?>.js"></script>
    <?php endif; ?>
    
    <script>
        // Prevent back button
        window.history.forward();
        function noBack() { window.history.forward(); }
        
        // Handle browser cache
        window.addEventListener("pageshow", function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>