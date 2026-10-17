// ============================================================
// Research Grant Management System - Main JavaScript
// ============================================================

$(document).ready(function() {
    initThemeToggle();
    var $sidebar = $('#sidebar');
    var $content = $('#content');
    var $overlay = $('.overlay');
    var $toggleBtn = $('#sidebarToggle');
    var $collapseBtn = $('#sidebarCollapse');
    var isMobile = function() { return $(window).width() < 992; };

    // Sidebar Toggle (hamburger in navbar)
    $toggleBtn.on('click', function() {
        if (isMobile()) {
            // Mobile: offcanvas open/close
            $sidebar.toggleClass('mobile-open');
            $overlay.toggleClass('active');
            $('body').toggleClass('sidebar-open');
        } else {
            // Desktop/Tablet: collapse/expand
            $sidebar.toggleClass('collapsed');
            $content.toggleClass('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', $sidebar.hasClass('collapsed') ? '1' : '0');
        }
    });

    // Sidebar Collapse Button (chevron in sidebar header)
    $collapseBtn.on('click', function() {
        $sidebar.toggleClass('collapsed');
        $content.toggleClass('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', $sidebar.hasClass('collapsed') ? '1' : '0');
    });

    // Close sidebar on overlay click (mobile only)
    $(document).on('click', '.overlay.active', function() {
        $sidebar.removeClass('mobile-open');
        $overlay.removeClass('active');
        $('body').removeClass('sidebar-open');
    });

    // Auto-close alerts
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);

    // Initialize DataTables
    if ($.fn.DataTable) {
        $('.datatable').each(function() {
            var $t = $(this);
            var colCount = $t.find('thead tr:first th').length;
            if (colCount === 0) return;
            var emptyMsg = 'No entries found';
            $t.find('tbody tr').each(function() {
                var $td = $(this).find('td');
                if ($td.length === 1 && $td.attr('colspan')) {
                    emptyMsg = $td.text().trim();
                    $(this).remove();
                }
            });
            $t.DataTable({
                responsive: true,
                pageLength: 10,
                destroy: true,
                language: {
                    search: '',
                    searchPlaceholder: 'Search...',
                    lengthMenu: '_MENU_ per page',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: emptyMsg,
                    infoFiltered: '(filtered from _MAX_ total entries)',
                    zeroRecords: emptyMsg,
                },
                dom: "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            });
        });
    }

    // Auto-resize textarea
    $(document).on('input', 'textarea.auto-resize', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Confirm Delete
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        var link = $(this).attr('href');
        var message = $(this).data('message') || 'Are you sure you want to delete this item?';

        Swal.fire({
            title: 'Confirm Delete',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
            }
        });
    });

    // Form Validation
    $(document).on('submit', '.needs-validation', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    // File input preview
    $(document).on('change', '.file-input-preview', function() {
        var input = this;
        var preview = $(input).data('preview');
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(preview).attr('src', e.target.result).show();
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    // Show password toggle
    $(document).on('click', '.toggle-password', function() {
        var input = $($(this).data('target'));
        var icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // Dynamic form submission with AJAX
    $(document).on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var button = form.find('button[type="submit"]');
        var originalText = button.html();

        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method') || 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Operation completed successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else if (response.reload) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'An error occurred.'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An unexpected error occurred. Please try again.'
                });
            },
            complete: function() {
                button.prop('disabled', false).html(originalText);
            }
        });
    });

    // Restore sidebar state on desktop
    if (!isMobile()) {
        var wasCollapsed = localStorage.getItem('sidebarCollapsed') === '1';
        if (wasCollapsed) {
            $sidebar.addClass('collapsed');
            $content.addClass('sidebar-collapsed');
        }
    }

    // Handle window resize
    var resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            var mobile = $(window).width() < 992;
            if (mobile) {
                // Switching to mobile: collapse sidebar and remove desktop collapsed state
                $sidebar.removeClass('collapsed mobile-open');
                $content.removeClass('sidebar-collapsed');
                $overlay.removeClass('active');
                $('body').removeClass('sidebar-open');
            } else {
                // Switching to desktop: restore collapsed state from localStorage
                $sidebar.removeClass('mobile-open');
                $overlay.removeClass('active');
                $('body').removeClass('sidebar-open');
                var wasCollapsed = localStorage.getItem('sidebarCollapsed') === '1';
                $sidebar.toggleClass('collapsed', wasCollapsed);
                $content.toggleClass('sidebar-collapsed', wasCollapsed);
            }
        }, 200);
    });

    // Close sidebar on Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $sidebar.hasClass('mobile-open')) {
            $sidebar.removeClass('mobile-open');
            $overlay.removeClass('active');
            $('body').removeClass('sidebar-open');
        }
    });
});

// Landing Page Navbar scroll effect
$(window).on('scroll', function() {
    if ($('.navbar-landing').length) {
        if ($(this).scrollTop() > 50) {
            $('.navbar-landing').addClass('scrolled');
        } else {
            $('.navbar-landing').removeClass('scrolled');
        }
    }
});

// Smooth scroll for anchor links
$(document).on('click', 'a[href^="#"]', function(e) {
    var target = $(this.getAttribute('href'));
    if (target.length) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: target.offset().top - 70
        }, 800);
    }
});

// Counter animation
function animateCounter(el) {
    var target = parseInt($(el).data('target'));
    var duration = 2000;
    var step = Math.ceil(target / (duration / 16));
    var current = 0;

    var timer = setInterval(function() {
        current += step;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        $(el).text(current.toLocaleString());
    }, 16);
}

// Initialize counters when visible
$(window).on('scroll', function() {
    $('.stat-number[data-target]').each(function() {
        if ($(this).isInViewport() && !$(this).data('counted')) {
            $(this).data('counted', true);
            animateCounter(this);
        }
    });
});

// Check if element is in viewport
$.fn.isInViewport = function() {
    var elementTop = $(this).offset().top;
    var elementBottom = elementTop + $(this).outerHeight();
    var viewportTop = $(window).scrollTop();
    var viewportBottom = viewportTop + $(window).height();
    return elementBottom > viewportTop && elementTop < viewportBottom;
};

// Toast notification function
function showToast(message, type) {
    type = type || 'success';
    var bgColor = type === 'success' ? '#198754' : type === 'error' ? '#DC3545' : type === 'warning' ? '#FFC107' : '#0D6EFD';

    var toast = `
        <div class="toast align-items-center text-white border-0" role="alert" style="position:fixed;top:20px;right:20px;z-index:9999;background:${bgColor}">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    $('body').append(toast);
    var toastEl = $('.toast').last();
    var bsToast = new bootstrap.Toast(toastEl[0], { delay: 3000 });
    bsToast.show();
    setTimeout(function() { toastEl.remove(); }, 3500);
}

// Print function
function printReport(elementId) {
    var content = $('#' + elementId).html();
    var win = window.open('', '_blank');
    win.document.write('<html><head><title>Print Report</title>');
    win.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">');
    win.document.write('<link rel="stylesheet" href="' + window.location.origin + '/research/assets/css/style.css">');
    win.document.write('</head><body><div class="container p-4">' + content + '</div></body></html>');
    win.document.close();
    win.print();
}

// Theme Toggle (Light / Dark Mode)
function initThemeToggle() {
    var theme = localStorage.getItem('app_theme') || document.documentElement.getAttribute('data-bs-theme') || 'dark';
    var icon = document.getElementById('themeToggleIcon');
    var btn = document.getElementById('themeToggleBtn');
    if (icon) {
        if (theme === 'light') {
            icon.className = 'bi bi-moon-stars-fill text-primary fs-5';
            if (btn) btn.setAttribute('title', 'Switch to Dark Mode');
        } else {
            icon.className = 'bi bi-sun-fill text-warning fs-5';
            if (btn) btn.setAttribute('title', 'Switch to Light Mode');
        }
    }
}

