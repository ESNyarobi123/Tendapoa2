<?php $__env->startSection('title', 'Admin - Chat Monitor'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin - Chat Monitor
        </h2>
        <p class="text-sm text-gray-600"><?php echo e($job->title); ?></p>
    </div>
    <a href="<?php echo e(route('admin.chats')); ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
        ← Back to All Chats
    </a>
</div>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <!-- Participants Info -->
            <div class="bg-white shadow-sm rounded-lg p-4 mb-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <h3 class="font-semibold text-gray-900">Muhitaji</h3>
                        <p class="text-gray-700"><?php echo e($job->muhitaji->name); ?></p>
                        <p class="text-xs text-gray-500"><?php echo e($job->muhitaji->email); ?></p>
                        <a href="<?php echo e(route('admin.user.details', $job->muhitaji)); ?>" class="text-xs text-blue-600 hover:underline">
                            View Profile →
                        </a>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg">
                        <h3 class="font-semibold text-gray-900">Mfanyakazi</h3>
                        <?php if($job->acceptedWorker): ?>
                            <p class="text-gray-700"><?php echo e($job->acceptedWorker->name); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($job->acceptedWorker->email); ?></p>
                            <a href="<?php echo e(route('admin.user.details', $job->acceptedWorker)); ?>" class="text-xs text-blue-600 hover:underline">
                                View Profile →
                            </a>
                        <?php else: ?>
                            <p class="text-gray-500 italic">Not assigned yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Conversation History (<?php echo e($messages->count()); ?> messages)</h3>
                    
                    <?php if($messages->isEmpty()): ?>
                        <div class="text-center py-12 text-gray-500">
                            No messages yet in this conversation.
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="border-l-4 <?php echo e($message->sender_id === $job->user_id ? 'border-blue-500 bg-blue-50' : 'border-green-500 bg-green-50'); ?> p-4 rounded">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="font-semibold text-gray-900"><?php echo e($message->sender->name); ?></span>
                                                <span class="text-xs px-2 py-1 rounded <?php echo e($message->sender_id === $job->user_id ? 'bg-blue-200 text-blue-800' : 'bg-green-200 text-green-800'); ?>">
                                                    <?php echo e($message->sender_id === $job->user_id ? 'Muhitaji' : 'Mfanyakazi'); ?>

                                                </span>
                                                <?php if($message->is_read): ?>
                                                    <span class="text-xs text-gray-500">✓✓ Read</span>
                                                <?php else: ?>
                                                    <span class="text-xs text-gray-400">✓ Sent</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-gray-700"><?php echo e($message->message); ?></p>
                                        </div>
                                        <div class="text-right text-xs text-gray-500">
                                            <div><?php echo e($message->created_at->format('M d, Y')); ?></div>
                                            <div><?php echo e($message->created_at->format('H:i')); ?></div>
                                            <?php if($message->is_read && $message->read_at): ?>
                                                <div class="text-green-600">Read: <?php echo e($message->read_at->format('H:i')); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/eunice/GITHUB COOP/Tendapoa2/resources/views/admin/chat-details.blade.php ENDPATH**/ ?>