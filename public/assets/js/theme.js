// Set theme immediately to avoid flash of unstyled content
(function() {
    try {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        } else {
            document.documentElement.setAttribute('data-bs-theme', 'light');
        }
    } catch (e) {
        console.warn('LocalStorage is not available', e);
    }
})();

document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const htmlElement = document.documentElement;

    // Fungsi untuk mengupdate icon sesuai tema
    function updateIcon(theme) {
        if (!themeIcon) return;
        if (theme === 'dark') {
            themeIcon.className = 'bi bi-moon-fill';
        } else {
            themeIcon.className = 'bi bi-sun-fill';
        }
    }

    // Update icon pada saat pertama load
    const currentTheme = htmlElement.getAttribute('data-bs-theme');
    updateIcon(currentTheme);

    // Event listener untuk tombol toggle
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const activeTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = activeTheme === 'light' ? 'dark' : 'light';
            
            htmlElement.setAttribute('data-bs-theme', newTheme);
            try {
                localStorage.setItem('theme', newTheme);
            } catch (e) {}
            updateIcon(newTheme);
            
            // Reload page to ensure all charts and server-rendered components 
            // adopt the new theme properly without visual bugs.
            setTimeout(() => {
                window.location.reload();
            }, 50);
        });
    }

    // Event listener untuk sinkronisasi antar tab
    window.addEventListener('storage', (event) => {
        if (event.key === 'theme') {
            const newTheme = event.newValue || 'light';
            htmlElement.setAttribute('data-bs-theme', newTheme);
            updateIcon(newTheme);
        }
    });
});

// Hide preloader on page load
window.addEventListener('load', () => {
    const preloader = document.getElementById('page-preloader');
    if (preloader) {
        preloader.classList.add('hidden');
        setTimeout(() => {
            preloader.style.display = 'none';
        }, 600);
    }
});
