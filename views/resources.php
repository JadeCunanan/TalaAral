<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

// --- Configure the Modular Topbar for this specific page ---
$topbar_search_placeholder = "Search resources, files, or courses...";
$topbar_search_mode = "local";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Resources | TalaAral</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Momo+Trust+Display&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/resources.css">
    <link rel="stylesheet" href="/assets/css/resources_accordion.css">
    <link rel="stylesheet" href="/assets/css/sidebar.css">
</head>

<body>

    <div class="flare-bg">
        <div class="flare orb-navy"></div>
        <div class="flare orb-gold"></div>
    </div>

    <div class="app-shell">

        <?php include 'components/sidebar.php'; ?>

        <div class="main-wrap">

            <?php include 'components/topbar.php'; ?>

            <main class="content">

                <div class="page-header reveal-up">
                    <div class="ph-left">
                        <h1>Learning <span class="text-gradient">Resources</span></h1>
                        <p>Study materials and files shared by your instructor via Canvas LMS.</p>
                    </div>
                    <div class="ph-right">
                        <div class="canvas-badge">
                            <i class="fa-solid fa-link"></i>
                            Powered by Canvas LMS
                        </div>
                    </div>
                </div>

                <div class="program-tabs-wrap reveal-up" style="transition-delay: 0.08s;">
                    <div class="program-tabs" id="programTabs">
                        <button class="program-tab active" data-program="all">
                            <i class="fa-solid fa-layer-group"></i> All Programs
                        </button>
                    </div>
                </div>

                <div class="filter-bar reveal-up" style="transition-delay: 0.12s;">
                    <button class="filter-btn active" data-filter="all">
                        <i class="fa-solid fa-layer-group"></i> All Files
                    </button>
                    <button class="filter-btn" data-filter="pdf">
                        <i class="fa-solid fa-file-pdf"></i> PDF
                    </button>
                    <button class="filter-btn" data-filter="word">
                        <i class="fa-solid fa-file-word"></i> Word
                    </button>
                    <button class="filter-btn" data-filter="ppt">
                        <i class="fa-solid fa-file-powerpoint"></i> PowerPoint
                    </button>
                    <button class="filter-btn" data-filter="image">
                        <i class="fa-solid fa-image"></i> Images
                    </button>
                    <button class="filter-btn" data-filter="other">
                        <i class="fa-solid fa-file"></i> Other
                    </button>
                </div>

                <div class="accordion-wrap reveal-up" id="accordionWrap" style="transition-delay: 0.18s;">
                    <div class="accordion-skeleton"></div>
                    <div class="accordion-skeleton"></div>
                    <div class="accordion-skeleton"></div>
                </div>

                <div class="resources-empty" id="resourcesEmpty" style="display:none;">
                    <i class="fa-solid fa-folder-open"></i>
                    <h3>No resources found</h3>
                    <p>No files match your search criteria or filters.</p>
                </div>

                <div class="resources-error" id="resourcesError" style="display:none;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <h3>Could not load resources</h3>
                    <p id="errorMessage">There was a problem connecting to Canvas LMS.</p>
                    <button class="btn-main" onclick="loadResources()">
                        <i class="fa-solid fa-rotate-right"></i> Try Again
                    </button>
                </div>

            </main>
        </div>
    </div>

    <div class="preview-overlay" id="previewOverlay">
        <div class="preview-modal">
            <div class="preview-header">
                <div class="preview-title-wrap">
                    <div class="preview-file-icon" id="previewIcon"></div>
                    <div>
                        <div class="preview-filename" id="previewFilename">Loading...</div>
                        <div class="preview-course" id="previewCourse"></div>
                    </div>
                </div>
                <div class="preview-actions">
                    <a href="#" class="btn-download" id="previewDownload" download>
                        <i class="fa-solid fa-arrow-down-to-line"></i> Download
                    </a>
                    <button class="preview-close" onclick="closePreview()">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            <div class="preview-body" id="previewBody">
                <div class="preview-loading">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <p>Loading file...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ── State ──
        let allResources = [];
        let currentSearchQuery = "";

        document.addEventListener('DOMContentLoaded', () => {
            // Reveal animations
            setTimeout(() => {
                document.querySelectorAll('.reveal-up').forEach(el => el.classList.add('active'));
            }, 100);

            loadResources();

            // ── TOPBAR SEARCH LISTENER ──
            document.addEventListener('pageSearch', (e) => {
                currentSearchQuery = e.detail.toLowerCase();
                renderAccordion();
            });

            // File type filter
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    renderAccordion();
                });
            });

            // Close preview on backdrop or Escape
            document.getElementById('previewOverlay').addEventListener('click', function(e) {
                if (e.target === this) closePreview();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closePreview();
            });
        });

        function loadResources() {
            const wrap = document.getElementById('accordionWrap');
            const empty = document.getElementById('resourcesEmpty');
            const error = document.getElementById('resourcesError');

            wrap.style.display = 'block';
            empty.style.display = 'none';
            error.style.display = 'none';
            wrap.innerHTML = `<div class="accordion-skeleton"></div><div class="accordion-skeleton"></div><div class="accordion-skeleton"></div>`;

            fetch('/backend/api/get_canvas_data.php')
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        showError(data.error);
                        return;
                    }
                    allResources = data;
                    buildProgramTabs(data);
                    renderAccordion();
                })
                .catch(() => showError('Network error — could not reach Canvas LMS.'));
        }

        function buildProgramTabs(files) {
            const tabsEl = document.getElementById('programTabs');
            const seen = new Set();
            const programs = [];
            files.forEach(f => {
                if (!seen.has(f.program_id)) {
                    seen.add(f.program_id);
                    programs.push({
                        id: f.program_id,
                        name: f.program_name,
                        abbr: f.abbreviation
                    });
                }
            });

            tabsEl.innerHTML = `<button class="program-tab active" data-program="all"><i class="fa-solid fa-layer-group"></i> All Programs</button>`;
            programs.forEach(p => {
                const btn = document.createElement('button');
                btn.className = 'program-tab';
                btn.dataset.program = p.id;
                btn.title = p.name;
                btn.innerHTML = `<i class="fa-solid fa-graduation-cap"></i> ${escHtml(p.abbr)}`;
                tabsEl.appendChild(btn);
            });

            tabsEl.querySelectorAll('.program-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    tabsEl.querySelectorAll('.program-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    renderAccordion();
                });
            });
        }

        function renderAccordion() {
            const wrap = document.getElementById('accordionWrap');
            const empty = document.getElementById('resourcesEmpty');
            const typeFilter = document.querySelector('.filter-btn.active')?.dataset.filter || 'all';
            const programFilter = document.querySelector('.program-tab.active')?.dataset.program || 'all';

            const filtered = allResources.filter(f => {
                const matchSearch = f.title.toLowerCase().includes(currentSearchQuery) ||
                    f.course_name.toLowerCase().includes(currentSearchQuery);
                const matchType = typeFilter === 'all' ||
                    (typeFilter === 'other' ? !['pdf', 'word', 'ppt', 'image', 'excel', 'video'].includes(f.type) : f.type === typeFilter);
                const matchProgram = programFilter === 'all' || String(f.program_id) === String(programFilter);
                return matchSearch && matchType && matchProgram;
            });

            if (!filtered.length) {
                wrap.style.display = 'none';
                empty.style.display = 'flex';
                return;
            }

            wrap.style.display = 'block';
            empty.style.display = 'none';

            const courseMap = new Map();
            filtered.forEach(f => {
                if (!courseMap.has(f.course_id)) {
                    courseMap.set(f.course_id, {
                        course_name: f.course_name,
                        abbreviation: f.abbreviation,
                        files: []
                    });
                }
                courseMap.get(f.course_id).files.push(f);
            });

            wrap.innerHTML = [...courseMap.entries()].map(([courseId, group]) => {
                const fileCount = group.files.length;
                const filesHtml = group.files.map(file => {
                    const proxyUrl = `/backend/api/canvas_proxy.php?url=${encodeURIComponent(file.url)}&filename=${encodeURIComponent(file.title)}`;
                    const isPreviewable = ['pdf', 'image', 'ppt', 'word', 'excel'].includes(file.type);

                    return `
                    <div class="resource-card" onclick="handleFileClick(this)" 
                         data-type="${escHtml(file.type)}" data-url="${escHtml(file.url)}" data-proxy="${escHtml(proxyUrl)}" 
                         data-title="${escHtml(file.title)}" data-course-name="${escHtml(file.course_name)}">
                        <div class="rc-icon type-${escHtml(file.type)}">${getFileIcon(file.type)}</div>
                        <div class="rc-body">
                            <div class="rc-title">${escHtml(file.title)}</div>
                            <div class="rc-meta">
                                <span class="rc-badge type-${escHtml(file.type)}">${escHtml(file.type.toUpperCase())}</span>
                                <span class="rc-size">${escHtml(file.size)}</span>
                            </div>
                        </div>
                        <div class="rc-action">${isPreviewable ? '<i class="fa-solid fa-eye"></i>' : '<i class="fa-solid fa-arrow-down-to-line"></i>'}</div>
                    </div>`;
                }).join('');

                return `
                <div class="accordion-item ${currentSearchQuery !== "" ? 'open' : ''}" id="acc-${courseId}">
                    <button class="accordion-header" onclick="toggleAccordion('acc-${courseId}')">
                        <div class="acc-left">
                            <div class="acc-folder-icon"><i class="fa-solid fa-folder"></i><i class="fa-solid fa-folder-open"></i></div>
                            <div class="acc-info">
                                <span class="acc-course-name">${escHtml(group.course_name)}</span>
                                <span class="acc-program-badge">${escHtml(group.abbreviation)}</span>
                            </div>
                        </div>
                        <div class="acc-right">
                            <span class="acc-count">${fileCount} file${fileCount === 1 ? '' : 's'}</span>
                            <i class="fa-solid fa-chevron-down acc-chevron"></i>
                        </div>
                    </button>
                    <div class="accordion-body"><div class="resources-grid">${filesHtml}</div></div>
                </div>`;
            }).join('');
        }

        function toggleAccordion(id) {
            document.getElementById(id)?.classList.toggle('open');
        }

        function handleFileClick(card) {
            const d = card.dataset;
            if (['pdf', 'image', 'ppt', 'word', 'excel'].includes(d.type)) {
                // We pass the PROXY url so that the download button inside the modal works reliably
                openPreview(d.proxy, d.title, d.courseName, d.type);
            } else {
                // If it's a ZIP or Audio file, just instantly download it
                window.location.href = d.proxy;
            }
        }

        function openPreview(fileUrl, title, courseName, type) {
            const overlay = document.getElementById('previewOverlay');
            const body = document.getElementById('previewBody');
            
            // Set header info
            document.getElementById('previewFilename').textContent = title;
            document.getElementById('previewCourse').textContent = courseName;
            document.getElementById('previewIcon').innerHTML = `<div class="pv-icon type-${type}">${getFileIcon(type)}</div>`;
            
            // Set the download button link in the header
            document.getElementById('previewDownload').href = fileUrl;

            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Route file to correct display logic
            if (type === 'pdf') {
                body.innerHTML = `<iframe src="${escHtml(fileUrl)}#${encodeURIComponent(title)}" class="preview-iframe" title="${escHtml(title)}" style="width:100%; height:100%; border:none;"></iframe>`;
            } 
            else if (['ppt', 'word', 'excel'].includes(type)) {
                // SMART FALLBACK FOR OFFICE FILES
                let appColor = '#cccccc';
                let appName = 'Document';
                let initial = 'D';

                if (type === 'word') { appColor = '#2b579a'; appName = 'Word Document'; initial = 'W'; }
                if (type === 'ppt') { appColor = '#b7472a'; appName = 'PowerPoint Presentation'; initial = 'P'; }
                if (type === 'excel') { appColor = '#217346'; appName = 'Excel Spreadsheet'; initial = 'E'; }

                body.innerHTML = `
                    <div style="display: flex; width: 100%; height: 100%; align-items: center; justify-content: center; background-color: #1e1e1e;">
                        <div style="background-color: #2a2a2a; padding: 40px; border-radius: 12px; text-align: center; border: 1px solid #333; max-width: 400px; width: 90%;">
                            <div style="width: 64px; height: 64px; border-radius: 12px; background-color: ${appColor}; color: white; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; margin-bottom: 20px;">
                                ${initial}
                            </div>
                            <h3 style="color: #ffffff; font-size: 1.2rem; margin-bottom: 8px; font-family: 'Inter', sans-serif;">Preview Not Supported</h3>
                            <p style="color: #aaaaaa; font-size: 0.95rem; margin-bottom: 5px; font-family: 'Inter', sans-serif;">Web browsers cannot preview Microsoft ${appName}s natively.</p>
                            <p style="color: #aaaaaa; font-size: 0.95rem; margin-bottom: 25px; font-family: 'Inter', sans-serif;">Please download the file to view its contents.</p>
                            
                            <a href="${escHtml(fileUrl)}" download class="btn-main" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 6px; font-family: 'Inter', sans-serif; font-weight: 500;">
                                <i class="fa-solid fa-arrow-down-to-line"></i> Download File
                            </a>
                        </div>
                    </div>
                `;
            } 
            else if (type === 'image') {
                const img = new Image();
                img.className = 'preview-image';
                img.src = fileUrl;
                img.onload = () => {
                    body.innerHTML = '';
                    body.appendChild(img);
                };
            }
        }

        function closePreview() {
            document.getElementById('previewOverlay').classList.remove('active');
            document.getElementById('previewBody').innerHTML = '';
            document.body.style.overflow = '';
        }

        function showError(message) {
            document.getElementById('accordionWrap').style.display = 'none';
            document.getElementById('resourcesEmpty').style.display = 'none';
            const errorEl = document.getElementById('resourcesError');
            errorEl.style.display = 'flex';
            document.getElementById('errorMessage').textContent = message;
        }

        function getFileIcon(type) {
            const icons = {
                pdf: '<i class="fa-solid fa-file-pdf"></i>',
                word: '<i class="fa-solid fa-file-word"></i>',
                ppt: '<i class="fa-solid fa-file-powerpoint"></i>',
                excel: '<i class="fa-solid fa-file-excel"></i>',
                image: '<i class="fa-solid fa-image"></i>',
                video: '<i class="fa-solid fa-file-video"></i>',
                audio: '<i class="fa-solid fa-file-audio"></i>',
                zip: '<i class="fa-solid fa-file-zipper"></i>'
            };
            return icons[type] || '<i class="fa-solid fa-file"></i>';
        }

        function escHtml(str) {
            const d = document.createElement('div');
            d.textContent = str ?? '';
            return d.innerHTML;
        }
    </script>
</body>

</html>