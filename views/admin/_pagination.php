<?php if (isset($pagination) && $pagination['total'] > 1): ?>
<div class="pagination">
    <span class="pagination-info">Showing page <?php echo $pagination['current']; ?> of <?php echo $pagination['total']; ?></span>
    
    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current'] - 1])); ?>" 
       class="pagination-btn <?php echo ($pagination['current'] <= 1) ? 'disabled' : ''; ?>"
       hx-get="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current'] - 1])); ?>"
       hx-target="#admin-content" hx-push-url="true">«</a>

    <?php 
    $start = max(1, $pagination['current'] - 2);
    $end = min($pagination['total'], $pagination['current'] + 2);
    
    if ($start > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" 
           class="pagination-btn"
           hx-get="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>"
           hx-target="#admin-content" hx-push-url="true">1</a>
        <?php if ($start > 2): ?><span style="color:var(--color-border)">...</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
           class="pagination-btn <?php echo ($i == $pagination['current']) ? 'active' : ''; ?>"
           hx-get="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
           hx-target="#admin-content" hx-push-url="true"><?php echo $i; ?></a>
    <?php endfor; ?>

    <?php if ($end < $pagination['total']): ?>
        <?php if ($end < $pagination['total'] - 1): ?><span style="color:var(--color-border)">...</span><?php endif; ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['total']])); ?>" 
           class="pagination-btn"
           hx-get="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['total']])); ?>"
           hx-target="#admin-content" hx-push-url="true"><?php echo $pagination['total']; ?></a>
    <?php endif; ?>

    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current'] + 1])); ?>" 
       class="pagination-btn <?php echo ($pagination['current'] >= $pagination['total']) ? 'disabled' : ''; ?>"
       hx-get="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current'] + 1])); ?>"
       hx-target="#admin-content" hx-push-url="true">»</a>
</div>
<?php endif; ?>
