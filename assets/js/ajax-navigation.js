/**
 * AJAX Navigation for Hostel Management System
 * Enhanced with debugging and more robust interception
 */

(function() {
    console.log('AJAX Navigation Script Loaded at ' + new Date().toLocaleTimeString());

    function init() {
        const pageWrapper = document.querySelector('.page-wrapper');
        console.log('Page Wrapper found:', !!pageWrapper);

        // Function to load page content
        async function loadPage(url, pushState = true) {
            console.log('Loading page via AJAX:', url);
            try {
                // Show preloader
                const preloader = document.querySelector('.preloader');
                if (preloader) preloader.style.display = 'block';

                const response = await fetch(url);
                if (!response.ok) throw new Error('Network response was not ok: ' + response.statusText);
                
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newContent = doc.querySelector('.page-wrapper');
                if (!newContent) {
                    console.warn('Target page does not have .page-wrapper. Falling back to full reload.', url);
                    window.location.href = url;
                    return;
                }

                // Update Page Content
                const currentPageWrapper = document.querySelector('.page-wrapper');
                if (currentPageWrapper) {
                    currentPageWrapper.innerHTML = newContent.innerHTML;
                    console.log('Content swapped successfully.');
                } else {
                    console.error('Current page lost its .page-wrapper!?');
                    window.location.href = url;
                    return;
                }

                // Update Document Title
                document.title = doc.title;

                // Update URL
                if (pushState) {
                    history.pushState({ url: url }, doc.title, url);
                    console.log('URL updated to:', url);
                }

                // Re-initialize UI Components
                reinitializeComponents();

                // Hide preloader
                if (preloader) {
                    if (typeof $ !== 'undefined') {
                        $(preloader).fadeOut();
                    } else {
                        preloader.style.display = 'none';
                    }
                }

                // Scroll to top
                window.scrollTo(0, 0);

                // Update Sidebar Active State
                updateSidebarActiveState(url);

            } catch (error) {
                console.error('AJAX navigation error:', error);
                window.location.href = url;
            }
        }

        function reinitializeComponents() {
            console.log('Re-initializing components...');
            if (typeof feather !== 'undefined') feather.replace();
            
            if (typeof $ !== 'undefined') {
                if ($.fn.tooltip) $('[data-toggle="tooltip"]').tooltip();
                if ($.fn.DataTable && $('#zero_config').length > 0) {
                    if (!$.fn.DataTable.isDataTable('#zero_config')) {
                        $('#zero_config').DataTable();
                    }
                }
            }
            
            // Re-run scripts in swapped content
            const wrapper = document.querySelector('.page-wrapper');
            if (wrapper) {
                const scripts = wrapper.querySelectorAll('script');
                scripts.forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });
            }
        }

        function updateSidebarActiveState(url) {
            if (typeof $ === 'undefined') return;
            const filename = url.split('/').pop().split('?')[0];
            $('#sidebarnav a').removeClass('active');
            $('#sidebarnav li').removeClass('selected');
            const element = $('#sidebarnav a').filter(function() {
                const href = $(this).attr('href');
                return href === filename || url.endsWith(href);
            });
            element.addClass('active').parents('li').addClass('selected');
        }

        // Use capture phase to intercept as early as possible
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;

            // Check if it's a sidebar link or any link that SHOULD be AJAX loaded
            const isSidebarLink = link.classList.contains('sidebar-link');
            const href = link.getAttribute('href');

            if (isSidebarLink && href && !href.startsWith('#') && !href.startsWith('http') && !href.includes('logout.php')) {
                console.log('Intercepted click on:', href);
                e.preventDefault();
                e.stopImmediatePropagation(); // Try to stop other listeners
                loadPage(href);
                return false;
            }
        }, true); // Capture phase

        window.addEventListener('popstate', function(e) {
            if (e.state && e.state.url) {
                loadPage(e.state.url, false);
            } else {
                location.reload();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
