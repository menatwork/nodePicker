<div id="nodePicker_container" <?php echo (version_compare(VERSION, "2.11", ">=")) ? 'class="version-211" ' : '' ?>>
    <select class="tl_select" id="nodePicker" name="nodePicker" onchange="window.location=this.options[this.selectedIndex].value">
        <?php foreach ($this->arrNodes as $value): ?>
        <option value="<?php echo $value["value"];?>" <?php if ($value["selected"]): ?> selected="selected" <?php endif; ?>>
                <?php echo str_repeat("&nbsp;&nbsp;&nbsp;", $value["level"]); ?><?php echo $value["name"]; ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>  

<?php if (version_compare(VERSION, "2.11", ">=")): ?>
    <script>
        new Chosen($("nodePicker"));
    </script>
<?php endif; ?>