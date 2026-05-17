<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$topbar_search_placeholder = "Search RTU news and articles...";
$topbar_search_mode = "local";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTU Updates | TalaAral</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Momo+Trust+Display&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/updates.css">
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
                    <h1>RTU <span class="text-gradient">Updates</span></h1>
                    <p>Latest news and announcements from Rizal Technological University.</p>
                </div>

                <div class="controls-bar reveal-up" style="transition-delay:0.08s;">
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-filter="all">All</button>
                        <button class="filter-tab" data-filter="news">News</button>
                        <button class="filter-tab" data-filter="announcement">Announcements</button>
                    </div>
                </div>

                <div class="skeleton-grid" id="skeletonGrid">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                        <div class="skeleton-card">
                            <div class="skeleton-thumb"></div>
                            <div class="skeleton-body">
                                <div class="skeleton-line short"></div>
                                <div class="skeleton-line tall full"></div>
                                <div class="skeleton-line full"></div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="updates-grid" id="updatesGrid" style="display:none;"></div>

            </main>
        </div>
    </div>

    <div class="article-overlay" id="articleOverlay">
        <div class="article-modal">
            <div class="article-modal-header">
                <div class="article-modal-meta" id="articleMeta"></div>
                <div class="article-modal-actions">
                    <a href="#" class="btn-read-original" id="articleOriginalLink" target="_blank" rel="noopener noreferrer">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Original
                    </a>
                    <button class="article-close-btn" onclick="closeArticle()">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            <div class="article-modal-body" id="articleBody">
                <div class="article-hero-img" id="articleHeroImg"></div>
                <h1 class="article-title" id="articleTitle"></h1>
                <div class="article-content" id="articleContent"></div>
            </div>
        </div>
    </div>

    <script>
        let allPosts = [];
        let filtered = [];
        let visibleCount = 9;
        let currentSearchQuery = "";
        const PAGE_SIZE = 9;
        let newsLoaded = false;
        let announcementsLoaded = false;

        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.querySelectorAll('.reveal-up').forEach(el => el.classList.add('active'));
            }, 100);

            loadUpdates();
            loadAnnouncements();

            document.addEventListener('pageSearch', (e) => {
                currentSearchQuery = e.detail.toLowerCase();
                visibleCount = PAGE_SIZE;
                applyFilters();
            });

            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    visibleCount = PAGE_SIZE;
                    applyFilters();
                });
            });

            document.getElementById('articleOverlay').addEventListener('click', function(e) {
                if (e.target === this) closeArticle();
            });

            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeArticle();
            });
        });

        function checkAllLoaded() {
            if (newsLoaded && announcementsLoaded) {
                document.getElementById('skeletonGrid').style.display = 'none';
                document.getElementById('updatesGrid').style.display = 'grid';
                allPosts.sort((a, b) => b.timestamp - a.timestamp);
                applyFilters();
            }
        }

        function loadUpdates() {
            fetch('/backend/api/get_rtu_updates.php')
                .then(r => r.json())
                .then(data => {
                    if (Array.isArray(data) && data.length > 0) {
                        allPosts = [...allPosts, ...data];
                    }

                    newsLoaded = true;
                    checkAllLoaded();

                    const openUrl = new URLSearchParams(window.location.search).get('open');
                    if (openUrl) {
                        const target = allPosts.find(p => p.url === openUrl);
                        if (target) setTimeout(() => openArticle(target), 150);
                    }
                })
                .catch(() => {
                    newsLoaded = true;
                    checkAllLoaded();
                });
        }

        function loadAnnouncements() {
            fetch('/backend/api/get_announcements.php')
                .then(r => r.json())
                .then(data => {
                    if (Array.isArray(data) && data.length > 0) {
                        const announcements = data.map(a => {
                            const images = parseAnnouncementImages(a.image_url);
                            const thumbnailUrl = images[0] || '';
                            const message = a.message && a.message.trim() ? a.message.trim() : '';

                            return {
                                id: 'ann_' + a.id,
                                title: a.title || 'Untitled announcement',
                                url: '#',
                                date: new Date(a.posted_at).toLocaleDateString('en-US', {
                                    month: 'short',
                                    day: 'numeric',
                                    year: 'numeric'
                                }),
                                timestamp: new Date(a.posted_at).getTime() / 1000,
                                category: 'announcement',
                                excerpt: message || a.title || '',
                                thumbnail: thumbnailUrl,
                                content: `
                                    ${images.length ? `
                                        <div class="announcement-images">
                                            ${images.map(img => `<img src="${escHtml(img)}" alt="Announcement image">`).join('')}
                                        </div>
                                    ` : ''}
                                    ${message ? `<p>${escHtml(message)}</p>` : ''}
                                `,
                                likes: a.likes,
                                shares: a.shares
                            };
                        });

                        allPosts = [...allPosts, ...announcements];
                    }

                    announcementsLoaded = true;
                    checkAllLoaded();
                })
                .catch(err => {
                    console.error('Announcements failed:', err);
                    announcementsLoaded = true;
                    checkAllLoaded();
                });
        }

        function parseAnnouncementImages(value) {
            if (!value) return [];

            if (Array.isArray(value)) {
                return value.filter(Boolean);
            }

            try {
                const parsed = JSON.parse(String(value));
                return Array.isArray(parsed) ? parsed.filter(Boolean) : [];
            } catch (e) {
                console.error('Invalid announcement image_url:', value, e);
                return [];
            }
        }

        function applyFilters() {
            const activeCat = document.querySelector('.filter-tab.active')?.dataset.filter || 'all';

            filtered = allPosts.filter(post => {
                const matchCat = activeCat === 'all' || post.category === activeCat;
                const matchSearch = post.title.toLowerCase().includes(currentSearchQuery) ||
                    (post.excerpt && post.excerpt.toLowerCase().includes(currentSearchQuery));

                return matchCat && matchSearch;
            });

            renderGrid();
        }

        function renderGrid() {
            const grid = document.getElementById('updatesGrid');

            if (filtered.length === 0) {
                grid.innerHTML = emptyState('No matches found.', 'fa-magnifying-glass');
                return;
            }

            const visible = filtered.slice(0, visibleCount);
            grid.innerHTML = visible.map(post => buildCard(post)).join('');

            grid.querySelectorAll('.update-card').forEach((card, index) => {
                card.addEventListener('click', (e) => {
                    e.preventDefault();
                    openArticle(visible[index]);
                });
            });
        }

        function buildCard(post) {
            const catLabel = post.category === 'announcement' ? 'Announcement' : 'News';
            const catClass = post.category === 'announcement' ? 'announcement' : 'news';
            const thumb = post.thumbnail ?
                `<img src="${escHtml(post.thumbnail)}" alt="Thumb" loading="lazy" onerror="this.parentElement.innerHTML='<i class=\\'card-thumb-icon fa-solid fa-newspaper\\'></i>'">` :
                `<i class="card-thumb-icon fa-solid fa-newspaper"></i>`;

            const socialBar = post.category === 'announcement' ? `
                <div class="card-social">
                    <span><i class="fa-solid fa-thumbs-up"></i> ${post.likes || 0}</span>
                    <span><i class="fa-solid fa-share"></i> ${post.shares || 0}</span>
                </div>` : '';

            return `
                <a class="update-card ${post.category === 'announcement' ? 'announcement-card' : ''}" href="${escHtml(post.url)}">
                    <div class="card-thumb">${thumb}</div>
                    <div class="card-body">
                        <div class="card-meta">
                            <span class="category-badge ${catClass}">${catLabel}</span>
                            <span class="card-date"><i class="fa-solid fa-calendar-days"></i> ${escHtml(post.date)}</span>
                        </div>
                        <div class="card-title-text">${escHtml(post.title)}</div>
                        <p class="card-excerpt">${escHtml(post.excerpt || '')}</p>
                        ${socialBar}
                        <div class="card-footer">${post.category === 'announcement' ? 'View post' : 'Read more'} <i class="fa-solid fa-arrow-right"></i></div>
                    </div>
                </a>`;
        }

        window.openArticle = function(post) {
            const overlay = document.getElementById('articleOverlay');
            const heroImg = document.getElementById('articleHeroImg');
            const catClass = post.category === 'announcement' ? 'announcement' : 'news';
            const catLabel = post.category === 'announcement' ? 'Announcement' : 'News';

            document.getElementById('articleTitle').textContent = post.title;
            document.getElementById('articleOriginalLink').href = post.url;
            document.getElementById('articleMeta').innerHTML = `
                <span class="category-badge ${catClass}">${catLabel}</span>
                <span class="article-date"><i class="fa-solid fa-calendar-days"></i> ${escHtml(post.date)}</span>`;

            if (post.thumbnail) {
                heroImg.innerHTML = `<img src="${escHtml(post.thumbnail)}" alt="Hero">`;
                heroImg.style.display = 'block';
            } else {
                heroImg.style.display = 'none';
            }

            document.getElementById('articleContent').innerHTML = post.content ||
                `<p>${escHtml(post.excerpt)}</p><p><a href="${escHtml(post.url)}" target="_blank">Read full article on RTU website...</a></p>`;

            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            document.getElementById('articleBody').scrollTop = 0;
        };

        window.closeArticle = function() {
            document.getElementById('articleOverlay').classList.remove('active');
            document.body.style.overflow = '';

            const url = new URL(window.location.href);
            if (url.searchParams.has('open')) {
                url.searchParams.delete('open');
                window.history.replaceState({}, '', url.toString());
            }
        };

        function emptyState(msg, icon) {
            return `<div class="full-empty"><i class="fa-solid ${icon}"></i><p>${msg}</p></div>`;
        }

        function escHtml(str) {
            const d = document.createElement('div');
            d.textContent = str ?? '';
            return d.innerHTML;
        }
    </script>
</body>

</html>