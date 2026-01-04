        </div>
    </div>
</div>
<div id="sidebar-overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1040; display:none;"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const sidebar = document.querySelector('.sidebar');
const toggle = document.getElementById('mobile-toggle');
const overlay = document.getElementById('sidebar-overlay');

if (toggle) {
    toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        sidebar.classList.toggle('active');
        overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
    });
}

if (overlay) {
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.style.display = 'none';
    });
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    if (window.innerWidth <= 992 && 
        !sidebar.contains(event.target) && 
        !toggle.contains(event.target) && 
        sidebar.classList.contains('active')) {
        sidebar.classList.remove('active');
        overlay.style.display = 'none';
    }
});
</script>
</body>
</html>
