<?php if($paginator->hasPages()): ?>
    <?php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $maxTabs = 10;
        $blockStart = (int) (floor(($currentPage - 1) / $maxTabs) * $maxTabs) + 1;
        $blockEnd = min($blockStart + $maxTabs - 1, $lastPage);
    ?>

    <nav aria-label="Pagination">
        <ul class="pagination pagination-limited mb-0">
            <li class="page-item <?php echo e($paginator->onFirstPage() ? 'disabled' : ''); ?>">
                <?php if($paginator->onFirstPage()): ?>
                    <span class="page-link">&laquo; Previous</span>
                <?php else: ?>
                    <a class="page-link" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">&laquo; Previous</a>
                <?php endif; ?>
            </li>

            <?php if($blockStart > 1): ?>
                <li class="page-item disabled" aria-hidden="true">
                    <span class="page-link">…</span>
                </li>
            <?php endif; ?>

            <?php for($page = $blockStart; $page <= $blockEnd; $page++): ?>
                <?php if($page == $currentPage): ?>
                    <li class="page-item active" aria-current="page">
                        <span class="page-link"><?php echo e($page); ?></span>
                    </li>
                <?php else: ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo e($paginator->url($page)); ?>"><?php echo e($page); ?></a>
                    </li>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if($blockEnd < $lastPage): ?>
                <li class="page-item disabled" aria-hidden="true">
                    <span class="page-link">…</span>
                </li>
            <?php endif; ?>

            <li class="page-item <?php echo e($paginator->hasMorePages() ? '' : 'disabled'); ?>">
                <?php if($paginator->hasMorePages()): ?>
                    <a class="page-link" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">Next &raquo;</a>
                <?php else: ?>
                    <span class="page-link">Next &raquo;</span>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
<?php endif; ?>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\pagination\bootstrap-limited.blade.php ENDPATH**/ ?>