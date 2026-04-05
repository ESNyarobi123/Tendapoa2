<?php $__env->startSection('title', 'Mazungumzo — ' . Str::limit($job->title, 40)); ?>

<?php $__env->startSection('content'); ?>
<?php
  $st = $job->status ?? '';
  $bannerClass = match ($st) {
    'assigned' => 'from-amber-50 to-orange-50/50 border-amber-100',
    'in_progress' => 'from-violet-50 to-purple-50/50 border-violet-100',
    'completed' => 'from-emerald-50 to-teal-50/50 border-emerald-100',
    default => 'from-slate-50 to-slate-100/80 border-slate-200',
  };
  $isMuhitajiChat = auth()->user()->id === $job->user_id;
  $workerThread = $isMuhitajiChat && request()->filled('worker_id');
?>

<div class="flex min-h-[100dvh] bg-slate-100/90">
  <?php echo $__env->make('components.user-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <main class="tp-main flex min-h-0 w-full min-w-0 flex-1 flex-col overflow-hidden pt-16 lg:pt-6">
    <div class="flex min-h-0 flex-1 flex-col px-4 pb-4 sm:px-6">
      <div class="mx-auto flex min-h-0 w-full max-w-6xl flex-1 flex-col">

        <?php if(session('success')): ?>
          <div class="mb-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] font-semibold text-emerald-900 shadow-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
          <div class="mb-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] font-semibold text-red-800 shadow-sm"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        
        <header class="flex shrink-0 items-center gap-3 rounded-t-2xl border border-b-0 border-slate-200/80 bg-white px-3 py-3 shadow-sm sm:gap-4 sm:px-4 sm:py-4">
          <a href="<?php echo e(route('chat.index')); ?>" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700" title="Rudi orodha">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          </a>
          <div class="flex min-w-0 flex-1 items-center gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-indigo-600 text-lg font-bold text-white shadow-md">
              <?php echo e(mb_substr($otherUser->name, 0, 1)); ?>

            </div>
            <div class="min-w-0">
              <h1 class="truncate text-[15px] font-bold text-slate-900 sm:text-base"><?php echo e($otherUser->name); ?></h1>
              <p class="mt-0.5 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                <span class="rounded-md px-2 py-0.5 font-semibold <?php echo e($otherUser->role === 'mfanyakazi' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800'); ?>">
                  <?php echo e($otherUser->role === 'mfanyakazi' ? 'Mfanyakazi' : 'Muhitaji'); ?>

                </span>
                <span class="truncate"><?php echo e($job->category?->name ?? ''); ?></span>
                <?php if($workerThread): ?>
                  <span class="rounded-md bg-violet-100 px-2 py-0.5 font-semibold text-violet-800">Mstari na mfanyakazi</span>
                <?php endif; ?>
              </p>
            </div>
          </div>
          <a href="<?php echo e(route('jobs.show', $job)); ?>" class="hidden shrink-0 rounded-xl bg-gradient-to-r from-brand-600 to-indigo-600 px-3 py-2 text-[12px] font-bold text-white shadow-md transition hover:from-brand-700 hover:to-indigo-700 sm:inline-flex sm:items-center sm:gap-1.5">
            <span>📋</span> Kazi
          </a>
        </header>

        <?php if($workerThread): ?>
          <div class="flex shrink-0 items-center gap-2 border-x border-violet-200/80 bg-gradient-to-r from-violet-50 to-indigo-50/40 px-3 py-2 text-[11px] text-violet-950 sm:px-4">
            <span class="font-bold">🔗</span>
            <span>Mazungumzo <strong>mahususi</strong> na <strong><?php echo e($otherUser->name); ?></strong> pekee kwenye kazi hii — kila mfanyakazi ana mstari wake.</span>
          </div>
        <?php endif; ?>

        
        <div class="flex shrink-0 flex-wrap items-center justify-between gap-2 border-x border-slate-200/80 bg-gradient-to-r px-3 py-2.5 sm:px-4 <?php echo e($bannerClass); ?>">
          <div class="min-w-0">
            <p class="truncate text-[12px] font-bold text-slate-800 sm:text-[13px]">📋 <?php echo e($job->title); ?></p>
            <p class="text-[11px] font-semibold tabular-nums text-slate-600"><?php echo e(number_format($job->price)); ?> TZS</p>
          </div>
          <span class="shrink-0 rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide ring-1 ring-slate-200/80 <?php echo e(match ($st) {
            'assigned' => 'bg-amber-100 text-amber-900',
            'in_progress' => 'bg-violet-100 text-violet-900',
            'completed' => 'bg-emerald-100 text-emerald-900',
            'pending_payment' => 'bg-rose-100 text-rose-900',
            default => 'bg-slate-100 text-slate-800',
          }); ?>">
            <?php switch($st):
              case ('assigned'): ?> Imekabidhiwa <?php break; ?>
              <?php case ('in_progress'): ?> Inaendelea <?php break; ?>
              <?php case ('completed'): ?> Imekamilika <?php break; ?>
              <?php case ('pending_payment'): ?> Malipo <?php break; ?>
              <?php default: ?> <?php echo e(ucfirst(str_replace('_', ' ', $st))); ?>

            <?php endswitch; ?>
          </span>
        </div>

        
        <div id="messages-container" class="min-h-0 flex-1 overflow-y-auto border-x border-slate-200/80 bg-slate-50/90 px-3 py-4 sm:px-4">
          <div class="messages-inner space-y-4">
            <?php if($messages->isEmpty()): ?>
              <div class="empty-state rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-12 text-center">
                <div class="text-4xl">👋</div>
                <p class="mt-3 text-[14px] font-bold text-slate-800">Anza mazungumzo</p>
                <p class="mx-auto mt-1 max-w-sm text-[12px] text-slate-600">Tuma ujumbe wa kwanza kwa <?php echo e($otherUser->name); ?>.</p>
              </div>
            <?php else: ?>
              <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                  $isSystemMessage = str_contains($message->message, '🎉 Hongera!') || str_contains($message->message, 'Umechaguliwa kufanya kazi');
                ?>
                <?php if($isSystemMessage): ?>
                  <div class="system-message flex justify-center">
                    <div class="system-message-content max-w-[95%] rounded-xl border border-amber-200/80 bg-amber-50 px-4 py-2.5 text-center text-[12px] font-medium text-amber-950 shadow-sm">
                      <p><?php echo e($message->message); ?></p>
                    </div>
                  </div>
                <?php else: ?>
                  <div class="message-wrapper flex <?php echo e($message->sender_id === auth()->id() ? 'sent justify-end' : 'received justify-start'); ?>">
                    <div class="message-content max-w-[min(85%,28rem)]">
                      <div class="message-bubble-wrapper flex gap-2 <?php echo e($message->sender_id === auth()->id() ? 'flex-row-reverse' : 'flex-row'); ?>">
                        <div class="user-avatar flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-[11px] font-bold text-slate-700">
                          <?php echo e(mb_substr($message->sender->name, 0, 1)); ?>

                        </div>
                        <div class="min-w-0">
                          <div class="message-bubble rounded-2xl px-3.5 py-2.5 shadow-sm <?php echo e($message->sender_id === auth()->id() ? 'rounded-br-md bg-gradient-to-br from-brand-600 to-brand-700 text-white' : 'rounded-bl-md border border-slate-200 bg-white text-slate-800'); ?>">
                            <p class="message-text whitespace-pre-wrap break-words text-[13px] leading-relaxed"><?php echo e($message->message); ?></p>
                          </div>
                          <div class="message-meta mt-1 flex items-center gap-2 text-[10px] font-medium <?php echo e($message->sender_id === auth()->id() ? 'justify-end text-brand-600/90' : 'text-slate-400'); ?>">
                            <span><?php echo e($message->created_at->format('H:i')); ?></span>
                            <?php if($message->sender_id === auth()->id()): ?>
                              <?php if($message->is_read): ?>
                                <span class="read-status text-emerald-600">✓✓</span>
                              <?php else: ?>
                                <span>✓</span>
                              <?php endif; ?>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
          </div>
        </div>

        
        <div class="message-input-box shrink-0 rounded-b-2xl border border-t-0 border-slate-200/80 bg-white p-3 shadow-sm sm:p-4">
          <form action="<?php echo e(route('chat.send', $job)); ?>" method="POST" id="message-form" class="flex items-end gap-2 sm:gap-3">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="receiver_id" value="<?php echo e($otherUser->id); ?>">
            <div class="input-wrapper min-w-0 flex-1">
              <label for="message-input" class="sr-only">Ujumbe</label>
              <textarea name="message" id="message-input" rows="2" required placeholder="Andika ujumbe… (Enter kutuma, Shift+Enter mstari mpya)"
                class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50/80 px-3.5 py-3 text-[13px] text-slate-900 placeholder:text-slate-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20"></textarea>
            </div>
            <button type="submit" class="send-btn flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-indigo-600 text-white shadow-md transition hover:from-brand-700 hover:to-indigo-700 sm:h-12 sm:w-12" title="Tuma">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </button>
          </form>
        </div>

        <a href="<?php echo e(route('jobs.show', $job)); ?>" class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white py-2.5 text-[12px] font-bold text-slate-700 shadow-sm sm:hidden">
          📋 Angalia kazi
        </a>

      </div>
    </div>
  </main>
</div>

<script>
(function () {
  var container = document.getElementById('messages-container');
  if (container) {
    container.scrollTop = container.scrollHeight;
  }

  var form = document.getElementById('message-form');
  var input = document.getElementById('message-input');

  function escapeHtml(text) {
    var d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
  }

  if (form && input && container) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var messageText = input.value.trim();
      if (!messageText) return;

      var formData = new FormData(form);
      fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        }
      })
        .then(function (r) {
          if (!r.ok) {
            return r.text().then(function (t) { throw new Error(t || r.status); });
          }
          return r.json();
        })
        .then(function (data) {
          if (data.success && data.message) {
            var messagesInner = container.querySelector('.messages-inner');
            var emptyState = messagesInner.querySelector('.empty-state');
            if (emptyState) emptyState.remove();

            var s = data.message.sender;
            var initial = (s && s.name) ? s.name.charAt(0) : '?';
            var safeMsg = escapeHtml(data.message.message);
            var messageHtml =
              '<div class="message-wrapper flex sent justify-end">' +
                '<div class="message-content max-w-[min(85%,28rem)]">' +
                  '<div class="message-bubble-wrapper flex gap-2 flex-row-reverse">' +
                    '<div class="user-avatar flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-[11px] font-bold text-slate-700">' + escapeHtml(initial) + '</div>' +
                    '<div class="min-w-0">' +
                      '<div class="message-bubble rounded-2xl rounded-br-md px-3.5 py-2.5 shadow-sm bg-gradient-to-br from-brand-600 to-brand-700 text-white">' +
                        '<p class="message-text whitespace-pre-wrap break-words text-[13px] leading-relaxed">' + safeMsg + '</p>' +
                      '</div>' +
                      '<div class="message-meta mt-1 flex items-center justify-end gap-2 text-[10px] font-medium text-brand-600/90">' +
                        '<span>Sasa</span><span>✓</span>' +
                      '</div>' +
                    '</div>' +
                  '</div>' +
                '</div>' +
              '</div>';

            messagesInner.insertAdjacentHTML('beforeend', messageHtml);
            container.scrollTop = container.scrollHeight;
            input.value = '';
            lastMessageId = data.message.id;
          }
        })
        .catch(function (err) { console.error(err); });
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        form.dispatchEvent(new Event('submit'));
      }
    });
  }

  var lastMessageId = <?php echo e($messages->last()?->id ?? 0); ?>;
  var otherUserId = <?php echo e($otherUser->id); ?>;
  var currentUserId = <?php echo e(auth()->id()); ?>;

  setInterval(function () {
    if (!container) return;
    fetch('<?php echo e(route("chat.poll", $job)); ?>?last_id=' + lastMessageId + '&other_user_id=' + otherUserId)
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.count > 0 && data.messages) {
          var messagesInner = container.querySelector('.messages-inner');
          var emptyState = messagesInner.querySelector('.empty-state');
          if (emptyState) emptyState.remove();

          data.messages.forEach(function (message) {
            var isFromCurrentUser = message.sender_id == currentUserId;
            if (!isFromCurrentUser) {
              var isSystemMessage = (message.message || '').includes('🎉 Hongera!') || (message.message || '').includes('Umechaguliwa kufanya kazi');
              var messageHtml;
              if (isSystemMessage) {
                messageHtml =
                  '<div class="system-message flex justify-center">' +
                    '<div class="system-message-content max-w-[95%] rounded-xl border border-amber-200/80 bg-amber-50 px-4 py-2.5 text-center text-[12px] font-medium text-amber-950 shadow-sm">' +
                      '<p>' + escapeHtml(message.message) + '</p>' +
                    '</div>' +
                  '</div>';
              } else {
                var senderName = (message.sender && message.sender.name) ? message.sender.name : '';
                var ini = senderName ? senderName.charAt(0) : '?';
                messageHtml =
                  '<div class="message-wrapper flex received justify-start">' +
                    '<div class="message-content max-w-[min(85%,28rem)]">' +
                      '<div class="message-bubble-wrapper flex gap-2 flex-row">' +
                        '<div class="user-avatar flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-[11px] font-bold text-slate-700">' + escapeHtml(ini) + '</div>' +
                        '<div class="min-w-0">' +
                          '<div class="message-bubble rounded-2xl rounded-bl-md border border-slate-200 bg-white px-3.5 py-2.5 text-slate-800 shadow-sm">' +
                            '<p class="message-text whitespace-pre-wrap break-words text-[13px] leading-relaxed">' + escapeHtml(message.message) + '</p>' +
                          '</div>' +
                          '<div class="message-meta mt-1 flex items-center gap-2 text-[10px] font-medium text-slate-400">' +
                            '<span>Sasa</span>' +
                          '</div>' +
                        '</div>' +
                      '</div>' +
                    '</div>' +
                  '</div>';
              }
              messagesInner.insertAdjacentHTML('beforeend', messageHtml);
            }
            lastMessageId = message.id;
          });
          container.scrollTop = container.scrollHeight;
        }
      })
      .catch(function (err) { console.error(err); });
  }, 3000);
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/chat/show.blade.php ENDPATH**/ ?>