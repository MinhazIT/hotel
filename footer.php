    <footer class="footer py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h4 class="mb-4"><i class="bi bi-building"></i> Marco Polo Hotel</h4>
                    <p class="text-muted">Experience luxury and comfort at Marco Polo Hotel. We provide exceptional service and unforgettable stays.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-twitter fs-5"></i></a>
                    </div>
                </div>
                <div class="col-lg-2">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="../index.php" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="#rooms" class="text-muted text-decoration-none">Rooms</a></li>
                        <li><a href="#about" class="text-muted text-decoration-none">About</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="mb-3">Contact</h6>
                    <ul class="list-unstyled text-muted">
                        <li><i class="bi bi-geo-alt me-2"></i>123 Hotel Street, City</li>
                        <li><i class="bi bi-telephone me-2"></i>+1 234 567 890</li>
                        <li><i class="bi bi-envelope me-2"></i>info@grandhotel.com</li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="mb-3">Newsletter</h6>
                    <form class="d-flex">
                        <input type="email" class="form-control me-2" placeholder="Your email">
                        <button type="submit" class="btn btn-accent">Subscribe</button>
                    </form>
                </div>
            </div>
            <hr class="my-4">
            <p class="text-center text-muted mb-0">&copy; 2026 Marco Polo Hotel. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if (isset($_SESSION['success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?= $_SESSION['success'] ?>',
                timer: 3000
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= $_SESSION['error'] ?>',
                timer: 3000
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
