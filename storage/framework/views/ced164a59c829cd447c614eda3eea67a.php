<?php
  use Illuminate\Support\Facades\Storage;
?>

<?php $__env->startSection('content'); ?>
<div class="flex min-h-screen bg-slate-50">
  <?php echo $__env->make('components.user-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <main class="tp-main w-full min-w-0 p-4 pt-16 sm:p-6 lg:pt-6">
    <div class="mx-auto max-w-6xl space-y-6">

      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div class="min-w-0 flex-1">
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Kazi zilizopo</h1>
            <p class="mt-1 max-w-xl text-[13px] leading-relaxed text-slate-500">
              Chagua kazi karibu nawe. Tumia chujio na ramani kuona maeneo.
            </p>
            <div class="mt-4 flex flex-wrap gap-3">
              <div class="inline-flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                <span class="text-lg leading-none">📋</span>
                <div>
                  <div class="text-[15px] font-bold text-slate-900"><?php echo e($jobs->total()); ?></div>
                  <div class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Jumla</div>
                </div>
              </div>
              <div class="inline-flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                <span class="text-lg leading-none">📍</span>
                <div>
                  <div class="text-[15px] font-bold text-brand-700"><?php echo e($jobs->where('distance_info.category', 'near')->count()); ?></div>
                  <div class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Karibu</div>
                </div>
              </div>
            </div>
          </div>
          <div class="flex shrink-0 gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1">
            <button type="button" id="list-view-btn" onclick="switchView('list')"
              class="feed-tab feed-tab-active rounded-lg px-4 py-2 text-[13px] font-semibold transition">Orodha</button>
            <button type="button" id="map-view-btn" onclick="switchView('map')"
              class="feed-tab rounded-lg px-4 py-2 text-[13px] font-semibold text-slate-600 transition">Ramani</button>
          </div>
        </div>

        <?php if(!auth()->user()->hasLocation()): ?>
          <div class="mt-5 flex gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[13px] text-amber-950">
            <span class="text-lg">⚠️</span>
            <div>
              <p class="font-semibold">Eneo halijawekwa</p>
              <p class="mt-0.5 text-amber-900/80">Weka eneo kwenye wasifu ili kuona umbali. <a href="<?php echo e(route('profile.edit')); ?>" class="font-semibold text-amber-900 underline">Hariri wasifu</a></p>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <form method="get" class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end">
          <div class="min-w-[180px] flex-1">
            <label for="category" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Aina</label>
            <select name="category" id="category" onchange="this.form.submit()"
              class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-[13px] text-slate-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
              <option value="">Zote</option>
              <?php $__currentLoopData = \App\Models\Category::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($c->slug); ?>" <?php echo e($cat == $c->slug ? 'selected' : ''); ?>><?php echo e($c->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="min-w-[180px] flex-1">
            <label for="distance" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Umbali</label>
            <select name="distance" id="distance" onchange="this.form.submit()"
              class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-[13px] text-slate-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
              <option value="">Wote</option>
              <option value="5" <?php echo e(request('distance') == '5' ? 'selected' : ''); ?>>≤ 5 km</option>
              <option value="10" <?php echo e(request('distance') == '10' ? 'selected' : ''); ?>>≤ 10 km</option>
              <option value="20" <?php echo e(request('distance') == '20' ? 'selected' : ''); ?>>≤ 20 km</option>
              <option value="50" <?php echo e(request('distance') == '50' ? 'selected' : ''); ?>>≤ 50 km</option>
            </select>
          </div>
        </form>
        <div class="mt-4 flex flex-wrap gap-2 text-[11px] text-slate-500">
          <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-50 px-2 py-1 font-medium text-emerald-800"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Karibu</span>
          <span class="inline-flex items-center gap-1.5 rounded-md bg-amber-50 px-2 py-1 font-medium text-amber-900"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Wastani</span>
          <span class="inline-flex items-center gap-1.5 rounded-md bg-red-50 px-2 py-1 font-medium text-red-800"><span class="h-2 w-2 rounded-full bg-red-500"></span> Mbali</span>
        </div>
      </div>

      <div id="map-container" class="hidden h-[65vh] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div id="map" class="h-full w-full"></div>
      </div>

      <div id="list-container">
        <?php if($jobs->count() > 0): ?>
          <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php
                $catClass = $job->distance_info['category'] ?? 'unknown';
                $strip = match($catClass) {
                  'near' => 'bg-emerald-500',
                  'moderate' => 'bg-amber-500',
                  'far' => 'bg-red-500',
                  default => 'bg-slate-300',
                };
                $imageUrl = null;
                if (isset($job->image_url) && $job->image_url) {
                    $imageUrl = $job->image_url;
                } elseif ($job->image) {
                    $filePath = storage_path('app/public/' . $job->image);
                    if (file_exists($filePath)) {
                        $imageUrl = asset('storage/' . $job->image) . '?v=' . filemtime($filePath);
                    }
                }
              ?>
              <article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-brand-200 hover:shadow-md">
                <div class="relative h-44 bg-slate-100">
                  <div class="absolute left-0 right-0 top-0 z-[1] h-1 <?php echo e($strip); ?>"></div>
                  <?php if($imageUrl): ?>
                    <img src="<?php echo e($imageUrl); ?>" alt="" class="h-full w-full object-cover"
                      onerror="this.style.display='none'; this.nextElementSibling?.classList.remove('hidden');">
                    <div class="hidden h-full w-full flex-col items-center justify-center bg-slate-100 text-slate-400">
                      <span class="text-3xl opacity-50">📷</span>
                      <span class="mt-1 text-[11px] font-medium">Hakuna picha</span>
                    </div>
                  <?php else: ?>
                    <div class="flex h-full w-full flex-col items-center justify-center text-slate-400">
                      <span class="text-3xl opacity-50">📷</span>
                      <span class="mt-1 text-[11px] font-medium">Hakuna picha</span>
                    </div>
                  <?php endif; ?>
                  <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-2 bg-gradient-to-t from-black/70 to-transparent px-3 pb-3 pt-10">
                    <span class="rounded-lg bg-brand-600 px-2.5 py-1 text-[13px] font-bold text-white shadow-sm"><?php echo e(number_format($job->price)); ?> <span class="text-[10px] font-semibold opacity-90">TZS</span></span>
                    <span class="rounded-lg bg-white/95 px-2 py-1 text-[11px] font-bold text-slate-800 shadow-sm backdrop-blur-sm">
                      <?php if($job->distance_info['distance'] ?? null): ?>
                        <?php echo e($job->distance_info['distance']); ?> km
                      <?php else: ?>
                        <?php echo e($job->distance_info['label'] ?? '—'); ?>

                      <?php endif; ?>
                    </span>
                  </div>
                </div>
                <div class="flex flex-1 flex-col p-4">
                  <div class="mb-2 flex flex-wrap gap-1.5">
                    <span class="rounded-md bg-brand-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-brand-800"><?php echo e($job->category->name); ?></span>
                    <?php if($job->poster_type === 'mfanyakazi'): ?>
                      <span class="rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-900">Mfanyakazi</span>
                    <?php else: ?>
                      <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-600">Muhitaji</span>
                    <?php endif; ?>
                  </div>
                  <h2 class="line-clamp-2 text-[15px] font-bold leading-snug text-slate-900"><?php echo e($job->title); ?></h2>
                  <p class="mt-2 line-clamp-2 flex-1 text-[12px] leading-relaxed text-slate-500">
                    <?php echo e($job->description ? Str::limit(strip_tags($job->description), 100) : 'Hakuna maelezo.'); ?>

                  </p>
                  <div class="mt-3 flex items-center justify-between gap-2 border-t border-slate-100 pt-3 text-[11px] text-slate-400">
                    <span><?php echo e($job->created_at->diffForHumans()); ?></span>
                    <a href="<?php echo e(route('jobs.show', $job)); ?>" class="inline-flex items-center gap-1 rounded-lg bg-brand-600 px-3 py-1.5 text-[12px] font-semibold text-white transition hover:bg-brand-700">Angalia</a>
                  </div>
                </div>
              </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
          <div class="mt-8 flex justify-center">
            <div class="text-[13px] text-slate-600 [&_.pagination]:flex [&_.pagination]:flex-wrap [&_.pagination]:justify-center [&_.pagination]:gap-1 [&_a]:rounded-lg [&_a]:border [&_a]:border-slate-200 [&_a]:px-3 [&_a]:py-1.5 [&_a]:font-medium [&_a]:text-slate-700 [&_a:hover]:bg-slate-50 [&_span]:rounded-lg [&_span]:bg-brand-50 [&_span]:px-3 [&_span]:py-1.5 [&_span]:font-semibold [&_span]:text-brand-800">
              <?php echo e($jobs->links()); ?>

            </div>
          </div>
        <?php else: ?>
          <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-16 text-center">
            <div class="text-4xl opacity-40">🔍</div>
            <p class="mt-3 text-[15px] font-semibold text-slate-800">Hakuna kazi</p>
            <p class="mt-1 text-[13px] text-slate-500">Jaribu aina nyingine au rudi baadaye.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<?php
  $user = auth()->user();
  $userLat = $user ? ($user->lat ?? null) : null;
  $userLng = $user ? ($user->lng ?? null) : null;
  $jobsArray = $jobs->items();
  $allJobsFromDb = \App\Models\Job::where('status', 'posted')->with('category')->get();
?>

<div id="feed-data" data-user-lat="<?php echo e($userLat ?? ''); ?>" data-user-lng="<?php echo e($userLng ?? ''); ?>"
  data-jobs="<?php echo e(json_encode($jobsArray)); ?>" data-all-jobs="<?php echo e(json_encode($allJobsFromDb)); ?>" class="hidden"></div>

<script>
  let map;
  let userLocationMarker = null;
  let jobMarkers = [];

  const feedDataEl = document.getElementById('feed-data');
  let userLat = feedDataEl?.dataset.userLat ? parseFloat(feedDataEl.dataset.userLat) : null;
  let userLng = feedDataEl?.dataset.userLng ? parseFloat(feedDataEl.dataset.userLng) : null;
  let allJobsData = [];
  if (feedDataEl) {
    try {
      allJobsData = JSON.parse(feedDataEl.dataset.allJobs || '[]');
    } catch (e) { console.error(e); }
  }

  function setToggleActive(listActive) {
    const listBtn = document.getElementById('list-view-btn');
    const mapBtn = document.getElementById('map-view-btn');
    const active = 'feed-tab-active bg-white text-brand-700 shadow-sm';
    const idle = 'text-slate-600';
    listBtn.className = 'feed-tab rounded-lg px-4 py-2 text-[13px] font-semibold transition ' + (listActive ? active : idle);
    mapBtn.className = 'feed-tab rounded-lg px-4 py-2 text-[13px] font-semibold transition ' + (!listActive ? active : idle);
  }

  function switchView(view) {
    const listContainer = document.getElementById('list-container');
    const mapContainer = document.getElementById('map-container');
    if (view === 'list') {
      listContainer.classList.remove('hidden');
      mapContainer.classList.add('hidden');
      setToggleActive(true);
    } else {
      listContainer.classList.add('hidden');
      mapContainer.classList.remove('hidden');
      setToggleActive(false);
      if (!map) initializeMap();
    }
  }

  function initializeMap() {
    if (typeof L === 'undefined') return;
    let centerLat = -6.7924, centerLng = 39.2083, zoom = 12;
    if (userLat && userLng) {
      centerLat = userLat;
      centerLng = userLng;
      zoom = 13;
    }
    map = L.map('map').setView([centerLat, centerLng], zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap'
    }).addTo(map);
    if (userLat && userLng) {
      userLocationMarker = L.marker([userLat, userLng], {
        icon: L.divIcon({
          className: 'user-location-marker',
          html: '<div style="background:#059669;width:22px;height:22px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.25);"></div>',
          iconSize: [22, 22],
          iconAnchor: [11, 11]
        })
      }).addTo(map);
      userLocationMarker.bindPopup('<strong>Eneo lako</strong>');
    }
    addJobMarkers();
  }

  function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
    return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
  }

  function addJobMarkers() {
    const jobs = Array.isArray(allJobsData) ? allJobsData : Object.values(allJobsData);
    jobs.forEach(job => {
      if (!job.lat || !job.lng || job.lat == 0 || job.lng == 0) return;
      let markerColor = '#64748b';
      let distanceLabel = 'Umbali haujulikani';
      if (userLat && userLng) {
        const d = calculateDistance(userLat, userLng, job.lat, job.lng);
        if (d <= 5) { markerColor = '#10b981'; distanceLabel = d.toFixed(1) + ' km — karibu'; }
        else if (d <= 10) { markerColor = '#f59e0b'; distanceLabel = d.toFixed(1) + ' km'; }
        else { markerColor = '#ef4444'; distanceLabel = d.toFixed(1) + ' km — mbali'; }
      }
      const marker = L.marker([job.lat, job.lng], {
        icon: L.divIcon({
          html: `<div style="background:${markerColor};width:28px;height:28px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.2);display:flex;align-items:center;justify-content:center;font-size:12px;">💼</div>`,
          iconSize: [28, 28],
          iconAnchor: [14, 14]
        })
      }).addTo(map);
      const price = new Intl.NumberFormat('sw-TZ').format(job.price);
      marker.bindPopup(`
        <div class="p-1" style="min-width:200px">
          <div class="font-bold text-slate-900 text-sm">${job.title}</div>
          <div class="text-emerald-700 font-bold text-sm mt-1">${price} TZS</div>
          <div class="text-xs text-slate-600 mt-1">${distanceLabel}</div>
          <a href="/jobs/${job.id}" class="mt-2 block rounded-lg bg-emerald-600 text-center text-xs font-semibold text-white py-2">Fungua</a>
        </div>
      `);
      jobMarkers.push(marker);
    });
  }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/feed/index.blade.php ENDPATH**/ ?>