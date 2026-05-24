<?php if (isset($filters)) extract($filters); ?>
<form class="filters" method="GET" action="<?php echo $filter_action ?? ''; ?>"
      data-admin-instant-search="1"
      hx-get="<?php echo htmlspecialchars($filter_action ?? ''); ?>"
      hx-target="#admin-content" hx-push-url="true">
    <input type="hidden" name="event" value="solarandstorage">
    <input type="hidden" name="page" value="1">
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;width:100%;margin-bottom:20px">
        <div style="position:relative; flex:1; min-width:200px">
            <input type="search" name="search" id="<?php echo $id_search ?? 'search-input'; ?>" value="<?php echo htmlspecialchars($search_query ?? ''); ?>" 
                   placeholder="<?php echo $search_placeholder ?? '🔍 Search...'; ?>"
                   autocomplete="off" spellcheck="false"
                   style="width:100%">
        </div>
        
        <?php if (!empty($filter_options)): ?>
            <?php foreach ($filter_options as $filter): ?>
                <select name="<?php echo $filter['name']; ?>" id="<?php echo $filter['id'] ?? ''; ?>">
                    <option value=""><?php echo $filter['label']; ?></option>
                    <?php foreach ($filter['options'] as $val => $label): ?>
                        <option value="<?php echo htmlspecialchars($val); ?>" <?php if (($filter['selected'] ?? '') == $val) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($search_query) || !empty($has_active_filters)): ?>
            <a href="<?php echo $filter_action; ?>" 
               hx-get="<?php echo $filter_action; ?>" 
               hx-target="#admin-content" 
               hx-push-url="true"
               style="font-size:12px;color:#888;text-decoration:none;margin-left:5px">Clear</a>
        <?php endif; ?>
    </div>
</form>
