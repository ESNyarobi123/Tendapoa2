<?php $__env->startSection('content'); ?>
<h2>
  <?php echo e($job->title); ?>

  <span class="badge"><?php echo e($job->category->name); ?></span>
</h2>

<div id="map" style="height:280px;border-radius:18px;margin:8px 0"></div>

<div class="card">
  <p><b>Bei:</b> <?php echo e(number_format($job->price)); ?> TZS</p>
  <p><?php echo e($job->description); ?></p>
  <?php if(auth()->guard()->check()): ?>
    <?php if(auth()->user()->role==='muhitaji' && auth()->id()===$job->user_id && $job->status==='posted'): ?>
      <small>Chagua mfanyakazi kupitia maoni (comments) hapa chini.</small>
    <?php endif; ?>
  <?php endif; ?>
</div>

<div class="card">
  <form method="post" action="<?php echo e(route('jobs.comment',$job)); ?>">
    <?php echo csrf_field(); ?>
    <textarea name="message" placeholder="Andika maoni au omba kazi..." style="width:100%;height:90px"></textarea>
    <label><input type="checkbox" name="is_application" value="1"> Hii ni ombi la kufanya kazi</label>
    <input type="number" name="bid_amount" placeholder="Pendekeza bei (hiari)">
    <button class="btn btn-primary">Tuma</button>
  </form>
  <hr>
  <?php $__currentLoopData = $job->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="margin:8px 0">
      <b><?php echo e($c->user->name); ?></b>
      <?php if($c->is_application): ?><span class="badge">Ameomba kazi</span><?php endif; ?>
      <div><?php echo e($c->message); ?></div>
      <?php if($c->bid_amount): ?><small>Pendekezo: <?php echo e(number_format($c->bid_amount)); ?> TZS</small><?php endif; ?>

      <?php if(auth()->guard()->check()): ?>
        <?php if(auth()->user()->id===$job->user_id && $job->status==='posted' && $c->user->role==='mfanyakazi'): ?>
          <form method="post" action="<?php echo e(route('jobs.accept',[$job,$c])); ?>">
            <?php echo csrf_field(); ?>
            <button class="btn" style="background:var(--green);color:#fff">Mchague huyu</button>
          </form>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<script>
const map = L.map('map').setView([<?php echo e($job->lat); ?>, <?php echo e($job->lng); ?>], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(map);
const target = L.marker([<?php echo e($job->lat); ?>, <?php echo e($job->lng); ?>]).addTo(map);

<?php if(auth()->check() && auth()->user()->lat && auth()->user()->lng): ?>
  const me = L.marker([<?php echo e(auth()->user()->lat); ?>, <?php echo e(auth()->user()->lng); ?>]).addTo(map);

  function hav(a,b){
    const toR=x=>x*Math.PI/180;
    const [lat1,lon1]=a,[lat2,lon2]=b; const R=6371e3;
    const dLat=toR(lat2-lat1), dLon=toR(lon2-lon1);
    const s1=Math.sin(dLat/2), s2=Math.sin(dLon/2);
    const c=2*Math.atan2(Math.sqrt(s1*s1+Math.cos(toR(lat1))*Math.cos(toR(lat2))*s2*s2), Math.sqrt(1-(s1*s1+Math.cos(toR(lat1))*Math.cos(toR(lat2))*s2*s2)));
    return R*c;
  }
  const d = hav([<?php echo e(auth()->user()->lat); ?>, <?php echo e(auth()->user()->lng); ?>],[<?php echo e($job->lat); ?>, <?php echo e($job->lng); ?>]);
  const km = (d/1000).toFixed(1);
  const b = document.createElement('span'); b.className='badge'; b.textContent=`Umbali ~ ${km} km`;
  document.querySelector('h2').append(b);
<?php endif; ?>
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/feed/`show.blade.php ENDPATH**/ ?>