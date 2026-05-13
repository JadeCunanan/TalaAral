<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

// --- Configure the Modular Topbar for this specific page ---
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

                <div class="load-more-wrap" id="loadMoreWrap" style="display:none;">
                    <button class="btn-load-more" id="loadMoreBtn">
                        <i class="fa-solid fa-rotate"></i> Load More
                    </button>
                </div>

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
        let currentFeedPage = 1; // tracks which RTU feed page we've loaded
        let isFetchingMore = false; // prevents double-clicks
        let noMorePages = false; // stops Load More when RTU runs out
        const PAGE_SIZE = 9;
        const MAX_FEED_PAGES = 5; // RTU has 5 pages

        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.querySelectorAll('.reveal-up').forEach(el => el.classList.add('active'));
            }, 100);

            loadUpdates();

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

            document.getElementById('loadMoreBtn').addEventListener('click', () => {
                // If we still have locally loaded posts to show, show them first
                if (visibleCount < filtered.length) {
                    visibleCount += PAGE_SIZE;
                    renderGrid();
                    return;
                }

                // Otherwise fetch the next RTU feed page
                if (isFetchingMore || noMorePages) return;

                const nextPage = currentFeedPage + 1;
                if (nextPage > MAX_FEED_PAGES) {
                    noMorePages = true;
                    document.getElementById('loadMoreWrap').style.display = 'none';
                    return;
                }

                fetchMoreFromRTU(nextPage);
            });

            document.getElementById('articleOverlay').addEventListener('click', function(e) {
                if (e.target === this) closeArticle();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeArticle();
            });
        });

        function loadResources() {
            loadUpdates();
        }

        function loadUpdates() {
            fetch('/backend/api/get_rtu_updates.php')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('skeletonGrid').style.display = 'none';
                    document.getElementById('updatesGrid').style.display = 'grid';

                    if (data.error || !Array.isArray(data) || data.length === 0) {
                        document.getElementById('updatesGrid').innerHTML = emptyState('No updates found.', 'fa-bullhorn');
                        return;
                    }

                    allPosts = data;
                    applyFilters();

                    const openUrl = new URLSearchParams(window.location.search).get('open');
                    if (openUrl) {
                        const target = allPosts.find(p => p.url === openUrl);
                        if (target) setTimeout(() => openArticle(target), 150);
                    }
                })
                .catch(() => {
                    document.getElementById('skeletonGrid').style.display = 'none';
                    document.getElementById('updatesGrid').innerHTML = emptyState('Connection error.', 'fa-triangle-exclamation');
                });
        }

        function fetchMoreFromRTU(page) {
            if (isFetchingMore) return;
            isFetchingMore = true;

            const btn = document.getElementById('loadMoreBtn');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Loading...';
            btn.disabled = true;

            // Send known IDs so backend can filter out duplicates
            const knownIds = allPosts.map(p => p.id).join(',');
            const url = `/backend/api/get_rtu_updates.php?page=${page}&known=${encodeURIComponent(knownIds)}`;

            fetch(url)
                .then(r => {
                    if (!r.ok) throw new Error('Network response was not ok');
                    return r.json();
                })
                .then(data => {
                    if (data.error || !Array.isArray(data.items)) {
                        throw new Error(data.error || 'Invalid response');
                    }

                    if (data.items.length > 0) {
                        // Merge new items into allPosts
                        const existingIds = new Set(allPosts.map(p => p.id));
                        const truly_new = data.items.filter(p => !existingIds.has(p.id));
                        allPosts = [...allPosts, ...truly_new];
                        currentFeedPage = page;
                        visibleCount += truly_new.length;
                        applyFilters();
                    }

                    // Check if there are more pages
                    if (!data.has_more || page >= MAX_FEED_PAGES) {
                        noMorePages = true;
                        document.getElementById('loadMoreWrap').style.display = 'none';
                    }
                })
                .catch(err => {
                    console.error('Load more error:', err);
                    // Don't crash — just show a subtle error and re-enable button
                    btn.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Try again';
                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                    }, 2000);
                })
                .finally(() => {
                    isFetchingMore = false;
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                });
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
            const loadMore = document.getElementById('loadMoreWrap');

            if (filtered.length === 0) {
                grid.innerHTML = emptyState('No matches found.', 'fa-magnifying-glass');
                loadMore.style.display = 'none';
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

            // Show Load More if there are more local posts OR more RTU pages
            loadMore.style.display = (filtered.length > visibleCount || (!noMorePages && currentFeedPage < MAX_FEED_PAGES)) ?
                'flex' : 'none';
        }

        function buildCard(post) {
            const catLabel = post.category === 'announcement' ? 'Announcement' : 'News';
            const catClass = post.category === 'announcement' ? 'announcement' : 'news';
            const thumb = post.thumbnail ?
                `<img src="${escHtml(post.thumbnail)}" alt="Thumb" loading="lazy" onerror="this.parentElement.innerHTML='<i class=\\'card-thumb-icon fa-solid fa-newspaper\\'></i>'">` :
                `<i class="card-thumb-icon fa-solid fa-newspaper"></i>`;

            return `
            <a class="update-card" href="${escHtml(post.url)}">
                <div class="card-thumb">${thumb}</div>
                <div class="card-body">
                    <div class="card-meta">
                        <span class="category-badge ${catClass}">${catLabel}</span>
                        <span class="card-date"><i class="fa-solid fa-calendar-days"></i> ${escHtml(post.date)}</span>
                    </div>
                    <div class="card-title-text">${escHtml(post.title)}</div>
                    <p class="card-excerpt">${escHtml(post.excerpt || '')}</p>
                    <div class="card-footer">Read more <i class="fa-solid fa-arrow-right"></i></div>
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