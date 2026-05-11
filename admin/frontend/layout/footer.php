<?php
// admin/frontend/layout/footer.php
?>
            </div> <!-- End .dashboard-content -->
        </main>
    </div> <!-- End .admin-wrapper -->
    
    <script>
        // Modal global logic to handle 'block' vs 'flex' display issues
        document.addEventListener('DOMContentLoaded', () => {
            // override the default display block when JS opens modals
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'style') {
                        const target = mutation.target;
                        if (target.classList.contains('modal') && target.style.display === 'block') {
                            target.style.display = 'flex';
                        }
                    }
                });
            });

            document.querySelectorAll('.modal').forEach(modal => {
                observer.observe(modal, { attributes: true });
            });
        });
    </script>
    <?php if (isset($extraJs)) echo $extraJs; ?>
</body>
</html>