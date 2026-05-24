<?php if (isset($header)) extract($header); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
        <h2 style="font-size:22px;font-weight:700"><?php echo $title; ?></h2>
        <?php if (!empty($subtitle)): ?>
            <div style="font-size:13px;color:#888;margin-top:4px;"><?php echo $subtitle; ?></div>
        <?php endif; ?>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
        <?php if (!empty($actions)): ?>
            <?php foreach ($actions as $action): ?>
                <?php if (($action['type'] ?? 'link') === 'button'): ?>
                    <button class="btn <?php echo $action['class'] ?? 'btn-primary'; ?>" 
                            <?php if (!empty($action['id'])) echo 'id="'.$action['id'].'"'; ?>
                            <?php if (!empty($action['onclick'])) echo 'onclick="'.$action['onclick'].'"'; ?>
                            <?php if (!empty($action['form'])) echo 'form="'.$action['form'].'"'; ?>
                            <?php if (!empty($action['type_attr'])) echo 'type="'.$action['type_attr'].'"'; ?>>
                        <?php echo $action['label']; ?>
                    </button>
                <?php else: ?>
                    <a href="<?php echo $action['url']; ?>" 
                       class="btn <?php echo $action['class'] ?? 'btn-outline'; ?>"
                       <?php if (isset($action['hx_boost'])) echo 'hx-boost="'.($action['hx_boost']?'true':'false').'"'; ?>>
                        <?php echo $action['label']; ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
